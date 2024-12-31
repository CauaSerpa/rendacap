<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-clock icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Cadastros Pendentes
                <div class="page-title-subheading">Aqui você pode acompanhar seus convidados que estão com o cadastro pendente.</div>
            </div>
        </div>
    </div>
</div>

<?php
// Função recursiva para listar usuários (diretos e indiretos em todos os níveis)
function listPendingUsers($conn, $networkIds, $currentUserId, &$processedUsers = []) {
    $allUsers = [];

    // Consulta para listar usuários diretos ou indiretos
    $stmt = $conn->prepare("
        SELECT u.id, u.firstname, u.lastname, u.username, u.email, s.status, p.price, p.name AS plan_name, u.token, u.whatsapp, 
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
        WHERE (u.status = 1 OR s.user_id IS NULL OR s.plan_id = 3) AND un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
        ORDER BY u.id DESC
    ");
    
    $stmt->execute(array_merge([$currentUserId, $currentUserId], $networkIds));
    $currentLevelUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os usuários encontrados ao array geral, evitando duplicatas
    foreach ($currentLevelUsers as $user) {
        if (!in_array($user['id'], $processedUsers)) {
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

    // Se houver mais usuários indiretos, chama a função recursivamente
    if (!empty($nextNetworkIds)) {
        $indirectUsers = listPendingUsers($conn, $nextNetworkIds, $currentUserId, $processedUsers);
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
    $pendingUsers = listPendingUsers($conn, $networkIds, $userId);

    // Ordena o array de usuários pela data de criação (mais recentes primeiro)
    usort($pendingUsers, function ($a, $b) {
        return strtotime($b['date_create']) - strtotime($a['date_create']);
    });
} else {
    $pendingUsers = []; // Sem usuários associados
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Cadastros Pendentes</h5>
                <div class="table-responsive">
                    <table id="latestRegistrations" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Email</th>
                                <th>Plano</th>
                                <th>Tipo</th>
                                <th>WhatsApp</th>
                                <th>Token</th>
                                <th>Data de Criação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendingUsers)) : ?>
                                <?php foreach ($pendingUsers as $user) : ?>
                                    <?php
                                        $user['fullname'] = $user['firstname'] . " " . $user['lastname'];
                                        $user['surname'] = explode(' ', $user['lastname'])[0];
                                        $user['shortname'] = $user['firstname'] . " " . $user['surname'];    
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['shortname']); ?></td>
                                        <td><?= htmlspecialchars($user['username']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td><?= htmlspecialchars($user['plan_name'] ?? "Indefinido"); ?></td>
                                        <td><?= htmlspecialchars($user['tipo']); ?></td>
                                        <td><?= htmlspecialchars($user['whatsapp']); ?></td>
                                        <td><?= htmlspecialchars($user['token']); ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($user['date_create'])); ?></td>
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
</div>

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