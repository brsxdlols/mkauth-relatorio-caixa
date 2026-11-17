<?php
// Public provider identity only; never expose credentials or settings in reports.
function rc_report_provider($link) {
    $identity=array('name'=>'Provedor não informado','cnpj'=>'CNPJ não informado no MK-AUTH','logo'=>'');
    try {
        $result=mysqli_query($link,'SELECT nome,razao,cnpj FROM sis_provedor LIMIT 1');
        $row=$result?mysqli_fetch_assoc($result):null;
        if($row){$name=trim((string)$row['razao']);if($name==='')$name=trim((string)$row['nome']);if($name!=='')$identity['name']=$name;if(trim((string)$row['cnpj'])!=='')$identity['cnpj']=trim((string)$row['cnpj']);}
    }catch(Throwable $error){}
    $logo=__DIR__.'/../../img/logo.jpg';
    if(!is_link($logo)&&is_file($logo)&&is_readable($logo)&&filesize($logo)<=524288){
        $info=@getimagesize($logo);$allowed=array('image/jpeg','image/png','image/gif');
        if($info&&in_array($info['mime'],$allowed,true))$identity['logo']='data:'.$info['mime'].';base64,'.base64_encode(file_get_contents($logo));
    }
    return $identity;
}
