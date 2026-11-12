#!/bin/sh
set -eu
ROOT=/opt/mk-auth
REF=main
SOURCE=
NO_MENU=
while [ "$#" -gt 0 ]; do
    case "$1" in
        --source) SOURCE="$2"; shift 2 ;;
        --root) ROOT="$2"; shift 2 ;;
        --ref) REF="$2"; shift 2 ;;
        --no-menu) NO_MENU=--no-menu; shift ;;
        *) echo "Opção inválida: $1" >&2; exit 1 ;;
    esac
done
[ "$(id -u)" -eq 0 ] || { echo 'Execute como root.' >&2; exit 1; }
command -v php >/dev/null || { echo 'PHP não encontrado.' >&2; exit 1; }
TMP_DIR="$(mktemp -d /tmp/rel_caixa_install.XXXXXX)"
trap 'rm -rf -- "$TMP_DIR"' EXIT
trap 'exit 1' HUP INT TERM
if [ -z "$SOURCE" ]; then
    case "$REF" in *[!a-zA-Z0-9._-]*|'') echo 'Ref inválida.' >&2; exit 1;; esac
    URL="https://github.com/brsxdlols/mkauth-relatorio-caixa/archive/$REF.tar.gz"
    if command -v curl >/dev/null; then curl -fL "$URL" -o "$TMP_DIR/source.tar.gz"; else wget -O "$TMP_DIR/source.tar.gz" "$URL"; fi
    tar -tzf "$TMP_DIR/source.tar.gz" > "$TMP_DIR/files"
    if grep -Eq '(^/|(^|/)\.\.(/|$))' "$TMP_DIR/files" || tar -tvzf "$TMP_DIR/source.tar.gz" | grep -Eq '^[lh]'; then echo 'Pacote contém caminho/link inseguro.' >&2; exit 1; fi
    mkdir "$TMP_DIR/extracted"
    tar -xzf "$TMP_DIR/source.tar.gz" --no-same-owner -C "$TMP_DIR/extracted"
    SOURCE="$(find "$TMP_DIR/extracted" -mindepth 1 -maxdepth 1 -type d | head -n 1)"
fi
SOURCE="$(CDPATH= cd -- "$SOURCE" && pwd -P)"
[ -f "$SOURCE/deploy.php" ] || { echo 'deploy.php ausente.' >&2; exit 1; }
[ -z "$(find -P "$SOURCE" -type l -print -quit)" ] || { echo 'Pacote com link simbólico recusado.' >&2; exit 1; }
find "$SOURCE/addons/rel_caixa" -type f -name '*.php' -exec php -l {} \; > "$TMP_DIR/lint"
# find -exec does not propagate a failed command status; explicitly validate each PHP file.
find "$SOURCE/addons/rel_caixa" -type f -name '*.php' -exec sh -c 'for file do php -l "$file" >/dev/null || exit 1; done' sh {} +
php -l "$SOURCE/deploy.php" >/dev/null
if [ -n "$NO_MENU" ]; then php "$SOURCE/deploy.php" install "$SOURCE" "$ROOT" --no-menu; else php "$SOURCE/deploy.php" install "$SOURCE" "$ROOT"; fi
