<?php 
declare(strict_types=1);

class Receita extends Transacao {
    public function __construct(int $id, float $valor, string $data, string $descricao) {
        parent::__construct($id, $valor, $data, $descricao);
    }

    public function getTipo(): string {
        return "Entrada";
    }
}