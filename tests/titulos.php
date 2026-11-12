<?php
require dirname(__DIR__) . '/addons/rel_caixa/titulos_lib.php';
function check($value,$message) { if (!$value) throw new RuntimeException($message); }
$r=array('status'=>'aberto','datavenc'=>'2026-11-10','nossonum'=>'gerencianet','linhadig'=>'36490'.str_repeat('0',42),'codigo_barras'=>'','chave_gnet'=>'','chave_gnet2'=>'x','conta_atual'=>3,'email'=>'cliente@example.com','cpf_cnpj'=>'1');
check(bt_bank($r)==='364','Banco real do boleto antigo');
check(bt_state($r,'2026-11-07')==='receber','A receber');
$r['datavenc']='2026-11-06';check(bt_state($r,'2026-11-07')==='vencido','Vencido calculado');
$r['status']='pago';check(bt_state($r,'2026-11-07')==='pago','Pago prevalece');check(bt_issues($r,array())===array(),'Pago não vira falha de emissão');
$r['status']='aberto';$r['nossonum']='ERROR';$r['linhadig']='99993'.str_repeat('0',42);$r['chave_gnet2']='';$r['email']='';$r['conta_atual']=0;
$issues=bt_issues($r,array());check(isset($issues['emissao'],$issues['conta'],$issues['email']),'Diagnóstico de ERROR, conta e e-mail');
check(bt_bank($r)==='indefinido','Não identifica marcador 999 como banco');
$r['nossonum']='123456789';check(bt_issues($r,array())===array(),'Cadastro incompleto não invalida boleto já emitido');
check(bt_cents('1.234,56')===123456 && bt_cents('75.00')===7500,'Valores em centavos');
check(bt_csv_cell('=1+1')==='"\'=1+1"','CSV neutraliza fórmula');
check(!bt_date_valid('2026-02-30') && bt_date_valid('2026-11-07'),'Datas estritas');
echo "OK: regras de banco, situação, diagnóstico, datas e CSV.\n";
