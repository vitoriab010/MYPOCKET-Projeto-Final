<?php
declare(strict_types=1);

require_once 'conexao.php';
require_once 'Usuario.php';
require_once 'Transacao.php';
require_once 'Receita.php';
require_once 'Despesa.php';
require_once 'Categoria.php';
require_once 'Carteira.php';
require_once 'UsuarioDAO.php';
require_once 'CarteiraDAO.php';
require_once 'CategoriaDAO.php';
require_once 'TransacaoDAO.php';

session_start();

$pdo = conectarBD();
$usuarioDAO = new UsuarioDAO($pdo);
$carteiraDAO = new CarteiraDAO($pdo);
$categoriaDAO = new CategoriaDAO($pdo);
$transacaoDAO = new TransacaoDAO($pdo);

$usuario = $usuarioDAO->buscarOuCriarPadrao();
$carteira = $carteiraDAO->buscarOuCriarPadrao($usuario->getId());

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

if ($acao === 'excluir') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    if ($id) {
        try {
            $transacaoDAO->excluir($id);
            $_SESSION['mensagem_sucesso'] = "Transação excluída com sucesso!";
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = "Erro ao excluir transação: " . $e->getMessage();
        }
    }
    header('Location: index.php');
    exit();
}

if ($acao === 'editar') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = (float)($_POST['valor'] ?? 0);
    $data = trim($_POST['data'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $nomeCategoria = trim($_POST['categoria'] ?? 'Geral');

    $transacaoAntiga = $id ? $transacaoDAO->buscarPorId($id) : null;

    if ($transacaoAntiga && !empty($descricao) && $valor > 0 && !empty($data)) {
        try {
            $tipoAntigo = $transacaoAntiga['tipo_transacao'] === 'receita' ? 'Entrada' : 'Saída';
            $carteira->reverterTransacao($tipoAntigo, (float)$transacaoAntiga['valor']);

            if ($tipo === 'receita') {
                $categoria = $categoriaDAO->buscarOuCriar($nomeCategoria, 'entrada');
                $transacaoNova = new Receita($id, $valor, $data, $descricao, $carteira->getId(), $categoria);
            } else {
                $isDiario = ($tipo === 'diario');
                $categoria = $categoriaDAO->buscarOuCriar($isDiario ? 'Diário' : $nomeCategoria, 'saida');
                $transacaoNova = new Despesa($id, $valor, $data, $descricao, $carteira->getId(), $categoria, $isDiario);
            }

            // adicionarTransacao() aplica o novo valor e valida saldo insuficiente, igual no cadastro
            $carteira->adicionarTransacao($transacaoNova);

            $transacaoDAO->atualizar($id, $transacaoNova, $categoria);
            $carteiraDAO->atualizarSaldo($carteira->getId(), $carteira->getSaldo());

            $_SESSION['mensagem_sucesso'] = "Transação atualizada com sucesso!";
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
        }
    } else {
        $_SESSION['mensagem_erro'] = "Dados inválidos para edição.";
    }
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = (float) ($_POST['valor'] ?? 0);
    $data = trim($_POST['data'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $nomeCategoria = trim($_POST['categoria'] ?? 'Geral');

    if (!empty($descricao) && $valor > 0 && !empty($data)) {
        try {
            if ($tipo === 'receita') {
                $categoria = $categoriaDAO->buscarOuCriar($nomeCategoria, 'entrada');
                $novaTransacao = new Receita(0, $valor, $data, $descricao, $carteira->getId(), $categoria);
            } else {
                $isDiario = ($tipo === 'diario');
                $categoria = $categoriaDAO->buscarOuCriar($isDiario ? 'Diário' : $nomeCategoria, 'saida');
                $novaTransacao = new Despesa(0, $valor, $data, $descricao, $carteira->getId(), $categoria, $isDiario);
            }

            $carteira->adicionarTransacao($novaTransacao);
            $transacaoDAO->inserir($novaTransacao, $categoria);
            $carteiraDAO->atualizarSaldo($carteira->getId(), $carteira->getSaldo());

            $_SESSION['mensagem_sucesso'] = "Lançamento realizado com sucesso!";
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
        }
    } else {
        $_SESSION['mensagem_erro'] = "Preencha todos os campos corretamente.";
    }

    header("Location: index.php");
    exit();
}