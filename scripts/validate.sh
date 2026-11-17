#!/bin/sh
set -eu
ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
for file in "$ROOT"/addons/rel_caixa/*.php "$ROOT/deploy.php" "$ROOT"/tests/*.php; do php -l "$file" >/dev/null; done
sh -n "$ROOT/install.sh"
sh -n "$ROOT/rollback.sh"
grep -q '"version": "3.7.7"' "$ROOT/addons/rel_caixa/manifest.json"
test "$(cat "$ROOT/VERSION")" = 3.7.7
php "$ROOT/tests/titulos.php"
php "$ROOT/tests/install.php"
php "$ROOT/tests/loja.php"
php "$ROOT/tests/acrescimos.php"
echo "Validation complete."
