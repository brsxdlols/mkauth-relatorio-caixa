#!/bin/sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

test -f "$ROOT/addons/rel_caixa/index.php"
test -f "$ROOT/addons/rel_caixa/manifest.json"
test -f "$ROOT/install.sh"
test -f "$ROOT/rollback.sh"

for file in "$ROOT"/addons/rel_caixa/*.php; do
    php -l "$file"
done

sh -n "$ROOT/install.sh"
sh -n "$ROOT/rollback.sh"
grep -q '"version": 3.4' "$ROOT/addons/rel_caixa/manifest.json"
grep -q "titulo.*\\\\s.*:?" "$ROOT/addons/rel_caixa/index.php"

echo "Validação concluída."

