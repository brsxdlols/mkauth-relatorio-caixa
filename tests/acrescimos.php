<?php
require dirname(__DIR__).'/addons/rel_caixa/acrescimos_lib.php';
function extra_check($ok,$label){if(!$ok)throw new RuntimeException($label);}
$receipt=array('entrada'=>'113.99','saida'=>'0','historico'=>'Recebimento do titulo: 5117','data'=>'2026-11-17 11:08:47');
$title=array('valor'=>'109.90','valorpag'=>'113.99','status'=>'pago','datapag'=>'2026-11-17 11:08:47');
$fee=$receipt;$fee['entrada']='0';$fee['saida']='1.38';
$r=rc_extra_map(array($fee,$receipt),array(5117=>$title));extra_check($r['total']===409&&$r['values']===array(null,409),'Diferença em centavos sem tarifa');
$r=rc_extra_map(array($receipt,$receipt),array(5117=>$title));extra_check($r['total']===0&&$r['unknown']===2,'Não duplica título');
$partial=$receipt;$partial['entrada']='50.00';$r=rc_extra_map(array($partial),array(5117=>$title));extra_check($r['values'][0]===null,'Parcial não atribuída');
$title['valor']='120.00';$r=rc_extra_map(array($receipt),array(5117=>$title));extra_check($r['values'][0]===0,'Desconto não é acréscimo');
$title['status']='aberto';$r=rc_extra_map(array($receipt),array(5117=>$title));extra_check($r['values'][0]===null,'Título aberto');
$title['status']='pago';$title['datapag']='2026-11-16';$r=rc_extra_map(array($receipt),array(5117=>$title));extra_check($r['values'][0]===null,'Outro dia');
echo "OK: acréscimos, tarifas, duplicidade, pagamento parcial, desconto e data.\n";

require_once dirname(__DIR__).'/addons/rel_caixa/loja_lib.php';
$receipt['usuario']='lytex';$fee['usuario']='lytex';$fee['historico']='Descontado a tarifa do titulo: 5117';
$display=rc_cash_display(array($fee,$receipt),array('values'=>array(null,409)));extra_check(count($display)===1&&bt_cents($display[0]['entrada'])===11399&&bt_cents($display[0]['saida'])===138&&$display[0]['_extra']===409,'Agrupa recebimento e tarifa sem alterar valores');
$display=rc_cash_display(array($fee,$receipt,$receipt),array('values'=>array(null,null,null)));extra_check(count($display)===3,'Não agrupa pares ambíguos');
$fee['usuario']='outro';$display=rc_cash_display(array($fee,$receipt),array('values'=>array(null,409)));extra_check(count($display)===2,'Não agrupa usuários diferentes');
echo "OK: agrupamento seguro de recebimento e tarifa.\n";
