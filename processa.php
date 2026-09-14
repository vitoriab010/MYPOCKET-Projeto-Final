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
