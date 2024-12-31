<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-wallet icon-gradient bg-plum-plate"></i>
            </div>
            <div>
                Financeiro / Saques
                <div class="page-title-subheading">Análise financeira dos saques dos usuários.</div>
            </div>
        </div>
    </div>
</div>

<?php
    // Consulta todos os saques pendentes e pagos
    $stmt = $conn->prepare("
        SELECT w.id, w.user_id, u.username, u.firstname, u.lastname, u.email, u.pix_key_type, u.pix_key, w.amount, w.status, w.request_date, w.payment_date, s.plan_id, p.name AS plan_name 
        FROM tb_withdrawals w
        JOIN tb_users u ON w.user_id = u.id
        LEFT JOIN tb_subscriptions s ON w.user_id = s.user_id AND s.status = 'ACTIVE'
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        ORDER BY w.request_date DESC
    ");
    $stmt->execute();
    $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Array para conversão de status
    $status_translation = [
        'pending' => 'Pendente',
        'cancelled' => 'Cancelado',
        'paid' => 'Pago',
    ];
?>

<?php
    function buildNetworkArray($conn, $userId) {
        // Inicializa o array para o usuário
        $userArray = [];
        $diretosCount = 0; // Inicializa a contagem de usuários diretos

        // Consulta para obter os dados do usuário
        $stmt = $conn->prepare("
            SELECT u.*, p.name AS plan_name
            FROM tb_users u
            LEFT JOIN tb_subscriptions s ON u.id = s.user_id
            LEFT JOIN tb_plans p ON s.plan_id = p.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $userList = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se o usuário possui um plano
        if (!empty($userList['plan_name'])) {
            // Adiciona o usuário ao array
            $userArray[$userList['id']] = [
                'plano' => strtolower($userList['plan_name']),
                'username' => $userList['username'],
                'convidados' => [],
                'diretos_count' => 0 // Contagem de diretos de cada usuário
            ];

            // Consulta para obter todos os network_id associados ao usuário
            $stmt = $conn->prepare("
                SELECT id
                FROM tb_networks
                WHERE referrer_id = ? OR inviter_id = ?
            ");
            $stmt->execute([$userId, $userId]);
            $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($networkIds)) {
                // Consulta adicional para obter mais detalhes dos usuários na rede, excluindo o plano bronze
                $stmt = $conn->prepare("
                    SELECT u.id, u.status, u.username, u.email, s.status AS subs_status, p.price, p.id AS plan_id, p.name AS plan_name, u.token, 
                        n.inviter_id, n.referrer_id, 
                        CASE 
                            WHEN n.inviter_id = ? AND n.referrer_id IS NULL THEN 'Direto'
                            WHEN n.referrer_id = ? THEN 'Direto'
                            ELSE 'Indireto' 
                        END AS tipo
                    FROM tb_users u
                    INNER JOIN tb_user_networks un ON u.id = un.user_id
                    LEFT JOIN tb_networks n ON un.network_id = n.id
                    LEFT JOIN tb_subscriptions s ON u.id = s.user_id
                    LEFT JOIN tb_plans p ON s.plan_id = p.id
                    WHERE u.status = 1 
                    AND un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
                    AND LOWER(p.name) != 'bronze'
                    ORDER BY u.id DESC
                ");
                $params = array_merge([$userId, $userId], $networkIds);
                $stmt->execute($params);
                $subusers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($subusers as $subuser) {
                    // Verifica se o usuário é direto e adiciona à contagem se for
                    if ($subuser['tipo'] === 'Direto') {
                        $diretosCount++;
                    }

                    // Recursivamente constrói a rede e conta diretos para o sub-usuário
                    $subuserArray = buildNetworkArray($conn, $subuser['id']);
                    
                    // Adiciona o sub-usuário ao array, com seu relacionamento (direto ou indireto)
                    $userArray[$userList['id']]['convidados'][$subuser['id']] = [
                        'username' => $subuser['username'],
                        'tipo' => $subuser['tipo'],
                        'plan_id' => strtolower($subuser['plan_id']),
                        'plano' => strtolower($subuser['plan_name']),
                        'email' => $subuser['email'],
                        'diretos_count' => $subuserArray['diretosCount'], // Adiciona contagem de diretos do subusuário
                        'convidados' => $subuserArray['network'] // Adiciona os subusuários desse subusuário
                    ];
                }

                // Atualiza a contagem de diretos no array principal
                $userArray[$userList['id']]['diretos_count'] = $diretosCount;
            }
        }

        // Retorna o array do usuário junto com a contagem de diretos
        return ['network' => $userArray, 'diretosCount' => $diretosCount];
    }
?>

<?php
    // Data atual
    $currentDate = date('Y-m-d');

    // Início e fim do mês atual
    $firstDayOfMonth = date('Y-m-01');
    $lastDayOfMonth = date('Y-m-t');

    // Consulta para contar e somar valores de saques solicitados hoje
    $sqlSaquesHoje = "
        SELECT COUNT(*) AS total_saques_hoje, SUM(amount) AS total_valor_hoje 
        FROM tb_withdrawals 
        WHERE DATE(request_date) = ?";
    
    // Consulta para contar e somar valores de saques pagos no mês atual
    $sqlSaquesPagosMes = "
        SELECT COUNT(*) AS total_saques_pagos_mes, SUM(amount) AS total_valor_pagos_mes 
        FROM tb_withdrawals 
        WHERE DATE(payment_date) BETWEEN ? AND ?";

    // Preparar e executar a consulta para saques de hoje
    $stmtSaquesHoje = $conn->prepare($sqlSaquesHoje);
    $stmtSaquesHoje->execute([$currentDate]);
    $resultSaquesHoje = $stmtSaquesHoje->fetch(PDO::FETCH_ASSOC);

    // Preparar e executar a consulta para saques pagos no mês
    $stmtSaquesPagosMes = $conn->prepare($sqlSaquesPagosMes);
    $stmtSaquesPagosMes->execute([$firstDayOfMonth, $lastDayOfMonth]);
    $resultSaquesPagosMes = $stmtSaquesPagosMes->fetch(PDO::FETCH_ASSOC);

    // Inicializar o array de resultados
    $withdrawalStats = [
        'total_valor_hoje' => $resultSaquesHoje['total_valor_hoje'] ?? 0,
        'total_saques_hoje' => $resultSaquesHoje['total_saques_hoje'] ?? 0,
        'total_valor_pagos_mes' => $resultSaquesPagosMes['total_valor_pagos_mes'] ?? 0,
        'total_saques_pagos_mes' => $resultSaquesPagosMes['total_saques_pagos_mes'] ?? 0,
    ];
?>

<?php
function listNetworkData($conn, $networkIds, $currentUserId, &$processedUsers = []) {
    $allUsers = [];

    // Consulta para listar usuários diretos ou indiretos
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.email, u.status, p.id AS plan_id, p.price, p.name AS plan_name, u.token, 
               n.inviter_id, n.referrer_id, s.payment_id, 
               CASE 
                   WHEN n.inviter_id = ? AND n.referrer_id IS NULL THEN 'direct'
                   WHEN n.referrer_id = ? THEN 'direct'
                   ELSE 'indirect' 
               END AS tipo,
               u.date_create
        FROM tb_users u
        INNER JOIN tb_user_networks un ON u.id = un.user_id
        LEFT JOIN tb_networks n ON un.network_id = n.id
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
        ORDER BY u.id DESC
    ");

    $stmt->execute(array_merge([$currentUserId, $currentUserId], $networkIds));
    $currentLevelUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($currentLevelUsers as $userDetail) {
        if (!in_array($userDetail['id'], $processedUsers)) {
            $allUsers[] = $userDetail;
            $processedUsers[] = $userDetail['id'];
        }
    }

    $nextNetworkIds = [];
    foreach ($currentLevelUsers as $userDetail) {
        $stmt = $conn->prepare("
            SELECT id
            FROM tb_networks
            WHERE referrer_id = ? OR inviter_id = ?
        ");
        $stmt->execute([$userDetail['id'], $userDetail['id']]);
        $nextNetworkIds = array_merge($nextNetworkIds, $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    if (!empty($nextNetworkIds)) {
        $indirectUsers = listNetworkData($conn, $nextNetworkIds, $currentUserId, $processedUsers);
        $allUsers = array_merge($allUsers, $indirectUsers);
    }

    return $allUsers;
}

// ID do usuário atual (da sessão)
$userId = $_SESSION['user_id'];

// Consulta para obter todos os network_id associados ao usuário
$stmt = $conn->prepare("
    SELECT id
    FROM tb_networks
    WHERE referrer_id = ? OR inviter_id = ?
");
$stmt->execute([$userId, $userId]);
$networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($networkIds)) {
    $networkData = listNetworkData($conn, $networkIds, $userId);

    $totalUsers = count($networkData);
    $totalCashback = 0;
    $belowThresholdCount = 0;
    $belowThresholdTotal = 0;
    $aboveThresholdCount = 0;
    $aboveThresholdTotal = 0;

    foreach ($networkData as $userDetail) {
        $cashback = $userDetail['cashback'] ?? 0; // Valor do cashback

        // Acumula o total de cashback
        $totalCashback += $cashback;

        // Verifica se o cashback está abaixo ou acima do limite
        if ($cashback < 25) {
            $belowThresholdCount++;
            $belowThresholdTotal += $cashback;
        } else {
            $aboveThresholdCount++;
            $aboveThresholdTotal += $cashback;
        }
    }

    $networkData = [
        'belowThresholdCount' => $belowThresholdCount,
        'belowThresholdTotal' => $belowThresholdTotal,
        'aboveThresholdCount' => $aboveThresholdCount,
        'aboveThresholdTotal' => $aboveThresholdTotal,
    ];
} else {
    $networkData = [
        'belowThresholdCount' => 0,
        'belowThresholdTotal' => 0,
        'aboveThresholdCount' => 0,
        'aboveThresholdTotal' => 0
    ];
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
                <div class="widget-subheading">Cashback (-) 25,00</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $networkData['belowThresholdTotal']; ?>"><?= number_format($networkData['belowThresholdTotal'], 2, ',', '.'); ?></span>
                </div>
                <div class="widget-description opacity-8 text-focus">
                    Usuários com cashback abaixo de R$ 25
                    <span class="text-success pl-1 pr-1">
                        <span class="count-total pr-1"><?= $networkData['belowThresholdCount']; ?></span>
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
                <div class="widget-subheading">Cashback (+) 25,00</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $networkData['aboveThresholdTotal']; ?>"><?= number_format($networkData['aboveThresholdTotal'], 2, ',', '.'); ?></span>
                </div>
                <div class="widget-description opacity-8 text-focus">
                    Usuários com cashback acima de R$ 25
                    <span class="text-success pl-1 pr-1">
                        <span class="count-total pr-1"><?= $networkData['aboveThresholdCount']; ?></span>
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
                <div class="widget-subheading">Solicitação de Saques Hoje</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $withdrawalStats['total_valor_hoje']; ?>"><?= number_format($withdrawalStats['total_valor_hoje'], 2, ',', '.'); ?></span>
                </div>
                <div class="widget-description opacity-8 text-focus">
                    Saques solicitados
                    <span class="text-success pl-1 pr-1">
                        <span class="count-total pr-1"><?= $withdrawalStats['total_saques_hoje']; ?></span>
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
                <div class="widget-subheading">Saques Pagos no Mês</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $withdrawalStats['total_valor_pagos_mes']; ?>"><?= number_format($withdrawalStats['total_valor_pagos_mes'], 2, ',', '.'); ?></span>
                </div>
                <div class="widget-description opacity-8 text-focus">
                    Saques pagos
                    <span class="text-success pl-1 pr-1">
                        <span class="count-total pr-1"><?= $withdrawalStats['total_saques_pagos_mes']; ?></span>
                        <i class="fa fa-angle-up "></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Saques Solicitados</h5>

                <!-- Botões de Filtro para os Status -->
                <div class="btn-group mb-4" id="filter-status" role="group" aria-label="Filtro de Status">
                    <button type="button" class="btn btn-secondary btn-filter-status active" data-filter="all">Todos (<span class="count-all">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter-status" data-filter="pending">Pendente (<span class="count-pending">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter-status" data-filter="paid">Pago (<span class="count-paid">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter-status" data-filter="canceled">Cancelado (<span class="count-canceled">0</span>)</button>
                </div>

                <table style="width: 100%;" id="withdrawals-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome do Usuário</th>
                            <th>Login</th>
                            <th>Email</th>
                            <th>Pacote</th>
                            <th>Qualificação</th>
                            <th>Valor Solicitado</th>
                            <th>Tipo de Chave Pix</th>
                            <th>Chave Pix</th>
                            <th>Status</th>
                            <th>Solicitado Em</th>
                            <th>Pago Em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($withdrawals)) : ?>
                            <?php foreach ($withdrawals as $withdrawal) : ?>
                                <?php
                                    $planId = $withdrawal['plan_id'];
                                    $userId = $withdrawal['user_id'];

                                    // Obtenha a rede do usuário
                                    $network = buildNetworkArray($conn, $userId);
                                    $directUsers = $network['network'][$userId]['convidados'] ?? [];
                                    $directUserCount = count($directUsers);

                                    // Variáveis para contagem de coronéis e general
                                    $colonelCount = 0;

                                    foreach ($directUsers as $convidadoId => $convidadoData) {
                                        $diretosCount = $convidadoData['diretos_count'];

                                        // Usuário com 10 ou mais diretos (Coronel)
                                        if ($diretosCount >= 10) {
                                            $colonelCount++;
                                        }
                                    }

                                    if ($colonelCount >= 15 && $directUserCount >= 10) {
                                        $withdrawal['classification'] = "Marechal";
                                    } elseif ($colonelCount >= 12 && $directUserCount >= 10) {
                                        $withdrawal['classification'] = "General 4";
                                    } elseif ($colonelCount >= 9 && $directUserCount >= 10) {
                                        $withdrawal['classification'] = "General 3";
                                    } elseif ($colonelCount >= 6 && $directUserCount >= 10) {
                                        $withdrawal['classification'] = "General 2";
                                    } elseif ($colonelCount >= 3 && $directUserCount >= 10) {
                                        $withdrawal['classification'] = "General 1";
                                    } elseif ($directUserCount >= 10) {
                                        $withdrawal['classification'] = "coronel";
                                    } elseif ($directUserCount >= 6) {
                                        $withdrawal['classification'] = "Afiliado 3";
                                    } elseif ($directUserCount >= 3) {
                                        $withdrawal['classification'] = "Afiliado 2";
                                    } else {
                                        $withdrawal['classification'] = "Afiliado 1";
                                    }

                                    $withdrawal['fullname'] = $withdrawal['firstname'] . " " . $withdrawal['lastname'];
                                    $withdrawal['surname'] = explode(' ', $withdrawal['lastname'])[0];
                                    $withdrawal['shortname'] = $withdrawal['firstname'] . " " . $withdrawal['surname'];
                                ?>
                                <tr data-status="<?= htmlspecialchars($withdrawal['status']); ?>">
                                    <td><?= $withdrawal['id']; ?></td>
                                    <td><?= $withdrawal['shortname']; ?></td>
                                    <td><?= $withdrawal['username']; ?></td>
                                    <td><?= $withdrawal['email']; ?></td>
                                    <td><?= $withdrawal['plan_name']; ?></td>
                                    <td><?= $withdrawal['classification']; ?></td>
                                    <td><?= $withdrawal['pix_key_type']; ?></td>
                                    <td><?= $withdrawal['pix_key']; ?></td>
                                    <td>R$ <?= number_format($withdrawal['amount'], 2, ',', '.'); ?></td>
                                    <td><?= $status_translation[$withdrawal['status']] ?? 'Desconhecido'; ?></td> <!-- Conversão de status -->
                                    <td><?= date('d/m/Y H:i', strtotime($withdrawal['request_date'])); ?></td>
                                    <td><?= (!empty($withdrawal['payment_date'])) ? date('d/m/Y H:i', strtotime($withdrawal['payment_date'])) : "Não Pago"; ?></td>
                                    <td>
                                        <?php if ($withdrawal['status'] == 'pending'): ?>
                                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>pagar-saque?id=<?= $withdrawal['id']; ?>" class="btn btn-success">Pagar</a>
                                        <?php else: ?>
                                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>pagar-saque?id=<?= $withdrawal['id']; ?>" class="btn btn-primary">Editar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="13" class="text-center">Nenhum saque encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inicializa DataTable
        var table = $("#withdrawals-table").DataTable({
            ordering: false,
            language: {
                "sProcessing": "Processando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                "sSearch": "Pesquisar:",
                "oPaginate": {
                    "sFirst": "Primeiro",
                    "sPrevious": "Anterior",
                    "sNext": "Próximo",
                    "sLast": "Último"
                }
            }
        });

        // Função para atualizar as contagens
        function updateCounts() {
            var total = table.rows().count();
            var countPendente = table
                .rows()
                .data()
                .filter(row => row[8] === "Pendente").length;
            var countPaid = table
                .rows()
                .data()
                .filter(row => row[8] === "Pago").length;
            var countCancelado = table
                .rows()
                .data()
                .filter(row => row[8] === "Cancelado").length;

            // Atualiza os textos dos botões
            $('.count-all').text(total);
            $('.count-pending').text(countPendente);
            $('.count-paid').text(countPaid);
            $('.count-canceled').text(countCancelado);
        }

        // Atualizar contagens ao carregar a página
        updateCounts();

        // Evento de clique nos botões de filtro
        $('.btn-filter').on('click', function() {
            var filter = $(this).attr('data-filter');

            // Ativa o botão clicado
            $('.btn-filter').removeClass('active');
            $(this).addClass('active');

            // Aplica o filtro no DataTable
            if (filter === 'all') {
                table.search('').draw();
            } else {
                table.search(filter).draw();
            }

            // Atualiza a contagem após aplicar o filtro
            updateCounts();
        });
    });

    $(document).ready(function(){
        $("input[type='search']").wrap("<form>");
        $("input[type='search']").closest("form").attr("autocomplete","off");
        $("input[type='search']").attr("autocomplete", "off");
    });
</script>