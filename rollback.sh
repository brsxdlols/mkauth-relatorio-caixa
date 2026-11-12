#!/bin/sh
set -eu
[ "$(id -u)" -eq 0 ] || { echo 'Execute como root.' >&2; exit 1; }
SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd -P)"
ROOT="${REL_CAIXA_ROOT:-/opt/mk-auth}"
BACKUP="${1:-}"
if [ -z "$BACKUP" ]; then
    [ ! -L "$SCRIPT_DIR/.ultimo_backup" ] || { echo 'Ponteiro simbólico recusado.' >&2; exit 1; }
    BACKUP="$(cat "$SCRIPT_DIR/.ultimo_backup")"
fi
php "$SCRIPT_DIR/deploy.php" rollback "$BACKUP" "$ROOT" --no-menu
