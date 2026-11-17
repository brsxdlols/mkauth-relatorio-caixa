<?php
// Read-only detail of receipts. Values remain included in the original cash entry.
require_once __DIR__.'/titulos_lib.php';
function rc_extra_title($row) {
    $matches=array();
    return preg_match('/t[ií]tulo(?:[^0-9]{0,30})([0-9]+)/iu',(string)$row['historico'],$matches) ? (int)$matches[1] : 0;
}
function rc_extra_map($rows,$titles) {
    $values=array_fill(0,count($rows),null);$candidates=array();$total=0;$unknown=0;
    foreach($rows as $i=>$row) {
        if(bt_cents($row['entrada'])<=0)continue;
        $id=rc_extra_title($row);
        if(!$id || !isset($titles[$id]))continue;
        $title=$titles[$id];$paid=bt_cents($title['valorpag']);
        if(strtolower(trim((string)$title['status']))!=='pago' || $paid<=0 || bt_cents($title['valor'])<=0 || abs(bt_cents($row['entrada'])-$paid)>1 || substr((string)$title['datapag'],0,10)!==substr((string)$row['data'],0,10))continue;
        $candidates[$id][]=$i;
    }
    foreach($candidates as $id=>$indexes) {
        // Never count the same title's surcharge twice or attribute partial payments.
        if(count($indexes)!==1)continue;
        $value=max(0,bt_cents($titles[$id]['valorpag'])-bt_cents($titles[$id]['valor']));
        $values[$indexes[0]]=$value;$total+=$value;
    }
    foreach($rows as $i=>$row)if(bt_cents($row['entrada'])>0 && $values[$i]===null)$unknown++;
    return array('values'=>$values,'total'=>$total,'unknown'=>$unknown);
}
function rc_extra_load($link,$rows) {
    $ids=array();$titles=array();
    foreach($rows as $row)if(bt_cents($row['entrada'])>0){$id=rc_extra_title($row);if($id)$ids[$id]=$id;}
    foreach(array_chunk($ids,500) as $batch) {
        $result=mysqli_query($link,'SELECT id,valor,valorpag,status,datapag FROM sis_lanc WHERE id IN ('.implode(',',$batch).')');
        if(!$result)continue;
        while($row=mysqli_fetch_assoc($result))$titles[(int)$row['id']]=$row;
    }
    return rc_extra_map($rows,$titles);
}
