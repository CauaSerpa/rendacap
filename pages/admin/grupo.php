<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Grupo
                <div class="page-title-subheading">Aqui você pode editar grupos existentes.</div>
            </div>
        </div>
    </div>
</div>

<?php
// Consultar o título que será editado
$group_id = $_GET['group_id']; // ID do título a ser editado
$title_stmt = $conn->prepare("SELECT * FROM tb_draw_titles WHERE id = ?");
$title_stmt->execute([$group_id]);
$title_data = $title_stmt->fetch(PDO::FETCH_ASSOC);

// Consultar os produtos associados ao título
$stmt = $conn->prepare("
    SELECT dt.id AS title_id, g.*, dt.plan_id
    FROM tb_draw_titles dt
    LEFT JOIN tb_groups g ON g.title_id = dt.id
    WHERE g.id = ?
");
$stmt->execute([$group_id]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

// Consultar os produtos associados ao título
$stmt = $conn->prepare("
    SELECT dtp.*, dp.name AS product_name 
    FROM tb_draw_title_products dtp
    JOIN tb_draw_products dp ON dp.id = dtp.product_id
    WHERE draw_title_id = ?
");
$stmt->execute([$group['title_id']]);
$titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorizar os produtos por título
$telesena_products = [];
$viva_sorte_products = [];
$hiper_cap_brasil_products = [];

foreach ($titles as $title) {
    if ($title['product_name'] === 'Telesena') {
        $telesena_products[] = $title;
    } elseif ($title['product_name'] === 'Viva Sorte') {
        $viva_sorte_products[] = $title;
    } elseif ($title['product_name'] === 'Hiper Cap Brasil') {
        $hiper_cap_brasil_products[] = $title;
    }
}

if (!empty($group['id'])) {
    // Consultar os produtos associados ao título
    $stmt = $conn->prepare("
        SELECT gu.*, u.firstname, u.lastname
        FROM tb_group_users gu
        JOIN tb_users u ON u.id = gu.user_id
        WHERE group_id = ?
    ");
    $stmt->execute([$group['id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Carregar todos os usuários
$stmt_all_users = $conn->prepare("SELECT id, username FROM tb_users WHERE status = 1");
$stmt_all_users->execute();
$all_users = $stmt_all_users->fetchAll(PDO::FETCH_ASSOC);

// Buscar todos os usuários associados a este título
$stmt_users = $conn->prepare("
    SELECT u.id, u.username
    FROM tb_users u
    JOIN tb_group_users gu ON u.id = gu.user_id
    JOIN tb_groups g ON gu.group_id = g.id
    WHERE g.title_id = ?
");
$stmt_users->execute([$group['title_id']]);
$associated_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Separar os usuários em associados e não associados
$associated_user_ids = array_column($associated_users, 'user_id');
$non_associated_users = array_filter($all_users, function($user) use ($associated_user_ids) {
    return !in_array($user['id'], $associated_user_ids);
});
?>

<style>
    #tableTitleUsersAssociated_filter {
        display: none;
    }
</style>

<div class="main-card mb-3 card">
    <div class="card-body">
        <form id="group-edit-form">
            <div class="form-row mb-3">
                <div class="col-md-4">
                    <div class="position-relative form-group">
                        <label for="identifier">Identificador</label>
                        <input type="text" class="form-control" id="identifier" name="identifier" value="<?= htmlspecialchars($group['identifier']); ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="position-relative form-group">
                        <label for="group_code">Código do Grupo</label>
                        <input type="text" class="form-control" id="group_code" name="group_code" value="<?= htmlspecialchars($group['group_code']); ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="position-relative form-group">
                        <label for="plan">Plano</label>
                        <select class="form-control" id="plan" name="plan" disabled>
                            <option value="">Selecione o Plano</option>
                            <option value="4" <?= $group['plan_id'] == 4 ? 'selected' : ''; ?>>Diamante</option>
                            <option value="1" <?= $group['plan_id'] == 1 ? 'selected' : ''; ?>>Ouro</option>
                            <option value="2" <?= $group['plan_id'] == 2 ? 'selected' : ''; ?>>Prata</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Listagem de Produtos -->
            <div class="form-row mb-3">
                <div class="col-md-2">
                    <div class="position-relative form-group text-center">
                        <h5 class="card-title">Telesena</h5>
                        <ul class="list-group">
                            <?php foreach ($telesena_products as $product) : ?>
                                <li class="list-group-item"><?= htmlspecialchars($product['title_id']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="position-relative form-group text-center">
                        <h5 class="card-title">Viva Sorte</h5>
                        <ul class="list-group">
                            <?php foreach ($viva_sorte_products as $product) : ?>
                                <li class="list-group-item"><?= htmlspecialchars($product['title_id']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="position-relative form-group text-center">
                        <h5 class="card-title">Hiper Cap Brasil</h5>
                        <ul class="list-group">
                            <?php foreach ($hiper_cap_brasil_products as $product) : ?>
                                <li class="list-group-item"><?= htmlspecialchars($product['title_id']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6">

                    <div class="main-card card">
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
                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>grupos" class="btn btn-light">Voltar</a>

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
                                WHERE u.status = 1 AND s.plan_id != 3 AND r.role_id = 3
                            ");
$all_users = $stmt_all_users->fetchAll(PDO::FETCH_ASSOC);

// Associar os IDs dos usuários já associados ao título para fácil verificação
$associated_user_ids = array_column($associated_users, 'id');
?>

<script>
$(document).ready(function () {
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

    // Verificar se o número máximo de usuários foi atingido
    $('.user-checkbox').on('change', function () {
        const selectedCount = $('.user-checkbox:checked').length;
        if (selectedCount > 4) {
            this.checked = false; // Desmarcar se exceder 4
            alert('Você pode selecionar no máximo 4 usuários.');
        }
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
        $('#group-edit-form input[name="selected_users"]').remove(); // Remover se já existir
        $('#group-edit-form').append(`<input type="hidden" name="selected_users" value="${selectedUsers.join(',')}">`);

        // Atualiza o campo oculto de usuários removidos no formulário
        $('#group-edit-form input[name="removed_users"]').remove(); // Remover se já existir
        if (removedUsers.length > 0) {
            $('#group-edit-form').append(`<input type="hidden" name="removed_users" value="${removedUsers.join(',')}">`);
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
        $('#group-edit-form input[name="selected_users"]').remove(); // Remover se já existir
        $('#group-edit-form').append(`<input type="hidden" name="selected_users" value="${selectedUsers.join(',')}">`);

        // Atualiza o campo oculto de usuários removidos no formulário
        $('#group-edit-form input[name="removed_users"]').remove(); // Remover se já existir
        if (removedUsers.length > 0) {
            $('#group-edit-form').append(`<input type="hidden" name="removed_users" value="${removedUsers.join(',')}">`);
        }
    });
});
</script>

<script>
    $(document).ready(() => {
        const table = $("#tableTitleUsersAssociated").DataTable({
            responsive: true,
            scrollCollapse: true,
            ordering: false,
            // pageLength: 15, // Define o número padrão de registros por página
            paging: false, // Desabilita paginacao por bug
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

<script>
    $('#group-edit-form').on('submit', function(e) {
        e.preventDefault();

        // Define os botões como variáveis
        btnSubmit = $("#btnSubmit");
        btnLoader = $("#btnLoader");

        // Desabilitar botão submit e habilitar loader
        btnSubmit.addClass("d-none");
        btnLoader.removeClass("d-none");

        let formData = new FormData(this);
        formData.append('group_id', <?= $group['id']; ?>); // Adiciona 'title_id' diretamente ao FormData
        formData.append('action', 'group-edit'); // Adiciona 'action' diretamente ao FormData

        $.ajax({
            url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/group-edit-form.php', // URL do arquivo PHP para editar o título
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == "success") {
                    // Redireciona o usuário após o toastr desaparecer
                    window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>grupos";
                } else {
                    toastr.error(response.message, 'Erro', {
                        closeButton: true,
                        progressBar: true
                    });
                }
            },
            error: function() {
                toastr.error('Erro ao editar o título. Tente novamente.', 'Erro', {
                    closeButton: true,
                    progressBar: true
                });
            },
            complete: function() {
                btnSubmit.removeClass("d-none");
                btnLoader.addClass("d-none");
            }
        });
    });
</script>