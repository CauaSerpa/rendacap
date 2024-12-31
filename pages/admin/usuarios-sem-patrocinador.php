<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users icon-gradient bg-plum-plate"></i>
            </div>
            <div>
                Usuários Sem Patrocinador
                <div class="page-title-subheading">Aqui você pode visualizar os usuários que ainda não possuem patrocinador.</div>
            </div>
        </div>
    </div>
</div>

<?php
// Consulta para obter os usuários sem patrocinador
$stmt = $conn->prepare("  
    SELECT u.id, 
           u.firstname, 
           u.lastname, 
           u.username, 
           u.email, 
           u.whatsapp
    FROM tb_users u
    LEFT JOIN tb_user_networks un ON u.id = un.user_id
    INNER JOIN tb_user_roles ur ON u.id = ur.user_id
    WHERE un.user_id IS NULL AND ur.role_id = 3
    ORDER BY u.id DESC
");
$stmt->execute();
$usersWithoutSponsor = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Usuários Sem Patrocinador</h5>

                <div class="table-responsive">
                    <table id="users-table" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Usuário</th>
                                <th>Email</th>
                                <th>WhatsApp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usersWithoutSponsor)) : ?>
                                <?php foreach ($usersWithoutSponsor as $user) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['id']); ?></td>
                                        <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                                        <td><?= htmlspecialchars($user['username']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td><?= htmlspecialchars($user['whatsapp']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum usuário encontrado.</td>
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

    $(document).ready(function(){
        $("input[type='search']").wrap("<form>");
        $("input[type='search']").closest("form").attr("autocomplete","off");
        $("input[type='search']").attr("autocomplete", "off");
    });
</script>
