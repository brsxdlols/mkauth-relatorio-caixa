<?php
if (empty($acesso_permitido)) { http_response_code(403); exit('Acesso não permitido.'); }
require_once __DIR__ . '/titulos_lib.php';
$bt_mode = isset($_GET['tab']) && $_GET['tab'] === 'problemas' ? 'problemas' : 'carteiras';
$bt_filters = array();
foreach (array('carteira','banco','situacao','inicio','fim','base','busca','motivo') as $key) $bt_filters[$key] = isset($_GET['bt_'.$key]) && is_string($_GET['bt_'.$key]) ? trim($_GET['bt_'.$key]) : '';
if ($bt_filters['situacao'] === '') $bt_filters['situacao'] = 'pendentes';
$bt_problem = ''; $bt_rows = array(); $bt_all = array(); $bt_accounts = array();
try {
    if (!bt_date_valid($bt_filters['inicio']) || !bt_date_valid($bt_filters['fim']) || ($bt_filters['inicio'] && $bt_filters['fim'] && $bt_filters['inicio'] > $bt_filters['fim'])) throw new RuntimeException('Informe um intervalo de datas válido.');
    list($bt_accounts,$bt_all) = bt_load($link);
    foreach ($bt_all as $bt_row) {
        if ($bt_filters['carteira'] !== '' && $bt_row['_wallet'] !== $bt_filters['carteira']) continue;
        if ($bt_filters['banco'] !== '' && $bt_row['_bank'] !== $bt_filters['banco']) continue;
        $state = $bt_filters['situacao'];
        if ($state === 'pendentes' && !in_array($bt_row['_state'], array('receber','vencido'), true)) continue;
        if (in_array($state, array('receber','vencido','pago','outro'), true) && $bt_row['_state'] !== $state) continue;
        $date = substr((string)$bt_row[$bt_filters['base'] === 'pagamento' ? 'datapag' : 'datavenc'],0,10);
        if (($bt_filters['inicio'] || $bt_filters['fim']) && (!$date || $date === '0000-00-00')) continue;
        if ($bt_filters['inicio'] && $date < $bt_filters['inicio']) continue;
        if ($bt_filters['fim'] && $date > $bt_filters['fim']) continue;
        if ($bt_filters['busca'] !== '' && stripos($bt_row['id'].' '.$bt_row['cliente_nome'].' '.$bt_row['login'], $bt_filters['busca']) === false) continue;
        if ($bt_mode === 'problemas' && !$bt_row['_issues']) continue;
        if ($bt_mode === 'problemas' && $bt_filters['motivo'] !== '' && !isset($bt_row['_issues'][$bt_filters['motivo']])) continue;
        $bt_rows[] = $bt_row;
    }
} catch (Throwable $bt_error) { $bt_problem = $bt_error->getMessage(); error_log('Caixa títulos: '.$bt_error->getMessage()); }
$bt_totals = array('receber'=>array(0,0),'vencido'=>array(0,0),'pago'=>array(0,0),'outro'=>array(0,0));
$bt_pending = array(); $bt_global = array();
foreach ($bt_all as $bt_row) {
    if ($bt_row['_wallet'] !== $bt_filters['carteira'] && $bt_filters['carteira'] !== '') continue;
    if ($bt_filters['banco'] !== '' && $bt_row['_bank'] !== $bt_filters['banco']) continue;
    if (!in_array($bt_row['_state'], array('receber','vencido'), true)) continue;
    $bt_pending[] = $bt_row;
    $key = $bt_row['_wallet'];
    if (!isset($bt_global[$key])) $bt_global[$key] = array($bt_row['_wallet_name'],0,0,'');
    $bt_global[$key][1]++; $bt_global[$key][2] += bt_cents($bt_row['valor']);
    if ($bt_row['datavenc'] > $bt_global[$key][3]) $bt_global[$key][3] = $bt_row['datavenc'];
}
foreach ($bt_rows as $bt_row) { $key=$bt_row['_state']; $bt_totals[$key][0]++; $bt_totals[$key][1]+=bt_cents($bt_row[$key === 'pago' ? 'valorpag' : 'valor']); }
$bt_page = max(1, isset($_GET['bt_pagina']) ? (int)$_GET['bt_pagina'] : 1);
$bt_pages = max(1,(int)ceil(count($bt_rows)/50)); $bt_page=min($bt_page,$bt_pages);
$bt_display = array_slice($bt_rows,($bt_page-1)*50,50);
$bt_labels = array('pendentes'=>'Pendentes (a receber + vencidos)','todos'=>'Todas','receber'=>'A receber','vencido'=>'Vencidos','pago'=>'Pagos','outro'=>'Outras situações');
$bt_wallet_options = array(); $bt_bank_options = array();
foreach ($bt_all as $bt_row) { $bt_wallet_options[$bt_row['_wallet']]=$bt_row['_wallet_name']; $bt_bank_options[$bt_row['_bank']]=bt_bank_name($bt_row['_bank']); }
asort($bt_wallet_options); asort($bt_bank_options);
?>
<div class="bt-heading"><div><h2><?php echo $bt_mode === 'problemas' ? 'Problemas nos boletos' : 'Títulos por banco e carteira'; ?></h2><p><?php echo $bt_mode === 'problemas' ? 'Falhas de emissão e pendências cadastrais, com orientação para correção.' : 'Acompanhe o que ainda resta nas carteiras antigas, sem depender da conta atual do cliente.'; ?></p></div><span class="rc-app-badge">Consulta</span></div>
<form class="bt-form" method="get"><input type="hidden" name="tab" value="<?php echo $bt_mode; ?>">
<?php foreach (array('carteira'=>array('Carteira original do título',$bt_wallet_options),'banco'=>array('Banco identificado no boleto',$bt_bank_options),'situacao'=>array('Situação',$bt_labels),'base'=>array('Filtrar datas por',array('vencimento'=>'Vencimento','pagamento'=>'Pagamento'))) as $bt_key=>$bt_option): ?>
<label><?php echo bt_h($bt_option[0]); ?><select name="bt_<?php echo $bt_key; ?>"><?php if (in_array($bt_key,array('carteira','banco'),true)): ?><option value="">Todos</option><?php endif; ?><?php foreach($bt_option[1] as $bt_value=>$bt_label): ?><option value="<?php echo bt_h($bt_value); ?>" <?php echo (string)$bt_value===$bt_filters[$bt_key] ? 'selected' : ''; ?>><?php echo bt_h($bt_label); ?></option><?php endforeach; ?></select></label><?php endforeach; ?>
<label>De<input type="date" name="bt_inicio" value="<?php echo bt_h($bt_filters['inicio']); ?>"></label><label>Até<input type="date" name="bt_fim" value="<?php echo bt_h($bt_filters['fim']); ?>"></label>
<label>Cliente, login ou título<input name="bt_busca" value="<?php echo bt_h($bt_filters['busca']); ?>" placeholder="Pesquisar…"></label>
<?php if($bt_mode==='problemas'): ?><label>Motivo<select name="bt_motivo"><option value="">Todos</option><?php foreach(array('emissao'=>'Falha de emissão','conta'=>'Conta não definida','email'=>'E-mail','documento'=>'CPF/CNPJ ausente','pendente'=>'Registro não confirmado','retorno'=>'Retorno da integração') as $bt_key=>$bt_label): ?><option value="<?php echo $bt_key; ?>" <?php echo $bt_filters['motivo']===$bt_key?'selected':''; ?>><?php echo bt_h($bt_label); ?></option><?php endforeach; ?></select></label><?php endif; ?>
<button class="btn btn-primary" type="submit">Aplicar filtros</button><a class="btn btn-secondary" href="?tab=<?php echo $bt_mode; ?>">Limpar</a></form>
<?php if($bt_problem): ?><p class="bt-warning" role="alert"><?php echo bt_h($bt_problem); ?> Confira a compatibilidade das tabelas no MK-Auth.</p><?php else: ?>
<?php if($bt_mode==='carteiras'): ?><div class="bt-warning"><strong><?php echo count($bt_pending); ?> títulos ainda pendentes nas carteiras/bancos selecionados</strong><p>Contagem sem limite de datas, cliente ou situação. <?php echo count($bt_pending) ? 'Ainda existem boletos para acompanhar antes de encerrar a carteira.' : 'Nenhum aberto/vencido localizado neste banco/carteira. Confira no banco antes de encerrar ou reemitir.'; ?></p></div>
<details class="bt-wallets"><summary>Saldo pendente por carteira · visão completa</summary><div class="table-wrapper"><table><thead><tr><th>Carteira original</th><th>Pendentes</th><th>Valor nominal</th><th>Último vencimento pendente</th></tr></thead><tbody><?php foreach($bt_global as $bt_item): ?><tr><td><?php echo bt_h($bt_item[0]); ?></td><td><?php echo $bt_item[1]; ?></td><td><?php echo bt_money($bt_item[2]); ?></td><td><?php echo bt_h(substr($bt_item[3],0,10)); ?></td></tr><?php endforeach; ?></tbody></table></div></details><?php endif; ?>
<div class="stats-bar"><?php foreach(array('receber'=>'A receber','vencido'=>'Vencidos','pago'=>'Pagos','outro'=>'Outras situações') as $bt_key=>$bt_label): ?><div class="stat-item"><div class="stat-content"><div class="stat-label"><?php echo $bt_label; ?> · <?php echo $bt_totals[$bt_key][0]; ?></div><div class="stat-value"><?php echo bt_money($bt_totals[$bt_key][1]); ?></div></div></div><?php endforeach; ?></div>
<p class="bt-note">Totais acima respeitam todos os filtros. Banco vem da linha digitável/código de barras ou evidência da integração; carteira vem do título. Nenhum registro bancário é consultado nem boleto é emitido por esta tela.</p>
<?php if($bt_mode==='problemas'): ?><p class="bt-note">As pendências são diagnósticos locais, não rejeições confirmadas pelo banco. “ERROR” sem retorno vinculado não permite afirmar a causa. Retornos Efí são ligados pela chave externa; tentativas antigas não são tratadas como erro de boletos já emitidos.</p><?php endif; ?>
<div class="rc-tools"><h2><?php echo count($bt_rows); ?> títulos encontrados</h2><button type="button" class="btn btn-secondary" onclick="btExport()">Exportar resultado CSV</button></div>
<div class="table-wrapper"><table class="bt-table"><thead><tr><th>Título / cliente</th><th>Carteira original</th><th>Banco no boleto</th><th>Conta atual do cliente</th><th>Vencimento</th><th>Situação</th><th>Valor</th><th><?php echo $bt_mode==='problemas'?'Motivo e orientação':'Registro / nosso número'; ?></th></tr></thead><tbody>
<?php foreach($bt_display as $bt_row): ?><tr><td><strong>#<?php echo (int)$bt_row['id']; ?></strong><br><?php if($bt_row['uuid_cliente']): ?><a href="../../cliente_det<?php echo bt_h($ext_mk); ?>?uuid=<?php echo rawurlencode($bt_row['uuid_cliente']); ?>" target="_blank" rel="noopener"><?php echo bt_h($bt_row['cliente_nome']); ?></a><?php else: ?>Cliente não localizado<?php endif; ?><br><small><?php echo bt_h($bt_row['login']); ?></small></td><td><?php echo bt_h($bt_row['_wallet_name']); ?></td><td><?php echo bt_h(bt_bank_name($bt_row['_bank'])); ?></td><td><?php echo bt_h($bt_row['_current']); ?><?php if((int)$bt_row['numconta']!==(int)$bt_row['conta_atual']): ?><br><small>Carteira diferente da conta atual</small><?php endif; ?></td><td><?php echo bt_h(substr((string)$bt_row['datavenc'],0,10)); ?></td><td><span class="badge badge-info"><?php echo bt_h(isset($bt_labels[$bt_row['_state']])?$bt_labels[$bt_row['_state']]:$bt_row['status']); ?></span><?php if($bt_row['_state']==='pago'): ?><br><small><?php echo bt_h(substr((string)$bt_row['datapag'],0,10)); ?></small><?php endif; ?></td><td><?php echo bt_money(bt_cents($bt_row[$bt_row['_state']==='pago'?'valorpag':'valor'])); ?></td><td><?php if($bt_mode==='problemas'): ?><?php foreach($bt_row['_issues'] as $bt_reason): ?><p><?php echo bt_h($bt_reason); ?></p><?php endforeach; ?><?php else: ?><?php echo bt_h($bt_row['nossonum']?:'Sem nosso número'); ?><?php endif; ?></td></tr><?php endforeach; ?>
<?php if(!$bt_display): ?><tr><td colspan="8">Nenhum título corresponde aos filtros.</td></tr><?php endif; ?></tbody></table></div>
<div class="bt-pagination"><span>Página <?php echo $bt_page; ?> de <?php echo $bt_pages; ?> · até 50 títulos por página</span><?php foreach(array(-1=>'Anterior',1=>'Próxima') as $bt_delta=>$bt_label): ?><?php if($bt_page+$bt_delta>0 && $bt_page+$bt_delta<=$bt_pages): ?><?php $bt_url=$_GET; $bt_url['bt_pagina']=$bt_page+$bt_delta; ?><a class="btn btn-secondary" href="?<?php echo bt_h(http_build_query($bt_url)); ?>"><?php echo $bt_label; ?></a><?php endif; ?><?php endforeach; ?></div>
<?php
// Only display-safe fields are embedded. No documents, gateway keys, or full payloads are sent to the browser.
$bt_export=array(array('Título','Cliente','Login','Carteira original','Banco no boleto','Conta atual','Vencimento','Situação','Valor','Diagnóstico'));
foreach($bt_rows as $bt_row) $bt_export[]=array($bt_row['id'],$bt_row['cliente_nome'],$bt_row['login'],$bt_row['_wallet_name'],bt_bank_name($bt_row['_bank']),$bt_row['_current'],substr((string)$bt_row['datavenc'],0,10),$bt_row['_state'],number_format(bt_cents($bt_row[$bt_row['_state']==='pago'?'valorpag':'valor'])/100,2,',',''),$bt_mode==='problemas'?implode(' | ',$bt_row['_issues']):'');
?>
<script type="application/json" id="bt-export-data"><?php echo json_encode($bt_export,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE); ?></script>
<script>function btExport(){const data=JSON.parse(document.getElementById('bt-export-data').textContent);const cell=v=>{v=String(v);if(/^\s*[=+@-]/.test(v))v="'"+v;return '"'+v.replace(/"/g,'""')+'"';};const csv=data.map(r=>r.map(cell).join(';')).join('\r\n');const url=URL.createObjectURL(new Blob(['\ufeff'+csv],{type:'text/csv;charset=utf-8;'}));const a=document.createElement('a');a.href=url;a.download='titulos-<?php echo $bt_mode; ?>.csv';a.click();setTimeout(()=>URL.revokeObjectURL(url),1000);}</script>
<?php endif; ?>
