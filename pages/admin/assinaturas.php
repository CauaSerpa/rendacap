<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-wallet icon-gradient bg-plum-plate"></i>
            </div>
            <div>
                Financeiro / Assinaturas
                <div class="page-title-subheading">Análise financeira dos planos de usuários.</div>
            </div>
        </div>
    </div>
</div>

<?php
// Consulta para obter todos os usuários da tabela tb_users
$stmt = $conn->prepare("
    SELECT u.id, u.firstname, u.lastname, u.username, u.email, s.status, p.price, p.name AS plan_name, u.token, u.date_create
    FROM tb_users u
    LEFT JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
    LEFT JOIN tb_plans p ON s.plan_id = p.id
    WHERE u.status = 1
    ORDER BY u.id DESC
");
$stmt->execute();
$usersDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inicializa contagens e totais
$totalUsers = 0;
$totalDiamond = 0;
$totalGold = 0;
$totalSilver = 0;
$totalGeneral = 0;
$totalUsersDiamond = 0; // Contador de usuários com plano Diamante
$totalUsersGold = 0; // Contador de usuários com plano Ouro
$totalUsersSilver = 0; // Contador de usuários com plano Prata

// Função para traduzir o status
function translate_status($status) {
    $status_translation = [
        'ACTIVE' => 'Ativo',
        'EXPIRED' => 'Expirado',
        'INACTIVE' => 'Inativo',
        'CANCELED' => 'Cancelado'
    ];

    return $status_translation[$status] ?? $status;
}

// Obtendo as datas para os títulos
$dataAtual = new DateTime();
$semanaTitulo = $dataAtual->format("W/Y");
$mesTitulo = $dataAtual->format("m/Y");

// Inicializa contagens para semana e mês
$totalDiamondWeek = 0;
$totalGoldWeek = 0;
$totalSilverWeek = 0;
$totalGeneralWeek = 0;

$totalDiamondMonth = 0;
$totalGoldMonth = 0;
$totalSilverMonth = 0;
$totalGeneralMonth = 0;

// Loop pelos detalhes dos usuários para contar planos
foreach ($usersDetails as $user) {
    // Condições para o total semanal
    $dateCreate = new DateTime($user['date_create']);
    if ($dateCreate->format("W/Y") == $semanaTitulo) {
        if ($user['plan_name'] === 'Diamante') {
            $totalDiamondWeek += $user['price'];
            $totalUsersDiamond++;
        } elseif ($user['plan_name'] === 'Ouro') {
            $totalGoldWeek += $user['price'];
            $totalUsersGold++;
        } elseif ($user['plan_name'] === 'Prata') {
            $totalSilverWeek += $user['price'];
            $totalUsersSilver++;
        }
        $totalGeneralWeek += $user['price'];
    }

    // Condições para o total mensal
    if ($dateCreate->format("m/Y") == $mesTitulo) {
        if ($user['plan_name'] === 'Diamante') {
            $totalDiamondMonth += $user['price'];
        } elseif ($user['plan_name'] === 'Ouro') {
            $totalGoldMonth += $user['price'];
        } elseif ($user['plan_name'] === 'Prata') {
            $totalSilverMonth += $user['price'];
        }
        $totalGeneralMonth += $user['price'];
    }
}
?>

<div class="row">
    <div class="col-md-6 col-lg-3">
        <div id="diamond-plan-card" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
            <div class="icon-wrapper rounded-circle">
                <div class="icon-wrapper-bg bg-success"></div>
                <i class="fa fa-fw text-success"></i>
            </div>
            <div class="widget-chart-content">
                <div class="widget-subheading">Usuários com Plano Diamante</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $totalDiamond; ?>"><?= number_format($totalDiamond, 2, ',', '.'); ?></span>
                </div>
                <div class="widget-description opacity-8 text-focus">
                    Total
                    <span class="text-success pl-1 pr-1">
                        <span class="count-total pr-1"><?= $totalUsers; ?></span>
                        <i class="fa fa-angle-up "></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div id="gold-plan-card" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-warning border-warning card">
            <div class="icon-wrapper rounded-circle">
                <div class="icon-wrapper-bg bg-warning"></div>
                <i class="fa fa-fw text-warning" aria-hidden="true"></i>
            </div>
            <div class="widget-chart-content">
                <div class="widget-subheading">Usuários com Plano Ouro</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $totalGold; ?>"><?= number_format($totalGold, 2, ',', '.'); ?></span>
                </div>
                <div class="widget-description opacity-8 text-focus">
                    Total
                    <span class="text-warning pl-1 pr-1">
                        <span class="count-total pr-1"><?= $totalUsers; ?></span>
                        <i class="fa fa-angle-up "></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div id="silver-plan-card" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-primary border-primary card">
            <div class="icon-wrapper rounded-circle">
                <div class="icon-wrapper-bg bg-primary"></div>
                <i class="fa fa-fw text-primary"></i>
            </div>
            <div class="widget-chart-content">
                <div class="widget-subheading">Usuários com Plano Prata</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $totalSilver; ?>"><?= number_format($totalSilver, 2, ',', '.'); ?></span>
                </div>
                <div class="widget-description opacity-8 text-focus">
                    Total
                    <span class="text-primary pl-1 pr-1">
                        <span class="count-total pr-1"><?= $totalUsers; ?></span>
                        <i class="fa fa-angle-up "></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div id="total-plan-card" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
            <div class="icon-wrapper rounded-circle">
                <div class="icon-wrapper-bg bg-success"></div>
                <i class="fa fa-fw text-success"></i>
            </div>
            <div class="widget-chart-content">
                <div class="widget-subheading">Total Geral</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $totalGeneral; ?>"><?= number_format($totalGeneral, 2, ',', '.'); ?></span>
                </div>
                <div class="widget-description opacity-8 text-focus">
                    Total
                    <span class="text-success pl-1 pr-1">
                        <span class="count-total pr-1"><?= $totalUsers; ?></span>
                        <i class="fa fa-angle-up "></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Usuários -->
<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">


                <div class="mb-3">
                    <h5 class="card-title fsize-2">Semana <?= $semanaTitulo ?> (Domingo a Sábado)</h5>
                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <div id="diamond-plan-card-month" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
                                <div class="icon-wrapper rounded-circle">
                                    <div class="icon-wrapper-bg bg-success"></div>
                                    <i class="fa fa-fw text-success"></i>
                                </div>
                                <div class="widget-chart-content">
                                    <div class="widget-subheading">Usuários com Plano Diamante (Semana)</div>
                                    <div class="widget-numbers">
                                        <small class="opacity-5 pr-1">R$</small>
                                        <span class="count-up-real" data-value="<?= $totalDiamondWeek; ?>"><?= number_format($totalDiamondWeek, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div id="gold-plan-card-month" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-warning border-warning card">
                                <div class="icon-wrapper rounded-circle">
                                    <div class="icon-wrapper-bg bg-warning"></div>
                                    <i class="fa fa-fw text-warning" aria-hidden="true"></i>
                                </div>
                                <div class="widget-chart-content">
                                    <div class="widget-subheading">Usuários com Plano Ouro (Semana)</div>
                                    <div class="widget-numbers">
                                        <small class="opacity-5 pr-1">R$</small>
                                        <span class="count-up-real" data-value="<?= $totalGoldWeek; ?>"><?= number_format($totalGoldWeek, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div id="silver-plan-card-month" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-primary border-primary card">
                                <div class="icon-wrapper rounded-circle">
                                    <div class="icon-wrapper-bg bg-primary"></div>
                                    <i class="fa fa-fw text-primary"></i>
                                </div>
                                <div class="widget-chart-content">
                                    <div class="widget-subheading">Usuários com Plano Prata (Semana)</div>
                                    <div class="widget-numbers">
                                        <small class="opacity-5 pr-1">R$</small>
                                        <span class="count-up-real" data-value="<?= $totalSilverWeek; ?>"><?= number_format($totalSilverWeek, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div id="total-plan-card-month" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
                                <div class="icon-wrapper rounded-circle">
                                    <div class="icon-wrapper-bg bg-success"></div>
                                    <i class="fa fa-fw text-success"></i>
                                </div>
                                <div class="widget-chart-content">
                                    <div class="widget-subheading">Total Geral (Semana)</div>
                                    <div class="widget-numbers">
                                        <small class="opacity-5 pr-1">R$</small>
                                        <span class="count-up-real" data-value="<?= $totalGeneralWeek; ?>"><?= number_format($totalGeneralWeek, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="mb-3">
                    <h5 class="card-title fsize-2">Mês <?= $mesTitulo ?> (01/<?= $dataAtual->format('m') ?> a 30/<?= $dataAtual->format('m') ?>)</h5>
                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <div id="diamond-plan-card-month" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
                                <div class="icon-wrapper rounded-circle">
                                    <div class="icon-wrapper-bg bg-success"></div>
                                    <i class="fa fa-fw text-success"></i>
                                </div>
                                <div class="widget-chart-content">
                                    <div class="widget-subheading">Usuários com Plano Diamante (Mês)</div>
                                    <div class="widget-numbers">
                                        <small class="opacity-5 pr-1">R$</small>
                                        <span class="count-up-real" data-value="<?= $totalDiamondMonth; ?>"><?= number_format($totalDiamondMonth, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div id="gold-plan-card-month" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-warning border-warning card">
                                <div class="icon-wrapper rounded-circle">
                                    <div class="icon-wrapper-bg bg-warning"></div>
                                    <i class="fa fa-fw text-warning" aria-hidden="true"></i>
                                </div>
                                <div class="widget-chart-content">
                                    <div class="widget-subheading">Usuários com Plano Ouro (Mês)</div>
                                    <div class="widget-numbers">
                                        <small class="opacity-5 pr-1">R$</small>
                                        <span class="count-up-real" data-value="<?= $totalGoldMonth; ?>"><?= number_format($totalGoldMonth, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div id="silver-plan-card-month" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-primary border-primary card">
                                <div class="icon-wrapper rounded-circle">
                                    <div class="icon-wrapper-bg bg-primary"></div>
                                    <i class="fa fa-fw text-primary"></i>
                                </div>
                                <div class="widget-chart-content">
                                    <div class="widget-subheading">Usuários com Plano Prata (Mês)</div>
                                    <div class="widget-numbers">
                                        <small class="opacity-5 pr-1">R$</small>
                                        <span class="count-up-real" data-value="<?= $totalSilverMonth; ?>"><?= number_format($totalSilverMonth, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div id="total-plan-card-month" class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
                                <div class="icon-wrapper rounded-circle">
                                    <div class="icon-wrapper-bg bg-success"></div>
                                    <i class="fa fa-fw text-success"></i>
                                </div>
                                <div class="widget-chart-content">
                                    <div class="widget-subheading">Total Geral (Mês)</div>
                                    <div class="widget-numbers">
                                        <small class="opacity-5 pr-1">R$</small>
                                        <span class="count-up-real" data-value="<?= $totalGeneralMonth; ?>"><?= number_format($totalGeneralMonth, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <h5 class="card-title">Usuários Cadastrados</h5>
                <div class="table-responsive">
                    <table id="users-table" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Email</th>
                                <th>Plano</th>
                                <th>Valor</th>
                                <th>Status da Assinatura</th>
                                <th>Data de Criação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usersDetails)) : ?>
                                <?php foreach ($usersDetails as $userDetail) : ?>
                                    <?php
                                        if (!empty($userDetail['price'])) {
                                            $userDetail['plan_price'] = "R$ " . number_format($userDetail['price'], 2, ',', '.');
                                        }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($userDetail['id']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['firstname'] . ' ' . $userDetail['lastname']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['username']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['email']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['plan_name'] ?? 'Indefinido'); ?></td>
                                        <td><?= htmlspecialchars($userDetail['plan_price'] ?? 'Indefinido'); ?></td>
                                        <td><?= translate_status($userDetail['status']) ?? "Indefinido"; ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($userDetail['date_create']))); ?></td>
                                    </tr>

                                    <?php
                                    // Contabiliza o total e o valor de cada plano
                                    $totalUsers++;

                                    if ($userDetail['plan_name'] == 'Diamante') {
                                        $totalUsersDiamond++;
                                        $totalDiamond += $userDetail['price'];
                                    } elseif ($userDetail['plan_name'] == 'Ouro') {
                                        $totalUsersGold++;
                                        $totalGold += $userDetail['price'];
                                    } elseif ($userDetail['plan_name'] == 'Prata') {
                                        $totalUsersSilver++;
                                        $totalSilver += $userDetail['price'];
                                    }
                                    // Total geral
                                    $totalGeneral += $userDetail['price'];
                                    ?>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="text-center">Nenhum usuário encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Cria tabela com DataTables
        $("#users-table").DataTable({
            ordering: false,
            language: {
                "sProcessing": "Processando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ a _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 a 0 de 0 registros",
                "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                "sSearch": "Buscar:",
                "oPaginate": {
                    "sFirst": "Primeiro",
                    "sLast": "Último",
                    "sNext": "Próximo",
                    "sPrevious": "Anterior"
                },
            }
        });

        // Atualiza os valores dos cards após calcular os totais
        $('#diamond-plan-card .count-up-real').text('<?= number_format($totalDiamond, 2, ',', '.'); ?>');
        $('#gold-plan-card .count-up-real').text('<?= number_format($totalGold, 2, ',', '.'); ?>');
        $('#silver-plan-card .count-up-real').text('<?= number_format($totalSilver, 2, ',', '.'); ?>');
        $('#total-plan-card .count-up-real').text('<?= number_format($totalGeneral, 2, ',', '.'); ?>');

        $('#diamond-plan-card .count-total').text('<?= $totalUsersDiamond; ?>');
        $('#gold-plan-card .count-total').text('<?= $totalUsersGold; ?>');
        $('#silver-plan-card .count-total').text('<?= $totalUsersSilver; ?>');
        $('#total-plan-card .count-total').text('<?= $totalUsers; ?>');
    });

    $(document).ready(function(){
        $("input[type='search']").wrap("<form>");
        $("input[type='search']").closest("form").attr("autocomplete","off");
        $("input[type='search']").attr("autocomplete", "off");
    });
</script>