<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users icon-gradient bg-plum-plate"></i>
            </div>
            <div>
                Cadastros
                <div class="page-title-subheading">Aqui você pode gerenciar e analisar todas os cadastros do sistema.</div>
            </div>
        </div>
    </div>
</div>

<?php
// Consulta para obter os detalhes dos usuários com um único registro por usuário
// $stmt = $conn->prepare("
//     SELECT u.id, 
//            u.firstname, 
//            u.lastname, 
//            u.username, 
//            u.email, 
//            s.status, 
//            p.price, 
//            p.name AS plan_name, 
//            u.token, 
//            u.date_create
//     FROM tb_users u
//     JOIN tb_user_roles r ON u.id = r.user_id
//     LEFT JOIN (
//         SELECT s.user_id, s.status, s.plan_id
//         FROM tb_subscriptions s
//         WHERE s.status = 'ACTIVE'
//         ORDER BY s.id DESC
//         LIMIT 1
//     ) s ON u.id = s.user_id
//     LEFT JOIN tb_plans p ON s.plan_id = p.id
//     WHERE u.status = 1 AND r.role_id = 3
//     ORDER BY u.id DESC
// ");
$stmt = $conn->prepare("
    SELECT u.id, 
           u.firstname, 
           u.lastname, 
           u.username, 
           u.email, 
           s.status, 
           p.price, 
           p.name AS plan_name, 
           u.token, 
           u.date_create
    FROM tb_users u
    JOIN tb_user_roles r ON u.id = r.user_id
    LEFT JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
    LEFT JOIN tb_plans p ON s.plan_id = p.id
    WHERE u.status = 1 AND r.role_id = 3
    ORDER BY u.id DESC
");
$stmt->execute();
$usersDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <h5 class="card-title">Cadastrados</h5>

                <!-- Botões de Filtro -->
                <div class="btn-group mb-4" id="filter" role="group" aria-label="Filtro de Usuários">
                    <button type="button" class="btn btn-secondary btn-filter active" data-filter="all">Todos (<span class="count-all">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="Bronze">Bronze (<span class="count-bronze">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="Prata">Prata (<span class="count-prata">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="Ouro">Ouro (<span class="count-ouro">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="Diamante">Diamante (<span class="count-diamante">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="Indefinido">Indefinido (<span class="count-indefinido">0</span>)</button>
                </div>

                <!-- Tabela de Usuários -->
                <div class="table-responsive">
                    <table id="users-table" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Email</th>
                                <th>Plano</th>
                                <th>Status da Assinatura</th>
                                <th>Data de Criação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usersDetails)) : ?>
                                <?php foreach ($usersDetails as $userDetail) : ?>
                                    <tr data-plan="<?= $userDetail['plan_name'] ?? 'Indefinido'; ?>">
                                        <td><?= htmlspecialchars($userDetail['id']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['firstname'] . ' ' . $userDetail['lastname']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['username']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['email']); ?></td>
                                        <td><?= htmlspecialchars($userDetail['plan_name'] ?? 'Indefinido'); ?></td>
                                        <td><?= translate_status($userDetail['status']) ?? "Indefinido"; ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($userDetail['date_create']))); ?></td>
                                        <td class="d-flex align-items-center">
                                            <form method="POST" action="<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/login-user.php">
                                                <input type="hidden" name="user_id" value="<?= $userDetail['id']; ?>">
                                                <input type="hidden" name="user_email" value="<?= $userDetail['email']; ?>">
                                                <button type="submit" class="btn btn-success">Login sem Senha</button>
                                            </form>
                                            <button class="btn btn-danger btn-delete-user ml-2" data-toggle="modal" data-target="#deleteUserModal" data-user-id="<?= $userDetail['id']; ?>" data-user-name="<?= htmlspecialchars($userDetail['firstname'] . ' ' . $userDetail['lastname']); ?>">
                                                Deletar
                                            </button>
                                        </td>
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
    $(document).ready(function () {
        // Abre o modal com as informações do usuário a ser deletado
        $('.btn-delete-user').on('click', function () {
            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');

            $('#userToDelete').text(userName);
            $('#userIdToDelete').val(userId);
        });

        // Confirmação da exclusão
        $('#confirmDeleteUser').on('click', function () {
            const userId = $('#userIdToDelete').val();
            const adminPassword = $('#adminPassword').val();

            if (!adminPassword) {
                alert('Por favor, insira sua senha para confirmar a exclusão.');
                return;
            }

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/delete-user.php',
                method: 'POST',
                data: {
                    action: 'delete-user',
                    user_id: userId,
                    admin_password: adminPassword
                },
                success: function(response) {
                    if(response.status == "success") {
                        location.reload(); // Atualiza a página
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Ocorreu um erro ao aprovar o comprovante.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Inicializa DataTable
        var table = $("#users-table").DataTable({
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
            var countBronze = table
                .rows()
                .data()
                .filter(row => row[4] === "Bronze").length;
            var countPrata = table
                .rows()
                .data()
                .filter(row => row[4] === "Prata").length;
            var countOuro = table
                .rows()
                .data()
                .filter(row => row[4] === "Ouro").length;
            var countDiamante = table
                .rows()
                .data()
                .filter(row => row[4] === "Diamante").length;
            var countIndefinido = table
                .rows()
                .data()
                .filter(row => row[4] === "Indefinido").length;

            // Atualiza os textos dos botões
            $('.count-all').text(total);
            $('.count-bronze').text(countBronze);
            $('.count-prata').text(countPrata);
            $('.count-ouro').text(countOuro);
            $('.count-diamante').text(countDiamante);
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