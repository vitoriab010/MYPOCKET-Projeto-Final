<?php
declare(strict_types=1);

class Categoria {
    private int $id;
    private string $nome;
    private string $tipo; // "Entrada" ou "Saída"

    public function __construct(int $id, string $nome, string $tipo) {
        $this->id = $id;
        $this->nome = $nome;
        $this->tipo = $tipo;
    }

    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getTipo(): string { return $this->tipo; }
}