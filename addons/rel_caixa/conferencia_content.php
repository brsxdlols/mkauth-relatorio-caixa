<?php
// Conferência somente leitura. Os resumos usam o período inteiro, sem o filtro de cliente.
function rc_escape($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function rc_cents($value) {
    $value = trim((string)$value);
    if (strpos($value, ',') !== false) $value = str_replace(',', '.', str_replace('.', '', $value));
    return (int)round((float)$value * 100);
}
function rc_money($cents) { return 'R$ ' . number_format($cents / 100, 2, ',', '.'); }
function rc_title_id($history) {
    return preg_match('/t[ií]tulo(?:[^0-9]{0,30})(\d+)/iu', (string)$history, $m) ? (int)$m[1] : 0;
}
function rc_query($link, $sql) {
    $result = mysqli_query($link, $sql);
    if (!$result) throw new RuntimeException('Não foi possível consultar a conferência.');
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
function rc_date($value) {
    $date = DateTime::createFromFormat('!Y-m-d', (string)$value);
    return $date && $date->format('Y-m-d') === $value ? $date : new DateTime('today');
}
$rc_start = rc_date(isset($_GET['data_inicial']) ? $_GET['data_inicial'] : date('Y-m-d'));
$rc_end = rc_date(isset($_GET['data_final']) ? $_GET['data_final'] : date('Y-m-d'));
$rc_label = $rc_start->format('d/m/Y') . ' a ' . $rc_end->format('d/m/Y');
$rc_groups = array('Dia' => array(), 'Usuário' => array(), 'Forma de pagamento' => array());
$rc_alerts = array();
$rc_error = false;
try {
    if ($rc_start > $rc_end) throw new RuntimeException('Período inválido.');
    $rc_from = $rc_start->format('Y-m-d');
    $rc_until = (clone $rc_end)->modify('+1 day')->format('Y-m-d');
    $rc_rows = rc_query($link, "SELECT id,data,usuario,historico,entrada,saida FROM sis_caixa WHERE data >= '$rc_from' AND data < '$rc_until' ORDER BY data,id");
    $rc_ids = array();
    $rc_receipts = array();
    foreach ($rc_rows as $rc_row) {
        $rc_id = rc_title_id($rc_row['historico']);
        if ($rc_id) $rc_ids[$rc_id] = $rc_id;
        if ($rc_id && rc_cents($rc_row['entrada']) > 0) $rc_receipts[$rc_id][] = $rc_row;
    }
    $rc_titles = array();
    foreach (array_chunk(array_values($rc_ids), 500) as $rc_chunk) {
        foreach (rc_query($link, 'SELECT id,login,valorpag,formapag FROM sis_lanc WHERE id IN (' . implode(',', $rc_chunk) . ')') as $rc_title) $rc_titles[$rc_title['id']] = $rc_title;
    }
    foreach ($rc_rows as $rc_row) {
        $rc_id = rc_title_id($rc_row['historico']);
        $rc_method = isset($rc_titles[$rc_id]) ? trim((string)$rc_titles[$rc_id]['formapag']) : '';
        if ($rc_method === '') $rc_method = $rc_id ? 'Não informada no título' : 'Sem título vinculado';
        foreach (array('Dia' => substr($rc_row['data'], 0, 10), 'Usuário' => $rc_row['usuario'] ?: 'Não informado', 'Forma de pagamento' => $rc_method) as $rc_dimension => $rc_key) {
            if (!isset($rc_groups[$rc_dimension][$rc_key])) $rc_groups[$rc_dimension][$rc_key] = array(0,0,0);
            $rc_groups[$rc_dimension][$rc_key][0]++;
            $rc_groups[$rc_dimension][$rc_key][1] += rc_cents($rc_row['entrada']);
            $rc_groups[$rc_dimension][$rc_key][2] += rc_cents($rc_row['saida']);
        }
    }
    foreach ($rc_receipts as $rc_id => $rc_entries) {
        $rc_sum = 0;
        foreach ($rc_entries as $rc_entry) $rc_sum += rc_cents($rc_entry['entrada']);
        $rc_login = isset($rc_titles[$rc_id]) ? $rc_titles[$rc_id]['login'] : '';
        if (count($rc_entries) > 1) $rc_alerts[] = array('Possível duplicidade / recebimento parcial', $rc_id, $rc_login, count($rc_entries) . ' entradas no período; conferir antes de corrigir.', rc_money($rc_sum));
        if (isset($rc_titles[$rc_id]) && rc_cents($rc_titles[$rc_id]['valorpag']) > 0 && abs($rc_sum - rc_cents($rc_titles[$rc_id]['valorpag'])) > 1) {
            $rc_alerts[] = array('Valor a conferir', $rc_id, $rc_login, 'Caixa no período: ' . rc_money($rc_sum) . '; valor pago no título: ' . rc_money(rc_cents($rc_titles[$rc_id]['valorpag'])) . '. Pode envolver recebimentos em outras datas.', rc_money($rc_sum - rc_cents($rc_titles[$rc_id]['valorpag'])));
        }
    }
    // Procura vínculo em todo o caixa para não acusar um lançamento feito em outra data.
    $rc_missing = rc_query($link, "SELECT l.id,l.login,l.valorpag FROM sis_lanc l WHERE l.status = 'pago' AND l.deltitulo = 0 AND l.datapag >= '$rc_from' AND l.datapag < '$rc_until' AND NOT EXISTS (SELECT 1 FROM sis_caixa c WHERE c.entrada > 0 AND CONVERT(c.historico USING utf8mb4) COLLATE utf8mb4_unicode_ci REGEXP CONVERT(CONCAT('t[ií]tulo[^0-9]{0,30}',l.id,'([^0-9]|$)') USING utf8mb4) COLLATE utf8mb4_unicode_ci)");
    foreach ($rc_missing as $rc_title) $rc_alerts[] = array('Pago sem entrada identificada', $rc_title['id'], $rc_title['login'], 'Nenhuma entrada vinculada pelo ID no histórico. Conferir lançamentos manuais e formato do histórico.', rc_money(rc_cents($rc_title['valorpag'])));
} catch (Throwable $rc_exception) {
    $rc_error = true;
    error_log('Rel Caixa conferência: ' . $rc_exception->getMessage());
}
?>
<section class="rc-conference">
    <div class="rc-status-line"><span class="rc-status-dot"></span><strong><?php echo $rc_error ? "Conferência indisponível" : (count($rc_alerts) ? count($rc_alerts) . " itens para revisar" : "Nenhum alerta encontrado"); ?></strong><span><?php echo rc_escape($rc_label); ?></span><span class="rc-scope">Todos os clientes do período</span></div>
    <details class="rc-analysis"><summary>Ver conferência e resumos <span>Por dia, usuário e pagamento</span></summary>

    <p><?php echo rc_escape($rc_label); ?> · Resumos e alertas consideram todos os clientes do período.</p>
    <p>Resultado do período = entradas − saídas. Não inclui saldo anterior nem representa saldo bancário.</p>
    <?php if ($rc_error): ?>
        <p role="alert">Não foi possível calcular a conferência. Verifique o período e tente novamente. Nenhum resultado foi considerado conferido.</p>
    <?php else: ?>
        <details><summary>Itens para conferir: <?php echo count($rc_alerts); ?></summary>
        <p>Alertas são indícios para revisão. A identificação depende do ID do título no histórico; compare também com o extrato bancário.</p>
        <?php if (!$rc_alerts): ?><p>Nenhuma divergência encontrada nas verificações disponíveis.</p><?php else: ?>
        <div class="table-wrapper"><table><thead><tr><th>Alerta</th><th>Título</th><th>Login</th><th>Detalhe</th><th>Valor / diferença</th></tr></thead><tbody>
        <?php foreach ($rc_alerts as $rc_alert): ?><tr><?php foreach ($rc_alert as $rc_cell): ?><td><?php echo rc_escape($rc_cell); ?></td><?php endforeach; ?></tr><?php endforeach; ?>
        </tbody></table></div><?php endif; ?></details>
        <div class="rc-summary-grid">
        <?php foreach ($rc_groups as $rc_dimension => $rc_items): ?>
        <details><summary>Resumo por <?php echo rc_escape($rc_dimension); ?></summary>
        <div class="table-wrapper"><table><thead><tr><th><?php echo rc_escape($rc_dimension); ?></th><th>Lanç.</th><th>Entradas</th><th>Saídas</th><th>Resultado</th></tr></thead><tbody>
        <?php foreach ($rc_items as $rc_key => $rc_values): ?><tr><td><?php echo rc_escape($rc_dimension === 'Dia' ? date('d/m/Y', strtotime($rc_key)) : $rc_key); ?></td><td><?php echo $rc_values[0]; ?></td><td><?php echo rc_money($rc_values[1]); ?></td><td><?php echo rc_money($rc_values[2]); ?></td><td><?php echo rc_money($rc_values[1] - $rc_values[2]); ?></td></tr><?php endforeach; ?>
        <?php if (!$rc_items): ?><tr><td colspan="5">Nenhum lançamento no período.</td></tr><?php endif; ?>
        </tbody></table></div></details><?php endforeach; ?>
        </div>
        <p>A forma de pagamento vem do título vinculado; saídas vinculadas são agrupadas na mesma forma.</p>
    <?php endif; ?>
    </details>
</section>
