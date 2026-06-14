<?php
declare(strict_types=1);

abstract class Transacao {
    private int $id;
    private float $valor;
    private string $data;
    private string $descricao;

    public function __construct(int $id, float $valor, string $data, string $descricao) {
        $this->id = $id;
        $this->valor = $valor;
        $this->data = $data;
        $this->descricao = $descricao;
    }

    public function getId(): int { return $this->id; }
    public function getValor(): float { return $this->valor; }
    public function getData(): string { return $this->data; }
    public function getDescricao(): string { return $this->descricao; }

    
    abstract public function getTipo(): string;
}