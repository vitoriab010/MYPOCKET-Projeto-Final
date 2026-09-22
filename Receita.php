<?php
declare(strict_types=1);

require_once 'Transacao.php';

class Receita extends Transacao {
    public function __construct(int $id, float $valor, string $data, string $descricao, int $idCarteira, ?Categoria $categoria = null) {
        parent::__construct($id, $valor, $data, $descricao, $idCarteira, $categoria);
    }

    public function getTipo(): string {
        return "Entrada";
    }
}
