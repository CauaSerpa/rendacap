<?php
// Função recursiva para listar usuários (diretos e indiretos em todos os níveis)
function listAssociatedUsers($conn, $networkIds, $currentUserId, $level = 1, &$processedUsers = []) {
    $allUsers = [];

    // Consulta para listar usuários diretos ou indiretos
    $stmt = $conn->prepare("
        SELECT u.id, u.status, u.firstname, u.lastname, u.username, u.email, s.status AS subs_status, p.price, p.name AS plan_name, u.token, 
               n.inviter_id, n.referrer_id, 
               CASE 
                   WHEN n.inviter_id = ? AND n.referrer_id IS NULL THEN 'Direto'
                   WHEN n.referrer_id = ? THEN 'Direto'
                   ELSE 'Indireto' 
               END AS tipo,
               u.date_create
        FROM tb_users u
        INNER JOIN tb_user_networks un ON u.id = un.user_id
        LEFT JOIN tb_networks n ON un.network_id = n.id
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE u.status = 1 AND un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
        ORDER BY u.id DESC
    ");
    
    $stmt->execute(array_merge([$currentUserId, $currentUserId], $networkIds));
    $currentLevelUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os usuários encontrados ao array geral, evitando duplicatas
    foreach ($currentLevelUsers as $user) {
        if (!in_array($user['id'], $processedUsers)) {
            // Se o referrer_id existir, atribui ao inviter_id
            $user['inviter_id'] = $user['referrer_id'] ?? $user['inviter_id'];
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

// Consulta para listar todos os usuários
$stmt = $conn->prepare("
    SELECT u.id, 
            CASE 
                WHEN u.status = 1 THEN 'Ativo'
                ELSE 'Desativado' 
            END AS status, 
            u.firstname, u.lastname, u.username, u.email, u.date_create, s.plan_id, p.name AS plan_name, n.inviter_id, n.referrer_id, a.city, a.state
    FROM tb_users u
    LEFT JOIN tb_address a ON u.id = a.user_id
    LEFT JOIN tb_user_networks un ON u.id = un.user_id
    LEFT JOIN tb_networks n ON un.network_id = n.id
    LEFT JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
    LEFT JOIN tb_plans p ON s.plan_id = p.id
");
$stmt->execute();
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Adiciona contagem de usuários associados para cada usuário
foreach ($allUsers as &$user) {
    $userId = $user['id'];

    // Consulta para obter todos os network_id associados ao usuário
    $stmt = $conn->prepare("
        SELECT id
        FROM tb_networks
        WHERE referrer_id = ? OR inviter_id = ?
    ");
    $stmt->execute([$userId, $userId]);
    $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Inicializa contador de planos
    $userCounts = [
        'Diamante' => 0,
        'Ouro' => 0,
        'Prata' => 0,
        'Bronze' => 0,
    ];

    // Se houver redes associadas, contar usuários por plano
    if (!empty($networkIds)) {
        $usersDetails = listAssociatedUsers($conn, $networkIds, $userId);

        // Contar usuários com base nos planos
        foreach ($usersDetails as $detail) {
            switch ($detail['plan_name']) {
                case 'Diamante':
                    $userCounts['Diamante']++;
                    break;
                case 'Ouro':
                    $userCounts['Ouro']++;
                    break;
                case 'Prata':
                    $userCounts['Prata']++;
                    break;
                case 'Bronze':
                    $userCounts['Bronze']++;
                    break;
            }
        }
    }

    // Armazena a contagem total de associados para ordenação
    $user['total_associados'] = array_sum($userCounts);
    $user['userCounts'] = $userCounts; // Adiciona a contagem de planos ao usuário

    // Consulta para obter o nome do usuário que é o convidador (inviter)
    $stmt = $conn->prepare("
        SELECT firstname, lastname 
        FROM tb_users 
        WHERE id = ?
    ");
    $stmt->execute([$user['inviter_id']]);
    $inviter = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Adiciona nome do convidador ao usuário
    $user['inviter_name'] = $inviter ? $inviter['firstname'] . ' ' . $inviter['lastname'] : 'Indefinido';
}

// Ordena os usuários pela contagem de associados (maior para menor)
usort($allUsers, function ($a, $b) {
    return $b['total_associados'] <=> $a['total_associados'];
});
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

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users icon-gradient bg-tempting-azure"></i>
            </div>
            <div>
                Login/Rede
                <div class="page-title-subheading">Aqui estão os detalhes de cada usuário e a contagem da rede que possuem.</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Usuários e Contagem de Redes</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered w-100" id="login-rede">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Plano</th>
                                <th>Qualificação</th>
                                <th>Patrocinador</th>
                                <th>Status</th>
                                <th>Data Entrada</th>
                                <th>Cidade/Estado</th>
                                <th>Diamante</th>
                                <th>Ouro</th>
                                <th>Prata</th>
                                <th>Bronze</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($allUsers)) : ?>
                                <?php foreach ($allUsers as $user) : ?>
                                    <?php
                                        $planId = $user['plan_id'];

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
                                            $user['classification'] = "Marechal";
                                        } elseif ($colonelCount >= 12 && $directUserCount >= 10) {
                                            $user['classification'] = "General 4";
                                        } elseif ($colonelCount >= 9 && $directUserCount >= 10) {
                                            $user['classification'] = "General 3";
                                        } elseif ($colonelCount >= 6 && $directUserCount >= 10) {
                                            $user['classification'] = "General 2";
                                        } elseif ($colonelCount >= 3 && $directUserCount >= 10) {
                                            $user['classification'] = "General 1";
                                        } elseif ($directUserCount >= 10) {
                                            $user['classification'] = "coronel";
                                        } elseif ($directUserCount >= 6) {
                                            $user['classification'] = "Afiliado 3";
                                        } elseif ($directUserCount >= 3) {
                                            $user['classification'] = "Afiliado 2";
                                        } else {
                                            $user['classification'] = "Afiliado 1";
                                        }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['firstname'] . " " . $user['lastname']); ?></td>
                                        <td><?= htmlspecialchars($user['username']); ?></td>
                                        <td><?= htmlspecialchars($user['plan_name'] ?? 'Indefinido'); ?></td>
                                        <td><?= htmlspecialchars($user['classification']); ?></td>
                                        <td><?= htmlspecialchars($user['inviter_name'] ?? 'Indefinido'); ?></td>
                                        <td><?= htmlspecialchars($user['status']); ?></td>
                                        <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($user['date_create'])); ?></td>
                                        <td><?= htmlspecialchars($user['city'] . " - " . $user['state']); ?></td>
                                        <td><?= htmlspecialchars($user['userCounts']['Diamante'] ?? 0); ?></td>
                                        <td><?= htmlspecialchars($user['userCounts']['Ouro'] ?? 0); ?></td>
                                        <td><?= htmlspecialchars($user['userCounts']['Prata'] ?? 0); ?></td>
                                        <td><?= htmlspecialchars($user['userCounts']['Bronze'] ?? 0); ?></td>
                                        <td>
                                            <button class="btn btn-danger btn-sm">Bloquear</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="13" class="text-center">Nenhum usuário encontrado.</td>
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
    // Datatables
    $(document).ready(() => {
        $("#login-rede").DataTable({
            responsive: true,
            scrollCollapse: true,
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