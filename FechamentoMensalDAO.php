<?php
declare(strict_types=1);

class FechamentoMensalDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Salva (ou atualiza, se já existir) o fechamento de um mês/carteira.
    // É chamado toda vez que o Calendario.php é aberto, recalculando os totais.
    public function salvar(int $idCarteira, string $mesAno, float $totalEntradas, float $totalSaidas, float $totalDiario, float $performance): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO fechamentomensal (id_carteira, mes_ano, total_entradas, total_saidas, total_diario, performance)
             VALUES (:id_carteira, :mes_ano, :total_entradas, :total_saidas, :total_diario, :performance)
             ON DUPLICATE KEY UPDATE
                total_entradas = VALUES(total_entradas),
                total_saidas = VALUES(total_saidas),
                total_diario = VALUES(total_diario),
                performance = VALUES(performance)"
        );
        $stmt->execute([
            'id_carteira' => $idCarteira,
            'mes_ano' => $mesAno,
            'total_entradas' => $totalEntradas,
            'total_saidas' => $totalSaidas,
            'total_diario' => $totalDiario,
            'performance' => $performance,
        ]);
    }
}
