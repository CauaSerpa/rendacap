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
                SELECT u.id, u.username, u.email, s.status, p.price, p.id AS plan_id, p.name AS plan_name, u.token, 
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

// ID do usuário atual
$userId = $_SESSION['user_id']; // Ou substitua pelo ID do usuário que deseja consultar

// Gera o array de rede
$result = buildNetworkArray($conn, $userId);

// Exibe o resultado
// echo '<pre>';
// print_r($result);
// echo '</pre>';
?>

<?php // if ($result['diretosCount'] >= 1): ?>

<?php
    // Inicializa o array de HTML
    $htmlOutput = '<div class="container d-flex align-items-center justify-content-center mb-4"><div class="stepper">';

    $planId = $user['plan_id'];

    // Obtenha a rede do usuário
    $network = buildNetworkArray($conn, $userId);
    $directUsers = $network['network'][$userId]['convidados'] ?? [];
    $directUserCount = count($directUsers);

    // // Caso não tenha diretos, exibir a mensagem
    // if ($directUserCount == 0) {
    //     return '
    //         <div class="row">
    //             <div class="col-md-12">
    //                 <div class="main-card mb-3 card">
    //                     <div class="card-body">
    //                         <p class="fsize-2 font-weight-bold mb-2">Você ainda não tem diretos.</p>
    //                         <p class="fsize-1 mb-0">Envie seu link de convite e comece a criar sua rede!</p>
    //                     </div>
    //                 </div>
    //             </div>
    //         </div>
    //     ';
    // }

    // Variáveis para contagem de coronéis e general
    $colonelCount = 0;

    foreach ($directUsers as $convidadoId => $convidadoData) {
        $diretosCount = $convidadoData['diretos_count'];

        // Usuário com 10 ou mais diretos (Coronel)
        if ($diretosCount >= 10) {
            $colonelCount++;
        }
    }
?>

<?php
    $patent['marechal'] = false;
    $patent['general'] = false;
    $patent['coronel'] = false;

    if ($colonelCount >= 15 && $directUserCount >= 10) {
        $patent['marechal'] = true;
        $patent['general'] = false;
        $patent['coronel'] = false;
    } elseif ($colonelCount >= 3 && $directUserCount >= 10 && ($user['plan_id'] == 1 || $user['plan_id'] == 4)) {
        $patent['general'] = true;
        $patent['marechal'] = false;
        $patent['coronel'] = false;
    } elseif ($directUserCount >= 10) {
        $patent['coronel'] = true;
        $patent['marechal'] = false;
        $patent['general'] = false;
    }
?>























<?php

function calculateCashbackSummary($conn, $allUsers) {
    $totalCashbackLevel1 = 0;
    $totalCashbackLevel2To10 = 0;
    $allCashback = 0;
    $totalDirectUsers = 0;

    // Separar os cashbacks por nível e contar usuários diretos
    foreach ($allUsers as $user) {
        if ($user['type'] === 'Direto') {
            $totalCashbackLevel1 += $user['cashback'];
            $totalDirectUsers++; // Incrementa para cada usuário direto
        } else {
            $totalCashbackLevel2To10 += $user['cashback'];
        }
    }

    // Total geral
    $totalCashback = $totalCashbackLevel1 + $totalCashbackLevel2To10;

    // Consulta para somar os saques do usuário na tabela tb_withdrawals
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT SUM(amount) as totalWithdrawn
        FROM tb_withdrawals
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $withdrawalData = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalWithdrawn = $withdrawalData['totalWithdrawn'] ?? 0;

    // Valor disponível para saque
    $availableCashback = $totalCashback - $totalWithdrawn;

    // Criação do array final
    $result = [
        'totalCashbackLevel1' => $totalCashbackLevel1,
        'totalDirectUsers' => $totalDirectUsers,
        'totalCashbackLevel2To10' => $totalCashbackLevel2To10,
        'totalCashback' => $totalCashback,
        'allCashback' => $availableCashback
    ];

    return $result;
}

?>


<?php

function calculateAccumulatedCashback($conn, $allUsers) {
    $totalCashback = 0;

    // Soma todos os cashbacks dos usuários
    foreach ($allUsers as $user) {
        $totalCashback += $user['cashback'];
    }

    // Consulta para somar os saques do usuário na tabela tb_withdrawals
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT SUM(amount) as totalWithdrawn
        FROM tb_withdrawals
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $withdrawalData = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalWithdrawn = $withdrawalData['totalWithdrawn'] ?? 0;

    // Valor disponível para saque
    $availableCashback = $totalCashback - $totalWithdrawn;

    // Cria o array contendo o total acumulado
    $result = [
        'totalCashbackAvailable' => $availableCashback
    ];

    return $result;
}

?>


<?php
function listAssociatedUsersWithCashback($conn, $networkIds, $tipo = 'Direto', $drawDate = null, $level = 0, &$uniqueIds = []) {
    $allUsers = [];
    $maxLevel = 10;

    $query = "
        SELECT u.id, u.username, u.email, n.inviter_id, n.referrer_id, s.status AS subscription_status, s.due_date, p.price AS plan_price, p.id AS plan_id, p.name AS plan_name, ? AS type
        FROM tb_users u
        INNER JOIN tb_user_networks un ON u.id = un.user_id
        INNER JOIN tb_networks n ON n.id = un.network_id
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE u.status = 1 AND un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
    ";

    if ($drawDate) {
        $dateParts = explode('-', $drawDate);
        $startDate = DateTime::createFromFormat('d/m/Y', trim($dateParts[0]))->format('Y-m-d');
        $endDate = DateTime::createFromFormat('d/m/Y', trim($dateParts[1]))->format('Y-m-d');
        $query .= " AND s.payment_status = 'paid' AND s.creation_date BETWEEN ? AND ?";
    }

    $query .= " ORDER BY u.id DESC";

    $stmt = $conn->prepare($query);
    $params = array_merge([$tipo], $networkIds);
    if ($drawDate) {
        $stmt->execute(array_merge($params, [$startDate, $endDate]));
    } else {
        $stmt->execute($params);
    }

    $currentLevelUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nextNetworkIds = [];

    foreach ($currentLevelUsers as $user) {
        $stmt = $conn->prepare("
            SELECT id
            FROM tb_networks 
            WHERE referrer_id = ? OR (inviter_id = ? AND (referrer_id != ? OR referrer_id IS NULL))
        ");
        $stmt->execute([$user['id'], $user['id'], $_SESSION['user_id']]);
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Adicione os resultados à lista de próximos IDs de rede
        $nextNetworkIds = array_merge($nextNetworkIds, $result);
    }

    foreach ($currentLevelUsers as &$user) {
        if (in_array($user['id'], $uniqueIds)) {
            continue; // Ignora usuários já adicionados
        }
        $uniqueIds[] = $user['id'];

        $cashback = 0;
        $today = new DateTime();
        $dueDate = $user['due_date'] ? new DateTime($user['due_date']) : null;

        if ($dueDate) {

            // Calcular a diferença em meses
            $start = $dueDate;
            $end = $today;
            $interval = $start->diff($end);

            // Verificar se a diferença é maior que 1 mês
            if ($interval->m >= 1 || $interval->y > 0) {
                if (in_array($user['plan_id'], [1, 2, 4]) && $dueDate && $dueDate > $today) {
                    if ($level === 0) {
                        $cashback = $user['plan_price'] * 0.10;
                    } elseif ($level === 1) {
                        $cashback = $user['plan_price'] * 0.05;
                    } elseif ($level === 2) {
                        $cashback = $user['plan_price'] * 0.03;
                    } elseif ($level === 3) {
                        $cashback = $user['plan_price'] * 0.02;
                    } elseif ($level === 4) {
                        $cashback = $user['plan_price'] * 0.01;
                    } elseif ($level > 4 && $level <= $maxLevel) {
                        $cashback = $user['plan_price'] * 0.005;
                    }
                }
            } else {
                if ($user['type'] === 'Direto' && in_array($user['plan_id'], [1, 2, 4]) && $dueDate && $dueDate > $today) {
                    $cashback = $user['plan_price'] * 0.20;
                } else if ($user['inviter_id'] == $_SESSION['user_id'] && !empty($user['referrer_id']) && $user['type'] === 'Indireto' && in_array($user['plan_id'], [1, 2, 4]) && $dueDate && $dueDate > $today) {
                    $cashback = $user['plan_price'] * 0.05;
                } else if ($level === 1 && $user['type'] === 'Indireto' && in_array($user['plan_id'], [1, 2, 4]) && $dueDate && $dueDate > $today) {
                    $cashback = $user['plan_price'] * 0.05;
                }
            }
        
        }

        $user['cashback'] = $cashback;
        $allUsers[] = $user;
    }

    if (!empty($nextNetworkIds)) {
        $indirectUsers = listAssociatedUsersWithCashback($conn, $nextNetworkIds, 'Indireto', $drawDate, $level + 1, $uniqueIds);
        $allUsers = array_merge($allUsers, $indirectUsers);
    }

    return $allUsers;
}

// ID do usuário atual (da sessão)
$userId = $_SESSION['user_id'];

// Obtém a data do GET
$drawDate = isset($_GET['draw_date']) ? $_GET['draw_date'] : null;

// Consulta para obter todos os network_id associados ao usuário
$stmt = $conn->prepare("
    SELECT id
    FROM tb_networks 
    WHERE referrer_id = ? OR (inviter_id = ? AND referrer_id IS NULL)
");
$stmt->execute([$userId, $userId]);
$networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Consulta para obter todos os network_id associados ao usuário
$stmt = $conn->prepare("
    SELECT id
    FROM tb_networks 
    WHERE inviter_id = ? AND referrer_id != ?
");
$stmt->execute([$userId, $userId]);
$indirectNetworkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($networkIds)) {
    $users = listAssociatedUsersWithCashback($conn, $networkIds, 'Direto', $drawDate);
    $cashbackSummary = calculateCashbackSummary($conn, $users);
    $accumulatedCashback = calculateAccumulatedCashback($conn, $users);
} else if (!empty($indirectNetworkIds)) {
    $users = listAssociatedUsersWithCashback($conn, $indirectNetworkIds, 'Indireto', $drawDate);
    $cashbackSummary = calculateCashbackSummary($conn, $users);
    $accumulatedCashback = calculateAccumulatedCashback($conn, $users);
} else {
    $users = []; // Sem usuários associados
    $cashbackSummary = calculateCashbackSummary($conn, $users);
    $accumulatedCashback = calculateAccumulatedCashback($conn, $users);
}

// Combine os resultados
$networkCashbackData = array_merge($cashbackSummary, $accumulatedCashback);
?>











<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-wallet icon-gradient bg-plum-plate"></i>
            </div>
            <div>
                Financeiro
                <div class="page-title-subheading">Aqui você pode gerenciar e analisar todas as suas informações financeiras de forma prática e segura, além de realizar saques com facilidade.</div>
            </div>
        </div>
        <div class="page-title-actions">
            <?php 
                // // Verifica o dia da semana (0 = Domingo, 1 = Segunda, ..., 4 = Quinta-feira)
                // $hoje = date('w');
                // $isWithdrawDay = ($hoje >= 2 && $hoje <= 4); // Terça (2) até Quinta (4)
                // $horaAtual = date('H:i');
                // $isWithdrawTime = ($horaAtual >= '08:00' && $hoje == 2 || $horaAtual <= '18:00' && $hoje == 4); // Entre 08:00h e 18:00h
                // $networkCashbackTotal = $networkCashbackData['totalCashbackAvailable'];
                // $userPlan = $user['plan_id']; // Supondo que você tem o plano do usuário no $userData
            ?>

            <!-- Botão de Saque
            <button id="withdrawBtn"
                    <?php if ($userPlan == 3): ?> 
                        data-toggle="tooltip" 
                        title="Saques estão disponíveis apenas para usuários de planos avançados. Faça o upgrade do seu plano para desbloquear esta funcionalidade."
                    <?php elseif ($isWithdrawDay && $isWithdrawTime): ?>
                        data-toggle="modal" data-target="#withdrawModal"
                        title="Saque disponível: Efetue seu saque das bonificações."
                    <?php else: ?>
                        data-toggle="tooltip" 
                        title="Saques disponíveis somente entre terça-feira às 08:00h e quinta-feira às 18:00h."
                    <?php endif; ?>
                    data-placement="top"
                    class="btn-shadow mr-3 btn btn-dark 
                    <?= ($userPlan == 3 || !$isWithdrawDay || !$isWithdrawTime) ? 'disabled' : ''; ?>"
            >
                Solicitar Saque
            </button> -->
            <!-- Botão para abrir o modal -->
            <!-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#withdrawModal">
                <i class="fa fa-fw"></i>
            </button> -->

            <?php
                // Obtém o dia da semana (0 = Domingo, 1 = Segunda, ..., 6 = Sábado)
                $hoje = date('w');

                // Obtém a hora atual no formato 24h
                $horaAtual = date('H:i');

                // Verifica o plano do usuário
                $userPlan = $user['plan_id']; // Supondo que a informação do plano está em $user

                // Inicializa a variável do botão como desabilitado
                $isButtonEnabled = false;
                $message = '';

                // Lógica para habilitar o botão
                if ($userPlan == 3) {
                    // Plano especial: sempre habilitado
                    $isButtonEnabled = false;
                    $message = "Saques estão disponíveis apenas para usuários de planos avançados. Faça o upgrade do seu plano para desbloquear esta funcionalidade.";
                } else {
                    // Habilita o botão apenas entre terça-feira às 08:00 e quinta-feira às 18:00
                    if (($hoje == 2 && $horaAtual >= '08:00') || // Terça-feira após 08:00
                        ($hoje == 3) || // Qualquer horário da quarta-feira
                        ($hoje == 4 && $horaAtual <= '18:00')) { // Quinta-feira até 18:00
                        $isButtonEnabled = true;
                        $message = "Saque disponível: Efetue seu saque das bonificações.";
                    } else {
                        $message = "Saques disponíveis somente entre terça-feira às 08:00h e quinta-feira às 18:00h.";
                    }
                }
            ?>

            <!-- Exemplo de uso no HTML -->
            <div>
                <button 
                    <?= $isButtonEnabled ? '' : 'disabled'; ?> 
                    class="btn btn-primary"
                    title="<?= $message; ?>"
                    data-toggle="modal" 
                    <?= $isButtonEnabled ? 'data-target="#withdrawModal"' : 'data-toggle="tooltip"'; ?>>
                    Realizar Saque
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
            <div class="icon-wrapper rounded-circle">
                <div class="icon-wrapper-bg bg-success"></div>
                <i class="pe-7s-wallet text-success"></i>
            </div>
            <div class="widget-chart-content">
                <div class="widget-subheading">Valor acumulado</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span id="cashbackValue" class="count-up-real" data-value="<?= $networkCashbackData['totalCashbackAvailable']; ?>">****</span> <!-- Valor oculto por padrão -->
                    <i id="toggleCashback" class="pe-7s-look text-success cursor-pointer" onclick="toggleVisibility()"></i>
                </div>
                <div class="widget-subheading">Saldo total acumulado na plataforma</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card mb-3 widget-chart widget-chart2 text-left card-btm-border <?= ($patent['coronel'] == true) ? "card-shadow-success border-success" : "card-shadow-danger border-danger"; ?> card">
            <div class="widget-chart-content">
                <h6 class="fsize-2 mb-0 <?= ($patent['coronel'] == true) ? "text-success" : "text-danger"; ?>"><?= ($patent['coronel'] == true) ? "QUALIFICADO" : "NÃO QUALIFICADO"; ?></h6>
                <p class="mb-2">Cashback <span class="text-uppercase <?= ($patent['general'] == true) ? "text-success" : "text-danger"; ?>">Coronel</span></p>

                <p class="mb-0">Mensalidade</p>
                <p class="mb-0">5% todo Brasil</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card mb-3 widget-chart widget-chart2 text-left card-btm-border <?= ($patent['general'] == true) ? "card-shadow-success border-success" : "card-shadow-danger border-danger"; ?> card">
            <div class="widget-chart-content">
                <h6 class="fsize-2 mb-0 <?= ($patent['general'] == true) ? "text-success" : "text-danger"; ?>"><?= ($patent['general'] == true) ? "QUALIFICADO" : "NÃO QUALIFICADO"; ?></h6>
                <p class="mb-2">Cashback <span class="text-uppercase <?= ($patent['general'] == true) ? "text-success" : "text-danger"; ?>">General</span></p>

                <p class="mb-0">Mensalidade</p>
                <p class="mb-0">10% todo Brasil</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card mb-3 widget-chart widget-chart2 text-left card-btm-border <?= ($patent['marechal'] == true) ? "card-shadow-success border-success" : "card-shadow-danger border-danger"; ?> card">
            <div class="widget-chart-content">
                <h6 class="fsize-2 mb-0 <?= ($patent['marechal'] == true) ? "text-success" : "text-danger"; ?>"><?= ($patent['marechal'] == true) ? "QUALIFICADO" : "NÃO QUALIFICADO"; ?></h6>
                <p class="mb-2">Cashback <span class="text-uppercase <?= ($patent['general'] == true) ? "text-success" : "text-danger"; ?>">Marechal</span></p>

                <p class="mb-0">Mensalidade</p>
                <p class="mb-0">5% todo Brasil</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
            <div class="icon-wrapper rounded-circle">
                <div class="icon-wrapper-bg bg-success"></div>
                <i class="fa fa-fw text-success"></i>
            </div>
            <div class="widget-chart-content">
                <div class="widget-subheading">Bonificação das Indicações Diretas</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $networkCashbackData['totalCashbackLevel1']; ?>"><?= number_format($networkCashbackData['totalCashbackLevel1'], 2, ',', '.'); ?></span>
                </div>
                <div class="widget-description opacity-8 text-focus">
                    Diretos
                    <span class="text-success pl-1 pr-1">
                        <span class="pr-1"><?= $networkCashbackData['totalDirectUsers']; ?></span>
                        <i class="fa fa-angle-up "></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-warning border-warning card">
            <div class="icon-wrapper rounded-circle">
                <div class="icon-wrapper-bg bg-warning"></div>
                <i class="fa fa-fw text-warning" aria-hidden="true" title="Copy to use line-chart"></i>
            </div>
            <div class="widget-chart-content">
                <div class="widget-subheading">Bonificações do 2º ao 10º nível</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $networkCashbackData['totalCashbackLevel2To10']; ?>"><?= number_format($networkCashbackData['totalCashbackLevel2To10'], 2, ',', '.'); ?></span>
                </div>
                <div class="widget-subheading">Bônus Rede</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-primary border-primary card">
            <div class="icon-wrapper rounded-circle">
                <div class="icon-wrapper-bg bg-primary"></div>
                <i class="fa fa-fw text-primary" aria-hidden="true" title="Copy to use bar-chart"></i>
            </div>
            <div class="widget-chart-content">
                <div class="widget-subheading">Saldo Total de Ganhos de Rede</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $networkCashbackData['totalCashback']; ?>"><?= number_format($networkCashbackData['totalCashback'], 2, ',', '.'); ?></span>
                </div>
                <div class="widget-subheading">Saldo Total</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
            <div class="icon-wrapper rounded-circle">
                <div class="icon-wrapper-bg bg-success"></div>
                <i class="fa fa-fw text-success" aria-hidden="true" title="Copy to use arrow-up"></i>
            </div>
            <div class="widget-chart-content">
                <div class="widget-subheading">Saldo Disponível para Saque</div>
                <div class="widget-numbers">
                    <small class="opacity-5 pr-1">R$</small>
                    <span class="count-up-real" data-value="<?= $networkCashbackData['allCashback']; ?>"><?= number_format($networkCashbackData['allCashback'], 2, ',', '.'); ?></span>
                </div>
                <div class="widget-subheading">Saldo Disponível</div>
            </div>
        </div>
    </div>
</div>


<script>
    // Função para ocultar e exibir o valor
    function toggleVisibility() {
        const valueElement = document.getElementById('cashbackValue');
        const currentText = valueElement.textContent.trim();
        
        if (currentText === '****') {
            valueElement.textContent = '<?= number_format($networkCashbackData['totalCashbackAvailable'], 2, ',', '.'); ?>'; // Exibe o valor
        } else {
            valueElement.textContent = '****'; // Oculta o valor
        }
    }
</script>

<style>
    .dataTables_scrollBody {
        margin-bottom: 1.25rem !important;
    }

    .btn-filter.active {
        background-color: #007bff;
        color: #fff;
    }

    .client-info {
        display: flex;
        align-items: center;
    }
    .client-info .avatar-icon {
        margin-right: 10px;
    }
</style>

<?php
// Array de status com as traduções desejadas
$status_translation = [
    'ACTIVE' => 'Ativo',
    'EXPIRED' => 'Expirado',
    'INACTIVE' => 'Inativo',
    'CANCELED' => 'Cancelado'
];

// Função para traduzir o status
function translate_status($status) {
    global $status_translation;
    
    // Verifica se o status existe no array de tradução
    if (isset($status_translation[$status])) {
        return $status_translation[$status];
    }

    // Retorna o status original se não houver tradução
    return $status;
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Meus Vouchers</h5>

                <div class="row mb-3">
                    <div class="col">
                        <!-- Botões de Filtro -->
                        <div class="btn-group mb-4" id="filter" role="group" aria-label="Filtro de Faturas">
                            <button type="button" class="btn btn-secondary btn-filter active" data-filter="all">Todos (<span class="count-all">0</span>)</button>
                            <button type="button" class="btn btn-secondary btn-filter" data-filter="ACTIVE">Pago (<span class="count-pago">0</span>)</button>
                            <button type="button" class="btn btn-secondary btn-filter" data-filter="INACTIVE">Pendente (<span class="count-pendente">0</span>)</button>
                            <button type="button" class="btn btn-secondary btn-filter" data-filter="EXPIRED">Expirado (<span class="count-expirado">0</span>)</button>
                            <button type="button" class="btn btn-secondary btn-filter" data-filter="CANCELED">Cancelado (<span class="count-cancelado">0</span>)</button>
                            <button type="button" class="btn btn-secondary btn-filter" data-filter="UNDEFINED">Indefinido (<span class="count-indefinido">0</span>)</button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <form method="GET" action="">
                            <label for="draw_date">Filtrar por data:</label>
                            <div class="d-flex align-items-center">
                                <input type="text" class="form-control" id="draw_date" name="draw_date" 
                                    value="<?php echo isset($_GET['draw_date']) ? htmlspecialchars($_GET['draw_date']) : ''; ?>">
                                <button type="submit" class="btn btn-primary btn-lg ml-2">Filtrar</button>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm py-0 mt-2" id="removeFilterBtn" 
                                    style="display: <?php echo isset($_GET['draw_date']) ? 'inline-block' : 'none'; ?>;"
                                    onclick="removeGet()">Remover Filtro</button>
                        </form>
                    </div>
                </div>


                <!-- Tabela de Faturas -->
                <div class="table-responsive">
                    <table id="invoices-table" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Plano</th>
                                <th>Tipo</th>
                                <th>Valor do Plano</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)) : ?>
                                <?php foreach ($users as $user) : ?>
                                    <?php
                                        $user['cashback'] = "R$ " . number_format($user['cashback'], 2, ',', '.');
                                    ?>
                                    <tr data-status="<?= $user['subscription_status'] ?? "UNDEFINED"; ?>">
                                        <td><?= $user['id']; ?></td>
                                        <td><?= $user['username']; ?></td>
                                        <td><?= $user['plan_name'] ?? "Indefinido"; ?></td>
                                        <td><?= $user['type']; ?></td>
                                        <td><?= $user['cashback']; ?></td>
                                        <td><?= translate_status($user['subscription_status']) ?? "Indefinido"; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum usuário encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    // Consulta SQL para buscar os pedidos de saque do usuário
    $sql = "
        SELECT 
            w.id,
            w.amount AS price,
            w.request_date,
            w.status,
            w.payment_date,
            w.admin_notes,
            w.proof_of_transfer
        FROM 
            tb_withdrawals w
        WHERE 
            w.user_id = :user_id
        ORDER BY 
            w.request_date DESC
    ";

    // Preparar e executar a consulta
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    // Obter os resultados
    $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    function translate_withdrawals_status($status) {
        $statuses = [
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'cancelled' => 'Cancelado'
        ];
        return $statuses[$status] ?? $status;
    }
?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Meus Pedidos De Saques</h5>

                <!-- Tabela de Saques Solicitados -->
                <div class="table-responsive">
                    <table id="withdrawals-table" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Valor Solicitado</th>
                                <th>Data do Saque</th>
                                <th>Status</th>
                                <th>Data de Pagamento</th>
                                <th>Notas do Administrador</th>
                                <th>Comprovante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($withdrawals)) : ?>
                                <?php foreach ($withdrawals as $withdrawal) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($withdrawal['id']); ?></td>
                                        <td><?= "R$ " . number_format($withdrawal['price'], 2, ',', '.'); ?></td>
                                        <td><?= date('d/m/Y H:i:s', strtotime($withdrawal['request_date'])); ?></td>
                                        <td><?= translate_withdrawals_status($withdrawal['status']) ?? "Indefinido"; ?></td>
                                        <td><?= !empty($withdrawal['payment_date']) ? date('d/m/Y H:i:s', strtotime($withdrawal['payment_date'])) : '--'; ?></td>
                                        <td><?= htmlspecialchars($withdrawal['admin_notes'] ?? '--'); ?></td>
                                        <td>
                                            <?php if (!empty($withdrawal['proof_of_transfer'])) : ?>
                                                <a href="<?= htmlspecialchars($withdrawal['proof_of_transfer']); ?>" target="_blank" download>Baixar Comprovante</a>
                                            <?php else : ?>
                                                --
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center">Nenhum saque encontrado.</td>
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
    $(function() {
        $('input[name="draw_date"]').daterangepicker({
            startDate: moment(),
            endDate: moment(),
            locale: {
                format: 'DD/MM/YYYY', // Formato de data
                applyLabel: 'Aplicar', // Texto do botão de aplicar
                cancelLabel: 'Cancelar', // Texto do botão de cancelar
                fromLabel: 'De', // Texto do rótulo "De"
                toLabel: 'Até', // Texto do rótulo "Até"
                weekLabel: 'Sem', // Texto do rótulo "Semana"
                customRangeLabel: 'Personalizado', // Texto do rótulo "Personalizado"
                daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'], // Dias da semana
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'], // Meses
            },
            opens: 'center', // Opção de abertura
            autoApply: true // Aplicar automaticamente
        });

        // Se a data estiver preenchida no input, inicializa o daterangepicker
        let drawDate = $('input[name="draw_date"]').val();
        if (drawDate) {
            let dates = drawDate.split(' - ');
            $('input[name="draw_date"]').daterangepicker({
                startDate: moment(dates[0], 'DD/MM/YYYY'),
                endDate: moment(dates[1], 'DD/MM/YYYY'),
                locale: {
                    format: 'DD/MM/YYYY',
                },
                opens: 'center',
                autoApply: true
            });
        }
    });

    $(document).ready(function() {
        // Pega os parâmetros da URL
        const urlParams = new URLSearchParams(window.location.search);
        const drawDate = urlParams.get('draw_date');

        // Se draw_date estiver presente, preenche o input e mostra o botão
        if (!drawDate) {
            $('input[name="draw_date"]').val("");
        }
    });

    // Função para remover o GET
    function removeGet() {
        const url = window.location.href.split('?')[0]; // Remove a query string
        window.location.href = url; // Redireciona para a URL sem o GET
    }
</script>

<script>
    $(document).ready(function() {
        function verificarTamanhoTela() {
            // Obtém a largura da tela
            var larguraTela = $(window).width();

            // Adiciona ou remove a classe baseado no tamanho da tela
            if (larguraTela < 600) { // Alterar o valor de 768 para o tamanho desejado
                $('#filter').addClass('btn-group-vertical').removeClass('btn-group');
            } else {
                $('#filter').addClass('btn-group').removeClass('btn-group-vertical');
            }
        }

        // Verifica o tamanho da tela ao carregar a página
        verificarTamanhoTela();

        // Detecta mudanças no tamanho da tela
        $(window).resize(function() {
            verificarTamanhoTela();
        });
    });
</script>

<!-- Count Up -->
<script>
    $(document).ready(function() {
        $('.count-up').each(function() {
            var $this = $(this);
            var value = $this.data('value');
            
            $this.text('0,00'); // Inicia com zero

            $({ count: 0 }).animate({ count: value }, {
                duration: 2000, // Duração da animação em milissegundos
                step: function() {
                    $this.text(Number(this.count).toFixed(2).replace('.', ','));
                },
                complete: function() {
                    $this.text(Number(this.count).toFixed(2).replace('.', ','));
                }
            });
        });
    });
</script>

<script>
    // Datatables

    $(document).ready(() => {
        // Inicializa DataTable
        var table = $("#invoices-table").DataTable({
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
            var countPago = table
                .rows()
                .data()
                .filter(row => row[6] === "Pago").length;
            var countPendente = table
                .rows()
                .data()
                .filter(row => row[6] === "Pendente").length;
            var countExpirado = table
                .rows()
                .data()
                .filter(row => row[6] === "Expirado").length;
            var countCancelado = table
                .rows()
                .data()
                .filter(row => row[6] === "Cancelado").length;
            var countIndefinido = table
                .rows()
                .data()
                .filter(row => row[6] === "Indefinido").length;

            // Atualiza os textos dos botões
            $('.count-all').text(total);
            $('.count-pago').text(countPago);
            $('.count-pendente').text(countPendente);
            $('.count-expirado').text(countExpirado);
            $('.count-cancelado').text(countCancelado);
            $('.count-indefinido').text(countIndefinido);
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

<script>
    // Datatables

    $(document).ready(() => {
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
    });
</script>