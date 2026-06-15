<?php

declare(strict_types=1);

require_once 'Transacao.php';
require_once 'Receita.php';
require_once 'Despesa.php';
require_once 'Carteira.php';

session_start();

if (!isset($_SESSION['carteira'])) {
    $_SESSION['carteira'] = new Carteira();
}

/** @var Carteira $carteira */
$carteira = $_SESSION['carteira'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = (float) ($_POST['valor'] ?? 0);
    $data = trim($_POST['data'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');

    if (!empty($descricao) && $valor > 0 && !empty($data)) {
        $id = count($carteira->getTransacoes()) + 1;
        try {
            $novaTransacao = ($tipo === 'receita')
                ? new Receita($id, $valor, $data, $descricao)
                : new Despesa($id, $valor, $data, $descricao);

            $carteira->adicionarTransacao($novaTransacao);
            $_SESSION['mensagem_sucesso'] = "Lançamento realizado com sucesso";
        } catch (Exception $e) {
          
            $_SESSION['mensagem_erro'] = $e->getMessage();
        }
    } else {
        $_SESSION['mensagem_erro'] = "Preencha todos os campos corretamente com valores maiores que zero.";
    }

    header("Location: index.php");
    exit();
}
