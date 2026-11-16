# 3.7.2 — 2026-11-16

- Retoma o layout clássico compacto com faixa superior, abas, cards e cabeçalho de tabela em azul do datacenter.
- Preserva todas as guias, filtros, modelos, nomes completos e rolagem horizontal.
- Tema limitado ao addon, sem modificar o menu nativo do MK-Auth.

# 3.7.1 — 2026-11-12

- Prepara a pasta dos modelos durante a instalação, com proprietário do PHP e modo 0700; preserva o conteúdo e recusa links.
- Aceita REL_CAIXA_WEB_USER para instalações com usuário PHP personalizado.
- Trata criação concorrente da pasta sem falha indevida.
- Informa quando o período não tem movimentos e oferece acesso ao último dia registrado.
- Testa o provisionamento de modelos também em instalação nova.

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
