<?php
declare(strict_types=1);

require_once 'conexao.php';
require_once 'Usuario.php';
require_once 'Transacao.php';
require_once 'Receita.php';
require_once 'Despesa.php';
require_once 'Categoria.php';
require_once 'Carteira.php';
require_once 'FechamentoMensal.php';
require_once 'UsuarioRepository.php';
require_once 'CarteiraRepository.php';
require_once 'TransacaoRepository.php';
require_once 'FechamentoMensalRepository.php';

session_start();

$pdo = conectarBD();
$usuarioRepositorio = new UsuarioRepository($pdo);
$carteiraRepositorio = new CarteiraRepository($pdo);
$transacaoRepositorio = new TransacaoRepository($pdo);
$fechamentoRepositorio = new FechamentoMensalRepository($pdo);

$usuario = $usuarioRepositorio->buscarOuCriarPadrao();
$carteira = $carteiraRepositorio->buscarOuCriarPadrao($usuario->getId());

foreach ($transacaoRepositorio->buscarPorCarteira($carteira->getId()) as $t) {
    $carteira->carregarTransacao($t);
}

$anoSelecionado = (int)($_GET['ano'] ?? date('Y'));
$mesesNome = [1 => 'JANEIRO', 'FEVEREIRO', 'MARÇO', 'ABRIL', 'MAIO', 'JUNHO', 'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO'];

// Agrupa transações por data (YYYY-MM-DD)
$transacoesPorData = [];
foreach ($carteira->getTransacoes() as $t) {
    $transacoesPorData[$t->getData()][] = $t;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>MyPocket - Calendário Anual <?php echo $anoSelecionado; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-cal { font-size: 0.8rem; }
        .table-cal th, .table-cal td { padding: 0.25rem; text-align: center; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 fw-bold text-primary">Calendário Anual - <?php echo $anoSelecionado; ?></h1>
        <a href="index.php" class="btn btn-outline-primary fw-bold">← Voltar para Lançamentos</a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
        <?php for ($m = 1; $m <= 12; $m++): 
            $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $m, $anoSelecionado);
            $ultimoDiaDoMes = sprintf("%04d-%02d-%02d", $anoSelecionado, $m, $diasNoMes);
            $saldoAteFimDoMes = $carteira->getSaldoAteData($ultimoDiaDoMes);
            $fechamento = new FechamentoMensal($m, $anoSelecionado, $carteira->getTransacoes(), $saldoAteFimDoMes);
            $fechamentoRepositorio->salvar(
                $carteira->getId(),
                sprintf("%04d-%02d-01", $anoSelecionado, $m),
                $fechamento->getTotalEntradas(),
                $fechamento->getTotalSaidas(),
                $fechamento->getTotalDiario(),
                $fechamento->getPerformance()
            );
        ?>
            <div class="col">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white text-center fw-bold">
                        <?php echo $mesesNome[$m]; ?>
                    </div>
                    <div class="card-body p-1 overflow-auto">
                        <table class="table table-bordered table-sm table-cal m-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Dia</th>
                                    <th>Entrada</th>
                                    <th>Saída</th>
                                    <th>Diário</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($d = 1; $d <= $diasNoMes; $d++): 
                                    $dataStr = sprintf("%04d-%02d-%02d", $anoSelecionado, $m, $d);
                                    $entradasDia = 0.0;
                                    $saidasDia = 0.0;
                                    $diarioDia = 0.0;

                                    if (isset($transacoesPorData[$dataStr])) {
                                        foreach ($transacoesPorData[$dataStr] as $t) {
                                            if ($t->getTipo() === "Entrada") {
                                                $entradasDia += $t->getValor();
                                            } else if ($t instanceof Despesa && $t->isDiario()) {
                                                $diarioDia += $t->getValor();
                                            } else {
                                                $saidasDia += $t->getValor();
                                            }
                                        }
                                    }
                                    $saldoAcumulado = $carteira->getSaldoAteData($dataStr);
                                ?>
                                    <tr>
                                        <td><?php echo $d; ?></td>
                                        <td class="text-success"><?php echo $entradasDia > 0 ? number_format($entradasDia, 2, ',', '.') : '-'; ?></td>
                                        <td class="text-danger"><?php echo $saidasDia > 0 ? number_format($saidasDia, 2, ',', '.') : '-'; ?></td>
                                        <td class="text-warning"><?php echo $diarioDia > 0 ? number_format($diarioDia, 2, ',', '.') : '-'; ?></td>
                                        <td class="fw-bold"><?php echo number_format($saldoAcumulado, 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>TOT</td>
                                    <td class="text-success"><?php echo number_format($fechamento->getTotalEntradas(), 2, ',', '.'); ?></td>
                                    <td class="text-danger"><?php echo number_format($fechamento->getTotalSaidas(), 2, ',', '.'); ?></td>
                                    <td class="text-warning"><?php echo number_format($fechamento->getTotalDiario(), 2, ',', '.'); ?></td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td colspan="2">Saída Total</td>
                                    <td colspan="3" class="text-danger">R$ <?php echo number_format($fechamento->getSaidaTotal(), 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2">Performance</td>
                                    <td colspan="3" class="<?php echo $fechamento->getPerformance() >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        R$ <?php echo number_format($fechamento->getPerformance(), 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>

</body>
</html>