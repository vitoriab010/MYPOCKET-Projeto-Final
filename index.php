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
require_once 'TransacaoDAO.php';

session_start();

$pdo = conectarBD();
$usuarioDAO = new UsuarioDAO($pdo);
$carteiraDAO = new CarteiraDAO($pdo);
$transacaoDAO = new TransacaoDAO($pdo);

$usuario = $usuarioDAO->buscarOuCriarPadrao();
$carteira = $carteiraDAO->buscarOuCriarPadrao($usuario->getId());

foreach ($transacaoDAO->buscarPorCarteira($carteira->getId()) as $t) {
    $carteira->carregarTransacao($t);
}

$transacaoEditando = null;
if (isset($_GET['editar_id'])) {
    $transacaoEditando = $transacaoDAO->buscarPorId((int)$_GET['editar_id']);
}

/** @var Carteira $carteira */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>MyPocket</title>
</head>

<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="display-5 fw-bold text-primary">MyPocket</h1>
                    <div class="d-flex align-items-center">
                        <a href="Calendario.php" class="btn btn-outline-dark me-3"> ver Calendário Anul</a>
                        <div class="card bg-white shadow-sm p-3 text-end">
                            <span class="text-muted small fw-bold">SALDO DISPONÍVEL</span>
                            <h2 class="text-success m-0">R$ <?php echo number_format($carteira->getSaldo(), 2, ',', '.'); ?></h2>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['mensagem_erro'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Erro:</strong> <?php echo $_SESSION['mensagem_erro'];
                                                unset($_SESSION['mensagem_erro']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                         <?php echo $_SESSION['mensagem_sucesso'];
                            unset($_SESSION['mensagem_sucesso']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-bold">
                        <?= $transacaoEditando ? 'Editar Lançamento' : 'Novo Lançamento Financeiro' ?>
                    </div>
                    <div class="card-body">
                        <form action="processa.php" method="POST" class="row g-3">
                            <input type="hidden" name="acao" value="<?= $transacaoEditando ? 'editar' : 'cadastrar' ?>">
                            <?php if ($transacaoEditando): ?>
                                <input type="hidden" name="id" value="<?= $transacaoEditando['id_transacao'] ?>">
                            <?php endif; ?>

                            <div class="col-md-6">
                                <label for="descricao" class="form-label fw-semibold">Descrição</label>
                                <input type="text" name="descricao" id="descricao" class="form-control" placeholder="Ex: Salário, Mercado, Luz..." value="<?= $transacaoEditando['descricao'] ?? '' ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="valor" class="form-label fw-semibold">Valor (R$)</label>
                                <input type="number" name="valor" id="valor" step="0.01" min="0.01" class="form-control" placeholder="0,00" value="<?= $transacaoEditando['valor'] ?? '' ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="data" class="form-label fw-semibold">Data</label>
                                <input type="date" name="data" id="data" class="form-control" value="<?= $transacaoEditando['data_transacao'] ?? date('Y-m-d') ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="tipo" class="form-label fw-semibold">Tipo de Movimentação</label>
                                <select name="tipo" id="tipo" class="form-select" required>
                                    <option value="receita" class="text-success fw-bold" <?= (isset($transacaoEditando['tipo_transacao']) && $transacaoEditando['tipo_transacao'] === 'receita') ? 'selected' : '' ?>>Receita (Entrada)</option>
                                    <option value="despesa" class="text-danger fw-bold" <?= (isset($transacaoEditando['tipo_transacao']) && $transacaoEditando['tipo_transacao'] === 'despesa') ? 'selected' : '' ?>>Despesa (Saída)</option>
                                    <option value="diario" class="text-warning fw-bold">Gasto Diário (Saída)</option>
                                </select>
                            </div>

                            <div class="col-12 text-end mt-4">
                                <?php if ($transacaoEditando): ?>
                                    <a href="index.php" class="btn btn-secondary me-2 fw-bold">Cancelar</a>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <?= $transacaoEditando ? 'Salvar Alterações' : 'Confirmar Lançamento' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white fw-bold">Histórico</div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped m-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $historico = $carteira->getTransacoes();
                                if (empty($historico)):
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Nenhuma transação registrada até o momento.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php /** @var Transacao $t */ ?>
                                    <?php foreach ($historico as $t): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($t->getData())); ?></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($t->getDescricao()); ?></td>
                                            <td><?php echo htmlspecialchars($t->getCategoria()?->getNome() ?? '-'); ?></td>
                                            <td>
                                                <?php if ($t->getTipo() === 'Entrada'): ?>
                                                    <span class="badge bg-success">Receita</span>
                                                <?php elseif ($t instanceof Despesa && $t->isDiario()): ?>
                                                    <span class="badge bg-warning text-dark">Gasto Diário</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Despesa</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end fw-bold <?php echo $t->getTipo() === 'Entrada' ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo ($t->getTipo() === 'Entrada' ? '+ ' : '- ') . "R$ " . number_format($t->getValor(), 2, ',', '.'); ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="index.php?editar_id=<?= $t->getId() ?>" class="btn btn-sm btn-outline-primary me-1">Editar</a>
                                                <a href="processa.php?acao=excluir&id=<?= $t->getId() ?>" onclick="return confirm('tem certeza meufi?')" class="btn btn-sm btn-outline-danger">Excluir</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>