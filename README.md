# Relatório de Caixa para MK-Auth

Addon **Relatório de Caixa 3.4.2** para análise financeira no MK-Auth, com
identificação de cliente, login, entradas, saídas, gráficos, evolução,
ticket médio e anomalias.

Esta distribuição inclui a correção para históricos nos formatos
`titulo 123`, `titulo: 123`, `título: 123` e `titulo do titulo: 123`, usando
o ID interno do título para localizar o login e o cliente.

## Instalação automática

Execute como `root` no servidor MK-Auth:

```bash
curl -fsSL https://raw.githubusercontent.com/brsxdlols/mkauth-relatorio-caixa/main/install.sh | bash
```

O instalador:

- valida PHP e a estrutura esperada do MK-Auth;
- cria backup do addon e do arquivo de menu;
- instala em `/opt/mk-auth/admin/addons/rel_caixa`;
- adiciona o atalho **Caixa** ao menu APLICATIVOS existente;
- usa o menu Financeiro como fallback quando APLICATIVOS não existe;
- não altera dados nem schema do banco;
- evita atalhos duplicados.

Após instalar, acesse:

`https://SEU-MK-AUTH/admin/addons/rel_caixa/`

Pode ser necessário atualizar a página com `Ctrl+F5`.

## Atualização

O mesmo comando de instalação pode ser executado novamente. Um novo backup
é criado antes da atualização.

## Rollback

Consulte os backups disponíveis:

```bash
ls -1dt /opt/mk-auth/backups/rel_caixa-*
```

Restaure o backup mais recente:

```bash
/opt/mk-auth/admin/addons/rel_caixa/rollback.sh
```

Ou informe um backup específico:

```bash
/opt/mk-auth/admin/addons/rel_caixa/rollback.sh \
  /opt/mk-auth/backups/rel_caixa-AAAAMMDD_HHMMSS
```

## Compatibilidade

- PHP 7.3 ou superior
- MK-Auth com diretório `/opt/mk-auth/admin/addons`
- MariaDB/MySQL com as tabelas padrão `sis_caixa`, `sis_lanc` e `sis_cliente`

## Segurança

O instalador somente grava arquivos do addon e a entrada de menu. Ele não
executa `INSERT`, `UPDATE`, `DELETE` ou alterações de schema no banco.

