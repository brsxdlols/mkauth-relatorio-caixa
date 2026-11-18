<?php
// Calendar shortcuts in the MK-AUTH PHP timezone; no database writes.
function rc_cash_periods($today=null) {
    $day=$today instanceof DateTimeImmutable?$today:new DateTimeImmutable('today');
    $day=$day->setTime(0,0,0);$sunday=$day->modify('-'.$day->format('w').' days');
    $ranges=array('hoje'=>array('CAIXA HOJE',$day,$day),'semana'=>array('CAIXA SEMANA',$sunday,$sunday->modify('+6 days')),'mes'=>array('CAIXA MÊS',$day->modify('first day of this month'),$day->modify('last day of this month')));
    $result=array();
    foreach($ranges as $key=>$range)$result[$key]=array('label'=>$range[0],'start'=>$range[1]->format('Y-m-d'),'end'=>$range[2]->format('Y-m-d'),'display'=>$range[1]->format('d/m/Y').($key==='hoje'?'':' a '.$range[2]->format('d/m/Y')));
    return $result;
}
