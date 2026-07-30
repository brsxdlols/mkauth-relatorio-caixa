#!/bin/sh
set -eu

REPO="brsxdlols/mkauth-relatorio-caixa"
MK_ROOT="/opt/mk-auth"
ADDONS_DIR="$MK_ROOT/admin/addons"
TARGET="$ADDONS_DIR/rel_caixa"
BACKUP_ROOT="$MK_ROOT/backups"
STAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_DIR="$BACKUP_ROOT/rel_caixa-$STAMP"
TMP_DIR="$(mktemp -d /tmp/rel_caixa_install.XXXXXX)"
ARCHIVE="$TMP_DIR/source.tar.gz"

cleanup() {
    rm -rf "$TMP_DIR"
}
trap cleanup EXIT INT TERM

if [ "$(id -u)" -ne 0 ]; then
    echo "Erro: execute como root." >&2
    exit 1
fi

if [ ! -d "$ADDONS_DIR" ]; then
    echo "Erro: diretório do MK-Auth não encontrado: $ADDONS_DIR" >&2
    exit 1
fi

if ! command -v php >/dev/null 2>&1; then
    echo "Erro: PHP não encontrado." >&2
    exit 1
fi

mkdir -p "$BACKUP_DIR"

if [ -d "$TARGET" ]; then
    cp -a "$TARGET" "$BACKUP_DIR/rel_caixa"
fi

if [ -f "$ADDONS_DIR/addon_aplicativos.js" ]; then
    cp -a "$ADDONS_DIR/addon_aplicativos.js" "$BACKUP_DIR/addon_aplicativos.js"
fi

if [ -f "$ADDONS_DIR/addon.js" ]; then
    cp -a "$ADDONS_DIR/addon.js" "$BACKUP_DIR/addon.js"
fi

if command -v curl >/dev/null 2>&1; then
    curl -fL "https://github.com/$REPO/archive/refs/heads/main.tar.gz" -o "$ARCHIVE"
elif command -v wget >/dev/null 2>&1; then
    wget -O "$ARCHIVE" "https://github.com/$REPO/archive/refs/heads/main.tar.gz"
else
    echo "Erro: instale curl ou wget." >&2
    exit 1
fi

tar -xzf "$ARCHIVE" -C "$TMP_DIR"
SOURCE_DIR="$(find "$TMP_DIR" -mindepth 1 -maxdepth 1 -type d -name 'mkauth-relatorio-caixa-*' | head -n 1)"

if [ -z "$SOURCE_DIR" ] || [ ! -f "$SOURCE_DIR/addons/rel_caixa/index.php" ]; then
    echo "Erro: pacote baixado inválido." >&2
    exit 1
fi

for file in "$SOURCE_DIR"/addons/rel_caixa/*.php; do
    php -l "$file" >/dev/null
done

mkdir -p "$TARGET"
cp -a "$SOURCE_DIR/addons/rel_caixa/." "$TARGET/"
cp -a "$SOURCE_DIR/rollback.sh" "$TARGET/rollback.sh"
chmod 755 "$TARGET/rollback.sh"
find "$TARGET" -type d -exec chmod 755 {} \;
find "$TARGET" -type f -exec chmod 644 {} \;
chmod 755 "$TARGET/rollback.sh"
chown -R root:root "$TARGET"

MENU_APP="$ADDONS_DIR/addon_aplicativos.js"
MENU_DEFAULT="$ADDONS_DIR/addon.js"
MENU_LINK='    <a href="/admin/addons/rel_caixa/" class="navbar-item"><i class="bi bi-coin"></i>&nbsp; <b>Caixa</b></a>'

if [ -f "$MENU_APP" ] && grep -q 'id="menu_amsis"' "$MENU_APP"; then
    if ! grep -q '/admin/addons/rel_caixa' "$MENU_APP"; then
        awk -v link="$MENU_LINK" '
            { print }
            /id="menu_amsis"/ && !inserted { print link; inserted=1 }
        ' "$MENU_APP" > "$TMP_DIR/menu.js"
        cat "$TMP_DIR/menu.js" > "$MENU_APP"
    fi
    MENU_USED="$MENU_APP"
else
    if [ ! -f "$MENU_DEFAULT" ]; then
        touch "$MENU_DEFAULT"
    fi
    if ! grep -q 'REL_CAIXA_MENU_INI' "$MENU_DEFAULT"; then
        {
            printf '\n// REL_CAIXA_MENU_INI\n'
            printf '%s\n' 'add_menu.financeiro('\''{"plink": "'\'' + minha_url + '\''addons/rel_caixa/", "ptext": "<b>Caixa</b>"}'\'');'
            printf '%s\n' '// REL_CAIXA_MENU_FIM'
        } >> "$MENU_DEFAULT"
    fi
    MENU_USED="$MENU_DEFAULT"
fi

printf '%s\n' "$MENU_USED" > "$BACKUP_DIR/menu-usado.txt"
printf '%s\n' "$BACKUP_DIR" > "$TARGET/.ultimo_backup"

echo "Relatório de Caixa instalado com sucesso."
echo "Destino: $TARGET"
echo "Atalho: $MENU_USED"
echo "Backup: $BACKUP_DIR"
echo "Acesso: /admin/addons/rel_caixa/"

