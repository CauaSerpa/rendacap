<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users icon-gradient bg-malibu-beach"></i>
            </div>
            <div>
                Meus Diretos
                <div class="page-title-subheading">Aqui você pode ver quem é seu direto</div>
            </div>
        </div>
    </div>
</div>

<?php
// Função para contar os diretos de um usuário
function countDirectUsers($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM tb_networks
        WHERE referrer_id = ? OR (inviter_id = ? AND referrer_id IS NULL)
    ");
    $stmt->execute([$userId, $userId]);
    return $stmt->fetchColumn();
}

// Função para listar usuários diretos com base no network_id
function listDirectUsers($conn, $userId) {
    // 1. Consulta para obter todos os network_id onde o inviter_id ou referrer_id seja o userId
    $stmt = $conn->prepare("
        SELECT id
        FROM tb_networks
        WHERE referrer_id = ? OR (inviter_id = ? AND referrer_id IS NULL)
    ");
    $stmt->execute([$userId, $userId]);
    $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Verifica se há network_ids encontrados
    if (empty($networkIds)) {
        return []; // Retorna um array vazio se não houver networks
    }

    // 2. Consulta para listar os user_id dos usuários associados àqueles network_ids
    $stmt = $conn->prepare("
        SELECT u.id, u.firstname, u.lastname, u.username, u.email, u.whatsapp, p.name AS plan_name, u.token, u.date_create
        FROM tb_user_networks un
        INNER JOIN tb_users u ON un.user_id = u.id
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE s.status = 'ACTIVE' AND un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
        ORDER BY u.id DESC
    ");
    $stmt->execute($networkIds);
    
    // Retorna todos os usuários encontrados
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ID do usuário atual (da sessão)
$userId = $_SESSION['user_id'];

// Lista os usuários diretos relacionados ao usuário atual
$users = listDirectUsers($conn, $userId);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Meus Diretos</h5>
                <div class="table-responsive">
                    <table id="directUsers" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>DD (Diretos dos Diretos)</th>
                                <th>Login</th>
                                <th>Email</th>
                                <th>Celular</th>
                                <th>Plano</th>
                                <th>Token</th>
                                <th>Data de Criação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)) : ?>
                                <?php foreach ($users as $user) : ?>
                                    <?php
                                        // Conta os diretos dos diretos
                                        $user['ddCount'] = countDirectUsers($conn, $user['id']);

                                        $user['fullname'] = $user['firstname'] . " " . $user['lastname'];
                                        $user['surname'] = explode(' ', $user['lastname'])[0];
                                        $user['shortname'] = $user['firstname'] . " " . $user['surname'];    
                                    ?>
                                    <tr>
                                        <td><?= $user['shortname']; ?></td>
                                        <td><?= $user['ddCount']; ?></td>
                                        <td><?= $user['username']; ?></td>
                                        <td><?= $user['email']; ?></td>
                                        <td><?= $user['whatsapp']; ?></td>
                                        <td><?= $user['plan_name'] ?? "Indefinido"; ?></td>
                                        <td><?= $user['token']; ?></td>
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
        const dataTable = $("#directUsers").DataTable({
            ordering: false,
            language: {
                "sProcessing": "Processando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
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