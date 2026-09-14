# MyPocket — com banco de dados (versão Usuário/Carteira/Categoria)

Este é o seu projeto (o que tem `Usuario`, `Categoria`, `FechamentoMensal` e
`Calendario.php`) com o banco de dados montado a partir do
`Modelagem_Diagramas_e_Dicionario.xlsx` que você me passou, e as classes já
conversando com esse banco.

## O banco (`banco/schema.sql`)

Segue exatamente o dicionário de dados que veio na planilha:

- **usuario** (id_usuario, nome, email único, senha)
- **carteira** (id_carteira, id_usuario FK, nome_carteira, tipo_carteira, saldo_atual)
- **categoria** (id_categoria, nome_categoria, tipo_categoria ENUM('entrada','saida'))
- **transacao** (id_transacao, id_carteira FK, id_categoria FK, data_transacao, descricao, valor, tipo_transacao ENUM('receita','despesa'))
- **receita** (id_transacao PK/FK, origem) — tabela "filha" de transacao
- **despesa** (id_transacao PK/FK, forma_pagamento) — tabela "filha" de transacao
- **fechamentomensal** (id_fechamento, id_carteira FK, mes_ano, total_entradas, total_saidas, total_diario, performance)

`receita` e `despesa` são as especializações de `transacao` (cada transação
vira uma linha em `transacao` + uma linha na tabela filha correspondente),
igual ao dicionário descreve.

Uma observação: o dicionário não tem uma coluna para marcar "isso é um gasto
diário fixo" na tabela `despesa`. Para não fugir do modelo que veio pronto,
resolvi isso criando automaticamente uma categoria chamada **"Diário"**
(tipo `saida`) sempre que o usuário lança um "Gasto Diário" — assim dá pra
identificar isso de novo quando os dados voltam do banco, sem precisar
inventar uma coluna que não estava no dicionário.

## O que foi adicionado no código

| Arquivo | Papel |
|---|---|
| `conexao.php` | Conexão PDO com o banco `mypocket`. |
| `UsuarioDAO.php` | Busca ou cria o usuário padrão (o projeto ainda não tem login). |
| `CarteiraDAO.php` | Busca/cria a carteira do usuário e atualiza `saldo_atual`. |
| `CategoriaDAO.php` | Busca uma categoria existente pelo nome+tipo ou cria uma nova (evita duplicar). |
| `TransacaoDAO.php` | Insere uma transação (+ a linha filha em `receita`/`despesa`) e busca todas as transações de uma carteira, já remontando os objetos `Receita`/`Despesa`. |
| `FechamentoMensalDAO.php` | Salva (upsert) o fechamento de um mês/carteira. |

Nas classes que já existiam, mudei só o essencial:

- **`Receita.php`** e **`Despesa.php`**: ganharam os campos do dicionário que
  faltavam (`origem` e `forma_pagamento`), como parâmetro opcional no final
  do construtor — não quebra nada que já chamava essas classes.
- **`Carteira.php`**: ganhou um método `carregarTransacao()`, usado só para
  repopular a carteira com transações que já existem no banco (sem repetir a
  validação de saldo, que já foi feita quando a transação foi criada da
  primeira vez).
- **`index.php`**, **`processa.php`**, **`Calendario.php`**: onde antes
  criavam `Usuario`/`Carteira` na sessão do zero a cada visita (perdendo tudo
  ao fechar o navegador), agora buscam/criam esses dados no banco e recarregam
  o histórico de transações de lá.
- **Bug que encontrei e corrigi**: em `Calendario.php`, a linha que cria o
  `FechamentoMensal` estava passando só 3 argumentos, mas a classe pede 4
  (falta o saldo acumulado até o fim do mês). Isso ia gerar um erro fatal de
  "argumentos insuficientes" assim que a página fosse aberta. Corrigi
  calculando esse saldo com `$carteira->getSaldoAteData()` antes de montar o
  fechamento.

## Como rodar

1. Rode `banco/schema.sql` no seu MySQL (`CREATE TABLE IF NOT EXISTS`, não
   apaga nada que já exista com esses nomes).
2. Ajuste usuário/senha em `conexao.php` se não for `root`/senha vazia.
3. Suba com XAMPP ou `php -S localhost:8000` **dentro** da pasta do projeto.
4. Acesse `index.php` — na primeira visita ele já cria o usuário e a
   carteira padrão no banco.

Testei tudo rodando de verdade contra um banco MySQL (criar usuário/carteira
automaticamente, lançar receita, despesa e gasto diário, bloquear despesa
maior que o saldo disponível, ver o histórico, e abrir o Calendário Anual)
antes de te mandar.
