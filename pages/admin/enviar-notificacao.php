<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon py-0">
                <i class="ion-android-notifications icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Enviar Notificação
                <div class="page-title-subheading">Aqui você pode enviar notificações.</div>
            </div>
        </div>
    </div>
</div>

<style>
    #tableTitleUsersAssociated_filter {
        display: none;
    }
</style>

<div class="main-card mb-3 card">
    <div class="card-body">
        <form id="send-notifications-form">
            <div class="form-row gap-3 mb-3">
                <div class="col-md-6">
                    <div class="position-relative form-group mb-3">
                        <label for="title">Título</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="position-relative form-group mb-3">
                        <label for="content">Conteúdo</label>
                        <textarea name="content" id="content" placeholder="Por favor, insira o conteúdo da notificação." class="form-control" rows="5" required></textarea>
                    </div>
                </div>

                <div class="col-md-5 offset-md-1">
                    <div class="position-relative form-group mb-3">
                        <label for="type">Tipo</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="" selected disabled>Selecione o Tipo de Notificação</option>
                            <option value="all">Todos os usuários</option>
                            <option value="plan_users">Usuários de um plano</option>
                            <option value="selecteds">Usuários selecionados</option>
                        </select>
                    </div>

                    <div id="plan-users-section" class="position-relative form-group mb-3 d-none">
                        <label for="plan_users">Plano</label>
                        <select class="form-control" id="plan_users" name="plan_users" required>
                            <option value="" selected disabled>Selecione o Plano dos Usuários que você deseja enviar uma Notificação</option>
                            <option value="4">Diamante</option>
                            <option value="1">Ouro</option>
                            <option value="2">Prata</option>
                            <option value="3">Bronze</option>
                        </select>
                    </div>

                    <div id="associated-users-section" class="main-card card d-none">
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
                                        <tr>
                                            <td colspan="2" class="text-center">Selecione os usuários que você deseja enviar a notificação.</td>
                                        </tr>
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
    
                <button type="submit" id="btnSubmit" class="btn btn-primary" style="width: 132px;">Enviar Notificação</button>
                <button id="btnLoader" class="btn btn-primary d-none" style="width: 132px;" disabled>
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
?>

<script>
$(document).ready(function () {
    // Detecta alterações no campo select
    $('#type').on('change', function () {
        const value = $(this).val();
        const planUsersSection = $('#plan-users-section');
        const associatedUsersSection = $('#associated-users-section');
        const planUsersInput = $('#plan_users'); // Campo select com o atributo "required"
        const associatedUsersInput = $('#associated_users'); // Caso tenha outro campo similar

        if (value === 'selecteds') {
            // Mostrar a seção de usuários associados
            associatedUsersSection.removeClass('d-none').addClass('d-block');
            planUsersSection.removeClass('d-block').addClass('d-none');

            // Tornar o campo associado obrigatório e remover o atributo do outro
            associatedUsersInput.attr('required', true);
            planUsersInput.removeAttr('required');
        } else if (value === 'plan_users') {
            // Mostrar a seção de planos de usuários
            planUsersSection.removeClass('d-none').addClass('d-block');
            associatedUsersSection.removeClass('d-block').addClass('d-none');

            // Tornar o campo de plano obrigatório e remover o atributo do outro
            planUsersInput.attr('required', true);
            associatedUsersInput.removeAttr('required');
        } else {
            // Ocultar ambas as seções
            associatedUsersSection.removeClass('d-block').addClass('d-none');
            planUsersSection.removeClass('d-block').addClass('d-none');

            // Remover o atributo "required" de ambos os campos
            planUsersInput.removeAttr('required');
            associatedUsersInput.removeAttr('required');
        }
    });

    // Popular a lista de usuários no modal
    const users = <?= json_encode($all_users); ?>;

    // Arrays para armazenar os usuários selecionados e removidos
    let removedUsers = [];
    let selectedUsers = users.slice(); // Clonar o array de IDs de usuários associados inicialmente

    // Depois adiciona os não associados
    users.forEach(user => {
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
        $('#send-notifications-form input[name="selected_users"]').remove(); // Remover se já existir
        $('#send-notifications-form').append(`<input type="hidden" name="selected_users" value="${selectedUsers.join(',')}">`);

        // Atualiza o campo oculto de usuários removidos no formulário
        $('#send-notifications-form input[name="removed_users"]').remove(); // Remover se já existir
        if (removedUsers.length > 0) {
            $('#send-notifications-form').append(`<input type="hidden" name="removed_users" value="${removedUsers.join(',')}">`);
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
        $('#send-notifications-form input[name="selected_users"]').remove(); // Remover se já existir
        $('#send-notifications-form').append(`<input type="hidden" name="selected_users" value="${selectedUsers.join(',')}">`);

        // Atualiza o campo oculto de usuários removidos no formulário
        $('#send-notifications-form input[name="removed_users"]').remove(); // Remover se já existir
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
        $('#send-notifications-form').on('submit', function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            btnSubmit = $("#btnSubmit");
            btnLoader = $("#btnLoader");

            // Desabilitar botão submit e habilitar loader
            btnSubmit.addClass("d-none");
            btnLoader.removeClass("d-none");

            let formData = new FormData(this);
            formData.append('action', 'send-notifications-form'); // Adicionado 'action' diretamente ao FormData

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/send-notifications-form.php', // URL do arquivo PHP para salvar a senha
                type: 'POST',
                data: formData, // Enviando FormData diretamente
                contentType: false,
                processData: false,
                success: function(response) {
                    if(response.status == "success") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>notificacoes";

                        // Reseta o formulario
                        $('#send-notifications-form')[0].reset();

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