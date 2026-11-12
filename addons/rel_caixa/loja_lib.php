<?php
// Read-only receipt projection. No native financial table is changed.
function lj_key($value) { return strtolower(strtr(trim((string)$value), array('Á'=>'a','À'=>'a','Ã'=>'a','Â'=>'a','É'=>'e','Ê'=>'e','Í'=>'i','Ó'=>'o','Ô'=>'o','Õ'=>'o','Ú'=>'u','Ç'=>'c','á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c'))); }
function lj_fee($row) {
    return (float)$row['saida'] > 0 && preg_match('/\btarif[ae]s?\b|\btaxas?\b.*(?:t[ií]tulo|banc|boleto|sicredi|lytex|gerencianet)/iu', (string)$row['historico']) === 1;
}
function lj_values($source,$key) {
    $values = isset($source[$key]) ? $source[$key] : array();
    if (!is_array($values)) $values = array($values);
    return array_values(array_unique(array_map('strval', array_filter(array_slice($values,0,200), 'is_scalar'))));
}
function lj_filter($source) {
    $filter=array('methods'=>lj_values($source,'lo_formas'),'accounts'=>lj_values($source,'lo_contas'),'users'=>lj_values($source,'lo_usuarios'));
    $filter['start']=isset($source['lo_inicio']) && is_string($source['lo_inicio']) ? $source['lo_inicio'] : date('Y-m-d');
    $filter['end']=isset($source['lo_fim']) && is_string($source['lo_fim']) ? $source['lo_fim'] : date('Y-m-d');
    $filter['search']=isset($source['lo_busca']) && is_string($source['lo_busca']) ? trim(substr($source['lo_busca'],0,200)) : '';
    $filter['fees']=isset($source['lo_tarifas']) && in_array($source['lo_tarifas'],array('incluir','ocultar','somente'),true)?$source['lo_tarifas']:'incluir';
    $filter['movement']=isset($source['lo_mov']) && in_array($source['lo_mov'],array('todos','entrada','saida'),true)?$source['lo_mov']:'todos';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$filter['start']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$filter['end']) || !bt_date_valid($filter['start']) || !bt_date_valid($filter['end']) || $filter['start']>$filter['end']) throw new RuntimeException('Informe datas válidas e início anterior ao fim.');
    return $filter;
}
function lj_matches($row,$filter) {
    if ($filter['methods'] && !in_array($row['_method'],$filter['methods'],true)) return false;
    if ($filter['accounts'] && !in_array($row['_account'],$filter['accounts'],true)) return false;
    if ($filter['users'] && !in_array((string)$row['usuario'],$filter['users'],true)) return false;
    if ($filter['fees']==='ocultar' && $row['_fee']) return false;
    if ($filter['fees']==='somente' && !$row['_fee']) return false;
    if ($filter['movement']==='entrada' && (float)$row['entrada']<=0) return false;
    if ($filter['movement']==='saida' && (float)$row['saida']<=0) return false;
    if ($filter['search']!=='' && strpos(lj_key($row['_name'].' '.$row['_login'].' '.$row['historico'].' '.$row['usuario']),lj_key($filter['search']))===false) return false;
    return true;
}
function lj_load($link,$filter,$itemsPath) {
    $methods=array('dinheiro'=>'Dinheiro','cheque'=>'Cheque','cartao'=>'Cartão','vale'=>'Vale','outros'=>'Outros'); $warnings=array();
    // Same native catalogue used by the + button on the payment form.
    if (class_exists('SQLite3') && is_file($itemsPath) && is_readable($itemsPath)) {
        try {
            $items=new SQLite3($itemsPath,SQLITE3_OPEN_READONLY);
            $result=$items->query("SELECT nome FROM item WHERE campo='titulos_formapag'");
            if (!$result) throw new RuntimeException('Catálogo indisponível');
            while($item=$result->fetchArray(SQLITE3_ASSOC)) if(trim((string)$item['nome'])!=='')$methods[lj_key($item['nome'])]=$item['nome'];
            $items->close();
        } catch(Throwable $error) {$warnings[]='Não foi possível ler os itens personalizados. As formas já utilizadas continuam disponíveis.';}
    } else $warnings[]='Catálogo personalizado não acessível; exibindo opções padrão e formas já utilizadas.';
    foreach(bt_fetch($link,"SELECT DISTINCT formapag FROM sis_lanc WHERE TRIM(COALESCE(formapag,''))<>''") as $r) $methods[lj_key($r['formapag'])]=$r['formapag'];
    $methods['__none__']='Sem forma identificada'; asort($methods,SORT_NATURAL|SORT_FLAG_CASE);
    $accounts=array('0'=>'Sem conta no título');
    foreach(bt_fetch($link,'SELECT id,nome,delconta FROM sis_boleto ORDER BY nome') as $r)$accounts[(string)$r['id']]=$r['nome'].' (#'.$r['id'].')'.($r['delconta']==='s'?' · desativada':'');
    $users=array();
    foreach(bt_fetch($link,'SELECT DISTINCT usuario FROM sis_caixa ORDER BY usuario') as $r)$users[(string)$r['usuario']]=(string)$r['usuario']!==''?$r['usuario']:'Sem usuário';
    asort($users,SORT_NATURAL|SORT_FLAG_CASE);
    $start=$filter['start']; $until=(new DateTime($filter['end']))->modify('+1 day')->format('Y-m-d');
    $rows=bt_fetch($link,"SELECT id,data,usuario,historico,entrada,saida FROM sis_caixa WHERE data>='$start' AND data<'$until' ORDER BY data DESC,id DESC");
    $ids=array(); foreach($rows as $r){$id=bt_title_id_store($r['historico']);if($id)$ids[$id]=$id;}
    $titles=array(); foreach(array_chunk(array_values($ids),500) as $chunk)foreach(bt_fetch($link,'SELECT l.id,l.formapag,l.numconta,l.login,c.nome,c.uuid_cliente,l.datavenc FROM sis_lanc l LEFT JOIN sis_cliente c ON c.login=l.login WHERE l.id IN ('.implode(',',$chunk).')') as $r)$titles[$r['id']]=$r;
    foreach($rows as &$r) {
        $id=bt_title_id_store($r['historico']);$title=isset($titles[$id])?$titles[$id]:array();
        $r['_title']=$id;$r['_method']=!empty($title['formapag'])?lj_key($title['formapag']):'__none__';$r['_account']=isset($title['numconta'])?(string)(int)$title['numconta']:'0';
        $r['_name']=isset($title['nome'])?$title['nome']:'Sem cliente vinculado';$r['_login']=isset($title['login'])?$title['login']:'';$r['_uuid']=isset($title['uuid_cliente'])?$title['uuid_cliente']:'';
        $r['_fee']=lj_fee($r);
        if(!isset($accounts[$r['_account']]))$accounts[$r['_account']]='Conta removida #'.$r['_account'];
    }unset($r);
    return array($rows,$methods,$accounts,$users,$warnings);
}
function bt_title_id_store($history){return preg_match('/t[ií]tulo(?:[^0-9]{0,30})(\d+)/iu',(string)$history,$m)?(int)$m[1]:0;}
function lj_summary($rows) {
    $summary=array('incoming'=>0,'outgoing'=>0,'count'=>0,'fees'=>0,'users'=>array(),'methods'=>array());
    foreach($rows as $row){$in=bt_cents($row['entrada']);$out=bt_cents($row['saida']);$summary['incoming']+=$in;$summary['outgoing']+=$out;if($in>0)$summary['count']++;if($row['_fee'])$summary['fees']+=$out;
        foreach(array('users'=>(string)$row['usuario'],'methods'=>$row['_method']) as $dim=>$key){if(!isset($summary[$dim][$key]))$summary[$dim][$key]=array(0,0,0);$summary[$dim][$key][0]++;$summary[$dim][$key][1]+=$in;$summary[$dim][$key][2]+=$out;}}
    return $summary;
}
