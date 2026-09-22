<?php
declare(strict_types=1);

require_once 'Transacao.php';

class Despesa extends Transacao {
    private bool $isDiario;

    public function __construct(int $id, float $valor, string $data, string $descricao, int $idCarteira, ?Categoria $categoria = null, bool $isDiario = false) {
        parent::__construct($id, $valor, $data, $descricao, $idCarteira, $categoria);
        $this->isDiario = $isDiario;
    }

    public function isDiario(): bool {
        return $this->isDiario;
    }

    public function getTipo(): string {
        return "Saída";
    }
}
