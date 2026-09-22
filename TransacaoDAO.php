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
    {$this->pdo = $pdo;}

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
            $stmt = $this->pdo->prepare("INSERT INTO receita (id_transacao) VALUES (:id)");
            $stmt->execute(['id' => $idTransacao]);
        } elseif ($transacao instanceof Despesa) {
            $stmt = $this->pdo->prepare("INSERT INTO despesa (id_transacao) VALUES (:id)");
            $stmt->execute(['id' => $idTransacao]);
        }

        return $idTransacao;
    }
    public function atualizar(int $id, Transacao $transacao, Categoria $categoria): bool
    {
        $tipoBanco = $transacao->getTipo() === 'Entrada' ? 'receita' : 'despesa';

        $stmt = $this->pdo->prepare(
            "UPDATE transacao
             SET descricao = :descricao, valor = :valor, data_transacao = :data_transacao,
                 tipo_transacao = :tipo_transacao, id_categoria = :id_categoria
             WHERE id_transacao = :id"
        );
        return $stmt->execute([
            'descricao' => $transacao->getDescricao(),
            'valor' => $transacao->getValor(),
            'data_transacao' => $transacao->getData(),
            'tipo_transacao' => $tipoBanco,
            'id_categoria' => $categoria->getId(),
            'id' => $id,
        ]);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM transacao WHERE id_transacao = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM receita WHERE id_transacao = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $this->pdo->prepare("DELETE FROM despesa WHERE id_transacao = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $this->pdo->prepare("DELETE FROM transacao WHERE id_transacao = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function buscarPorCarteira(int $idCarteira): array
    {
        $sql = "SELECT t.*, c.nome_categoria, c.tipo_categoria
                FROM transacao t
                LEFT JOIN categoria c ON t.id_categoria = c.id_categoria
                WHERE t.id_carteira = :id_carteira
                ORDER BY t.data_transacao DESC, t.id_transacao DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_carteira' => $idCarteira]);

        $transacoes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $categoria = new Categoria((int) $linha['id_categoria'], $linha['nome_categoria'], $linha['tipo_categoria']);

            if ($linha['tipo_transacao'] === 'receita') {
                $transacoes[] = new Receita(
                    (int) $linha['id_transacao'],
                    (float) $linha['valor'],
                    $linha['data_transacao'],
                    (string) $linha['descricao'],
                    (int) $linha['id_carteira'],
                    $categoria
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
                    $isDiario
                );
            }
        }

        return $transacoes;
    }
}
