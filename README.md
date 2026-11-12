# Atualização 3.7.1

O instalador prepara `data/loja` antes do primeiro acesso, com modo 0700 e acesso do PHP. Mantém modelos existentes e recusa links. Detecta o proprietário não root do addon/diretório de addons; se necessário, usa `www-data`. Em pools personalizados, execute `REL_CAIXA_WEB_USER=usuario_php sh install.sh ...`. A correção só muda permissões dos arquivos de preferências do addon, sem alterar permissões de usuários no MK-Auth ou configurações nativas.

Períodos sem movimentos agora mostram a data do último lançamento e um link explícito para consultar esse dia sem filtros de forma/conta/usuário.

# Caixa 3.7.0 — Caixa da loja e modelos

Esta versão acrescenta a guia **Caixa da loja**, sem duplicar lançamentos ou separar fisicamente saldos no banco nativo.

- Filtros combinados por várias formas de pagamento, contas do título e usuários do lançamento.
- As formas incluem as opções padrão do MK-Auth, os itens personalizados de `dados/item.db` (`titulos_formapag`, aberto somente para leitura) e os valores utilizados em `sis_lanc.formapag`.
- Dinheiro e PIX manual são separados das opções de integração, como `boleto-lytex` e `pix-lytex`. Use o atalho Dinheiro + PIX ou monte sua própria seleção.
- Resumos por usuário e forma, CSV do resultado completo filtrado, impressão e paginação.
- Modelos por usuário persistidos no servidor em `addons/rel_caixa/data/loja/`, fora das tabelas financeiras. Sem salvar datas, o modelo abre no dia atual. A instalação e seus backups preservam esses arquivos personalizados.
- As preferências usam arquivos `.php` com guarda de acesso, conteúdo codificado, permissão 0600, bloqueio de escrita e proteção contra links. O diretório precisa ser gravável pelo usuário do PHP. Falhas para salvar são exibidas; não há fallback silencioso.
- Salvamento, aplicação e exclusão de modelos exigem sessão e a permissão existente do addon, além de token CSRF nas operações POST. Um usuário não acessa modelos de outro.
- Correção do botão de tarifas na guia Movimentações: reconhece Lytex, Sicredi e demais históricos de tarifa. Sem tarifas no período, o botão informa isso. Os cards dessa guia somam as linhas visíveis, acompanhando a pesquisa, tipo de movimento e visibilidade de tarifas.

**Origem das informações:** os valores e o usuário são do lançamento em `sis_caixa`; a forma de pagamento e a conta são do título vinculado pelo ID no histórico. Alterações posteriores no título podem afetar essa classificação. Movimentos sem vínculo permanecem não identificados. Não se presume que todo lançamento `aut` seja bancário: o MK-Auth também usa esse marcador em recebimentos manuais.

A guia é uma conferência; não recebe títulos, não cria um segundo lançamento, não emite boletos, não altera carteira do cliente e não constitui saldo físico com abertura/fechamento e sangrias. Para conferir a loja, selecione as formas e os atendentes desejados.

Compatibilidade: PHP 7.3 e 8.x. A extensão SQLite3 é opcional; sem ela, o addon avisa que o catálogo personalizado não pôde ser lido, preservando opções padrão e utilizadas. Testes: `sh scripts/validate.sh`.

Para integrar à suíte, use o commit fixado da publicação 3.7.0 e execute o instalador completo com `--source /checkout --no-menu`. Nunca copie sobre links do carregador nativo. A documentação da instalação segura abaixo continua válida; a versão atual de VERSION e manifest.json é 3.7.0.

---

# Caixa para MK-Auth — 3.6.0

Fonte oficial: `brsxdlols/mkauth-relatorio-caixa`.

## Novidades

- Títulos por banco e carteira: carteira vinculada ao título (`sis_lanc.numconta`), banco identificado na linha digitável/código de barras e conta atual do cliente em colunas separadas.
- Filtros por carteira, banco, cliente/login/título, situação (pendentes, a receber, vencidos, pagos, outras) e datas de vencimento ou pagamento.
- Indicador de pendentes sem limite de datas/cliente/situação para acompanhar o encerramento de carteiras antigas. Os cards e a lista respeitam todos os filtros.
- Página de problemas: marcador ERROR, falta de conta, e-mail ausente/inválido, documento ausente, emissão não confirmada e último retorno Efí quando houver chave externa vinculável.
- CSV de todo o resultado filtrado; listagem paginada de 50 itens.
- Mantém as funções de caixa, resumos, conferência, gráficos e impressão anteriores.

A identificação de banco não é prova de registro ou liquidação. Ausência de nosso número significa registro não confirmado, não necessariamente rejeição. Erros sem retorno vinculado são apresentados sem inventar causa. A consulta não abre endpoints de emissão, não gera boletos, não recebe pagamentos e não muda contas de clientes.

## Integração com a suíte dashboard/busca

A suíte `brsxdlols/mkauth-dashboard-busca-suite` deve fixar o commit informado na publicação desta versão e baixar a árvore completa desse commit. Instale com:

```sh
sh /caminho/do/checkout-caixa/install.sh \
  --source /caminho/do/checkout-caixa \
  --root /opt/mk-auth \
  --no-menu
```

`--no-menu` deixa a organização do menu a cargo da suíte. O pacote precisa conter `deploy.php`, `rollback.sh` e `addons/rel_caixa/`. Não copie o conteúdo do addon diretamente sobre destinos existentes, especialmente com `cp` seguindo links.

Alternativa pelo instalador oficial, fixando uma revisão:

```sh
sh install.sh --ref COMMIT_COMPLETO --no-menu
```

O `install.sh` usado também deve vir do commit fixado. O download padrão sem `--source` vem exclusivamente de `brsxdlols/mkauth-relatorio-caixa`.

A versão está em `addons/rel_caixa/manifest.json` (`3.6.0`). Rota do addon: `/admin/addons/rel_caixa/`. Guias novas: `?tab=carteiras` e `?tab=problemas`.

## Atualização segura

Execute como root em Linux/MK-Auth, com PHP CLI 7.3+:

```sh
curl -fsSL https://raw.githubusercontent.com/brsxdlols/mkauth-relatorio-caixa/main/install.sh | sh
```

Antes da ativação:

1. Valida PHP, caminhos e ausência de links no pacote.
2. Cria backup completo do addon e cópia dos menus em `/opt/mk-auth/backups/rel_caixa-DATA-ID/`.
3. Prepara o addon em diretório temporário protegido, no mesmo filesystem do destino.
4. Preserva `config.php`, carregador `addons.class.php` existente (inclusive link para o carregador nativo), arquivos personalizados e modos/proprietários dos arquivos substituídos.
5. Ativa a versão preparada por renomeação de diretório; guarda o caminho do backup em `.ultimo_backup`.

A raiz, os diretórios ancestrais e o destino do addon não podem ser links. Links em diretórios que receberiam arquivos do pacote causam recusa antes da ativação. Links em arquivos atualizados são substituídos no staging, sem abrir o arquivo apontado. Configuração e carregador existentes são preservados, sem escrever através de links. Links desconhecidos preservados nunca são percorridos pela cópia ou limpeza.

Menus simbólicos/compartilhados são preservados; use `--no-menu` para integração com suíte. O atalho pode ser cadastrado pela suíte apontando para a rota acima.

O instalador não acessa banco de dados e não altera tabelas, títulos, saldos nem permissões de usuários. A autorização continua usando a sessão MK-Auth e a permissão existente `perm_relFat` do `config.php` preservado.

## Rollback

```sh
/opt/mk-auth/admin/addons/rel_caixa/rollback.sh
# ou
/opt/mk-auth/admin/addons/rel_caixa/rollback.sh \
  /opt/mk-auth/backups/rel_caixa-DATA-ID
```

Restaura o snapshot do addon e cria um backup de segurança do estado atual. Não restaura menus compartilhados automaticamente para não desfazer mudanças da suíte ou de outros addons. Para uma primeira instalação sem addon anterior, a remoção exige revisão manual. `REL_CAIXA_ROOT` pode selecionar uma raiz alternativa nos testes.

## Compatibilidade e limites

- PHP 7.3 e PHP 8.x; testes locais executados em 7.3 e 8.0, CI configurada para 7.3, 8.0 e 8.3.
- MariaDB/MySQL padrão MK-Auth: `sis_lanc`, `sis_cliente`, `sis_boleto`; `sis_gneterros` é opcional.
- A listagem considera títulos não excluídos. O banco original nem sempre está disponível: nesse caso mostra “Banco não identificado”, sem assumir a conta atual do cliente.
- A carteira do título é o vínculo salvo atualmente em `sis_lanc.numconta`; se esse campo tiver sido editado retroativamente, o addon não pode reconstruir o histórico perdido.
- Os dados são consultados localmente; não há conciliação on-line com bancos nem agendamento de novas emissões.
- Para calcular a visão completa, a consulta carrega os títulos não excluídos em memória. Em bases muito grandes, avaliar paginação/agregação no servidor antes de ampliar a implantação.

## Validação

```sh
sh scripts/validate.sh
```

Os testes do instalador usam apenas diretórios temporários e arquivos sentinela. Não acessam o banco do MK-Auth. Cobrem configuração, modos, backup, rollback, links no carregador, arquivo vinculado a alvo nativo e diretórios/raiz simbólicos. Os testes de títulos cobrem estados, banco, diagnósticos, datas e CSV.
