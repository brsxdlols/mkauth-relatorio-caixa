<?php
// Preferences only. Stores no payment records and never uses the native database.
function lj_templates($dir,$user,$action='read',$name='',$filter=array()) {
    if($user==='')throw new RuntimeException('Usuário não identificado para salvar modelos.');
    foreach(array(dirname($dir),$dir) as $path){if(is_link($path))throw new RuntimeException('Diretório de modelos simbólico recusado.');if(!is_dir($path) && !@mkdir($path,0700) && !is_dir($path))throw new RuntimeException('Sem permissão para criar a pasta dos modelos.');}
    $path=$dir.'/'.hash('sha256',$user).'.php';$prefix="<?php http_response_code(404); exit; ?>\n";
    if(is_link($path))throw new RuntimeException('Arquivo de modelos simbólico recusado.');
    if(!file_exists($path)){$initial=@fopen($path,'x');if($initial){fwrite($initial,$prefix.base64_encode('{}'));fclose($initial);chmod($path,0600);}}
    if(!is_file($path)||stat($path)['nlink']>1)throw new RuntimeException('Arquivo de modelos inválido.');
    $fp=fopen($path,'r+');if(!$fp||!flock($fp,LOCK_EX))throw new RuntimeException('Não foi possível acessar os modelos.');
    try{
        $text=stream_get_contents($fp,262145);if(strlen($text)>262144||strpos($text,$prefix)!==0)throw new RuntimeException('Arquivo de modelos inválido.');
        $data=json_decode(base64_decode(substr($text,strlen($prefix)),true),true);if(!is_array($data))throw new RuntimeException('Arquivo de modelos inválido.');
        if($action!=='read'){
            $name=trim($name);if($name===''||strlen($name)>80)throw new RuntimeException('Nome do modelo: use de 1 a 80 caracteres.');
            if($action==='save'){if(!isset($data[$name])&&count($data)>=40)throw new RuntimeException('Limite de 40 modelos por usuário.');$data[$name]=$filter;}
            elseif($action==='delete')unset($data[$name]);else throw new RuntimeException('Operação inválida.');
            $encoded=json_encode($data,JSON_INVALID_UTF8_SUBSTITUTE);$payload=$prefix.base64_encode($encoded);rewind($fp);if(fwrite($fp,$payload)!==strlen($payload)||!ftruncate($fp,strlen($payload))||!fflush($fp))throw new RuntimeException('Falha ao salvar os modelos.');
        }
        return $data;
    }finally{flock($fp,LOCK_UN);fclose($fp);}
}
