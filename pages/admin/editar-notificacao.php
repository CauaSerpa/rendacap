<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon py-0">
                <i class="ion-android-notifications icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Editar Notificação
                <div class="page-title-subheading">Aqui você pode editar notificações.</div>
            </div>
        </div>
    </div>
</div>

<?php
    // Busca todas as notificações
    $query = "
        SELECT n.*
        FROM tb_notifications n
        WHERE n.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['id']]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);

    // Carregar todos os usuários
    $stmt_all_users = $conn->prepare("SELECT id, username FROM tb_users WHERE status = 1");
    $stmt_all_users->execute();
    $all_users = $stmt_all_users->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar todos os usuários associados a este título
    $stmt_users = $conn->prepare("
        SELECT u.id, u.username
        FROM tb_users u
        JOIN tb_notification_recipients nr ON u.id = nr.user_id
        JOIN tb_notifications n ON nr.notification_id = n.id
        WHERE n.id = ?
    ");
    $stmt_users->execute([$notification['id']]);
    $associated_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
    
    // Separar os usuários em associados e não associados
    $associated_user_ids = array_column($associated_users, 'user_id');
    $non_associated_users = array_filter($all_users, function($user) use ($associated_user_ids) {
        return !in_array($user['id'], $associated_user_ids);
    });
?>

<div class="main-card mb-3 card">
    <div class="card-body">
        <form id="edit-notification-form">
            <div class="form-row gap-3 mb-3">
                <div class="col-md-6">
                    <div class="position-relative form-group mb-3">
                        <label for="title">Título</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= $notification['title']; ?>" required>
                    </div>
                    <div class="position-relative form-group mb-3">
                        <label for="content">Conteúdo</label>
                        <textarea name="content" id="content" placeholder="Por favor, insira o conteúdo da notificação." class="form-control" rows="5" required><?= $notification['content']; ?></textarea>
                    </div>
                </div>
                <div class="col-md-5 offset-md-1">
                    <div class="position-relative form-group mb-3">
                        <label for="type">Tipo</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="" selected disabled>Selecione o Tipo de Notificação</option>
                            <option value="all" <?= isset($notification['type']) && $notification['type'] == 'all' ? 'selected' : '' ?>>Todos os usuários</option>
                            <option value="plan_users" <?= isset($notification['type']) && $notification['type'] == 'plan_users' ? 'selected' : '' ?>>Usuários de um plano</option>
                            <option value="selecteds" <?= isset($notification['type']) && $notification['type'] == 'selecteds' ? 'selected' : '' ?>>Usuários selecionados</option>
                        </select>
                    </div>

                    <div id="plan-users-section" class="position-relative form-group mb-3 <?= ($notification['type'] != 'plan_users') ? "d-none" : ""; ?>">
                        <label for="plan_users">Plano</label>
                        <select class="form-control" id="plan_users" name="plan_users">
                            <option value="" selected disabled>Selecione o Plano dos Usuários que você deseja enviar uma Notificação</option>
                            <option value="4" <?= isset($notification['plan']) && $notification['plan'] == '4' ? 'selected' : '' ?>>Diamante</option>
                            <option value="1" <?= isset($notification['plan']) && $notification['plan'] == '1' ? 'selected' : '' ?>>Ouro</option>
                            <option value="2" <?= isset($notification['plan']) && $notification['plan'] == '2' ? 'selected' : '' ?>>Prata</option>
                            <option value="3" <?= isset($notification['plan']) && $notification['plan'] == '3' ? 'selected' : '' ?>>Bronze</option>
                        </select>
                    </div>

                    <div id="associated-users-section" class="main-card card <?= ($notification['type'] != 'selecteds') ? "d-none" : ""; ?>">
                        <div class="card-body">

                            <!-- Tabela para listar usuários associados -->
                            <div class="mb-3 d-flex align-items-center justify-content-between">
                                <h5>Usuários Associados</h5>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#selectUsersModal">Selecionar Usuários</button>
                            </div>

                            <!-- Exibir os usuários associados -->
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="w-100">Usuário</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="associated_users_list">
                                        <?php if (!empty($associated_users)) : ?>
                                            <?php foreach ($associated_users as $user): ?>
                                                <tr data-id="<?= $user['id']; ?>">
                                                    <td><?= $user['username']; ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-user" data-id="<?= $user['id']; ?>">Remover</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="2" class="text-center">Nenhum usuário associado encontrado.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex align-items-center justify-content-between">
                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>notificacoes" class="btn btn-light">Voltar</a>
                <button type="submit" id="btnSubmit" class="btn btn-primary" style="width: 82px;">Salvar</button>
                <button id="btnLoader" class="btn btn-primary d-none" style="width: 82px;" disabled>
                    <div class="loader">
                        <div class="ball-pulse">
                            <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                            <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                            <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                        </div>
                    </div>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Consultar todos os usuários do sistema
$stmt_all_users = $conn->query("
                                SELECT u.id, u.username, p.name AS plan_name
                                FROM tb_users u
                                JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
                                JOIN tb_plans p ON s.plan_id = p.id
                                JOIN tb_user_roles r ON u.id = r.user_id
                                WHERE u.status = 1 AND r.role_id = 3
                            ");
$all_users = $stmt_all_users->fetchAll(PDO::FETCH_ASSOC);

// Associar os IDs dos usuários já associados ao título para fácil verificação
$associated_user_ids = array_column($associated_users, 'id');
?>

<script>
$(document).ready(function () {
    // Detecta alterações no campo select
    $('#type').on('change', function () {
        const value = $(this).val();
        const planUsersSection = $('#plan-users-section');
        const associatedUsersSection = $('#associated-users-section');

        if (value === 'selecteds') {
            // Mostrar a seção de usuários associados
            associatedUsersSection.removeClass('d-none').addClass('d-block');

            // Ocultar a seção de usuários associados
            planUsersSection.removeClass('d-block').addClass('d-none');
        } else if (value === 'plan_users') {
            // Mostrar a seção de usuários associados
            planUsersSection.removeClass('d-none').addClass('d-block');

            // Ocultar a seção de usuários associados
            associatedUsersSection.removeClass('d-block').addClass('d-none');
        } else {
            // Ocultar a seção de usuários associados
            associatedUsersSection.removeClass('d-block').addClass('d-none');
            planUsersSection.removeClass('d-block').addClass('d-none');
        }
    });

    // Popular a lista de usuários no modal
    const users = <?= json_encode($all_users); ?>;
    const associatedUserIds = <?= json_encode($associated_user_ids); ?>;

    // Arrays para armazenar os usuários selecionados e removidos
    let removedUsers = [];
    let selectedUsers = associatedUserIds.slice(); // Clonar o array de IDs de usuários associados inicialmente

    // Filtrar usuários associados e não associados
    const associatedUsers = users.filter(user => associatedUserIds.includes(user.id));
    const nonAssociatedUsers = users.filter(user => !associatedUserIds.includes(user.id));

    // Adiciona usuários associados primeiro
    associatedUsers.forEach(user => {
        const row = `
            <tr>
                <td><input type="checkbox" class="user-checkbox" value="${user.id}" checked></td>
                <td>${user.username}</td>
                <td>${user.plan_name}</td>
            </tr>`;
        $('#userList').append(row);
    });

    // Depois adiciona os não associados
    nonAssociatedUsers.forEach(user => {
        const row = `
            <tr>
                <td><input type="checkbox" class="user-checkbox" value="${user.id}"></td>
                <td>${user.username}</td>
                <td>${user.plan_name}</td>
            </tr>`;
        $('#userList').append(row);
    });

    // Confirmar a seleção de usuários e atualizar a lista
    $('#confirmSelection').on('click', function () {
        // Obter os IDs dos usuários selecionados no modal
        selectedUsers = $('.user-checkbox:checked').map(function () {
            return this.value;
        }).get();

        // Atualiza a lista de usuários associados no formulário
        $('#associated_users_list').empty();
        selectedUsers.forEach(userId => {
            const userRow = `<tr data-id="${userId}"><td>${users.find(user => user.id == userId).username}</td><td><button type="button" class="btn btn-danger btn-sm remove-user" data-id="${userId}">Remover</button></td></tr>`;
            $('#associated_users_list').append(userRow);
        });

        // Atualiza o campo oculto de usuários selecionados no formulário
        $('#edit-notification-form input[name="selected_users"]').remove(); // Remover se já existir
        $('#edit-notification-form').append(`<input type="hidden" name="selected_users" value="${selectedUsers.join(',')}">`);

        // Atualiza o campo oculto de usuários removidos no formulário
        $('#edit-notification-form input[name="removed_users"]').remove(); // Remover se já existir
        if (removedUsers.length > 0) {
            $('#edit-notification-form').append(`<input type="hidden" name="removed_users" value="${removedUsers.join(',')}">`);
        }

        // Fechar o modal
        $('#selectUsersModal').modal('hide');
    });

    // Função para remover usuário associado
    $(document).on('click', '.remove-user', function () {
        const userId = $(this).data('id');
        const row = $(this).closest('tr');
        
        // Remover o usuário da lista visível
        row.remove();

        // Desmarcar o checkbox no modal, se aplicável
        $('.user-checkbox[value="' + userId + '"]').prop('checked', false);

        // Adicionar o ID do usuário ao array de removidos
        if (!removedUsers.includes(userId)) {
            removedUsers.push(userId);
        }

        // Remover o usuário do array de selecionados (se aplicável)
        selectedUsers = selectedUsers.filter(id => id != userId);

        // Atualiza o campo oculto de usuários selecionados no formulário
        $('#edit-notification-form input[name="selected_users"]').remove(); // Remover se já existir
        $('#edit-notification-form').append(`<input type="hidden" name="selected_users" value="${selectedUsers.join(',')}">`);

        // Atualiza o campo oculto de usuários removidos no formulário
        $('#edit-notification-form input[name="removed_users"]').remove(); // Remover se já existir
    });
});
</script>

<script>
    $(document).ready(() => {
        const table = $("#tableTitleUsersAssociated").DataTable({
            responsive: true,
            scrollCollapse: true,
            ordering: false,
            pageLength: 15, // Define o número padrão de registros por página
            lengthChange: false, // Desabilita o seletor de páginas
            language: {
                "sProcessing": "Processando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                "sInfoPostFix": "",
                "sSearch": "", // Desabilita o texto de pesquisa padrão
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

        // Mapeia o input de busca do DataTables
        $('#search-input').on('keyup', function () {
            table.search(this.value).draw();
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#edit-notification-form').on('submit', function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            btnSubmit = $("#btnSubmit");
            btnLoader = $("#btnLoader");

            // Desabilitar botão submit e habilitar loader
            btnSubmit.addClass("d-none");
            btnLoader.removeClass("d-none");

            let formData = new FormData(this);
            formData.append('action', 'edit-notification-form'); // Adicionado 'action' diretamente ao FormData
            formData.append('notification_id', <?= $notification['id']; ?>); // Adiciona 'title_id' diretamente ao FormData

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/edit-notification-form.php', // URL do arquivo PHP para salvar a senha
                type: 'POST',
                data: formData, // Enviando FormData diretamente
                contentType: false,
                processData: false,
                success: function(response) {
                    if(response.status == "success") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>notificacoes";

                        // Desabilitar loader e habilitar botão submit
                        btnSubmit.removeClass("d-none");
                        btnLoader.addClass("d-none");
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        // Desabilitar loader e habilitar botão submit
                        btnSubmit.removeClass("d-none");
                        btnLoader.addClass("d-none");
                    }
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Desabilitar loader e habilitar botão submit
                    btnSubmit.removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        });
    });
</script>