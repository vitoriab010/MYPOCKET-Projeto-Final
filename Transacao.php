<?php
declare(strict_types=1);

require_once 'Categoria.php';

abstract class Transacao {
    private int $id;
    private float $valor;
    private string $data;
    private string $descricao;
    private int $idCarteira;
    private ?Categoria $categoria;

    public function __construct(int $id, float $valor, string $data, string $descricao, int $idCarteira, ?Categoria $categoria = null) {
        $this->id = $id;
        $this->valor = $valor;
        $this->data = $data;
        $this->descricao = $descricao;
        $this->idCarteira = $idCarteira;
        $this->categoria = $categoria;
    }

    public function getId(): int { return $this->id; }
    public function getValor(): float { return $this->valor; }
    public function getData(): string { return $this->data; }
    public function getDescricao(): string { return $this->descricao; }
    public function getIdCarteira(): int { return $this->idCarteira; }
    public function getCategoria(): ?Categoria { return $this->categoria; }

    abstract public function getTipo(): string;
}
