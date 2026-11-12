<?php
// Default bridge for fresh installations. Existing loaders (including native
// MK-Auth symlinks) are preserved by deploy.php.
$rcRoot = dirname(__DIR__, 3);
$rcNative = is_file($rcRoot . '/include/addons.inc.hhvm') ? $rcRoot . '/include/addons.inc.hhvm' : $rcRoot . '/include/addons.inc.php';
if (!is_file($rcNative)) { http_response_code(500); exit('Carregador nativo de addons não encontrado.'); }
require_once $rcNative;
$Manifest = json_decode(file_get_contents(__DIR__ . '/manifest.json'));
