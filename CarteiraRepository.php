<?php
declare(strict_types=1);

require_once 'Carteira.php';

class CarteiraRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarOuCriarPadrao(int $idUsuario): Carteira
    {
        $stmt = $this->pdo->prepare("SELECT * FROM carteira WHERE id_usuario = :id_usuario LIMIT 1");
        $stmt->execute(['id_usuario' => $idUsuario]);
        $linha = $stmt->fetch();

        if ($linha) {
            return new Carteira(
                (int) $linha['id_carteira'],
                $linha['nome_carteira'],
                (string) ($linha['tipo_carteira'] ?? ''),
                (int) $linha['id_usuario'],
                (float) $linha['saldo_atual']
            );
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO carteira (id_usuario, nome_carteira, tipo_carteira, saldo_atual)
             VALUES (:id_usuario, :nome_carteira, :tipo_carteira, 0)"
        );
        $stmt->execute([
            'id_usuario' => $idUsuario,
            'nome_carteira' => 'Carteira Principal',
            'tipo_carteira' => 'Corrente',
        ]);

        $id = (int) $this->pdo->lastInsertId();
        return new Carteira($id, 'Carteira Principal', 'Corrente', $idUsuario, 0.0);
    }

    public function atualizarSaldo(int $idCarteira, float $saldo): void
    {
        $stmt = $this->pdo->prepare("UPDATE carteira SET saldo_atual = :saldo WHERE id_carteira = :id");
        $stmt->execute(['saldo' => $saldo, 'id' => $idCarteira]);
    }
}
