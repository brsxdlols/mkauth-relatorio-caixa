# 3.7.0 — 2026-11-12

- Caixa da loja com filtros múltiplos de recebimento, conta e usuário, catálogo nativo e formas utilizadas.
- Modelos salvos por usuário no servidor, sem alterações em tabelas financeiras.
- Resumos, CSV e impressão da seleção completa.
- Corrige identificação e botão de tarifas; totais dos cards acompanham linhas visíveis.
- Testes de tarifas, filtros, valores, persistência e isolamento de modelos.

# 3.6.0 — 2026-11-07

- Adiciona Títulos por banco/carteira e Problemas nos boletos.
- Identifica carteira pelo título, separando a conta atual do cliente e o banco do boleto.
- Adiciona visão de pendentes completa para acompanhar carteiras antigas, filtros e CSV.
- Diagnóstico local sem tentativa automática de emissão ou alteração financeira.
- Instalador com staging protegido, backup, preservação de configuração/permissões e proteção contra links simbólicos.
- Interface `--source`, `--root`, `--ref` e `--no-menu` para integração com a suíte dashboard/busca.
- Rollback preserva menus compartilhados e faz novo backup antes de restaurar.
- Testes em PHP 7.3/8.0 e matriz CI 7.3/8.0/8.3.
