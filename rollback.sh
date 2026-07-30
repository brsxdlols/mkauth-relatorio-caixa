#!/bin/sh
set -eu

MK_ROOT="/opt/mk-auth"
ADDONS_DIR="$MK_ROOT/admin/addons"
TARGET="$ADDONS_DIR/rel_caixa"
BACKUP_ROOT="$MK_ROOT/backups"
BACKUP_DIR="${1:-}"

if [ "$(id -u)" -ne 0 ]; then
    echo "Erro: execute como root." >&2
    exit 1
fi

if [ -z "$BACKUP_DIR" ]; then
    BACKUP_DIR="$(find "$BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d -name 'rel_caixa-*' | sort -r | head -n 1)"
fi

if [ -z "$BACKUP_DIR" ] || [ ! -d "$BACKUP_DIR" ]; then
    echo "Erro: backup não encontrado." >&2
    exit 1
fi

if [ -d "$BACKUP_DIR/rel_caixa" ]; then
    rm -rf "$TARGET"
    cp -a "$BACKUP_DIR/rel_caixa" "$TARGET"
else
    echo "Aviso: o backup indica que o addon não existia antes da instalação."
    rm -rf "$TARGET"
fi

if [ -f "$BACKUP_DIR/addon_aplicativos.js" ]; then
    cp -a "$BACKUP_DIR/addon_aplicativos.js" "$ADDONS_DIR/addon_aplicativos.js"
fi

if [ -f "$BACKUP_DIR/addon.js" ]; then
    cp -a "$BACKUP_DIR/addon.js" "$ADDONS_DIR/addon.js"
fi

echo "Rollback concluído a partir de: $BACKUP_DIR"

