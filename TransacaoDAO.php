<?php
declare(strict_types=1);

require_once 'Transacao.php';
require_once 'Receita.php';
require_once 'Despesa.php';
require_once 'Categoria.php';

class TransacaoDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function inserir(Transacao $transacao, Categoria $categoria): int
    {
        $tipoBanco = $transacao->getTipo() === 'Entrada' ? 'receita' : 'despesa';

        $stmt = $this->pdo->prepare(
            "INSERT INTO transacao (id_carteira, id_categoria, data_transacao, descricao, valor, tipo_transacao)
             VALUES (:id_carteira, :id_categoria, :data_transacao, :descricao, :valor, :tipo_transacao)"
        );
        $stmt->execute([
            'id_carteira' => $transacao->getIdCarteira(),
            'id_categoria' => $categoria->getId(),
            'data_transacao' => $transacao->getData(),
            'descricao' => $transacao->getDescricao(),
            'valor' => $transacao->getValor(),
            'tipo_transacao' => $tipoBanco,
        ]);

        $idTransacao = (int) $this->pdo->lastInsertId();

        if ($transacao instanceof Receita) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO receita (id_transacao, origem) VALUES (:id, :origem)"
            );
            $stmt->execute(['id' => $idTransacao, 'origem' => $transacao->getOrigem()]);
        } elseif ($transacao instanceof Despesa) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO despesa (id_transacao, forma_pagamento) VALUES (:id, :forma_pagamento)"
            );
            $stmt->execute(['id' => $idTransacao, 'forma_pagamento' => $transacao->getFormaPagamento()]);
        }

        return $idTransacao;
    }

    // Traz de volta todas as transações de uma carteira, já reconstruindo
    // os objetos Receita/Despesa (com categoria) para o resto do app usar normal.
    public function buscarPorCarteira(int $idCarteira): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, c.nome_categoria, c.tipo_categoria,
                    r.origem, d.forma_pagamento
             FROM transacao t
             JOIN categoria c ON c.id_categoria = t.id_categoria
             LEFT JOIN receita r ON r.id_transacao = t.id_transacao
             LEFT JOIN despesa d ON d.id_transacao = t.id_transacao
             WHERE t.id_carteira = :id_carteira
             ORDER BY t.data_transacao, t.id_transacao"
        );
        $stmt->execute(['id_carteira' => $idCarteira]);

        $transacoes = [];
        foreach ($stmt->fetchAll() as $linha) {
            $categoria = new Categoria((int) $linha['id_categoria'], $linha['nome_categoria'], $linha['tipo_categoria']);

            if ($linha['tipo_transacao'] === 'receita') {
                $transacoes[] = new Receita(
                    (int) $linha['id_transacao'],
                    (float) $linha['valor'],
                    $linha['data_transacao'],
                    (string) $linha['descricao'],
                    (int) $linha['id_carteira'],
                    $categoria,
                    $linha['origem']
                );
            } else {
                $isDiario = $linha['nome_categoria'] === 'Diário';
                $transacoes[] = new Despesa(
                    (int) $linha['id_transacao'],
                    (float) $linha['valor'],
                    $linha['data_transacao'],
                    (string) $linha['descricao'],
                    (int) $linha['id_carteira'],
                    $categoria,
                    $isDiario,
                    $linha['forma_pagamento']
                );
            }
        }

        return $transacoes;
    }
}
