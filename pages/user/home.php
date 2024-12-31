<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-home icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Início
                <div class="page-title-subheading">Aqui você faz a gestão do seu negócio RCB.</div>
            </div>
        </div>
    </div>
</div>

<?php
// Função recursiva para listar usuários (diretos e indiretos em todos os níveis)
function listNetworkData($conn, $networkIds, $currentUserId, &$processedUsers = []) {
    $allUsers = [];

    // Consulta para listar usuários diretos ou indiretos
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.email, u.status, p.id AS plan_id, p.price, p.name AS plan_name, u.token, 
               n.inviter_id, n.referrer_id, s.payment_id, s.status AS subs_status, 
               CASE 
                   WHEN n.inviter_id = ? AND n.referrer_id IS NULL THEN 'direct'
                   WHEN n.referrer_id = ? THEN 'direct'
                   ELSE 'indirect' 
               END AS tipo,
               u.date_create
        FROM tb_users u
        INNER JOIN tb_user_networks un ON u.id = un.user_id
        LEFT JOIN tb_networks n ON un.network_id = n.id
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
        ORDER BY u.id DESC
    ");
    
    $stmt->execute(array_merge([$currentUserId, $currentUserId], $networkIds));
    $currentLevelUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os usuários encontrados ao array geral, evitando duplicatas
    foreach ($currentLevelUsers as $userDetail) {
        if (!in_array($userDetail['id'], $processedUsers)) {
            $allUsers[] = $userDetail;
            $processedUsers[] = $userDetail['id']; // Marca o usuário como processado
        }
    }

    // Coleta os IDs dos usuários encontrados para buscar seus convidados
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

    // Se houver mais usuários indiretos, chama a função recursivamente
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

// Variáveis para contagem
$directUsers = [];
$indirectUsers = [];
$pendingUsers = [];
$allUsers = [];

// Se houver redes associadas, listar os usuários
if (!empty($networkIds)) {
    $networkData = listNetworkData($conn, $networkIds, $userId);

    // Separar os usuários em diretos e indiretos
    foreach ($networkData as $userDetail) {
        if (
            $userDetail['status'] != 1 || 
            $userDetail['plan_id'] == 3 || 
            strpos($userDetail['payment_id'], 'subs_test_') !== false || // Contém 'subs_test_'
            strpos($userDetail['payment_id'], 'free_subs_') !== false    // Contém 'free_subs_'
        ) {
            $pendingUsers[] = $userDetail;
        }

        if (
            $userDetail['tipo'] == 'direct' && 
            isset($userDetail['payment_id']) && 
            strpos($userDetail['payment_id'], 'subs_test_') === false && 
            strpos($userDetail['payment_id'], 'free_subs_') === false
        ) {
            $directUsers[] = $userDetail;
        } elseif ($userDetail['tipo'] == 'indirect') {
            $indirectUsers[] = $userDetail;
        }
    }

    // Contagens
    $totalUsersCount = count($networkData); // Total de usuários
    $directUsersCount = count($directUsers); // Total de diretos
    $indirectUsersCount = count($indirectUsers); // Total de indiretos
    $pendingUsersCount = count($pendingUsers); // Total de indiretos

    $networkData = [
        // Necessarios
        'directUsersPaidCount' => $directUsersCount,
        'planBronzeCount' => $pendingUsersCount,
        'indirectUserCount' => $indirectUsersCount,
        'totalUsers' => $totalUsersCount
    ];

    // // Exibir as contagens
    // echo "Total de usuários: " . $totalUsersCount . "<br>";
    // echo "Usuários diretos: " . $directUsersCount . "<br>";
    // echo "Usuários indiretos: " . $indirectUsersCount . "<br>";
    // echo "Usuários pendentes: " . $pendingUsersCount . "<br>";
} else {
    $networkData = [
        // Necessarios
        'directUsersPaidCount' => 0,
        'planBronzeCount' => 0,
        'indirectUserCount' => 0,
        'totalUsers' => 0
    ];
}
?>












<?php
// function countAllSubusers($conn, $networkIds, $depth = 0, &$layerCounts = [], &$maxDepth = 0, &$pendingUsersCount = 0, &$planBronzeCount = 0, &$directUsersPaidCount = 0) {
//     if ($depth > 0) {
//         if (!isset($layerCounts[$depth])) {
//             $layerCounts[$depth] = 0;
//         }
//     }

//     $count = 0;

//     foreach ($networkIds as $networkId) {
//         // Consulta para obter todos os user_id associados a network_id
//         $stmt = $conn->prepare("
//             SELECT user_id
//             FROM tb_user_networks
//             WHERE network_id = ?
//         ");
//         $stmt->execute([$networkId]);
//         $subuser_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

//         // Conta os subusuários associados ao network_id atual
//         if ($depth > 0) {
//             $layerCounts[$depth] += count($subuser_ids);
//         }
//         $count += count($subuser_ids);

//         foreach ($subuser_ids as $subuser_id) {
//             // Verificar o plano do subusuário
//             $stmt = $conn->prepare("
//                 SELECT status, plan_id
//                 FROM tb_subscriptions
//                 WHERE user_id = ?
//                 AND payment_id NOT LIKE '%subs_test_%'
//             ");
//             $stmt->execute([$subuser_id]);
//             $subscriptionData = $stmt->fetch(PDO::FETCH_ASSOC);

//             // Se o usuário não tiver assinatura ou estiver com assinatura não paga, contar como não pago
//             if (!$subscriptionData || $subscriptionData['status'] != 'ACTIVE') {
//                 $pendingUsersCount++;
//             }

//             // Contar usuários com base no plano (plan_id = 3 ou outros planos)
//             if (!$subscriptionData || $subscriptionData['plan_id'] == 3) {
//                 $planBronzeCount++;
//             } else {
//                 $directUsersPaidCount++;
//             }

//             // Consulta para obter todos os network_id associados ao subusuário
//             $stmt = $conn->prepare("
//                 SELECT id
//                 FROM tb_networks
//                 WHERE referrer_id = ? OR inviter_id = ?
//             ");
//             $stmt->execute([$subuser_id, $subuser_id]);
//             $subnetworkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

//             // Contagem recursiva
//             $count += countAllSubusers($conn, $subnetworkIds, $depth + 1, $layerCounts, $maxDepth, $pendingUsersCount, $planBronzeCount, $directUsersPaidCount);
//         }
//     }

//     // Atualizar a profundidade máxima
//     if ($depth > $maxDepth) {
//         $maxDepth = $depth;
//     }

//     return $count;
// }

// function getNetworkUsers($currentUserId, $conn) {
//     // Consulta para obter todos os network_id associados ao usuário
//     $stmt = $conn->prepare("
//         SELECT id
//         FROM tb_networks
//         WHERE referrer_id = ? OR inviter_id = ?
//     ");
//     $stmt->execute([$currentUserId, $currentUserId]);
//     $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

//     if ($networkIds) {
//         // Consulta para obter todos os network_id associados ao usuário diretamente
//         $stmt = $conn->prepare("
//             SELECT id
//             FROM tb_networks
//             WHERE referrer_id = ? OR (inviter_id = ? AND referrer_id IS NULL)
//         ");
//         $stmt->execute([$currentUserId, $currentUserId]);
//         $networkDirectIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

//         if ($networkDirectIds) {
//             // Contar todos os usuários diretamente relacionados
//             $stmt = $conn->prepare("
//                 SELECT user_id
//                 FROM tb_user_networks
//                 WHERE network_id IN (" . implode(',', array_fill(0, count($networkDirectIds), '?')) . ")
//             ");
//             $stmt->execute($networkDirectIds);
//             $directUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

//             // Contar o número de usuários diretamente relacionados
//             $directUserCount = count($directUsers);
//         } else {
//             $directUserCount = 0;
//         }

//         // Contar todos os usuários relacionados (incluindo subníveis) e os usuários sem assinatura paga
//         $layerCounts = [];
//         $maxDepth = 0;
//         $pendingUsersCount = 0; // Inicializar a contagem de usuários sem assinatura paga
//         $planBronzeCount = 0;  // Contagem de usuários com plan_id = 3
//         $directUsersPaidCount = 0;  // Contagem de usuários com outros planos
//         $totalCount = countAllSubusers($conn, $networkIds, 0, $layerCounts, $maxDepth, $pendingUsersCount, $planBronzeCount, $directUsersPaidCount);

//         // Contar usuários indiretos (excluindo os diretamente relacionados)
//         $indirectUserCount = $totalCount - $directUserCount;
//     } else {
//         $directUserCount = 0;
//         $indirectUserCount = 0;
//         $planBronzeCount = 0;
//         $totalCount = 0;
//         $pendingUsersCount = 0;
//     }

//     return [
//         // Necessarios
//         'directUsersPaidCount' => $directUsersPaidCount,
//         'planBronzeCount' => $planBronzeCount,
//         'indirectUserCount' => $indirectUserCount,
//         'totalUsers' => $totalCount
//     ];
// }

// // Exemplo de uso
// $currentUserId = $_SESSION['user_id']; // ID do usuário atual
// $networkData = getNetworkUsers($currentUserId, $conn);

// // Exibir o resultado
// echo '<pre>';
// print_r($networkData);
// echo '</pre>';
?>

<?php
function countAllSubusers($conn, $networkIds, $depth = 0, &$layerCounts = [], &$maxDepth = 0, &$unpaidUsers = 0) {
    if ($depth > 0) {
        if (!isset($layerCounts[$depth])) {
            $layerCounts[$depth] = 0;
        }
    }

    $count = 0;

    foreach ($networkIds as $networkId) {
        // Consulta para obter todos os user_id associados a network_id
        $stmt = $conn->prepare("
            SELECT user_id
            FROM tb_user_networks
            WHERE network_id = ?
        ");
        $stmt->execute([$networkId]);
        $subuser_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Conta os subusuários associados ao network_id atual
        if ($depth > 0) {
            $layerCounts[$depth] += count($subuser_ids);
        }
        $count += count($subuser_ids);

        foreach ($subuser_ids as $subuser_id) {
            // Verificar o status da assinatura do subusuário
            $stmt = $conn->prepare("
                SELECT status
                FROM tb_subscriptions
                WHERE user_id = ?
                AND payment_id NOT LIKE '%subs_test_%'
                AND plan_id NOT LIKE 3
            ");
            $stmt->execute([$subuser_id]);
            $subscriptionStatus = $stmt->fetchColumn();

            // Se o usuário não tiver assinatura ou estiver com assinatura não paga, contar como não pago
            if (!$subscriptionStatus || $subscriptionStatus != 'ACTIVE') {
                $unpaidUsers++;
            }

            // Consulta para obter todos os network_id associados ao subusuário
            $stmt = $conn->prepare("
                SELECT id
                FROM tb_networks
                WHERE referrer_id = ? OR inviter_id = ?
            ");
            $stmt->execute([$subuser_id, $subuser_id]);
            $subnetworkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Contagem recursiva
            $count += countAllSubusers($conn, $subnetworkIds, $depth + 1, $layerCounts, $maxDepth, $unpaidUsers);
        }
    }

    // Atualizar a profundidade máxima
    if ($depth > $maxDepth) {
        $maxDepth = $depth;
    }

    return $count;
}

// ID do usuário atual
$userId = $_SESSION['user_id']; // Ou substitua pelo ID do usuário que deseja consultar

// Consulta para obter todos os network_id associados ao usuário
$stmt = $conn->prepare("
    SELECT id
    FROM tb_networks
    WHERE referrer_id = ? OR inviter_id = ?
");
$stmt->execute([$userId, $userId]);
$networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($networkIds) {
    // Consulta para obter todos os network_id associados ao usuário
    $stmt = $conn->prepare("
        SELECT id
        FROM tb_networks
        WHERE referrer_id = ? OR (inviter_id = ? AND referrer_id IS NULL)
    ");
    $stmt->execute([$userId, $userId]);
    $networkDirectIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if ($networkDirectIds) {
        // Contar todos os usuários diretamente relacionados
        $stmt = $conn->prepare("
            SELECT user_id
            FROM tb_user_networks
            WHERE network_id IN (" . implode(',', array_fill(0, count($networkDirectIds), '?')) . ")
        ");
        $stmt->execute($networkDirectIds);
        $directUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Contar o número de usuários diretamente relacionados
        $directUserCount = count($directUsers);
    } else {
        $directUserCount = 0;
    }
    
    // Contar todos os usuários relacionados (incluindo subníveis) e os usuários sem assinatura paga
    $layerCounts = [];
    $maxDepth = 0;
    $unpaidUsers = 0; // Inicializar a contagem de usuários sem assinatura paga
    $totalCount = countAllSubusers($conn, $networkIds, 0, $layerCounts, $maxDepth, $unpaidUsers);
    
    // Contar usuários indiretos (excluindo os diretamente relacionados)
    $indirectUserCount = $totalCount - $directUserCount;
    
    // Exibir a contagem de camadas
    $nonEmptyLayers = array_filter($layerCounts, fn($count) => $count > 0); // Filtrar camadas não vazias
    $totalNonEmptyLayers = count($nonEmptyLayers);
    
    // Exibir a contagem de camadas
    $nonEmptyLayers = array_filter($layerCounts, fn($count) => $count > 0); // Filtrar camadas não vazias
    $totalNonEmptyLayers = count($nonEmptyLayers);

    
    // Exibir o número de usuários sem assinatura paga
    // echo "Número total de usuários sem assinatura ou com assinatura não paga: " . $unpaidUsers . "<br>";
    
    // echo "Número total de camadas existentes: " . $totalNonEmptyLayers . "<br>";
    
    // // Exibir contagem de usuários por camada
    // foreach ($nonEmptyLayers as $layer => $count) {
    //     echo "Total de usuários na camada " . ($layer + 1) . ": " . $count . "<br>";
    // }
    
    // // Exibir os resultados
    // echo "Total de usuários diretamente relacionados: " . $directUserCount . "<br>";
    // echo "Total de usuários indiretos relacionados: " . $indirectUserCount . "<br>";
    // echo "Total de usuários relacionados (incluindo subníveis): " . $totalCount . "<br>";
    // echo "Total de usuários do segundo nível: " . $secondLevelUserCount . "<br>";

    // echo "Número total de camadas existentes: " . $totalNonEmptyLayers . "<br>";
    
    // // Exibir contagem de usuários por camada
    // foreach ($nonEmptyLayers as $layer => $count) {
    //     echo "Total de usuários na camada " . ($layer + 1) . ": " . $count . "<br>";
    // }
    
    // $countAllCashback = $cashbackPerDirectUser + $cashbackPerLayerUser;
    
    // // Exibir cashback total para camadas de 2 a 10
    // echo "Cashback total para camada 1: R$ " . number_format($cashbackPerDirectUser, 2, ',', '.') . "<br>";
    // echo "Cashback total para camadas de 2 a 10: R$ " . number_format($cashbackByLayerTotal, 2, ',', '.') . "<br>";
} else {
    $directUserCount = 0;
    $indirectUserCount = 0;
    $totalCount = 0;
    $unpaidUsers = 0;
    $nonEmptyLayers = 0;
    $totalNonEmptyLayers = 0;
    $nonEmptyLayers = 0;
    $totalNonEmptyLayers = 0;
}


// Geracao antiga (com trava)
// Geracao de cada plano
// if ($user['plan_id'] == 4) {
//     $generation = "10";
// } else if ($user['plan_id'] == 3) {
//     $generation = "1";
// } else if ($user['plan_id'] == 2) {
//     $generation = "5";
// } else if ($user['plan_id'] == 1) {
//     $generation = "7";
// }

// Nova geracao
$generation = "10";
?>

<style>
    .stepper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 90%;
    }
    
    .stepper .stepper-rank {
        display: flex;
        justify-content: space-around;
        align-items: center;
        width: 90%;
    }

    .stepper .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        text-align: center;
    }

    .stepper .step .circle {
        position: relative;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background-color: #4CAF50;
        border: 2px solid #4CAF50;
    }

    .stepper .step .circle::before {
        position: absolute;
        left: 50%;
        top: 50%;
        content: "";
        width: 30px;
        height: 30px;
        display: flex;
        border: 1px solid #4CAF50;
        transform: translate(-50%, -50%);
        border-radius: 50%;
    }

    .stepper .step.red .circle {
        background-color: #FF0000;
        border: 2px solid #FF0000;
    }

    .stepper .step.red .circle::before {
        border: 1px solid #FF0000;
    }

    .stepper .step p {
        font-size: .88rem;
        text-wrap: nowrap;
        color: #4CAF50;
        margin-top: 5px;
        font-weight: 700;
    }

    .stepper .step.red p {
        color: #FF0000;
    }

    .stepper .line {
        max-width: 145px;
        width: 100%;
        height: 2px;
        background-color: #4CAF50;
        margin-top: 44px;
    }

    .stepper .red.line {
        background-color: #FF0000;
    }

    .stepper .line:last-child {
        display: none;
    }

    @media only screen and (max-width: 600px) {
        .stepper .stepper {
            flex-direction: column;
            gap: 1rem;
        }
        .stepper .line {
            display: none;
        }
    }
</style>

<div class="main-card mb-3 card">
    <div class="no-gutters row">
        <div class="col-md-3 d-grid">
            <div class="d-flex flex-column align-items-center justify-content-center p-4" style="background: rgb(247, 249, 250); border-radius: .25rem 0 0 .25rem;">
                <img src="<?= $user['avatar']; ?>" alt="Networking Icon" style="max-height: 150px; margin: 0 2rem 1rem 2rem;">
                <div class="text-center">
                    <div>Pacote Atual</div>
                    <div class="fsize-4 font-weight-bold mb-0"><?= strtoupper($user['plan_name']); ?></div>
                    <?php if ($user['plan_id'] == 1): ?>
                    <div class="fsize-1">24 T.C. no mês em GRUPO com 05 Participantes <br><span class="font-weight-semibold">Cashback de 23,5% até a 10ª geração</span></div>
                    <?php elseif ($user['plan_id'] == 2): ?>
                    <div class="fsize-1">12 T.C. no mês em GRUPO com 05 Participantes <br><span class="font-weight-semibold">Cashback de 23,5% até a 10ª geração</span></div>
                    <?php elseif ($user['plan_id'] == 3): ?>
                    <div class="fsize-1"><span class="font-weight-semibold">Cashback de 10% até a 1ª geração</span></div>
                    <?php elseif ($user['plan_id'] == 4): ?>
                    <div class="fsize-1">48 T.C. no mês em GRUPO com 05 Participantes <br><span class="font-weight-semibold">Cashback de 23,5% até a 10ª geração</span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-9 d-grid">
            <div class="card-body">
                <h5 class="card-title">Parabéns!!</h5>
                <p class="fsize-2">Seja Bem Vindo ao Programa de Cashback Coletivo Renda CAP Brasil.<br>Esse plano lhe dá acesso a cashbacks até sua <span class="font-weight-semibold"><?= $generation; ?>ª geração</span>.</p>
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-3 widget-chart card-hover-shadow-2x text-left">
                            <div class="icon-wrapper rounded">
                                <div class="icon-wrapper-bg bg-success"></div>
                                <i class="lnr-cog text-success"></i>
                            </div>
                            <div class="widget-chart-content">
                                <div class="widget-subheading">Diretos</div>
                                <div class="count-up-number widget-numbers m-0" data-value="<?= $networkData['directUsersPaidCount']; ?>"><?= $networkData['directUsersPaidCount']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-3 widget-chart card-hover-shadow-2x text-left">
                            <div class="icon-wrapper rounded">
                                <div class="icon-wrapper-bg bg-success"></div>
                                <i class="pe-7s-clock text-success"></i>
                            </div>
                            <div class="widget-chart-content">
                                <div class="widget-subheading">Cadastros Pendentes</div>
                                <div class="count-up-number widget-numbers m-0" data-value="<?= $networkData['planBronzeCount']; ?>"><?= $networkData['planBronzeCount']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-3 widget-chart card-hover-shadow-2x text-left">
                            <div class="icon-wrapper rounded">
                                <div class="icon-wrapper-bg bg-success"></div>
                                <i class="lnr-screen text-success"></i>
                            </div>
                            <div class="widget-chart-content">
                                <div class="widget-subheading">Indiretos</div>
                                <div class="count-up-number widget-numbers m-0" data-value="<?= $networkData['indirectUserCount']; ?>"><?= $networkData['indirectUserCount']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card mb-3 widget-chart card-hover-shadow-2x text-left">
                            <div class="icon-wrapper rounded">
                                <div class="icon-wrapper-bg bg-success"></div>
                                <i class="lnr-laptop-phone text-success"></i>
                            </div>
                            <div class="widget-chart-content">
                                <div class="widget-subheading">Total</div>
                                <div class="count-up-number widget-numbers m-0" data-value="<?= $networkData['totalUsers']; ?>"><?= $networkData['totalUsers']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--<div class="d-flex align-items-center justify-content-center mb-4"> 
                    <div class="stepper">
                        <?php for ($i = 1; $i <= $generation; $i++): ?>
                            <!-- Step: Se a geração for maior que 2 e o número de diretos for menor que a geração, aplicar a classe 'red' -->
                            <!-- Alterei para 0 por um teste, mas o original e 1 -->
                            <!--<div class="step <?= ($i > 1 && $i > $networkData['directUsersPaidCount']) ? 'red' : ''; ?>">
                                <p><?= $i; ?>ª Geração</p>
                                <div class="circle"></div>
                            </div>

                            <?php if ($i < $generation): ?>
                                <!-- Line: A linha fica vermelha se o número de diretos for menor que a geração atual -->
                                <!--<div class="line <?= ($i >= $networkData['directUsersPaidCount']) ? 'red' : ''; ?>"></div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>

                <?php
                    if ($user['plan_id'] == 1) {
                        $indications = max(0, 7 - $networkData['directUsersPaidCount']);
                    } elseif ($user['plan_id'] == 2) {
                        $indications = max(0, 5 - $networkData['directUsersPaidCount']);
                    } elseif ($user['plan_id'] == 3) {
                        $indications = max(0, 1 - $networkData['directUsersPaidCount']);
                    } elseif ($user['plan_id'] == 4) {
                        $indications = max(0, 10 - $networkData['directUsersPaidCount']);
                    }
                ?>

                <div class="row">
                    <div class="col">
                        <p class="fsize-1 font-weight-bold mb-0">Aqui começa sua Rota de Colisão com a Prosperidade.</p>
                        <p class="fsize-1">Para liberação dos seus cashbacks, basta indicar um novo participante por cada geração.<br>Para usufruir de todos cashbacks, <span class="font-weight-semibold"><ins>Você precisa indicar mais <?= $indications; ?> participantes</ins></span>.</p>
                    </div>
                    <?php if ($user['plan_id'] == 3): ?>
                    <div class="col">
                        <p class="fsize-1 mb-0">Plano Diamante * Plano Ouro * Plano Prata</p>
                        <p class="fsize-1">Assine agora mesmo e participe dos <ins>Prêmios Semanais de R$ 3.000.000,00</ins></p>
                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>minha-conta/assinatura" class="btn btn-success btn-pulse">Contratar</a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- <?php if ($user['plan_id'] == 1 && $directUserCount >= 7): ?>
                    <p class="fsize-3 mb-0">Parabéns, você já atingiu o máximo!</p>
                    <p class="fsize-2">Agora que você atingiu o limite de <span class="font-weight-semibold">7 diretos</span> e já está aproveitando todos os benefícios da <span class="font-weight-semibold"><?= $generation; ?>ª geração</span>, é hora de subir para o próximo nível! Ao migrar para o plano <span class="font-weight-semibold">Diamante</span>, você poderá desbloquear ainda mais benefícios e maximizar seus ganhos!</p>
                <?php elseif ($user['plan_id'] == 1): ?>
                    <p class="fsize-3 mb-0">Seu sucesso está ao alcance!</p>
                    <p class="fsize-2">Para desbloquear o <span class="font-weight-semibold"><?= $generation; ?>ª geração</span> e aproveitar todo o potencial de ganhos, você só precisa indicar mais <span class="font-weight-semibold"><?= max(0, 7 - $directUserCount); ?> usuários</span>. Não perca essa chance de multiplicar seus resultados!</p>
                <?php endif; ?>

                <?php if ($user['plan_id'] == 2 && $directUserCount >= 5): ?>
                    <p class="fsize-3 mb-0">Você já está no caminho certo!</p>
                    <p class="fsize-2">Com <span class="font-weight-semibold">5 diretos</span> conquistados, você já atingiu o máximo do seu plano atual! Que tal subir para o plano <span class="font-weight-semibold">Ouro</span> e liberar o potencial completo das suas gerações, com ainda mais benefícios e maiores ganhos?</p>
                <?php elseif ($user['plan_id'] == 2): ?>
                    <p class="fsize-3 mb-0">Falta pouco para atingir o máximo!</p>
                    <p class="fsize-2">Com apenas mais <span class="font-weight-semibold"><?= max(0, 5 - $directUserCount); ?> usuários</span>, você poderá aproveitar os ganhos completos da <span class="font-weight-semibold"><?= $generation; ?>ª geração</span>. Alcance seu potencial total hoje!</p>
                <?php endif; ?>

                <?php if ($user['plan_id'] == 3): ?>
                    <p class="fsize-3 mb-0">Você já está no topo!</p>
                    <p class="fsize-2">Com o seu plano atual, você já aproveita todos os benefícios da <span class="font-weight-semibold"><?= $generation; ?>ª geração</span>! Que tal subir para o plano <span class="font-weight-semibold">Ouro</span> e liberar o potencial completo das suas gerações, com ainda mais benefícios e maiores ganhos?</p>
                <?php endif; ?>

                <?php if ($user['plan_id'] == 4 && $directUserCount >= 10): ?>
                    <p class="fsize-3 mb-0">Parabéns, você já atingiu o máximo!</p>
                    <p class="fsize-2">Agora que você atingiu o limite de <span class="font-weight-semibold">10 diretos</span> e já está aproveitando todos os benefícios da <span class="font-weight-semibold"><?= $generation; ?>ª geração</span>!</p>
                <?php elseif ($user['plan_id'] == 4): ?>
                    <p class="fsize-3 mb-0">Seu sucesso está ao alcance!</p>
                    <p class="fsize-2">Para desbloquear o <span class="font-weight-semibold"><?= $generation; ?>ª geração</span> e aproveitar todo o potencial de ganhos, você só precisa indicar mais <span class="font-weight-semibold"><?= max(0, 10 - $directUserCount); ?> usuários</span>. Não perca essa chance de multiplicar seus resultados!</p>
                <?php endif; ?> -->
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 d-grid">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Link de Indicação</h5>
                <div class="input-group">
                    <input type="text" class="form-control"
                        id="clipboard-token" value="<?= $user['invite_link']; ?>" readonly>
                    <div class="input-group-append">
                        <button type="button" data-clipboard-target="#clipboard-token" class="btn-icon btn btn-primary clipboard-trigger">
                            <i class="pe-7s-copy-file btn-icon-wrapper"></i>
                            Copiar Link
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted">
                    Clique em copiar link para copiar seu link.
                </small>
            </div>
        </div>
    </div>
    <div class="col-lg-6 d-grid">
        <div class="main-card mb-3 card">
            <div class="card-body table-responsive">
                <table class="mb-0 table w-100">
                    <thead>
                        <tr>
                            <th>Primeira Ativação</th>
                            <th>Última Ativação</th>
                            <th>Próxima Ativação</th>
                            <th>Pacote Atual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $subs['creation_date']; ?></td>
                            <td><?= $subs['created_at']; ?></td>
                            <td><?= $subs['due_date']; ?></td>
                            <td><?= $user['plan_name']; ?> - Genhe Até a <?= $generation; ?>ª Geração</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
function getNetworkUsers($currentUserId, $conn) {
    $maxLevel = 10; // Número máximo de níveis
    $cashbackData = [];
    $totalCashbackLevel1 = 0; // Total cashback do nível 1
    $totalCashbackLevel2To10 = 0; // Total cashback dos níveis 2 a 10
    
    // Função para buscar usuários da rede recursivamente
    function fetchUsersByLevel($currentUserId, $level, $maxLevel, $cashbackData, $conn, &$totalCashbackLevel1, &$totalCashbackLevel2To10) {
        if ($level > $maxLevel) {
            return $cashbackData;
        }

        // Obtém os usuários do nível atual
        $query = "
            SELECT 
                u.id AS user_id, 
                u.username, 
                p.name AS plan_name, 
                p.price 
            FROM tb_networks n 
            JOIN tb_user_networks un ON n.id = un.network_id 
            JOIN tb_users u ON u.id = un.user_id 
            JOIN tb_subscriptions s ON s.user_id = u.id AND s.status = 'ACTIVE'
            JOIN tb_plans p ON p.id = s.plan_id 
            WHERE (n.inviter_id = ? OR n.referrer_id = ?)
            AND s.payment_id NOT LIKE '%subs_test_%'
        ";

        $stmt = $conn->prepare($query);
        $stmt->execute([$currentUserId, $currentUserId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $user) {
            // Calcula o cashback
            $cashbackPercent = 0;
            if ($level == 1) {
                $cashbackPercent = 10; // 10%
            } elseif ($level == 2) {
                $cashbackPercent = 5; // 5%
            } elseif ($level >= 3 && $level <= 10) {
                $cashbackPercent = 1; // 1%
            }

            $cashbackValue = ($user['price'] * $cashbackPercent) / 100;

            // Atualiza os totais de cashback
            if ($level == 1) {
                $totalCashbackLevel1 += $cashbackValue; // Adiciona ao total do nível 1
            } elseif ($level >= 2 && $level <= 10) {
                $totalCashbackLevel2To10 += $cashbackValue; // Adiciona ao total do nível 2 a 10
            }

            // Armazena os dados do usuário
            $cashbackData[$user['user_id']] = [
                'plano' => $user['plan_name'],
                'username' => $user['username'],
                'nivel' => $level,
                'cashback' => [
                    'percentual' => $cashbackPercent,
                    'valor' => number_format($cashbackValue, 2, ',', '')
                ],
                'convidados' => [] // Inicializa como um array vazio
            ];

            // Recursivamente busca os convidados do usuário
            $cashbackData[$user['user_id']]['convidados'] = fetchUsersByLevel($user['user_id'], $level + 1, $maxLevel, [], $conn, $totalCashbackLevel1, $totalCashbackLevel2To10);
        }

        return $cashbackData;
    }

    // Inicia a busca a partir do usuário atual
    $cashbackData = fetchUsersByLevel($currentUserId, 1, $maxLevel, $cashbackData, $conn, $totalCashbackLevel1, $totalCashbackLevel2To10);
    
    // Adiciona os totais de cashback ao resultado
    $result = [
        'cashbackData' => $cashbackData,
        'totalCashbackLevel1' => $totalCashbackLevel1,
        'totalCashbackLevel2To10' => $totalCashbackLevel2To10,
        'totalCashback' => $totalCashbackLevel1 + $totalCashbackLevel2To10
    ];

    return $result;
}

// Exemplo de uso
$currentUserId = $_SESSION['user_id']; // ID do usuário atual
$networkCashbackData = getNetworkUsers($currentUserId, $conn);

// Exibe o resultado
// echo '<pre>';
// print_r($networkCashbackData);
// echo '</pre>';
?>

<?php
// ID do usuário atual
$userId = $_SESSION['user_id']; // Ou substitua pelo ID do usuário que deseja consultar

// Consulta para obter todos os network_id associados ao usuário
$stmt = $conn->prepare("
    SELECT id
    FROM tb_networks
    WHERE inviter_id = ? OR referrer_id = ?
");
$stmt->execute([$userId, $userId]);
$networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($networkIds) {
    // Contar todos os usuários diretamente relacionados
    $stmt = $conn->prepare("
        SELECT user_id
        FROM tb_user_networks
        WHERE network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
    ");
    $stmt->execute($networkIds);
    $directUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Contar o número de usuários diretamente relacionados
    $directUserCount = count($directUsers);
    
    // Contar todos os usuários relacionados (incluindo subníveis) e os usuários sem assinatura paga
    $layerCounts = [];
    $maxDepth = 0;
    $unpaidUsers = 0; // Inicializar a contagem de usuários sem assinatura paga
    $totalCount = countAllSubusers($conn, $networkIds, 0, $layerCounts, $maxDepth, $unpaidUsers);
    
    // Contar usuários indiretos (excluindo os diretamente relacionados)
    $indirectUserCount = $totalCount - $directUserCount;
    
    // Exibir o número de usuários sem assinatura paga
    // echo "Número total de usuários sem assinatura ou com assinatura não paga: " . $unpaidUsers . "<br>";
    
    // Exibir a contagem de camadas
    $nonEmptyLayers = array_filter($layerCounts, fn($count) => $count > 0); // Filtrar camadas não vazias
    $totalNonEmptyLayers = count($nonEmptyLayers);
    // echo "Número total de camadas existentes: " . $totalNonEmptyLayers . "<br>";
    
    // // Exibir contagem de usuários por camada
    // foreach ($nonEmptyLayers as $layer => $count) {
    //     echo "Total de usuários na camada " . ($layer + 1) . ": " . $count . "<br>";
    // }
    
    // // Exibir os resultados
    // echo "Total de usuários diretamente relacionados: " . $directUserCount . "<br>";
    // echo "Total de usuários indiretos relacionados: " . $indirectUserCount . "<br>";
    // echo "Total de usuários relacionados (incluindo subníveis): " . $totalCount . "<br>";
    // echo "Total de usuários do segundo nível: " . $secondLevelUserCount . "<br>";
    
    // Exibir a contagem de camadas
    $nonEmptyLayers = array_filter($layerCounts, fn($count) => $count > 0); // Filtrar camadas não vazias
    $totalNonEmptyLayers = count($nonEmptyLayers);
    // echo "Número total de camadas existentes: " . $totalNonEmptyLayers . "<br>";
    
    // // Exibir contagem de usuários por camada
    // foreach ($nonEmptyLayers as $layer => $count) {
    //     echo "Total de usuários na camada " . ($layer + 1) . ": " . $count . "<br>";
    // }
    
    // $countAllCashback = $cashbackPerDirectUser + $cashbackPerLayerUser;
    
    // // Exibir cashback total para camadas de 2 a 10
    // echo "Cashback total para camada 1: R$ " . number_format($cashbackPerDirectUser, 2, ',', '.') . "<br>";
    // echo "Cashback total para camadas de 2 a 10: R$ " . number_format($cashbackByLayerTotal, 2, ',', '.') . "<br>";
} else {
    $directUserCount = 0;
    $indirectUserCount = 0;
    $totalCount = 0;
    $unpaidUsers = 0;
    $nonEmptyLayers = 0;
    $totalNonEmptyLayers = 0;
    $nonEmptyLayers = 0;
    $totalNonEmptyLayers = 0;
}

// Geracao de cada plano
if ($user['plan_id'] == 3) {
    $generation = "1";
} else if ($user['plan_id'] == 2) {
    $generation = "5";
} else if ($user['plan_id'] == 1) {
    $generation = "10";
}
?>
<?php
function countCashbackOfSubusers($conn, $networkIds, $depth = 0, &$layerCounts = [], &$maxDepth = 0) {
    if ($depth > 0) {
        if (!isset($layerCounts[$depth])) {
            $layerCounts[$depth] = 0;
        }
    }

    $count = 0;

    foreach ($networkIds as $networkId) {
        // Consulta para obter todos os user_id associados a network_id
        $stmt = $conn->prepare("
            SELECT user_id
            FROM tb_user_networks
            WHERE network_id = ?
        ");
        $stmt->execute([$networkId]);
        $subuser_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Conta os subusuários associados ao network_id atual
        if ($depth > 0) {
            $layerCounts[$depth] += count($subuser_ids);
        }
        $count += count($subuser_ids);

        foreach ($subuser_ids as $subuser_id) {
            // Verifica se o subusuário tem uma assinatura ativa
            $stmt = $conn->prepare("
                SELECT COUNT(*) FROM tb_subscriptions
                WHERE user_id = ? AND status = 'ACTIVE'
                AND payment_id NOT LIKE '%subs_test_%'
            ");
            $stmt->execute([$subuser_id]);
            $hasActiveSubscription = $stmt->fetchColumn() > 0;

            // Somente conta se o subusuário tem uma assinatura ativa
            if ($hasActiveSubscription) {
                // Consulta para obter todos os network_id associados ao subusuário
                $stmt = $conn->prepare("
                    SELECT id
                    FROM tb_networks
                    WHERE inviter_id = ? OR referrer_id = ?
                ");
                $stmt->execute([$subuser_id, $subuser_id]);
                $subnetworkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

                // Contagem recursiva
                $count += countAllSubusers($conn, $subnetworkIds, $depth + 1, $layerCounts, $maxDepth);
            }
        }
    }

    // Atualizar a profundidade máxima
    if ($depth > $maxDepth) {
        $maxDepth = $depth;
    }

    return $count;
}

// ID do usuário atual
$userId = $_SESSION['user_id']; // Ou substitua pelo ID do usuário que deseja consultar

// Consulta para obter todos os network_id associados ao usuário
$stmt = $conn->prepare("
    SELECT id
    FROM tb_networks
    WHERE inviter_id = ? OR referrer_id = ?
");
$stmt->execute([$userId, $userId]);
$networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if  ($networkIds) {
    // Contar todos os usuários diretamente relacionados ao perfil atual
    $stmt = $conn->prepare("
        SELECT user_id
        FROM tb_user_networks
        WHERE network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
    ");
    $stmt->execute($networkIds);
    $directUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Contar o número de usuários diretamente relacionados
    $directUserCount = count($directUsers);
    
    // Contar todos os usuários relacionados (incluindo subníveis)
    $layerCounts = [];
    $maxDepth = 0;
    $totalCount = countCashbackOfSubusers($conn, $networkIds, 0, $layerCounts, $maxDepth);
    
    // Contar usuários indiretos (excluindo os diretamente relacionados)
    $indirectUserCount = $totalCount - $directUserCount;
    
    // Separar o segundo nível de usuários (aqueles convidados pelos usuários diretos)
    $secondLevelUsers = [];
    foreach ($directUsers as $directUserId) {
        $stmt = $conn->prepare("
            SELECT user_id
            FROM tb_user_networks
            WHERE network_id IN (
                SELECT id
                FROM tb_networks
                WHERE inviter_id = ? OR referrer_id = ?
            )
        ");
        $stmt->execute([$directUserId, $directUserId]);
        $secondLevelUsers = array_merge($secondLevelUsers, $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
    
    // Contar o número de usuários do segundo nível
    $secondLevelUserCount = count($secondLevelUsers);
    
    // Supõe que $user['plan_price'] já está definido e contém o preço do plano
    $planPrice = $user['plan_price']; // O valor do preço do plano
    
    // Calcular cashback por usuário direto (10% do plan_price) apenas se houver assinaturas ativas
    $activeDirectUserCount = 0;
    foreach ($directUsers as $directUserId) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM tb_subscriptions
            WHERE user_id = ? AND status = 'ACTIVE'
            AND payment_id NOT LIKE '%subs_test_%'
        ");
        $stmt->execute([$directUserId]);
        if ($stmt->fetchColumn() > 0) {
            $activeDirectUserCount++;
        }
    }
    $cashbackPerDirectUser = 0.10 * $planPrice;
    $totalCashback = $activeDirectUserCount * $cashbackPerDirectUser;
    
    // Inicializar variáveis para contagem e cashback das camadas 2 a 10
    $layerCounts[1] = $activeDirectUserCount; // Inicializa a contagem da camada 1 (diretos)
    $cashbackByLayer = [];
    $cashbackByLayerTotal = 0;
    
    // Calcular o cashback e contagem para cada camada de 2 a 10
    for ($i = 2; $i <= min(10, $maxDepth); $i++) {
        $layerCount = isset($layerCounts[$i]) ? $layerCounts[$i] : 0;
        $layerCounts[$i] = $layerCount; // Atualiza a contagem da camada
    
        // Calcular cashback por camada (1% do plan_price) apenas se houver assinaturas ativas
        $activeLayerUserCount = 0;
        for ($j = 0; $j < $layerCount; $j++) {
            // Verifica se o usuário da camada tem uma assinatura ativa (você pode precisar ajustar a lógica aqui)
            // Se tiver, conta para o cashback
            $activeLayerUserCount++;
        }
        
        // Calcular cashback por camada
        $cashbackByLayer[$i] = $activeLayerUserCount * 0.01 * $planPrice;
        $cashbackByLayerTotal += $cashbackByLayer[$i];
    }
    
    // Calcular cashback total
    $countAllCashback = $cashbackPerDirectUser + $cashbackByLayerTotal;
    
    // Exibir cashback total para camadas de 2 a 10
    // echo "Cashback total para camada 1: R$ " . number_format($cashbackPerDirectUser, 2, ',', '.') . "<br>";
    // echo "Cashback total para camadas de 2 a 10: R$ " . number_format($cashbackByLayerTotal, 2, ',', '.') . "<br>";
} else {
    $activeDirectUserCount = 0;
    $cashbackByLayerTotal = 0;
    $totalCashback = 0;
}
?>
















<?php

function getNetworkCashbackData($conn, $userId, $drawDate = null) {
    // Função para listar usuários associados e calcular o cashback
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
            $stmt = $conn->prepare("SELECT id FROM tb_networks WHERE referrer_id = ? OR (inviter_id = ? AND (referrer_id != ? OR referrer_id IS NULL))");
            $stmt->execute([$user['id'], $user['id'], $_SESSION['user_id']]);
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $nextNetworkIds = array_merge($nextNetworkIds, $result);
        }

        foreach ($currentLevelUsers as &$user) {
            if (in_array($user['id'], $uniqueIds)) {
                continue;
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
                    
            } else {
                $cashback = 0;
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

    // Função para calcular o resumo de cashback
    function calculateCashbackSummary($conn, $allUsers) {
        $totalCashbackLevel1 = 0;
        $totalCashbackLevel2To10 = 0;
        $totalDirectUsers = 0;

        foreach ($allUsers as $user) {
            if ($user['type'] === 'Direto') {
                $totalCashbackLevel1 += $user['cashback'];
                $totalDirectUsers++;
            } else {
                $totalCashbackLevel2To10 += $user['cashback'];
            }
        }

        $totalCashback = $totalCashbackLevel1 + $totalCashbackLevel2To10;

        return [
            'totalCashbackLevel1' => $totalCashbackLevel1,
            'totalDirectUsers' => $totalDirectUsers,
            'totalCashbackLevel2To10' => $totalCashbackLevel2To10,
            'totalCashback' => $totalCashback,
        ];
    }

    // Função para calcular cashback acumulado
    function calculateAccumulatedCashback($conn, $allUsers) {
        $totalCashback = 0;
        foreach ($allUsers as $user) {
            $totalCashback += $user['cashback'];
        }

        $stmt = $conn->prepare("SELECT SUM(amount) as totalWithdrawn FROM tb_withdrawals WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $withdrawalData = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalWithdrawn = $withdrawalData['totalWithdrawn'] ?? 0;

        $availableCashback = $totalCashback - $totalWithdrawn;

        return [
            'totalCashbackAvailable' => $availableCashback
        ];
    }

    // Obter IDs das redes associadas ao usuário
    $stmt = $conn->prepare("SELECT id FROM tb_networks WHERE referrer_id = ? OR (inviter_id = ? AND referrer_id IS NULL)");
    $stmt->execute([$userId, $userId]);
    $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Consulta para obter todos os network_id associados ao usuário
    $stmt = $conn->prepare("SELECT id FROM tb_networks WHERE inviter_id = ? AND referrer_id != ?");
    $stmt->execute([$userId, $userId]);
    $indirectNetworkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($networkIds)) {
        $users = listAssociatedUsersWithCashback($conn, $networkIds, 'Direto', $drawDate);
    } else if (!empty($indirectNetworkIds)) {
        $users = listAssociatedUsersWithCashback($conn, $indirectNetworkIds, 'Indireto', $drawDate);
    } else {
        $users = [];
    }

    $cashbackSummary = calculateCashbackSummary($conn, $users);
    $accumulatedCashback = calculateAccumulatedCashback($conn, $users);

    return array_merge($cashbackSummary, $accumulatedCashback);
}


$networkCashbackData = getNetworkCashbackData($conn, $_SESSION['user_id']);

?>
















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
                    <span class="count-up-real" data-value="<?= $networkCashbackData['totalCashbackAvailable']; ?>"><?= number_format($networkCashbackData['totalCashbackAvailable'], 2, ',', '.'); ?></span>
                </div>
                <div class="widget-subheading">Saldo Disponível</div>
            </div>
        </div>
    </div>
</div>

<?php if ($user['plan_id'] != 3) : ?>

<style>
    tr[data-draw="telesena"] {
        background-color: rgba(63, 106, 216, .3) !important;
    }
    tr[data-draw="viva-sorte"] {
        background-color: rgba(58, 196, 125, .3) !important;
    }
    tr[data-draw="hiper-cap-brasil"] {
        background-color: rgba(247, 185, 36, .3) !important;
    }
</style>

<div class="row">

    <?php
        // Supondo que você tenha a variável $current_user_id com o ID do usuário atual
        $current_user_id = $_SESSION['user_id']; // ou outra forma de obter o ID do usuário

        // $stmt = $conn->prepare("
        //     SELECT dt.*, dp.name AS product, tu.group_code,
        //         CASE 
        //             WHEN dp.name = 'Telesena' THEN 1
        //             WHEN dp.name = 'Hiper Cap Brasil' THEN 2
        //             WHEN dp.name = 'Viva Sorte' THEN 3
        //             ELSE 4
        //         END AS product_priority
        //     FROM tb_draw_titles dt
        //     JOIN tb_draw_title_products dtp ON dtp.draw_title_id = dt.id
        //     JOIN tb_draw_products dp ON dp.id = dtp.product_id
        //     JOIN tb_title_users tu ON dt.id = tu.title_id
        //     WHERE tu.user_id = :current_user_id
        //     ORDER BY WEEKOFYEAR(dtp.draw_date) DESC, product_priority ASC
        // ");
        // $stmt->bindParam(':current_user_id', $current_user_id);
        // $stmt->execute();
        // $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // // Encontrar o título com a data de sorteio mais recente
        // $nextTitle = null;
        // if (!empty($titles)) {
        //     $nextTitle = $titles[0]; // Como ordenamos pela data de sorteio, o primeiro é o próximo a vencer
        // }

        // Obtém o início da semana (segunda-feira) e o fim da semana (domingo) em formato Y-m-d
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

        // Consulta para listar os produtos dos títulos que o usuário faz parte
        $query = "
            SELECT 
                dtp.id, 
                g.id AS group_id, 
                g.group_code, 
                dp.name AS product, 
                dtp.series, 
                dtp.title, 
                dtp.operation_code, 
                dtp.dv, 
                dtp.lucky_number, 
                dtp.draw_date, 
                dtp.id AS title_id
            FROM tb_group_users gu
            JOIN tb_groups g ON gu.group_id = g.id
            JOIN tb_draw_titles dt ON g.title_id = dt.id
            LEFT JOIN tb_draw_title_products dtp ON dt.id = dtp.draw_title_id
            JOIN tb_draw_products dp ON dtp.product_id = dp.id
            WHERE gu.user_id = ?
            AND dtp.draw_date BETWEEN ? AND ?
            ORDER BY dp.id ASC, dtp.draw_date DESC
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$current_user_id, $startOfWeek, $endOfWeek]);
        $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Encontrar o título com a data de sorteio mais recente
        $nextTitle = null;
        if (!empty($titles)) {
            $nextTitle = $titles[0]; // Como ordenamos pela data de sorteio, o primeiro é o próximo a vencer
        }
    ?>

    <style>
        .dataTables_scrollBody {
            margin-bottom: 1.25rem !important;
        }
    </style>

    <div class="col-lg-6 d-grid">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Meus Títulos</h5>
                <div class="table-responsive">
                    <table id="my-titles-table" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Código do Grupo</th>
                                <th>Produto</th>
                                <th>Série</th>
                                <th>Título</th>
                                <th>Código de Operação</th>
                                <th>DV</th>
                                <th>Número da Sorte</th>
                                <th>Sorteio</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($titles)) : ?>
                                <?php foreach ($titles as $title) : ?>
                                    <tr data-draw="<?= str_replace(' ', '-', strtolower($title['product'])); ?>">
                                        <td><?= $title['group_code'] ?? "-- Indefinido --"; ?></td>
                                        <td><?= htmlspecialchars($title['product']); ?></td>
                                        <td><?= htmlspecialchars($title['series']); ?></td>
                                        <td><?= htmlspecialchars($title['title']); ?></td>
                                        <td><?= $title['operation_code'] ?? "-- Indefinido --"; ?></td>
                                        <td><?= htmlspecialchars($title['dv']); ?></td>
                                        <td><?= htmlspecialchars($title['lucky_number']); ?></td>
                                        <td><?= htmlspecialchars(date('d/M', strtotime($title['draw_date']))); ?></td>
                                        <td>
                                            <button class="btn <?= ($nextTitle && $nextTitle['id'] == $title['id']) ? 'btn-secondary' : 'btn-primary'; ?> btn-sm get-users"
                                                    data-title-id="<?= $title['id']; ?>"
                                                    <?= ($nextTitle && $nextTitle['id'] == $title['id']) ? 'disabled' : ''; ?>>
                                                <?= ($nextTitle && $nextTitle['id'] == $title['id']) ? 'Exibindo' : 'Ver'; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9" class="text-center">Você não possui nenhum título essa semana.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
        if ($nextTitle) {
            // Consulta para listar os usuários do grupo
            $query = "
                SELECT s.user_id, s.plan_id, p.name AS plan_name, u.firstname, u.lastname, u.email, u.username, u.profile_image, a.city, a.state
                FROM tb_group_users gu
                JOIN tb_users u ON gu.user_id = u.id
                JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
                JOIN tb_plans p ON s.plan_id = p.id
                JOIN tb_address a ON u.id = a.user_id
                WHERE gu.group_id = ? 
                ORDER BY s.id ASC
            ";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nextTitle['group_id']]);
            $sorted_participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    ?>

    <style>
        .list-group .avatar {
            width: 42px;
            height: 42px;
            object-fit: cover;
        }
    </style>

    <div class="col-lg-6 d-grid">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Meus Grupos 5 Participantes</h5>

                <ul class="list-group" id="participant-list">
                    <?php if (!empty($sorted_participants)) : ?>
                        <?php foreach ($sorted_participants as $participant): ?>
                            <?php
                                $participant['fullname'] = $participant['firstname'] . " " . $participant['lastname'];
                                $participant['surname'] = explode(' ', $participant['lastname'])[0];
                                $participant['shortname'] = $participant['firstname'] . " " . $participant['surname'];

                                $no_image_src = INCLUDE_PATH_DASHBOARD . 'images/avatars/' . strtolower($participant['plan_name']) . ".png";
                                $avatar_src = INCLUDE_PATH_DASHBOARD . 'files/profile/avatar/' . $participant['user_id'] . '/' . $participant['profile_image'];

                                $participant['avatar'] = (($participant['profile_image'] !== 'no-image.svg') ? $avatar_src : $no_image_src);
                            ?>

                            <li class="list-group-item">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left mr-3">
                                            <img width="42" class="avatar rounded-circle" src="<?= $participant['avatar']; ?>" alt="Avatar de Usuário">
                                        </div>
                                        <div class="widget-content-left">
                                            <div class="widget-heading"><?= $participant['shortname']; ?> <?= ($participant['user_id'] == $current_user_id) ? '(Você)' : ''; ?></div>
                                            <div class="widget-subheading"><?= $participant['username']; ?></div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div><?= $participant['city'] . ", " . $participant['state']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>

                        <!-- Usuario RendaCap -->
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-wrapper">
                                    <div class="widget-content-left mr-3">
                                        <img width="42" class="avatar rounded-circle" src="<?= INCLUDE_PATH_DASHBOARD; ?>images/favicon.png" alt="Avatar de Usuário">
                                    </div>
                                    <div class="widget-content-left">
                                        <div class="widget-heading"><?= $project_name; ?></div>
                                        <div class="widget-subheading">rendacapbrasil</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php else : ?>
                        <li class="list-group-item">Você não está participando de nenhum sorteio essa semana.</li>
                    <?php endif; ?>
                </ul>

            </div>
        </div>
    </div>
</div>


<?php endif; ?>

<?php if ($user['plan_id'] != 3) : ?>

<style>
    .chart-container {
        position: relative;
        width: 300px;
        height: 300px;
    }
    .chart {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: conic-gradient(
            #ff6384 0% 16.66%, /* Red */
            #36a2eb 16.66% 33.33%, /* Blue */
            #cc65fe 33.33% 50%, /* Purple */
            #ffce56 50% 66.66%, /* Yellow */
            #4bc0c0 66.66% 83.33%, /* Teal */
            #f7464a 83.33% 100% /* Light Red */
        );
    }
    .images {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    .images div {
        position: absolute;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 3px solid #fff;
        background-size: cover;
        background-position: center;
    }
    .images .img1 {
        top: 20%;
        left: 33%;
        transform: translate(-50%, -50%);
        background-image: url(https://via.placeholder.com/50?text=1);
    }
    .images .img2 {
        top: 20%;
        right: 33%;
        transform: translate(50%, -50%);
        background-image: url(https://via.placeholder.com/50?text=2);
    }
    .images .img3 {
        top: 50%;
        right: 15%;
        transform: translate(50%, -50%);
        background-image: url(https://via.placeholder.com/50?text=3);
    }
    .images .img4 {
        bottom: 20%;
        right: 33%;
        transform: translate(50%, 50%);
        background-image: url(https://via.placeholder.com/50?text=4);
    }
    .images .img5 {
        bottom: 20%;
        left: 33%;
        transform: translate(-50%, 50%);
        background-image: url(https://via.placeholder.com/50?text=5);
    }
    .images .img6 {
        top: 50%;
        left: 15%;
        transform: translate(-50%, -50%);
        background-image: url(https://via.placeholder.com/50?text=6);
    }
</style>


<style>
    .semaphore {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    
    .semaphore .stepper-rank {
        display: flex;
        justify-content: space-around;
        align-items: center;
        width: 90%;
    }

    .semaphore .step {
        display: flex;
        flex-direction: row;
        align-items: center;
        position: relative;
        text-align: center;
    }

    .semaphore .step .circle {
        position: relative;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background-color: #4CAF50;
        border: 2px solid #4CAF50;
    }

    .semaphore .step .circle::before {
        position: absolute;
        left: 50%;
        top: 50%;
        content: "";
        width: 30px;
        height: 30px;
        display: flex;
        border: 1px solid #4CAF50;
        transform: translate(-50%, -50%);
        border-radius: 50%;
    }

    .semaphore .step.red .circle {
        background-color: #FF0000;
        border: 2px solid #FF0000;
    }

    .semaphore .step.red .circle::before {
        border: 1px solid #FF0000;
    }

    .semaphore .step.warning .circle {
        background-color: #F7B924;
        border: 2px solid #F7B924;
    }

    .semaphore .step.warning .circle::before {
        border: 1px solid #F7B924;
    }

    .semaphore .step p {
        text-wrap: nowrap;
        color: #4CAF50;
        margin: 0;
        font-weight: 700;
    }

    .semaphore .step.red p {
        color: #FF0000;
    }

    .semaphore .step.warning p {
        color: #F7B924;
    }

    .semaphore .line {
        max-width: 145px;
        height: 20px;
        width: 2px;
        background-color: #4CAF50;
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .semaphore .red.line {
        background-color: #FF0000;
    }

    .semaphore .warning.line {
        background-color: #F7B924;
    }

    .semaphore .line:last-child {
        display: none;
    }

    @media only screen and (max-width: 600px) {
        .semaphore {
            margin-top: 1.5rem;
            width: 100%;
        }
    }
</style>


<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="row">
                    <!-- <div class="col-lg-4 d-flex justify-content-center">
                        <div class="chart-container">
                            <div class="chart"></div>
                            <div class="images">
                                <div class="img1"></div>
                                <div class="img2"></div>
                                <div class="img3"></div>
                                <div class="img4"></div>
                                <div class="img5"></div>
                                <div class="img6"></div>
                            </div>
                        </div>
                    </div> -->

                    <style>
                        .chart-img {
                            width: 300px;
                            height: 300px;
                            pointer-events: none;
                        }

                        @media only screen and (max-width: 600px) {
                            .chart-img {
                                width: 150px;
                                height: 150px;
                            }
                        }
                    </style>

                    <div class="<?= ($nextTitle) ? "col-lg-5" : "col-lg-12"; ?> d-flex align-items-center justify-content-center mb-3">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/chart/chart.png" alt="Gráfico Pizza" class="chart-img">
                        <ol class="group font-weight-semibold fsize-1 ml-3" id="draw-participant-list">
                            <?php if (!empty($sorted_participants)) : ?>
                                <?php foreach ($sorted_participants as $participant): ?>
                                    <?php
                                        $participant['fullname'] = $participant['firstname'] . " " . $participant['lastname'];
                                        $participant['surname'] = explode(' ', $participant['lastname'])[0];
                                        $participant['shortname'] = $participant['firstname'] . " " . $participant['surname'];
                                    ?>

                                    <li><?= $participant['shortname']; ?> <?= ($participant['user_id'] == $current_user_id) ? '(Você)' : ''; ?></li>
                                <?php endforeach; ?>

                                <!-- Usuario RendaCap -->
                                <li><?= $project_name; ?></li>
                            <?php else : ?>
                                <li class="list-group-item">Você ainda não está participando de nenhum sorteio essa semana.</li>
                            <?php endif; ?>
                        </ol>
                    </div>

                    <?php if ($nextTitle) : ?>
                    <div class="col-lg-5" id="draw-info">
                        <h4 class="font-weight-semibold mb-4">Sorteio <?= $nextTitle['product']; ?></h4>
                        <div class="mb-4">
                            <div class="d-inline-block text-center mr-5">
                                <h5 class="font-weight-bold">Produto</h5>
                                <h5 class="font-weight-bold"><?= $nextTitle['product']; ?></h5>
                            </div>
                            <div class="d-inline-block text-center mr-5">
                                <h5 class="font-weight-bold">Série</h5>
                                <h5 class="font-weight-bold"><?= $nextTitle['series']; ?></h5>
                            </div>
                            <div class="d-inline-block text-center mr-5">
                                <h5 class="font-weight-bold">Número Título</h5>
                                <h5 class="font-weight-bold"><?= $nextTitle['title']; ?></h5>
                            </div>
                            <div class="d-inline-block text-center mr-5">
                                <h5 class="font-weight-bold">DV</h5>
                                <h5 class="font-weight-bold"><?= $nextTitle['dv']; ?></h5>
                            </div>
                        </div>
                        <h5 class="font-weight-bold mb-4">Número da Sorte: <?= $nextTitle['lucky_number']; ?></h5>
                        <h1 class="font-weight-semibold">Semana <?= date('W'); ?></h1>
                    </div>
                    <div class="col-lg-2 d-flex align-items-center">
                        <div class="semaphore">
                            <?php
                                // Obter user_id da sessão
                                $userId = $_SESSION['user_id'];

                                // Passo 1: Consultar a tabela tb_group_users
                                $query = $conn->prepare("
                                    SELECT gu.group_id 
                                    FROM tb_group_users gu
                                    WHERE gu.user_id = :user_id
                                ");
                                $query->execute(['user_id' => $userId]);
                                $groupIds = $query->fetchAll(PDO::FETCH_COLUMN);

                                if (empty($groupIds)) {
                                    echo "O usuário não participa de nenhum grupo.";
                                    exit;
                                }

                                // Passo 2: Consultar a tabela tb_groups
                                $query = $conn->prepare("
                                    SELECT g.title_id 
                                    FROM tb_groups g
                                    WHERE g.id IN (" . implode(',', array_map('intval', $groupIds)) . ")
                                ");
                                $query->execute();
                                $titleIds = $query->fetchAll(PDO::FETCH_COLUMN);

                                if (empty($titleIds)) {
                                    echo "Nenhum título encontrado para os grupos do usuário.";
                                    exit;
                                }

                                // Passo 3: Consultar a tabela tb_draw_titles
                                $query = $conn->prepare("
                                    SELECT COUNT(*) as total_titles
                                    FROM tb_draw_titles dt
                                    WHERE dt.id IN (" . implode(',', array_map('intval', $titleIds)) . ") -- Substituir title_column_name pelo nome correto
                                ");
                                $query->execute();
                                $totalTitles = (int)$query->fetchColumn();

                                // Passo 4: Determinar a semana atual (1 a 4)
                                $currentWeek = $totalTitles % 4;

                                // Exibir o resultado
                                // echo "O usuário está atualmente na semana {$week} do ciclo.";











                                // // Obter a data de criação e converter para timestamp
                                // list($day, $month, $year) = explode('/', $subs['creation_date']);
                                // $creationDate = mktime(0, 0, 0, $month, $day, $year); // Cria timestamp da data de criação
                                // $currentDate = time(); // Data atual
                                // $monthsToShow = 1; // Exibir apenas o mês atual

                                // // Obter o número da semana atual do mês
                                // $currentWeek = ceil(date('j', $currentDate) / 7); // Divide o dia do mês por 7 e arredonda para cima

                                // $currentWeek--;

                                // Loop para gerar as semanas do mês atual
                                for ($week = 1; $week <= 4; $week++):
                                    $statusClass = '';
                                    if ($week < $currentWeek) {
                                        $statusClass = 'red'; // Semanas anteriores ficam vermelhas
                                    } elseif ($week == $currentWeek) {
                                        $statusClass = 'green'; // Semana atual fica verde
                                    } else {
                                        $statusClass = 'warning'; // Semanas futuras ficam amarelas
                                    }
                            ?>

                                <div class="step <?= $statusClass; ?>">
                                    <div class="circle"></div>
                                    <p class="ml-2"><?= $week; ?>ª semana</p>
                                </div>

                                <?php if ($week < 4): ?>
                                    <div class="line <?= $statusClass; ?>"></div>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var lastClickedButton = null; // Variável para armazenar o último botão clicado

        $('.get-users').click(function() {
            $('#my-titles-table .btn-secondary').each(function() {
                $(this).removeClass('btn-secondary')   // Remove a classe btn-secondary
                    .addClass('btn-primary')           // Adiciona a classe btn-primary
                    .text('Ver')                       // Altera o texto para 'Ver'
                    .prop('disabled', false);          // Habilita o botão
            });

            var titleId = $(this).data('title-id');

            // Reabilita o botão anterior, se existir
            if (lastClickedButton && lastClickedButton !== this) {
                $(lastClickedButton).prop('disabled', false);
                $(lastClickedButton).text('Ver'); // Texto padrão
                $(lastClickedButton).removeClass('btn-secondary').addClass('btn-primary'); // Retorna à classe padrão
            }

            // Desabilita o botão que foi clicado
            $(this).prop('disabled', true);
            $(this).text('Exibindo');
            $(this).removeClass('btn-primary').addClass('btn-secondary');

            // Atualiza a referência do último botão clicado
            lastClickedButton = this;

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/get_draw_data.php', // URL do script que retorna os dados do título
                method: 'POST',
                data: { title_id: titleId },
                dataType: 'json',
                success: function(data) {
                    // Limpa a lista de participantes
                    $('#participant-list').empty();

                    // Adiciona os novos participantes
                    if (data.participants.length > 0) {
                        data.participants.forEach(function(participant) {
                            var avatarSrc = participant.profile_image !== 'no-image.svg' 
                                ? '<?= INCLUDE_PATH_DASHBOARD; ?>files/profile/avatar/' + participant.user_id + '/' + participant.profile_image 
                                : '<?= INCLUDE_PATH_DASHBOARD; ?>images/avatars/' + participant.plan_name.toLowerCase() + ".png";

                            var listItem = `
                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-wrapper">
                                            <div class="widget-content-left mr-3">
                                                <img width="42" class="avatar rounded-circle" src="${avatarSrc}" alt="Avatar de Usuário">
                                            </div>
                                            <div class="widget-content-left">
                                                <div class="widget-heading">${participant.firstname} ${participant.lastname}</div>
                                                <div class="widget-subheading">${participant.username}</div>
                                            </div>
                                            <div class="widget-content-right">
                                                <div>${participant.city}, ${participant.state}</div>
                                            </div>
                                        </div>
                                    </div>
                                </li>`;
                            $('#participant-list').append(listItem);
                        });
                    } else {
                        $('#participant-list').append('<li class="list-group-item">Nenhum participante encontrado.</li>');
                    }

                    // Adiciona o usuário "RendaCap" na lista
                    var rendaCapItem = `
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-wrapper">
                                    <div class="widget-content-left mr-3">
                                        <img width="42" class="avatar rounded-circle" src="<?= INCLUDE_PATH_DASHBOARD; ?>images/favicon.png" alt="Avatar de Usuário">
                                    </div>
                                    <div class="widget-content-left">
                                        <div class="widget-heading">Renda CAP Brasil</div>
                                        <div class="widget-subheading">rendacapbrasil</div>
                                    </div>
                                </div>
                            </div>
                        </li>`;
                    $('#participant-list').append(rendaCapItem);

                    // Atualiza a lista de usuários
                    let userList = '';
                    data.participants.forEach(function(participant) {
                        userList += '<li>' + participant.shortname + ' ' + (participant.user_id === data.current_user_id ? '(Você)' : '') + '</li>';
                    });
                    $('#draw-participant-list').html(userList);

                    // Adiciona o usuário "RendaCap" na lista
                    var rendaCapItemParticipantList = `<li>Renda CAP Brasil</li>`;
                    $('#draw-participant-list').append(rendaCapItemParticipantList);

                    // Atualiza as informações do sorteio
                    let drawInfo = `
                        <h4 class="font-weight-semibold mb-4">Sorteio ${data.next_draw.product}</h4>
                        <div class="mb-4">
                            <div class="d-inline-block text-center mr-5">
                                <h5 class="font-weight-bold">Produto</h5>
                                <h5 class="font-weight-bold">${data.next_draw.product}</h5>
                            </div>
                            <div class="d-inline-block text-center mr-5">
                                <h5 class="font-weight-bold">Série</h5>
                                <h5 class="font-weight-bold">${data.next_draw.series}</h5>
                            </div>
                            <div class="d-inline-block text-center mr-5">
                                <h5 class="font-weight-bold">Número Título</h5>
                                <h5 class="font-weight-bold">${data.next_draw.title}</h5>
                            </div>
                            <div class="d-inline-block text-center mr-5">
                                <h5 class="font-weight-bold">DV</h5>
                                <h5 class="font-weight-bold">${data.next_draw.dv}</h5>
                            </div>
                        </div>
                        <h5 class="font-weight-bold mb-4">Número da Sorte: ${data.next_draw.lucky_number}</h5>
                        <ul class="list-group" style="list-style: none;">
                            <li class="small"><span class="font-weight-semibold">Edição Nº 4</span> - Sorteio ${data.next_draw.draw_date}</li>
                            <li class="small"><span class="font-weight-semibold">Data da Compra</span> - ${data.next_draw.purchase_date}</li>
                            <li class="small"><span class="font-weight-semibold">Os Resgates dos T.C. do Clube Renda CAP Brasil, serão destinados integralmentes ao TeleTon e GACC /SJC</span></li>
                        </ul>
                    `;
                    $('#draw-info').html(drawInfo);
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Erro ao carregar dados do sorteio.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                }
            });
        });
    });
</script>

<?php endif; ?>

<?php if ($user['plan_id'] != 3) : ?>

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
                LEFT JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
                LEFT JOIN tb_plans p ON s.plan_id = p.id
                WHERE u.status = 1 
                AND un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
                AND p.id != 3
                ORDER BY u.id DESC
            ");
            $params = array_merge([$userId, $userId], $networkIds);
            $stmt->execute($params);
            $subusers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($subusers as $subuser) {
                // Verifica se o usuário é direto e adiciona à contagem se for
                if ($subuser['tipo'] === 'Direto' && $subuser['status'] === 'ACTIVE' && $subuser['plan_id'] !== 3) {
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
$shop_id = $_SESSION['user_id']; // Ou substitua pelo ID do usuário que deseja consultar

// Gera o array de rede
$result = buildNetworkArray($conn, $shop_id);

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
    $directUserCount = $network['network'][$userId]['diretos_count'] ?? 0;

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
            $htmlOutput .= '
                <div class="step" data-toggle="tooltip" title="Coronel">
                    <svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#EA3323">
                        <path d="M480-147q-14 0-28.5-5T426-168l-69-63q-106-97-191.5-192.5T80-634q0-94 63-157t157-63q53 0 100 22.5t80 61.5q33-39 80-61.5T660-854q94 0 157 63t63 157q0 115-85 211T602-230l-68 62q-11 11-25.5 16t-28.5 5Z"/>
                    </svg>
                </div>';
        } else {
            // Usuário com menos de 10 diretos (Direto)
            $htmlOutput .= '
                <div class="step" data-toggle="tooltip" title="Direto">
                    <div class="circle"></div>
                </div>';
        }
    }

    $svg_star = '<svg xmlns="http://www.w3.org/2000/svg" class="ml-1" height="24px" viewBox="0 -960 960 960" width="24px" fill="#E5AA17"><path d="m480-362 111 84q12 8 24 .5t7-21.5l-42-139 109-78q12-9 7-22.5T677-552H544l-45-146q-5-14-19-14t-19 14l-45 146H283q-14 0-19 13.5t7 22.5l109 78-42 139q-5 14 7 21.5t24-.5l111-84Zm0 282q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>';

    // Determina a classificação do usuário com base nas condições
    $classification = '';
    if ($colonelCount > 0) {
        $classification .= "Coronéis: {$colonelCount} " . $svg_star;
    }
    
    $classification .= "Diretos: {$directUserCount}";

    // Adiciona o resultado da classificação ao HTML
    $htmlOutput .= "<p class='mt-3'>{$classification}</p>";
    $htmlOutput .= '</div></div>'; // Fecha os contêineres da stepper

    $generaisCount = 0;
    $marechaisCount = 0;
?>

<?php
    $star_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" style="fill: rgba(247, 185, 36, 1);transform: ;msFilter:; margin-right: .3rem; margin-bottom: .6rem;"><path d="M21.947 9.179a1.001 1.001 0 0 0-.868-.676l-5.701-.453-2.467-5.461a.998.998 0 0 0-1.822-.001L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713l4.213 4.107-1.49 6.452a1 1 0 0 0 1.53 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4 4.536-4.082c.297-.268.406-.686.278-1.065z"></path></svg>';

    if ($colonelCount >= 15 && $directUserCount >= 10 && $user['plan_id'] == 4) {
        $user['patent_img_alt'] = "Marechal";
        $user['patent_img_file'] = "marechal";
        $user['patent'] = "Marechal";
    } elseif ($colonelCount >= 12 && $directUserCount >= 10 && $user['plan_id'] == 4) {
        $user['patent_img_alt'] = "General";
        $user['patent_img_file'] = "general2";
        $user['patent'] = "General $star_svg $star_svg $star_svg $star_svg";
    } elseif ($colonelCount >= 9 && $directUserCount >= 10 && $user['plan_id'] == 4) {
        $user['patent_img_alt'] = "General";
        $user['patent_img_file'] = "general2";
        $user['patent'] = "General $star_svg $star_svg $star_svg";
    } elseif ($colonelCount >= 6 && $directUserCount >= 10 && $user['plan_id'] == 4) {
        $user['patent_img_alt'] = "General";
        $user['patent_img_file'] = "general2";
        $user['patent'] = "General $star_svg $star_svg";
    } elseif ($colonelCount >= 3 && $directUserCount >= 10 && ($user['plan_id'] == 1 || $user['plan_id'] == 4)) {
        $user['patent_img_alt'] = "General";
        $user['patent_img_file'] = "general1";
        $user['patent'] = "General $star_svg";
    } elseif ($directUserCount >= 10) {
        $user['patent_img_alt'] = "Coronel";
        $user['patent_img_file'] = "coronel";
        $user['patent'] = "Coronel";
    } elseif ($directUserCount >= 6) {
        $user['patent_img_alt'] = "Afiliado";
        $user['patent_img_file'] = "afiliado3";
        $user['patent'] = "Afiliado 3";
    } elseif ($directUserCount >= 3) {
        $user['patent_img_alt'] = "Afiliado";
        $user['patent_img_file'] = "afiliado2";
        $user['patent'] = "Afiliado 2";
    } else {
        $user['patent_img_alt'] = "Afiliado";
        $user['patent_img_file'] = "afiliado1";
        $user['patent'] = "Afiliado 1";
    }
?>

<div class="main-card mb-3 card">
    <div class="no-gutters row">
        <div class="col-lg-9 d-grid">
            <div class="card-body">
                <p class="fsize-2 font-weight-bold">Indicadores e metas</p>
                <div class="row mx-4 d-flex">
                    <div class="col-md-6 col-lg-3 d-grid">
                        <div class="card mb-3 widget-chart card-hover-shadow-2x text-left">
                            <div class="widget-chart-content d-flex align-items-center" style="flex-direction: unset !important;">
                                <div class="count-up-number widget-numbers m-0" data-value="<?= $directUserCount; ?>"><?= $directUserCount; ?></div>
                                <div class="fsize-3 font-weight-bold ml-3">Diretos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 d-grid">
                        <div class="card mb-3 widget-chart card-hover-shadow-2x text-left">
                            <div class="widget-chart-content d-flex align-items-center" style="flex-direction: unset !important;">
                                <div class="count-up-number widget-numbers m-0" data-value="<?= $colonelCount; ?>"><?= $colonelCount; ?></div>
                                <div class="fsize-2 font-weight-bold ml-3">Coronéis<br>direto a você</div>
                            </div>
                        </div>
                    </div>
                </div>



                <style>
                    .container-levels {
                        margin: 0 3rem;
                    }

                    .level-container {
                        width: 100%;
                        margin: 0;
                        position: relative;
                        text-align: center;
                    }

                    .level {
                        height: 20px;
                        width: 0;
                        border-radius: 10px;
                        transition: width 0.5s;
                        top: 0;
                        position: absolute;
                        z-index: 1;
                    }

                    #level1 {
                        background-color: #7B8350;
                    }

                    #level2 {
                        background-color: #39B278;
                    }

                    #level3 {
                        background-color: #FFCC3E;
                    }

                    #levelAffiliate {
                        background-color: #C4C4C4; /* Cor para Afiliado */
                    }

                    #levelbg1 {
                        background-color: #7B8350;
                    }

                    #levelbg2 {
                        background-color: #39B278;
                    }

                    #levelbg3 {
                        background-color: #FFCC3E;
                    }

                    #levelbgAffiliate {
                        background-color: #C4C4C4; /* Fundo para Afiliado */
                    }

                    .level-bg {
                        height: 20px;
                        border-radius: 10px;
                        position: relative;
                        opacity: .5;
                        z-index: 0;
                    }

                    .level-label {
                        text-align: center;
                        opacity: 1;
                    }

                    @keyframes pulsate {
                        0% {
                            transform: scale(1);
                        }
                        50% {
                            transform: scale(1.1);
                        }
                        100% {
                            transform: scale(1);
                        }
                    }

                    .lavel-pulse {
                        animation: pulsate 1.5s ease-in-out infinite;
                    }

                    @media only screen and (max-width: 600px) {
                        .container-levels {
                            margin: 0 !important;
                        }

                        .container-stars div.col-3 {
                            padding-left: .375rem !important;
                            padding-right: .375rem !important;
                        }

                        .container-stars div svg {
                            width: 12px !important;
                            height: 12px !important;
                        }
                    }
                </style>

                <div class="container-levels">
                    <div class="row mb-3">
                        <!-- Linha de Afiliado -->
                        <div class="col-md-4 mb-3">
                            <div class="level-container position-relative" style="margin-top: 27.59px;">
                                <div class="level-bg" id="levelbgAffiliate"></div> <!-- Fundo da régua Afiliado -->
                                <div class="level lavel-pulse" id="levelAffiliate"></div> <!-- Nível da régua Afiliado -->
                                <div class="level-label fsize-2 font-weight-bold mt-2">Afiliado</div>
                                <p class="text-left fsize-2 mt-3">Torne-se Afiliado, venda até 9 assinaturas direto a você.</p>
                            </div>
                        </div>
                        
                        <!-- Linha de Coronel -->
                        <div class="col-md-2 mb-3">
                            <div class="level-container position-relative" style="margin-top: 27.59px;">
                                <div class="level-bg" id="levelbg1"></div> <!-- Fundo da régua Coronel -->
                                <div class="level lavel-pulse" id="level1"></div> <!-- Nível da régua Coronel -->
                                <div class="level-label fsize-2 font-weight-bold mt-2">Coronel</div>
                                <p class="text-left fsize-2 mt-3">Torne-se Coronel, venda 10 assinaturas direto a você.</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="row container-stars">
                                <div class="col-3 d-flex align-items-center justify-content-center">
                                    <?= $star_svg; ?>
                                </div>
                                <div class="col-3 d-flex align-items-center justify-content-center">
                                    <?= $star_svg; ?>
                                    <?= $star_svg; ?>
                                </div>
                                <div class="col-3 d-flex align-items-center justify-content-center">
                                    <?= $star_svg; ?>
                                    <?= $star_svg; ?>
                                    <?= $star_svg; ?>
                                </div>
                                <div class="col-3 d-flex align-items-center justify-content-center">
                                    <?= $star_svg; ?>
                                    <?= $star_svg; ?>
                                    <?= $star_svg; ?>
                                    <?= $star_svg; ?>
                                </div>
                            </div>
                            <div class="level-container position-relative">
                                <div class="level-bg" id="levelbg2"></div> <!-- Fundo da régua -->
                                <div class="level lavel-pulse" id="level2"></div> <!-- Nível da régua -->
                                <div class="level-label fsize-2 font-weight-bold mt-2">General</div>
                                <p class="text-left fsize-2 mt-3">Torne-se General e participe da divisão de 10% cashback de todo Brasil.</p>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="level-container position-relative" style="margin-top: 27.59px;">
                                <div class="level-bg" id="levelbg3"></div> <!-- Fundo da régua -->
                                <div class="level lavel-pulse" id="level3"></div> <!-- Nível da régua -->
                                <div class="level-label fsize-2 font-weight-bold mt-2">Marechal</div>
                                <p class="text-left fsize-2 mt-3">Marechal, Sua Vida dos Sonhos.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    $(document).ready(function() {
                        const directCount = <?= $directUserCount; ?>;
                        const colonelCount = <?= $colonelCount; ?>;
                        const userPlan = <?= $user['plan_id']; ?>;

                        // Atualiza a régua de Afiliado (0 a 9)
                        const levelAffiliate = document.getElementById('levelAffiliate');
                        if (directCount <= 9) {
                            levelAffiliate.style.width = (directCount * 10) + '%'; // 10% por número até 9
                        } else {
                            levelAffiliate.style.width = '100%'; // Preenche completamente até 9
                        }

                        // Atualiza a régua de Coronel (10)
                        const level1 = document.getElementById('level1');
                        if (directCount >= 10) {
                            level1.style.width = '100%'; // Preenche completamente ao atingir 10 diretos
                        } else {
                            level1.style.width = '0%'; // Não preenche se for diferente de 10
                        }

                        // Atualiza a régua de General (1 a 12)
                        const level2 = document.getElementById('level2');
                        if (userPlan == 4 && directCount >= 10) {
                            if (colonelCount >= 1 && colonelCount <= 12) {
                                level2.style.width = (colonelCount * (100 / 12)) + '%'; // Preenche proporcionalmente
                            } else if (colonelCount > 12) {
                                level2.style.width = '100%'; // Preenche completamente se for maior que 12
                            } else {
                                level2.style.width = '0%'; // Não preenche se for menor que 1
                            }
                        }

                        // Atualiza a régua de Marechal (13 ou mais)
                        const level3 = document.getElementById('level3');
                        if (userPlan == 4 && directCount >= 10) {
                            if (colonelCount >= 13) {
                                level3.style.width = '100%'; // Preenche completamente ao atingir 13 ou mais
                            } else {
                                level3.style.width = '0%'; // Não preenche se for menor que 13
                            }
                        }
                    });
                </script>

            </div>
        </div>


        <style>
            .targets {
                border: 3px solid black;
                border-radius: .6rem;
            }

            .targets.marechal {
                border: 3px solid green !important;
                border-radius: 50% !important;
                width: 300px;
                height: 300px;
            }
        </style>

        <div class="col-md-3 d-grid">
            <div class="position-relative d-flex flex-column align-items-center justify-content-center p-4" style="background: rgb(253, 241, 213); border-radius: .25rem 0 0 .25rem;">
                <div class="fsize-3 font-weight-bold mb-3">Minha Qualificação</div>
                <div class="targets <?= $user['patent_img_file']; ?> d-flex flex-column align-items-center justify-content-center py-3">
                    <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/targets/<?= $user['patent_img_file']; ?>.png" alt="<?= $user['patent_img_alt']; ?> Icone" style="max-height: 170px; margin: 0 2rem 1rem 2rem;">
                    <div class="fsize-4 font-weight-bold mb-0 text-uppercase text-center"><?= $user['patent']; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($user['plan_id'] == 3): ?>
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <p class="fsize-2 font-weight-bold">Adquira o plano diamante para desbloquear recursos incríveis!</p>
                <p class="fsize-1 mb-2">Escolha um de nossos planos com incríveis benefícios: Prata * Ouro * Diamante</p>
                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>minha-conta/assinatura" class="mt-1 btn btn-primary">Alterar Plano</a>
            </div>
        </div>
    </div>
</div>

<?php // else: ?>

<!-- <div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <p class="fsize-2 font-weight-bold mb-2">Você ainda não tem diretos.</p>
                <p class="fsize-1 mb-0">Envie seu link de convite e comece a criar sua rede!</p>
            </div>
        </div>
    </div>
</div> -->
<?php //endif; ?>

<?php endif; ?>

<style>
    .dataTables_scrollBody {
        margin-bottom: 1.25rem !important;
    }
</style>

<?php
    // // Preparar a consulta para buscar os últimos usuários cadastrados
    // $stmt = $conn->prepare("
    //     SELECT u.*, p.name AS plan_name
    //     FROM tb_users u
    //     LEFT JOIN tb_subscriptions s ON u.id = s.user_id
    //     LEFT JOIN tb_plans p ON s.plan_id = p.id
    //     ORDER BY u.date_create DESC
    // ");
    // $stmt->execute();

    // // Armazenar os resultados em um array
    // $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
// Função recursiva para listar usuários (diretos e indiretos em todos os níveis)
function listAssociatedUsers($conn, $networkIds, $currentUserId, $level = 1, &$processedUsers = []) {
    $allUsers = [];

    // Consulta para listar usuários diretos ou indiretos
    $stmt = $conn->prepare("
        SELECT u.id, u.firstname, u.lastname, u.username, a.city, a.state, s.status, p.price, p.name AS plan_name, u.token, 
               n.inviter_id, n.referrer_id,
               CASE 
                   WHEN n.inviter_id = ? AND n.referrer_id IS NULL THEN 'Direto'
                   WHEN n.referrer_id = ? THEN 'Direto'
                   ELSE 'Indireto' 
               END AS tipo,
               u.date_create
        FROM tb_users u
        INNER JOIN tb_user_networks un ON u.id = un.user_id
        LEFT JOIN tb_address a ON u.id = a.user_id
        LEFT JOIN tb_networks n ON un.network_id = n.id
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE u.status = 1 AND un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
        ORDER BY u.id DESC
    ");
    
    $stmt->execute(array_merge([$currentUserId, $currentUserId], $networkIds));
    $currentLevelUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os usuários encontrados ao array geral, evitando duplicatas
    foreach ($currentLevelUsers as $user) {
        if (!in_array($user['id'], $processedUsers)) {
            $user['level'] = $level; // Adiciona o nível do usuário
            $allUsers[] = $user;
            $processedUsers[] = $user['id']; // Marca o usuário como processado
        }
    }

    // Coleta os IDs dos usuários encontrados para buscar seus convidados
    $nextNetworkIds = [];
    foreach ($currentLevelUsers as $user) {
        $stmt = $conn->prepare("
            SELECT id
            FROM tb_networks
            WHERE referrer_id = ? OR inviter_id = ?
        ");
        $stmt->execute([$user['id'], $user['id']]);
        $nextNetworkIds = array_merge($nextNetworkIds, $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    // Se houver mais usuários indiretos, chama a função recursivamente, incrementando o nível
    if (!empty($nextNetworkIds)) {
        $indirectUsers = listAssociatedUsers($conn, $nextNetworkIds, $currentUserId, $level + 1, $processedUsers);
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

// Se houver redes associadas, listar os usuários
if (!empty($networkIds)) {
    $usersDetails = listAssociatedUsers($conn, $networkIds, $userId);

    // Ordena o array de usuários pela data de criação (mais recentes primeiro)
    usort($usersDetails, function ($a, $b) {
        return strtotime($b['date_create']) - strtotime($a['date_create']);
    });
} else {
    $usersDetails = []; // Sem usuários associados
}
?>

<div class="row">

    <div class="<?= ($user['plan_id'] != 3) ? "col-lg-6" : "col-lg-12"; ?>">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Últimos Cadastros</h5>
                <div class="table-responsive">
                    <table id="latestRegistrations" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Cidade - Est</th>
                                <th>Plano</th>
                                <th>Tipo</th>
                                <th>Geração</th>
                                <th>Token</th>
                                <th>Data de Criação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usersDetails)) : ?>
                                <?php foreach ($usersDetails as $userDetail) : ?>
                                    <?php
                                        $userDetail['fullname'] = $userDetail['firstname'] . " " . $userDetail['lastname'];
                                        $userDetail['surname'] = explode(' ', $userDetail['lastname'])[0];
                                        $userDetail['shortname'] = $userDetail['firstname'] . " " . $userDetail['surname'];    
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($userDetail['shortname']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['username']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['city'] . ", " . $userDetail['state']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['plan_name'] ?? "Indefinido"); ?></td>
                                        <td><?= htmlspecialchars($userDetail['tipo']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['level']); ?></td> <!-- Mostra o nível do usuário -->
                                        <td><?= htmlspecialchars($userDetail['token']); ?></td>
                                        <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($userDetail['date_create'])); ?></td>
                                    </tr>
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

    <?php if ($user['plan_id'] != 3) : ?>

    <div class="col-lg-6">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Programa de Fidelidade</h5>
                <p class="fsize-2">Para ativar o benefício, adicione o cartão de crédito para desconto automático das mensalidades que em <span class="font-weight-semibold">6 meses</span> você receberá um prêmio do RendaCAP Brasil.</p>

                <div class="d-flex align-items-center justify-content-center mb-4">
                    <div class="stepper">
                        <?php
                            // Obter a data de criação e converter para timestamp
                            list($day, $month, $year) = explode('/', $subs['creation_date']);
                            $creationDate = mktime(0, 0, 0, $month, $day, $year); // Cria timestamp da data de criação
                            $currentDate = time(); // Para verificar se estamos em um mês futuro
                            $monthsToShow = 6;

                            // Loop para gerar os meses
                            for ($i = 0; $i < $monthsToShow; $i++):
                                // Adicionar o mês correspondente
                                $currentMonth = date('n', strtotime("+$i month", $creationDate)); // Mês numérico
                                $year = date('y', strtotime("+$i month", $creationDate)); // Ano
                                $formattedMonth = substr(ucfirst($month_names[$currentMonth]), 0, 3); // Pega as três primeiras letras do mês
                                $formattedDate = "$formattedMonth/$year"; // Formata: Mês/Ano
                                $isFutureMonth = (strtotime("+$i month", $creationDate) > $currentDate); // Verifica se é um mês futuro
                        ?>

                            <div class="step <?= ($isFutureMonth ? 'red' : ''); ?>">
                                <p><?= $formattedDate; ?></p>
                                <div class="circle"></div>
                            </div>

                            <?php if ($i < $monthsToShow - 1): ?>
                                <div class="line <?= ($isFutureMonth ? 'red' : ''); ?>"></div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>

                <p class="fsize-2">O afiliado que manter suas mensalidades rigorosamente em dia durante o período de 6 meses receberá brindes, T.C. Individual, e participará de <span class="font-weight-semibold">Sorteios Exclusivos aos FIDELIDADE</span></p>
            </div>
        </div>
        </div>

    <?php endif; ?>

</div>

<!-- Count Up -->
<script>
    $(document).ready(function() {
        // Animação para valores como Reais (ex: R$ 0,00)
        $('.count-up-real').each(function() {
            var $this = $(this);
            var value = $this.data('value');
            
            $this.text('0,00'); // Inicia com zero

            $({ count: 0 }).animate({ count: value }, {
                duration: 2000, // Duração da animação em milissegundos
                step: function() {
                    // Formata o valor para 2 casas decimais e substitui o ponto por vírgula
                    $this.text(Number(this.count).toFixed(2).replace('.', ','));
                },
                complete: function() {
                    // Garante que o valor final seja corretamente formatado
                    $this.text(Number(this.count).toFixed(2).replace('.', ','));
                }
            });
        });

        // Animação para valores numéricos inteiros (ex: 0)
        $('.count-up-number').each(function() {
            var $this = $(this);
            var value = $this.data('value');
            
            $this.text('0'); // Inicia com zero

            $({ count: 0 }).animate({ count: value }, {
                duration: 2000, // Duração da animação em milissegundos
                step: function() {
                    // Formata o valor como número inteiro
                    $this.text(Math.floor(this.count));
                },
                complete: function() {
                    // Garante que o valor final seja corretamente formatado
                    $this.text(Math.floor(this.count));
                }
            });
        });
    });
</script>

<script>
    // Datatables

    $(document).ready(() => {
        $("#my-titles-table").DataTable({
            ordering: false,
            pageLength: 4, // Exibir no máximo 6 registros por página
            lengthChange: false, // Ocultar o seletor de quantidade de registros
            language: {
                "sProcessing": "Processando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                "sInfoPostFix": "",
                "sSearch": "Pesquisar:",
                "sUrl": "",
                "sInfoThousands": ".",
                "sLoadingRecords": "Carregando...",
                "oPaginate": {
                    "sFirst": "Primeiro",
                    "sPrevious": "Anterior",
                    "sNext": "Próximo",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": ativar para classificar a coluna em ordem crescente",
                    "sSortDescending": ": ativar para classificar a coluna em ordem decrescente"
                }
            }
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
        $("#latestRegistrations").DataTable({
            ordering: false,
            language: {
                "sProcessing": "Processando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                "sInfoPostFix": "",
                "sSearch": "Pesquisar:",
                "sUrl": "",
                "sInfoThousands": ".",
                "sLoadingRecords": "Carregando...",
                "oPaginate": {
                    "sFirst": "Primeiro",
                    "sPrevious": "Anterior",
                    "sNext": "Próximo",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": ativar para classificar a coluna em ordem crescente",
                    "sSortDescending": ": ativar para classificar a coluna em ordem decrescente"
                }
            }
        });
    });

    $(document).ready(function(){
        $("input[type='search']").wrap("<form>");
        $("input[type='search']").closest("form").attr("autocomplete","off");
        $("input[type='search']").attr("autocomplete", "off");
    });
</script>