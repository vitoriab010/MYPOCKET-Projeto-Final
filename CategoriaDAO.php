<?php
declare(strict_types=1);

require_once 'Categoria.php';

class CategoriaDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Evita criar categoria duplicada: se já existe uma com o mesmo
    // nome e tipo, reaproveita ela.
    public function buscarOuCriar(string $nome, string $tipo): Categoria
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM categoria WHERE nome_categoria = :nome AND tipo_categoria = :tipo"
        );
        $stmt->execute(['nome' => $nome, 'tipo' => $tipo]);
        $linha = $stmt->fetch();

        if ($linha) {
            return new Categoria((int) $linha['id_categoria'], $linha['nome_categoria'], $linha['tipo_categoria']);
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO categoria (nome_categoria, tipo_categoria) VALUES (:nome, :tipo)"
        );
        $stmt->execute(['nome' => $nome, 'tipo' => $tipo]);

        $id = (int) $this->pdo->lastInsertId();
        return new Categoria($id, $nome, $tipo);
    }
}
