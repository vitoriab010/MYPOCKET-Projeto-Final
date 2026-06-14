<?php
declare(strict_types=1);

class Carteira {
    private float $saldo = 0.0;
    private array $transacoes = [];

    public function adicionarTransacao(object $transacao): void {
        // determine tipo in a safe way (supports getTipo() or public property 'tipo')
        $tipo = null;
        if (method_exists($transacao, 'getTipo')) {
            $tipo = $transacao->getTipo();
        } elseif (property_exists($transacao, 'tipo')) {
            $tipo = $transacao->tipo;
        }

        if ($tipo === "Entrada") {
            $this->saldo += $transacao->getValor();
        } elseif ($transacao->getTipo() === "Saída") {
            if ($transacao->getValor() > $this->saldo) {
                throw new Exception("saldo insuficiente para realizar esta despesa");
            }
            $this->saldo -= $transacao->getValor();
        }
        $this->transacoes[] = $transacao;
    }

    public function getSaldo(): float {
        return $this->saldo;
    }

    public function getTransacoes(): array {
        return $this->transacoes;
    }
}