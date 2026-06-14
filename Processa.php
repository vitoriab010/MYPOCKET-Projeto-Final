<?php
declare(strict_types=1);

require_once 'Transacao.php';
require_once 'Receita.php';
require_once 'Despesa.php';
require_once 'Carteira.php';

session_start();

// Se a carteira não existir na sessão do usuário, criamos uma nova
if (!isset($_SESSION['carteira'])) {
    $_SESSION['carteira'] = new Carteira();
}

/** @var Carteira $carteira */
$carteira = $_SESSION['carteira'];

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = (float) ($_POST['valor'] ?? 0);
    $data = trim($_POST['data'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');

    // Validação básica dos campos obrigatórios
    if (!empty($descricao) && $valor > 0 && !empty($data)) {
        // Gera um ID incremental baseado na quantidade de transações existentes
        $id = count($carteira->getTransacoes()) + 1;

        try {
            // Cria a instância correta da subclasse utilizando Polimorfismo
            if ($tipo === 'receita') {
                $novaTransacao = new Receita($id, $valor, $data, $descricao);
            } else {
                $novaTransacao = new Despesa($id, $valor, $data, $descricao);
            }

            // Tenta adicionar na carteira (onde valida o saldo)
            $carteira->adicionarTransacao($novaTransacao);
            $_SESSION['mensagem_sucesso'] = "Lançamento realizado com sucesso!";
            
        } catch (Exception $e) {
            // Captura a exceção caso o saldo fique negativo e guarda a mensagem para a interface
            $_SESSION['mensagem_erro'] = $e->getMessage();
        }
    } else {
        $_SESSION['mensagem_erro'] = "Preencha todos os campos corretamente com valores maiores que zero.";
    }

    // RNF05 - Padrão PRG (Post-Redirect-Get): Redireciona de volta para o index.php
    header("Location: index.php");
    exit();
}