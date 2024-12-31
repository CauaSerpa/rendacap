<?php
// Consulta para listar todos os usuários e suas redes
$stmt = $conn->prepare("
    SELECT u.id, 
           u.firstname, 
           u.lastname, 
           u.username, 
           p.name AS plan_name, 
           u.status, 
           u.date_create,
           (SELECT COUNT(*) 
            FROM tb_user_networks un
            INNER JOIN tb_networks n ON un.network_id = n.id
            WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id)) AS total_diretos,
           (SELECT COUNT(*) 
            FROM tb_user_networks un
            INNER JOIN tb_networks n ON un.network_id = n.id
            INNER JOIN tb_subscriptions s ON un.user_id = s.user_id
            WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id) AND s.plan_id = 4 AND s.status = 'ACTIVE') AS total_diamante,
           (SELECT COUNT(*) 
            FROM tb_user_networks un
            INNER JOIN tb_networks n ON un.network_id = n.id
            INNER JOIN tb_subscriptions s ON un.user_id = s.user_id
            WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id) AND s.plan_id = 1 AND s.status = 'ACTIVE') AS total_ouro,
           (SELECT COUNT(*) 
            FROM tb_user_networks un
            INNER JOIN tb_networks n ON un.network_id = n.id
            INNER JOIN tb_subscriptions s ON un.user_id = s.user_id
            WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id) AND s.plan_id = 2 AND s.status = 'ACTIVE') AS total_prata,
           (SELECT COUNT(*) 
            FROM tb_user_networks un
            INNER JOIN tb_networks n ON un.network_id = n.id
            INNER JOIN tb_subscriptions s ON un.user_id = s.user_id
            WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id) AND s.plan_id = 3 AND s.status = 'ACTIVE') AS total_bronze
    FROM tb_users u
    JOIN tb_user_roles ur ON u.id = ur.user_id
    LEFT JOIN tb_subscriptions s ON u.id = s.user_id
    LEFT JOIN tb_plans p ON s.plan_id = p.id
    WHERE u.status = 1 AND ur.role_id = 3
    GROUP BY u.id
    ORDER BY total_diretos DESC
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users icon-gradient bg-tempting-azure"></i>
            </div>
            <div>
                Usuários com mais Diretos
                <div class="page-title-subheading">Aqui estão os usuários com mais diretos, organizados por plano.</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title">Usuários e Contagem de Diretos</h5>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/export-users-directs-table.php" class="btn btn-success mb-3">Exportar CSV</a> <!-- Botão de exportação -->
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered w-100" id="users-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Plano</th>
                                <th>Total Diretos</th>
                                <th>Diamante</th>
                                <th>Ouro</th>
                                <th>Prata</th>
                                <th>Bronze</th>
                                <th>Data Entrada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)) : ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['plan_name'] ?? 'Nenhum') ?></td>
                                        <td><?= $user['total_diretos'] ?></td>
                                        <td><?= $user['total_diamante'] ?></td>
                                        <td><?= $user['total_ouro'] ?></td>
                                        <td><?= $user['total_prata'] ?></td>
                                        <td><?= $user['total_bronze'] ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($user['date_create']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9" class="text-center">Nenhum usuário encontrado.</td>
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
        $("#users-table").DataTable({
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