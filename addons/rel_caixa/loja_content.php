<?php
if(empty($acesso_permitido)){http_response_code(403);exit('Acesso não permitido.');}
require_once __DIR__.'/titulos_lib.php';require_once __DIR__.'/loja_lib.php';require_once __DIR__.'/loja_templates.php';
if(empty($_SESSION['rc_loja_csrf']))$_SESSION['rc_loja_csrf']=bin2hex(random_bytes(24));
$lo_filter=lj_filter(array());
$lo_message='';$lo_error='';$lo_templates=array();$lo_rows=array();$lo_all=array();$lo_methods=array();$lo_accounts=array();$lo_users=array();$lo_warnings=array();
function lj_get_filter($f){return array('lo_formas'=>$f['methods'],'lo_contas'=>$f['accounts'],'lo_usuarios'=>$f['users'],'lo_inicio'=>isset($f['start'])?$f['start']:date('Y-m-d'),'lo_fim'=>isset($f['end'])?$f['end']:date('Y-m-d'),'lo_busca'=>$f['search'],'lo_tarifas'=>$f['fees'],'lo_mov'=>$f['movement']);}
try{
    $source=$_GET;if(!isset($_GET['lo_aplicar'])&&$_SERVER['REQUEST_METHOD']!=='POST')$source['lo_formas']=array('dinheiro','pix');
    $lo_filter=lj_filter($source);
    try{$lo_templates=lj_templates(__DIR__.'/data/loja',(string)$usuario_logado);}catch(Throwable $e){$lo_message=$e->getMessage();}
    if($_SERVER['REQUEST_METHOD']==='POST'){
        if(!isset($_POST['lo_csrf'])||!is_string($_POST['lo_csrf'])||!hash_equals($_SESSION['rc_loja_csrf'],$_POST['lo_csrf']))throw new RuntimeException('Sessão de formulário inválida. Atualize a página e tente novamente.');
        $action=isset($_POST['lo_action'])&&is_string($_POST['lo_action'])?$_POST['lo_action']:'';
        $model=isset($_POST['lo_model'])&&is_string($_POST['lo_model'])?$_POST['lo_model']:'';
        $raw=isset($_POST['lo_filter'])&&is_string($_POST['lo_filter'])?json_decode($_POST['lo_filter'],true):array();
        if(is_array($raw))$lo_filter=lj_filter($raw);
        if($action==='apply'){
            if(!isset($lo_templates[$model]))throw new RuntimeException('Selecione um filtro salvo cadastrado.');
            $lo_filter=lj_filter(lj_get_filter($lo_templates[$model]));$lo_message='Modelo aplicado: '.$model;
        }elseif($action==='save'){
            $name=isset($_POST['lo_name'])&&is_string($_POST['lo_name'])?trim($_POST['lo_name']):'';
            if($name==='')$name=$model;
            $saved=$lo_filter;if(empty($_POST['lo_dates']))unset($saved['start'],$saved['end']);
            $lo_templates=lj_templates(__DIR__.'/data/loja',(string)$usuario_logado,'save',$name,$saved);$lo_message='Filtro salvo: '.$name;
        }elseif($action==='delete'){$lo_templates=lj_templates(__DIR__.'/data/loja',(string)$usuario_logado,'delete',$model);$lo_message='Modelo excluído.';}
        else throw new RuntimeException('Operação de modelo inválida.');
    }
    list($lo_all,$lo_methods,$lo_accounts,$lo_users,$lo_warnings)=lj_load($link,$lo_filter,dirname(__DIR__,3).'/dados/item.db');
    foreach($lo_all as $r)if(lj_matches($r,$lo_filter))$lo_rows[]=$r;
}catch(Throwable $e){$lo_error=$e->getMessage();}
$lo_summary=lj_summary($lo_rows);$lo_page=max(1,isset($_GET['lo_pagina'])?(int)$_GET['lo_pagina']:1);$lo_pages=max(1,(int)ceil(count($lo_rows)/50));$lo_page=min($lo_page,$lo_pages);$lo_display=array_slice($lo_rows,($lo_page-1)*50,50);
?>
<div class="bt-heading"><div><h2>CAIXA PERSONALIZADO</h2><p>Filtre por um ou mais usuários, formas de recebimento e contas. Combine as opções e salve como modelo para consultar novamente. Os lançamentos continuam no caixa principal.</p></div><span class="rc-app-badge">Visão de conferência</span></div>
<?php if($lo_error): ?><p class="bt-warning" role="alert"><?php echo bt_h($lo_error); ?></p><?php endif; ?>
<?php if($lo_message): ?><p class="lo-message" role="status"><?php echo bt_h($lo_message); ?></p><?php endif; ?>
<?php foreach($lo_warnings as $warning): ?><p class="bt-warning"><?php echo bt_h($warning); ?></p><?php endforeach; ?>
<form method="get" class="bt-form lo-form" id="lo-filter-form"><input type="hidden" name="tab" value="loja"><input type="hidden" name="lo_aplicar" value="1">
<label>De<input type="date" name="lo_inicio" required value="<?php echo bt_h($lo_filter['start']); ?>"></label><label>Até<input type="date" name="lo_fim" required value="<?php echo bt_h($lo_filter['end']); ?>"></label>
<label>Cliente, login ou histórico<input name="lo_busca" value="<?php echo bt_h($lo_filter['search']); ?>" placeholder="Pesquisar…"></label>
<label>Movimentação<select name="lo_mov"><?php foreach(array('todos'=>'Entradas e saídas','entrada'=>'Somente entradas','saida'=>'Somente saídas') as $v=>$n): ?><option value="<?php echo $v; ?>" <?php echo $lo_filter['movement']===$v?'selected':''; ?>><?php echo $n; ?></option><?php endforeach; ?></select></label>
<label>Tarifas<select name="lo_tarifas"><?php foreach(array('incluir'=>'Incluir tarifas','ocultar'=>'Excluir tarifas dos totais','somente'=>'Somente tarifas') as $v=>$n): ?><option value="<?php echo $v; ?>" <?php echo $lo_filter['fees']===$v?'selected':''; ?>><?php echo $n; ?></option><?php endforeach; ?></select></label>
<div class="lo-multis"><?php foreach(array('methods'=>array('Formas de recebimento','lo_formas',$lo_methods),'accounts'=>array('Conta bancária do boleto','lo_contas',$lo_accounts),'users'=>array('Recebido por','lo_usuarios',$lo_users)) as $key=>$group): ?><details open><summary><?php echo bt_h($group[0]); ?> <small>· múltipla escolha</small></summary><div class="lo-checks"><?php foreach($group[2] as $v=>$n): ?><label><input type="checkbox" name="<?php echo $group[1]; ?>[]" value="<?php echo bt_h($v); ?>" <?php echo in_array((string)$v,$lo_filter[$key],true)?'checked':''; ?>><span><?php echo bt_h($n); ?></span></label><?php endforeach; ?></div><small>Nenhuma seleção = todas as opções.</small></details><?php endforeach; ?></div>
<div class="lo-actions"><button type="submit" class="btn btn-primary">Aplicar filtros</button><button type="button" class="btn btn-secondary" onclick="loQuick()">Dinheiro + PIX</button><a class="btn btn-secondary" href="?tab=loja&lo_aplicar=1">Todos os recebimentos</a></div></form>
<form method="post" class="lo-models" id="lo-model-form" action="?tab=loja"><input type="hidden" name="lo_csrf" value="<?php echo bt_h($_SESSION['rc_loja_csrf']); ?>"><input type="hidden" name="lo_filter" id="lo-filter-snapshot" value="<?php echo bt_h(json_encode(lj_get_filter($lo_filter),JSON_INVALID_UTF8_SUBSTITUTE)); ?>">
<div><strong>Filtros salvos</strong><p>Salvos neste servidor para seu usuário. Sem salvar as datas, o filtro abre no dia atual.</p></div><select name="lo_model" aria-label="Filtro salvo"><option value="">Selecione um filtro salvo</option><?php foreach($lo_templates as $name=>$unused): ?><option value="<?php echo bt_h($name); ?>"><?php echo bt_h($name); ?></option><?php endforeach; ?></select><button class="btn btn-secondary" name="lo_action" value="apply">Aplicar</button>
<details><summary>Salvar filtro ou gerenciar</summary><label>Nome<input name="lo_name" maxlength="80" placeholder="Ex.: Loja — Dinheiro e PIX"></label><label><input type="checkbox" name="lo_dates" value="1"> Salvar também as datas escolhidas</label><button class="btn btn-primary" name="lo_action" value="save">Salvar filtro</button><button class="btn btn-secondary" name="lo_action" value="delete">Excluir filtro selecionado</button></details></form>
<p class="bt-note">Forma de pagamento e conta vêm do título vinculado; usuário e valores vêm do lançamento de caixa. Sem vínculo, a linha permanece como “não identificada”. Alterações posteriores no título podem afetar essa classificação. PIX de integração (ex.: pix-lytex) é uma opção separada do PIX manual.</p>
<?php if (!$lo_error): ?>
<div class="lo-message" role="status"><strong>Filtros aplicados:</strong>
<?php $lo_labels=array();foreach(array('methods'=>array($lo_methods,'Todas as formas'),'accounts'=>array($lo_accounts,'Todas as contas'),'users'=>array($lo_users,'Todos os usuários')) as $dim=>$meta){$labels=array();foreach($lo_filter[$dim] as $key)$labels[]=isset($meta[0][$key])?$meta[0][$key]:$key;$lo_labels[]=count($labels)?implode(' + ',$labels):$meta[1];} $lo_labels[]=array('todos'=>'Entradas e saídas','entrada'=>'Somente entradas','saida'=>'Somente saídas')[$lo_filter['movement']];$lo_labels[]=array('incluir'=>'Inclui tarifas','ocultar'=>'Exclui tarifas','somente'=>'Somente tarifas')[$lo_filter['fees']];if($lo_filter['search']!=='')$lo_labels[]='Busca: '.$lo_filter['search'];echo bt_h(implode(' · ',$lo_labels)); ?>
</div>
<?php if (!$lo_rows): ?>
<div class="bt-warning">
<?php if ($lo_all): ?>Há <?php echo count($lo_all); ?> lançamentos nesse período, mas nenhum atende aos filtros selecionados. Experimente “Todos os recebimentos” ou ajuste as seleções.
<?php else: ?>Nenhum lançamento foi registrado no caixa entre <?php echo bt_h(date('d/m/Y',strtotime($lo_filter['start'])).' e '.date('d/m/Y',strtotime($lo_filter['end']))); ?>.
<?php $lo_latest=bt_fetch($link,'SELECT MAX(data) AS last_date FROM sis_caixa'); $lo_last=isset($lo_latest[0]['last_date'])?substr((string)$lo_latest[0]['last_date'],0,10):''; ?>
<?php if ($lo_last): ?><p>Último dia com movimento: <?php echo bt_h(date('d/m/Y',strtotime($lo_last))); ?>. <a href="?<?php echo bt_h(http_build_query(array('tab'=>'loja','lo_aplicar'=>1,'lo_inicio'=>$lo_last,'lo_fim'=>$lo_last))); ?>">Ver esse dia com todos os recebimentos</a></p><?php endif; ?>
<?php endif; ?>
</div>
<?php endif; ?>
<div class="stats-bar"><?php foreach(array(array('Recebimentos',$lo_summary['count']),array('Entradas',bt_money($lo_summary['incoming'])),array('Saídas',bt_money($lo_summary['outgoing'])),array('Resultado filtrado',bt_money($lo_summary['incoming']-$lo_summary['outgoing']))) as $item): ?><div class="stat-item"><div class="stat-content"><div class="stat-label"><?php echo $item[0]; ?></div><div class="stat-value"><?php echo $item[1]; ?></div></div></div><?php endforeach; ?></div>
<p class="bt-note"><?php echo count($lo_rows); ?> lançamentos · tarifas neste resultado: <?php echo bt_money($lo_summary['fees']); ?> · todos os totais respeitam as seleções acima. Sem saldo anterior.</p>
<div class="rc-tools"><h2>Resumo da conferência</h2><div><button class="btn btn-secondary" type="button" onclick="loExport()">Exportar CSV</button> <button class="btn btn-secondary" type="button" onclick="rcPrintChoose('loja')">Imprimir</button></div></div>
<div class="lo-summaries"><?php foreach(array('users'=>array('Por usuário',$lo_users),'methods'=>array('Por recebimento',$lo_methods)) as $dim=>$meta): ?><details open><summary><?php echo $meta[0]; ?></summary><div class="table-wrapper"><table><thead><tr><th>Opção</th><th>Lançamentos</th><th>Entradas</th><th>Saídas</th><th>Resultado</th></tr></thead><tbody><?php foreach($lo_summary[$dim] as $key=>$values): ?><tr><td><?php echo bt_h(isset($meta[1][$key])?$meta[1][$key]:$key); ?></td><td><?php echo $values[0]; ?></td><td><?php echo bt_money($values[1]); ?></td><td><?php echo bt_money($values[2]); ?></td><td><?php echo bt_money($values[1]-$values[2]); ?></td></tr><?php endforeach; ?></tbody></table></div></details><?php endforeach; ?></div>
<p class="lo-period">Período de movimentação: <?php echo bt_h(date('d/m/Y',strtotime($lo_filter['start'])).' a '.date('d/m/Y',strtotime($lo_filter['end']))); ?> · <?php echo count($lo_rows); ?> lançamentos selecionados.</p>
<div class="table-wrapper"><table class="lo-table"><thead><tr><th>Movimentação</th><th>Cliente / título</th><th>Usuário</th><th>Recebimento</th><th>Conta</th><th>Histórico</th><th>Entrada</th><th>Saída</th></tr></thead><tbody><?php foreach($lo_display as $r): ?><tr><td><?php echo bt_h($r['data']); ?></td><td><?php if($r['_uuid']): ?><a target="_blank" rel="noopener" href="../../cliente_det<?php echo bt_h($ext_mk); ?>?uuid=<?php echo rawurlencode($r['_uuid']); ?>"><?php echo bt_h($r['_name']); ?></a><?php else: ?><?php echo bt_h($r['_name']); ?><?php endif; ?><br><small><?php echo bt_h($r['_login']); ?> · <?php echo $r['_title']?'#'.$r['_title']:'Sem título'; ?></small></td><td><?php echo bt_h($r['usuario']); ?></td><td><?php echo bt_h(isset($lo_methods[$r['_method']])?$lo_methods[$r['_method']]:$r['_method']); ?></td><td><?php echo bt_h($lo_accounts[$r['_account']]); ?></td><td><?php echo bt_h($r['historico']); ?><?php if($r['_fee']): ?><br><span class="badge badge-info">Tarifa</span><?php endif; ?></td><td><?php echo bt_money(bt_cents($r['entrada'])); ?></td><td><?php echo bt_money(bt_cents($r['saida'])); ?></td></tr><?php endforeach; ?><?php if(!$lo_rows): ?><tr><td colspan="8">Nenhum lançamento para essas seleções. Experimente ampliar as datas ou os filtros.</td></tr><?php endif; ?></tbody></table></div>
<div class="bt-pagination">Página <?php echo $lo_page; ?> de <?php echo $lo_pages; ?><?php foreach(array(-1=>'Anterior',1=>'Próxima') as $step=>$text): ?><?php if($lo_page+$step>0 && $lo_page+$step<=$lo_pages): ?><?php $params=lj_get_filter($lo_filter);$params['tab']='loja';$params['lo_aplicar']=1;$params['lo_pagina']=$lo_page+$step; ?><a class="btn btn-secondary" href="?<?php echo bt_h(http_build_query($params)); ?>"><?php echo $text; ?></a><?php endif; ?><?php endforeach; ?></div>
<?php $lo_export=array(array('Movimentação','Cliente','Título','Usuário','Recebimento','Conta','Histórico','Entrada','Saída'));foreach($lo_rows as $r)$lo_export[]=array($r['data'],$r['_name'],$r['_title'],$r['usuario'],$lo_methods[$r['_method']],$lo_accounts[$r['_account']],$r['historico'],number_format(bt_cents($r['entrada'])/100,2,',',''),number_format(bt_cents($r['saida'])/100,2,',',''));$lo_export[]=array('Totais','','','','','','',number_format($lo_summary['incoming']/100,2,',',''),number_format($lo_summary['outgoing']/100,2,',','')); ?>
<script type="application/json" id="lo-export-data" data-period-start="<?php echo bt_h($lo_filter['start']); ?>" data-period-end="<?php echo bt_h($lo_filter['end']); ?>"><?php echo json_encode($lo_export,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE); ?></script>
<script>
function loQuick(){document.querySelectorAll('#lo-filter-form input[name="lo_formas[]"]').forEach(el=>el.checked=['dinheiro','pix'].includes(el.value));document.getElementById('lo-filter-form').submit();}
document.getElementById('lo-model-form').addEventListener('submit',function(){const values={};new FormData(document.getElementById('lo-filter-form')).forEach((v,k)=>{if(k.endsWith('[]')){k=k.slice(0,-2);(values[k]||(values[k]=[])).push(v);}else values[k]=v;});document.getElementById('lo-filter-snapshot').value=JSON.stringify(values);});
function loExport(){const rows=JSON.parse(document.getElementById('lo-export-data').textContent);const cell=v=>{v=String(v);if(/^\s*[=+@-]/.test(v))v="'"+v;return '"'+v.replace(/"/g,'""')+'"';};const csv='\ufeff'+rows.map(r=>r.map(cell).join(';')).join('\r\n');const url=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8;'}));const a=document.createElement('a');a.href=url;a.download='caixa-personalizado.csv';a.click();setTimeout(()=>URL.revokeObjectURL(url),1000);}
</script>

<?php endif; ?>
