<?php
// Filesystem-only installer shared by standalone installation and suite integration.
// PHP 7.3+. Never connects to a database.
function cx_exists($path) { return file_exists($path) || is_link($path); }
function cx_physical($path) {
    $parts = explode('/', trim($path, '/')); $current = '';
    foreach ($parts as $part) {
        if ($part === '' || $part === '.' || $part === '..') throw new RuntimeException('Caminho inválido.');
        $current .= '/' . $part;
        if (is_link($current)) throw new RuntimeException('Diretório ou destino simbólico não permitido: ' . $current);
    }
}
function cx_metadata($source, $dest) {
    if (is_link($source)) return;
    if (!chmod($dest, fileperms($source) & 0777)) throw new RuntimeException('Falha ao preservar modo: ' . $dest);
    if (!chown($dest, fileowner($source)) || !chgrp($dest, filegroup($source))) throw new RuntimeException('Falha ao preservar proprietário: ' . $dest);
}
function cx_copy($source, $dest, $links, $protectRoot = false) {
    if (cx_exists($dest)) throw new RuntimeException('Destino já existe: ' . $dest);
    if (is_link($source)) {
        if (!$links || !symlink(readlink($source), $dest)) throw new RuntimeException('Link no pacote ou falha ao preservar link: ' . $source);
    } elseif (is_dir($source)) {
        if (!mkdir($dest, $protectRoot ? 0700 : 0755)) throw new RuntimeException('Falha ao criar diretório.');
        foreach (new DirectoryIterator($source) as $entry) if (!$entry->isDot()) cx_copy($entry->getPathname(), $dest . '/' . $entry->getFilename(), $links);
        if (!$protectRoot) cx_metadata($source, $dest);
    } elseif (is_file($source)) {
        if (!copy($source, $dest)) throw new RuntimeException('Falha na cópia.');
        cx_metadata($source, $dest);
    } else throw new RuntimeException('Tipo de arquivo não permitido: ' . $source);
}
function cx_overlay($source, $dest, $relative = '') {
    foreach (new DirectoryIterator($source) as $entry) {
        if ($entry->isDot()) continue;
        $name = $entry->getFilename(); $src = $entry->getPathname(); $dst = $dest . '/' . $name; $rel = $relative . $name;
        if (is_link($src)) throw new RuntimeException('Link não permitido no pacote: ' . $rel);
        if ($relative === '' && in_array($name, array('config.php', 'addons.class.php'), true) && cx_exists($dst)) continue;
        if ($entry->isDir()) {
            if (is_link($dst) || (cx_exists($dst) && !is_dir($dst))) throw new RuntimeException('Diretório de destino inseguro: ' . $rel);
            if (!is_dir($dst) && !mkdir($dst, 0755)) throw new RuntimeException('Falha ao criar diretório.');
            cx_overlay($src, $dst, $rel . '/');
        } elseif ($entry->isFile()) {
            if (is_dir($dst) && !is_link($dst)) throw new RuntimeException('Conflito de arquivo/diretório: ' . $rel);
            $tmp = $dest . '/.cx-' . bin2hex(random_bytes(6));
            if (!copy($src, $tmp)) throw new RuntimeException('Falha ao preparar arquivo.');
            if (is_file($dst) && !is_link($dst)) cx_metadata($dst, $tmp); else chmod($tmp, 0644);
            // Rename replaces a link itself; it never opens or writes its target.
            if (!rename($tmp, $dst)) throw new RuntimeException('Falha ao substituir arquivo.');
        } else throw new RuntimeException('Arquivo especial no pacote.');
    }
}
function cx_remove($path) {
    if (is_link($path) || is_file($path)) { if (!unlink($path)) throw new RuntimeException('Falha ao remover temporário.'); return; }
    if (!is_dir($path)) return;
    foreach (new DirectoryIterator($path) as $entry) if (!$entry->isDot()) cx_remove($entry->getPathname());
    if (!rmdir($path)) throw new RuntimeException('Falha ao remover diretório temporário.');
}
function cx_prepare_templates($stage, $target, $addons) {
    $uid = null; $gid = null;
    $explicit = getenv('REL_CAIXA_WEB_USER');
    if ($explicit !== false && $explicit !== '') {
        $user = function_exists('posix_getpwnam') ? posix_getpwnam($explicit) : false;
        if (!$user || $user['uid'] === 0) throw new RuntimeException('REL_CAIXA_WEB_USER deve indicar o usuário não root do PHP.');
        $uid = $user['uid']; $gid = $user['gid'];
    } else {
        foreach (array($target, $addons) as $candidate) {
            if (is_dir($candidate) && fileowner($candidate) > 0) { $uid = fileowner($candidate); $gid = filegroup($candidate); break; }
        }
        if ($uid === null && function_exists('posix_getpwnam')) {
            $user = posix_getpwnam('www-data');
            if ($user) { $uid = $user['uid']; $gid = $user['gid']; }
        }
    }
    if ($uid === null) throw new RuntimeException('Informe REL_CAIXA_WEB_USER para preparar a pasta dos modelos.');
    foreach (array($stage . '/data', $stage . '/data/loja') as $dir) {
        if (is_link($dir) || (cx_exists($dir) && !is_dir($dir))) throw new RuntimeException('Pasta dos modelos insegura: ' . $dir);
        if (!is_dir($dir) && !mkdir($dir, 0700)) throw new RuntimeException('Falha ao preparar pasta dos modelos.');
        if (!chown($dir, $uid) || !chgrp($dir, $gid) || !chmod($dir, 0700)) throw new RuntimeException('Falha ao configurar acesso aos modelos.');
    }
    foreach (new DirectoryIterator($stage . '/data/loja') as $entry) {
        if ($entry->isDot()) continue;
        if ($entry->isLink()) throw new RuntimeException('Link na pasta de modelos recusado.');
        if ($entry->isFile() && preg_match('/^[a-f0-9]{64}\.php$/', $entry->getFilename())) {
            if (!chown($entry->getPathname(), $uid) || !chgrp($entry->getPathname(), $gid) || !chmod($entry->getPathname(), 0600)) throw new RuntimeException('Falha ao configurar modelo existente.');
        }
    }
}
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
if (version_compare(PHP_VERSION, '7.3', '<')) { fwrite(STDERR, "PHP 7.3+ necessário.\n"); exit(1); }
$stage = null; $old = null; $target = null; $installed = false;
try {
    $mode = isset($argv[1]) ? $argv[1] : '';
    $source = isset($argv[2]) ? rtrim($argv[2], '/') : '';
    $root = isset($argv[3]) ? rtrim($argv[3], '/') : '/opt/mk-auth';
    $noMenu = in_array('--no-menu', $argv, true);
    if (!in_array($mode, array('install','rollback'), true) || $source === '' || $source[0] !== '/' || $root === '' || $root[0] !== '/') throw new RuntimeException('Uso: deploy.php install|rollback /fonte /opt/mk-auth [--no-menu]');
    $addons = $root . '/admin/addons'; $target = $addons . '/rel_caixa'; $backups = $root . '/backups';
    cx_physical($addons); cx_physical($target); cx_physical($backups);
    if (!is_dir($addons)) throw new RuntimeException('Diretório addons ausente.');
    $lockPath = $addons . '/.rel_caixa-install.lock';
    if (is_link($lockPath) || (is_file($lockPath) && file_get_contents($lockPath) !== '')) throw new RuntimeException('Arquivo de trava inseguro.');
    $lock = fopen($lockPath, 'c'); if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Outra instalação em andamento.');
    if (cx_exists($target) && !is_dir($target)) throw new RuntimeException('Destino não é diretório.');
    if (!is_dir($backups) && !mkdir($backups, 0700)) throw new RuntimeException('Falha ao criar backups.');
    $backup = $backups . '/rel_caixa-' . date('Ymd_His') . '-' . bin2hex(random_bytes(4));
    if (!mkdir($backup, 0700)) throw new RuntimeException('Falha ao criar backup.');
    if (is_dir($target)) cx_copy($target, $backup . '/rel_caixa', true);
    foreach (array('addon.js','addon_aplicativos.js') as $menuName) if (cx_exists($addons . '/' . $menuName)) cx_copy($addons . '/' . $menuName, $backup . '/' . $menuName, true);
    $stage = $addons . '/.rel_caixa-stage-' . bin2hex(random_bytes(6));
    if ($mode === 'install') {
        if (!is_dir($source . '/addons/rel_caixa') || !is_file($source . '/addons/rel_caixa/index.php')) throw new RuntimeException('Fonte inválida.');
        // Validate the entire package before copying anything into the staging tree.
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $entry) if ($entry->isLink()) throw new RuntimeException('Pacote contém link simbólico.');
        if (is_dir($target)) cx_copy($target, $stage, true, true); else mkdir($stage, 0700);
        cx_overlay($source . '/addons/rel_caixa', $stage);
        foreach (array('rollback.sh','deploy.php') as $file) {
            $tempSource = $source . '/' . $file; $dest = $stage . '/' . $file;
            if (!is_file($tempSource) || is_link($tempSource)) throw new RuntimeException('Arquivo de instalação ausente.');
            if (cx_exists($dest)) { if (is_dir($dest) && !is_link($dest)) throw new RuntimeException('Conflito de destino.'); unlink($dest); }
            if (!copy($tempSource, $dest)) throw new RuntimeException('Falha ao copiar instalador.');
            chmod($dest, $file === 'rollback.sh' ? 0755 : 0644);
        }
    } else {
        cx_physical($source);
        if (dirname($source) !== $backups || !is_dir($source . '/rel_caixa') || is_link($source . '/rel_caixa')) throw new RuntimeException('Backup de atualização inválido. Primeira instalação deve ser removida manualmente.');
        cx_copy($source . '/rel_caixa', $stage, true, true);
    }
    if ($mode === 'install') cx_prepare_templates($stage, $target, $addons);
    // Never write through a symlink in the backup pointer.
    $pointer = $stage . '/.ultimo_backup';
    if (cx_exists($pointer)) { if (is_dir($pointer) && !is_link($pointer)) throw new RuntimeException('Ponteiro de backup inválido.'); unlink($pointer); }
    if (file_put_contents($pointer, $backup . "\n") === false) throw new RuntimeException('Falha no ponteiro de backup.');
    chmod($pointer, 0600);
    $old = $addons . '/.rel_caixa-previous-' . bin2hex(random_bytes(6));
    if (is_dir($target) && !rename($target, $old)) throw new RuntimeException('Falha ao reservar versão anterior.');
    if (!rename($stage, $target)) { if (is_dir($old)) rename($old, $target); throw new RuntimeException('Falha ao ativar versão.'); }
    $stage = null; $installed = true;
    // Menu changes are optional; do not touch shared/symlinked menus or restore old shared menus on rollback.
    if (!$noMenu && $mode === 'install') {
        $app = $addons . '/addon_aplicativos.js'; $default = $addons . '/addon.js';
        $menu = !is_link($app) && is_file($app) && strpos(file_get_contents($app), 'id="menu_amsis"') !== false ? $app : $default;
        if (is_link($menu) || (cx_exists($menu) && !is_file($menu)) || (is_file($menu) && stat($menu)['nlink'] > 1)) {
            echo "Menu compartilhado/link preservado; acesso direto: /admin/addons/rel_caixa/\n";
        } else {
            $text = is_file($menu) ? file_get_contents($menu) : '';
            if (strpos($text, 'addons/rel_caixa') === false) {
                if ($menu === $app) $text = preg_replace('/(^.*id="menu_amsis".*$)/m', '$1' . "\n" . '    <a href="/admin/addons/rel_caixa/" class="navbar-item"><b>Caixa</b></a>', $text, 1);
                else $text .= "\n// REL_CAIXA_MENU_INI\nadd_menu.financeiro('{\"plink\": \"' + minha_url + 'addons/rel_caixa/\", \"ptext\": \"<b>Caixa</b>\"}');\n// REL_CAIXA_MENU_FIM\n";
                $tmpMenu = $addons . '/.rel_caixa-menu-' . bin2hex(random_bytes(6));
                if (file_put_contents($tmpMenu, $text) === false) throw new RuntimeException('Falha ao preparar menu.');
                if (is_file($menu)) cx_metadata($menu, $tmpMenu); else chmod($tmpMenu, 0644);
                if (!rename($tmpMenu, $menu)) throw new RuntimeException('Falha ao atualizar menu.');
            }
        }
    }
    if ($mode === 'rollback') cx_metadata($source . '/rel_caixa', $target);
    elseif (is_dir($old)) cx_metadata($old, $target);
    else chmod($target, 0755);
    if (is_dir($old)) cx_remove($old);
    echo "Caixa atualizado. Backup: $backup\nDestino: $target\n";
} catch (Throwable $error) {
    if ($stage && is_dir($stage)) cx_remove($stage);
    if (!$installed && $old && is_dir($old) && !cx_exists($target)) rename($old, $target);
    fwrite(STDERR, 'Erro: ' . $error->getMessage() . ($installed ? ' Addon ativado; confira menu e backup.' : '') . "\n"); exit(1);
}
