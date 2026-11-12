<?php
// Read-only domain rules, compatible with PHP 7.3 and 8.x.
function bt_h($value) { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function bt_money($value) { return 'R$ ' . number_format($value / 100, 2, ',', '.'); }
function bt_cents($value) {
    $value = trim((string)$value);
    if (strpos($value, ',') !== false) $value = str_replace(',', '.', str_replace('.', '', $value));
    return (int)round((float)$value * 100);
}
function bt_state($row, $today) {
    $state = strtolower(trim((string)$row['status']));
    if ($state === 'pago') return 'pago';
    if (!in_array($state, array('aberto','vencido'), true)) return 'outro';
    return !empty($row['datavenc']) && substr($row['datavenc'], 0, 10) < $today ? 'vencido' : 'receber';
}
function bt_bank($row) {
    foreach (array('linhadig','codigo_barras') as $field) {
        $digits = preg_replace('/\D/', '', isset($row[$field]) ? (string)$row[$field] : '');
        if (in_array(strlen($digits), array(44,47), true) && substr($digits,0,3) !== '999') return substr($digits,0,3);
    }
    if (strtolower(trim((string)$row['nossonum'])) === 'gerencianet' || !empty($row['chave_gnet']) || !empty($row['chave_gnet2'])) return 'efi';
    return 'indefinido';
}
function bt_bank_name($code) {
    $names = array('001'=>'Banco do Brasil', '033'=>'Santander', '104'=>'Caixa', '237'=>'Bradesco', '341'=>'Itaú', '364'=>'Efí / Gerencianet', '756'=>'Sicoob', '748'=>'Sicredi', 'efi'=>'Efí / Gerencianet (integração)', 'indefinido'=>'Banco não identificado');
    return isset($names[$code]) ? $names[$code] : 'Banco ' . $code;
}
function bt_issues($row, $accounts, $lastError = '') {
    if (in_array(strtolower((string)$row['status']), array('pago','cancelado','cancelada'), true)) return array();
    $issues = array(); $number = trim((string)$row['nossonum']);
    $failed = preg_match('/^(error|erro|falha)$/i', $number);
    if ($failed) $issues['emissao'] = 'Falha de emissão marcada no título. O banco não informou o motivo neste campo.';
    $hasDocument = $number !== '' && !$failed;
    if (!$hasDocument) {
        $account = isset($row['conta_atual']) ? (int)$row['conta_atual'] : 0;
        if (!$account || !isset($accounts[$account])) $issues['conta'] = 'Cliente sem conta bancária válida no cadastro. Selecione a carteira correta no cadastro do cliente.';
        if (empty($row['email'])) $issues['email'] = 'Cadastro sem e-mail. Informe um e-mail válido e confira as exigências da integração.';
        elseif (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) $issues['email'] = 'E-mail do cadastro inválido. Corrija antes de tentar emitir.';
        if (empty($row['cpf_cnpj'])) $issues['documento'] = 'CPF/CNPJ não preenchido no cadastro. Confira os dados do pagador.';
        if (!$issues) $issues['pendente'] = 'Sem nosso número: registro/emissão ainda não confirmado. Não significa rejeição pelo banco.';
        if ($lastError !== '') $issues['retorno'] = 'Último retorno Efí vinculado à chave do título: ' . $lastError;
    }
    return $issues;
}
function bt_date_valid($value) {
    if ($value === '') return true;
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
}
function bt_csv_cell($value) {
    $value = (string)$value;
    if (preg_match('/^[\s]*[=+@\-]/u', $value)) $value = "'" . $value;
    return '"' . str_replace('"', '""', $value) . '"';
}
function bt_fetch($link, $sql) {
    $result = mysqli_query($link, $sql);
    if (!$result) throw new RuntimeException('Não foi possível consultar os títulos.');
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
function bt_load($link) {
    $accounts = array();
    foreach (bt_fetch($link, 'SELECT id,nome,banco,carteira,delconta FROM sis_boleto') as $row) $accounts[(int)$row['id']] = $row;
    $rows = bt_fetch($link, "SELECT l.id,l.login,l.status,l.datavenc,l.datapag,l.nossonum,l.linhadig,l.codigo_barras,l.numconta,l.chave_gnet,l.chave_gnet2,l.valor,l.valorpag,c.nome AS cliente_nome,c.uuid_cliente,c.conta AS conta_atual,c.email,c.cpf_cnpj FROM sis_lanc l LEFT JOIN sis_cliente c ON c.login=l.login WHERE COALESCE(l.deltitulo,0)=0 ORDER BY l.datavenc DESC,l.id DESC");
    $errors = array();
    $table = mysqli_query($link, "SHOW TABLES LIKE 'sis_gneterros'");
    if ($table && mysqli_num_rows($table)) {
        // Errors use gateway IDs, not local title IDs. Never join them by l.id.
        foreach (bt_fetch($link, 'SELECT e.titulo,e.mensagem FROM sis_gneterros e INNER JOIN (SELECT titulo,MAX(id) id FROM sis_gneterros GROUP BY titulo) newest ON e.id=newest.id') as $row) {
            $message = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(array('<br>','<br />','<br/>'), ' ', (string)$row['mensagem']))));
            // Do not expose gateway credentials or full payloads. Only short plain messages.
            if (preg_match('/token|secret|authorization|password|bearer|client_id/i', $message)) $message = 'Retorno contém dados de autenticação; consultar log restrito no MK-Auth.';
            $errors[(string)$row['titulo']] = substr($message, 0, 600);
        }
    }
    $today = date('Y-m-d');
    foreach ($rows as &$row) {
        $row['_state'] = bt_state($row, $today); $row['_bank'] = bt_bank($row);
        $account = (int)$row['numconta'];
        $row['_wallet'] = (string)$account;
        $row['_wallet_name'] = isset($accounts[$account]) ? $accounts[$account]['nome'] . ' (#' . $account . ')' : ($account ? 'Carteira removida #' . $account : 'Sem carteira no título');
        $row['_current'] = isset($accounts[(int)$row['conta_atual']]) ? $accounts[(int)$row['conta_atual']]['nome'] : 'Não definida';
        $last = '';
        foreach (array('chave_gnet2','chave_gnet') as $key) if (!empty($row[$key]) && isset($errors[(string)$row[$key]])) { $last = $errors[(string)$row[$key]]; break; }
        $row['_issues'] = bt_issues($row, $accounts, $last);
    }
    unset($row);
    return array($accounts,$rows);
}
