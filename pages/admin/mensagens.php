<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-comment icon-gradient bg-mean-fruit"></i>
            </div>
            <div>
                Mensagens de Dúvidas
                <div class="page-title-subheading">Aqui você pode visualizar todas as dúvidas enviadas pelos clientes.</div>
            </div>
        </div>
    </div>
</div>

<?php
$stmt = $conn->prepare("
    SELECT cm.id, u.username, u.firstname, u.lastname, cm.email, cm.phone, cm.subject, cm.message, cm.status, cm.created_at
    FROM tb_contact_messages cm
    JOIN tb_users u ON u.id = cm.user_id
    ORDER BY cm.created_at DESC
");
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Array para conversão de status
$status_translation = [
    'pending' => 'Pendente',
    'answered' => 'Respondida',
];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Dúvidas dos Clientes</h5>

                <!-- Botões de Filtro -->
                <div class="btn-group mb-4" id="filter" role="group" aria-label="Filtro de Status">
                    <button type="button" class="btn btn-secondary btn-filter active" data-filter="all">Todos (<span class="count-all">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="pending">Pendente (<span class="count-pending">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="answered">Respondida (<span class="count-answered">0</span>)</button>
                </div>

                <table style="width: 100%;" id="CustomerMessages" class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Login</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Assunto</th>
                            <th>Mensagem</th>
                            <th>Status</th>
                            <th>Data de Envio</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($messages)) : ?>
                            <?php foreach ($messages as $message) : ?>
                                <tr data-status="<?= htmlspecialchars($message['status']); ?>">
                                    <td><?= $message['id']; ?></td>
                                    <td><?= htmlspecialchars($message['username']); ?></td>
                                    <td><?= htmlspecialchars($user['firstname'] . " " . $user['lastname']); ?></td>
                                    <td>
                                        <a href="mailto:<?= $message['email']; ?>">
                                            <?= htmlspecialchars($message['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="tel:<?= $message['phone']; ?>">
                                            <?= htmlspecialchars($message['phone']); ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($message['subject']); ?></td>
                                    <td><?= htmlspecialchars($message['message']); ?></td>
                                    <td><?= $status_translation[$message['status']] ?? 'Desconhecido'; ?></td> <!-- Conversão de status -->
                                    <td><?= date('d/m/Y H:i', strtotime($message['created_at'])); ?></td>
                                    <td>
                                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>responder-mensagem?id=<?= $message['id']; ?>" class="btn btn-secondary btn-sm">Visualizar</a>
                                        <button class="btn btn-danger btn-sm" onclick="openDeleteMessage(<?= $message['id']; ?>)">Deletar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10" class="text-center">Nenhuma mensagem encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Funções para aprovar e recusar o comprovante
    function openDeleteMessage(id) {
        if (confirm("Tem certeza que deseja deletar essa mensagem?")) {
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/delete-message.php',
                method: 'POST',
                data: {
                    message_id: id,
                    action: 'delete-message'
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
        }
    }
</script>

<script>
    $(document).ready(function() {
        // Inicializa DataTable
        var table = $("#CustomerMessages").DataTable({
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
            var countPendente = table
                .rows()
                .data()
                .filter(row => row[8] === "Pendente").length;
            var countRespondida = table
                .rows()
                .data()
                .filter(row => row[8] === "Respondida").length;

            // Atualiza os textos dos botões
            $('.count-all').text(total);
            $('.count-pending').text(countPendente);
            $('.count-answered').text(countRespondida);
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