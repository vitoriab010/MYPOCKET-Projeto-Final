<?php
declare(strict_types=1);

class FechamentoMensal {
    private int $id;
    private string $mesAno;
    private float $totalEntradas = 0.0;
    private float $totalSaidas = 0.0;
    private float $totalDiario = 0.0;
    private float $performance;

    public function __construct(int $mes, int $ano, array $transacoes, float $saldoAcumuladoAteFimDoMes) {
        $this->id = $ano * 100 + $mes;
        $this->mesAno = sprintf("%02d/%04d", $mes, $ano);
        $this->performance = $saldoAcumuladoAteFimDoMes;

        foreach ($transacoes as $t) {
            $tData = strtotime($t->getData());
            if ((int)date('m', $tData) === $mes && (int)date('Y', $tData) === $ano) {
                if ($t->getTipo() === "Entrada") {
                    $this->totalEntradas += $t->getValor();
                } else if ($t->getTipo() === "Saída") {
                    if ($t instanceof Despesa && $t->isDiario()) {
                        $this->totalDiario += $t->getValor();
                    } else {
                        $this->totalSaidas += $t->getValor();
                    }
                }
            }
        }
    }

    public function getId(): int { return $this->id; }
    public function getMesAno(): string { return $this->mesAno; }
    public function getTotalEntradas(): float { return $this->totalEntradas; }
    public function getTotalSaidas(): float { return $this->totalSaidas; }
    public function getTotalDiario(): float { return $this->totalDiario; }
    public function getSaidaTotal(): float { return $this->totalSaidas + $this->totalDiario; }
    public function getPerformance(): float { return $this->performance; }
}