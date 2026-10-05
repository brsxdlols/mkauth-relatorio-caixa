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
grep -q '"version": "3.4.2"' "$ROOT/addons/rel_caixa/manifest.json"

php -r '
$casos = array(
    "recebimento do titulo 2300 / cliente",
    "Descontado a tarifa do titulo: 2300 e cliente",
    "Recebimento do título: 2300 / cliente",
    "recebimento do titulo do titulo: 2300, cliente"
);
foreach ($casos as $historico) {
    if (!preg_match("/t[ií]tulo(?:[^0-9]{0,30})(\\d+)/iu", $historico, $m) || $m[1] !== "2300") {
        fwrite(STDERR, "Falha ao extrair ID de: $historico\n");
        exit(1);
    }
}
'

echo "Validação concluída."

