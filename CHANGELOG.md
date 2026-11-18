# 3.7.18 — 2026-11-17

- Atualiza o identificador de cache dos estilos e scripts a cada instalação, inclusive no Safari.

- Atalhos CAIXA SEMANA (domingo a sábado) e CAIXA MÊS (mês atual completo), com datas explícitas e filtros limpos.

- Baixar PDF direto nos modelos Simples e Detalhado, com geração local no navegador e sem cabeçalhos/URLs do Chrome.

- Menu do Caixa por seletor no mobile, sincronizado com todas as guias.
- Ajusta cabeçalho, atalhos, filtros, cards, gráficos, tabelas e modelos ao toque e à largura do celular.
- Prévia dos relatórios adapta resumos e tabelas ao mobile sem alterar a impressão. Regras restritas à página do addon.

# 3.7.17 — 2026-11-17

- Valores sem fundos coloridos: entradas verdes, acréscimos azuis e tarifas/saídas vermelhas.
- Rodapé da tela com TOTAL alinhado à direita e subtotal do caixa. Relatórios incluem subtotal por linha e no fechamento.
- Orienta desativar cabeçalhos e rodapés do navegador para não imprimir a URL.

# 3.7.16 — 2026-11-17

- Relatórios simples e detalhados mostram coluna e total de tarifas de boleto separados da saída total.
- Resumos detalhados incluem tarifas por conta, recebimento, motivo e usuário.
- Fechamento informa tarifas e outras saídas sem descontar taxas novamente do resultado.

# 3.7.15 — 2026-11-17

- Usa a logo personalizada de mkfiles/logo.jpg, em vez da marca padrão do sistema.
- Exibe primeiro o nome do provedor, depois a razão social e o CNPJ em linhas separadas.

# 3.7.14 — 2026-11-17

- Relatórios simples e detalhados começam com nome, CNPJ e logo disponível do provedor, lidos do MK-AUTH.
- Botão Fechar da prévia com ícone, bordas arredondadas e estados de interação em azul.

# 3.7.13 — 2026-11-17

- Corrige sobreposição dos cabeçalhos da tabela com quebra e distribuição das colunas.
- Centraliza e estiliza a escolha do relatório, com prévia flexível e uma única rolagem interna.
- Detalhado organiza resumos em duas colunas e separa lançamentos longos em nova página na impressão, com fonte legível.

# 3.7.12 — 2026-11-17

- Mantém os cinco cards em uma linha no desktop e remove larguras mínimas que expandiam a página.
- Ajusta colunas e quebra de nomes da tabela ao desktop; em telas pequenas a rolagem fica apenas dentro da tabela.

# 3.7.11 — 2026-11-17

- Centraliza CAIXA HOJE e CONFERÊNCIA COMPLETA em cards acima dos filtros. O segundo abre e destaca resumos e alertas do período inteiro.

- Corrige contraste e alinhamento dos totais da tabela.
- Impressão com escolha Simples ou Detalhado e prévia independente do CSS nativo, evitando página em branco.
- Detalhado inclui resumos por conta, recebimento, motivo e usuário. Ambos respeitam filtros aplicados e exibem entradas, saídas, acréscimos e resultado sem duplicar valores.

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
