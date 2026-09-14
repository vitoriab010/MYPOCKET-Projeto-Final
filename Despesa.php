<?php
declare(strict_types=1);

require_once 'Transacao.php';

class Despesa extends Transacao {
    private bool $isDiario;
    private ?string $formaPagamento;

    public function __construct(int $id, float $valor, string $data, string $descricao, int $idCarteira, ?Categoria $categoria = null, bool $isDiario = false, ?string $formaPagamento = null) {
        parent::__construct($id, $valor, $data, $descricao, $idCarteira, $categoria);
        $this->isDiario = $isDiario;
        $this->formaPagamento = $formaPagamento;
    }

    public function isDiario(): bool {
        return $this->isDiario;
    }

    public function getFormaPagamento(): ?string {
        return $this->formaPagamento;
    }

    public function getTipo(): string {
        return "Saída";
    }
}
