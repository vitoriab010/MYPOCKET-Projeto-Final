<?php 
declare(strict_types=1);

require_once 'Transacao.php';

class Receita extends Transacao {
    private ?string $origem;

    public function __construct(int $id, float $valor, string $data, string $descricao, int $idCarteira, ?Categoria $categoria = null, ?string $origem = null) {
        parent::__construct($id, $valor, $data, $descricao, $idCarteira, $categoria);
        $this->origem = $origem;
    }

    public function getOrigem(): ?string {
        return $this->origem;
    }

    public function getTipo(): string {
        return "Entrada";
    }
}