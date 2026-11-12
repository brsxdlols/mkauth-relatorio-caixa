<?php
// Isolated filesystem regression tests. No MK-Auth database is accessed.
$source=dirname(__DIR__);$base=sys_get_temp_dir().'/caixa-test-'.bin2hex(random_bytes(6));
mkdir($base,0700);$root=$base.'/mk';mkdir($root.'/admin/addons/rel_caixa',0755,true);
$target=$root.'/admin/addons/rel_caixa';$native=$base.'/native.php';file_put_contents($native,'NATIVE-SENTINEL');
file_put_contents($target.'/config.php','CUSTOM-CONFIG');chmod($target.'/config.php',0640);
symlink($native,$target.'/addons.class.php');symlink($native,$target.'/index.php');
function ck($ok,$text){if(!$ok)throw new RuntimeException($text);}
function runDeploy($source,$mode,$input,$root){$cmd=escapeshellarg(PHP_BINARY).' '.escapeshellarg($source.'/deploy.php').' '.escapeshellarg($mode).' '.escapeshellarg($input).' '.escapeshellarg($root).' --no-menu 2>&1';exec($cmd,$output,$status);clearstatcache();return array($status,implode("\n",$output));}
list($code,$output)=runDeploy($source,'install',$source,$root);ck($code===0,$output);
ck(file_get_contents($native)==='NATIVE-SENTINEL','Arquivo nativo alterado');
ck(is_link($target.'/addons.class.php'),'Link do carregador nativo perdido');
ck(!is_link($target.'/index.php'),'Link de arquivo atualizado não foi substituído com segurança');
ck(file_get_contents($target.'/config.php')==='CUSTOM-CONFIG','Configuração perdida');ck((fileperms($target.'/config.php')&0777)===0640,'Modo do config alterado');
$backup=trim(file_get_contents($target.'/.ultimo_backup'));ck(is_dir($backup.'/rel_caixa'),'Backup ausente');
list($code,$output)=runDeploy($source,'rollback',$backup,$root);ck($code===0,$output);ck(is_link($target.'/index.php'),'Rollback não preservou link');ck(file_get_contents($native)==='NATIVE-SENTINEL','Rollback seguiu link');
// A symlink in an existing destination directory must abort before activation.
mkdir($base.'/assets');symlink($base.'/assets',$target.'/img');
list($code,$output)=runDeploy($source,'install',$source,$root);ck($code!==0,'Diretório simbólico foi seguido');ck(is_link($target.'/index.php'),'Instalação recusada alterou addon');
// A root symlink must also be rejected.
symlink($root,$base.'/alias');list($code,$output)=runDeploy($source,'install',$source,$base.'/alias');ck($code!==0,'Raiz simbólica aceita');
echo "OK: configuração, modos, backups, rollback e links simbólicos; fixture: $base\n";
