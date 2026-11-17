# 3.7.10 — 2026-11-17

- Atalho CAIXA HOJE abre a data atual, com todos os recebimentos e sem filtros anteriores.
- Renomeia o botão de taxas para explicitar seu efeito na tabela e esclarece que os cards sempre incluem as taxas.

# 3.7.9 — 2026-11-17

- Totais de entradas, acréscimos e saídas no rodapé da tabela, respeitando busca, cards e visibilidade das tarifas.
- Descrição ao passar o mouse nos acréscimos pagos, esclarecendo juros e multa já incluídos na entrada.

# 3.7.8 — 2026-11-17

- Une recebimento e taxa de boleto em uma linha quando o par de título, data e usuário é único; preserva lançamentos originais e mostra a descrição da taxa ao passar o mouse.

- Card de acréscimos agora filtra recebimentos com acréscimos positivos, com suporte a teclado, preservando os totais do período.
- Destaca em verde o valor do card e os acréscimos positivos na tabela.

# 3.7.7 — 2026-11-17

- Coluna e card compacto de Acréscimos pagos: diferença positiva entre valor pago e original, já incluída nas entradas.
- Vincula apenas recebimento único com valor e data compatíveis com o título pago; casos sem identificação ficam explícitos.
- Exporta o detalhamento e total de acréscimos sem somá-los novamente ao saldo.

# 3.7.6 — 2026-11-17

- Compensa a altura do menu fixo uma única vez no html do Caixa, eliminando o espaçador nativo antigo apenas nesta página.
- Mantém 12px de respiro antes do cabeçalho e acompanha alterações do menu e largura da tela. Sem alterar CSS global ou arquivos nativos.

# 3.7.5 — 2026-11-16

- Exibe resumo dos filtros aplicados e aviso de resultado vazio antes dos totais.
- Simplifica nomes dos filtros e modelos salvos, com datas no padrão brasileiro.
- Consulta de filtros salvos não cria arquivos vazios; criação informa falha de gravação e atualiza cache de metadados. Preserva arquivos existentes.

# 3.7.4 — 2026-11-16

- Renomeia a guia para CAIXA PERSONALIZADO e explica os filtros combinados por usuário, recebimento e conta, com modelos salvos.

# 3.7.3 — 2026-11-16

- Mantém entradas, saídas, recebimentos e resultado completos do período nos cards, independentemente dos filtros visuais da tabela.
- Exibe entradas e saídas juntas ao abrir a tela; ocultar tarifas não altera o resultado do caixa.
- Esclarece o escopo da tabela, CSV e impressão.

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
