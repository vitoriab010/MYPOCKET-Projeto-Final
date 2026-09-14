<?php
declare(strict_types=1);

require_once 'Transacao.php';
require_once 'Usuario.php';

class Carteira {
    private int $id;
    private string $nome;
    private float $saldo;
    private string $tipo;
    private int $idUsuario;
    private array $transacoes = [];

    public function __construct(int $id, string $nome, string $tipo, int $idUsuario, float $saldo = 0.0) {
        $this->id = $id;
        $this->nome = $nome;
        $this->tipo = $tipo;
        $this->idUsuario = $idUsuario;
        $this->saldo = $saldo;
    }

    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getTipo(): string { return $this->tipo; }
    public function getIdUsuario(): int { return $this->idUsuario; }

    public function adicionarTransacao(Transacao $transacao): void {
        if ($transacao->getTipo() === "Entrada") {
            $this->saldo += $transacao->getValor();
        } elseif ($transacao->getTipo() === "Saída") {
            if ($transacao->getValor() > $this->saldo) {
                throw new Exception("Saldo insuficiente para realizar esta despesa.");
            }
            $this->saldo -= $transacao->getValor();
        }
        $this->transacoes[] = $transacao;
    }
    public function carregarTransacao(Transacao $transacao): void {
        $this->transacoes[] = $transacao;
    }

    public function getSaldo(): float {
        return $this->saldo;
    }

    public function getTransacoes(): array {
        return $this->transacoes;
    }

    public function getSaldoAteData(string $dataCorte): float {
        $saldoData = 0.0;
        foreach ($this->transacoes as $t) {
            if ($t->getData() <= $dataCorte) {
                if ($t->getTipo() === "Entrada") {
                    $saldoData += $t->getValor();
                } else {
                    $saldoData -= $t->getValor();
                }
            }
        }
        return $saldoData;
    }
}
