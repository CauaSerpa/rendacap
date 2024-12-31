<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-albums icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Comprovantes de Doação
                <div class="page-title-subheading">Aqui você pode visualizar e aprovar ou recusar os comprovantes enviados pelos usuários.</div>
            </div>
        </div>
    </div>
</div>

<?php
// Supondo que você tenha a conexão com o banco de dados em $conn
$stmt = $conn->prepare("
    SELECT dr.*, u.firstname, u.lastname, u.username, u.email
    FROM tb_donation_receipt dr
    JOIN tb_users u ON u.id = dr.user_id
    WHERE dr.id IN (
        SELECT MAX(id)
        FROM tb_donation_receipt
        GROUP BY user_id
    )
    ORDER BY dr.id DESC
");
$stmt->execute();
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Array para conversão de status
$status_translation = [
    'pending' => 'Pendente',
    'approved' => 'Aprovado',
    'rejected' => 'Rejeitado',
];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Comprovantes</h5>

                <!-- Botões de Filtro -->
                <div class="btn-group mb-4" id="filter" role="group" aria-label="Filtro de Status">
                    <button type="button" class="btn btn-secondary btn-filter active" data-filter="all">Todos (<span class="count-all">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="pending">Pendente (<span class="count-pending">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="approved">Aprovado (<span class="count-approved">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="rejected">Rejeitado (<span class="count-rejected">0</span>)</button>
                </div>

                <table style="width: 100%;" id="DonationReceipts" class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID do Comprovante</th>
                            <th>Comprovante</th>
                            <th>Usuário</th>
                            <th>Login</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Data de Envio</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($receipts)) : ?>
                            <?php foreach ($receipts as $receipt) : ?>
                                <tr data-status="<?= $receipt['status']; ?>">
                                    <td><?= $receipt['id']; ?></td>
                                    <td>
                                        <img src="<?= $receipt['proof']; ?>" alt="Comprovante <?= $receipt['id']; ?>" style="width: 40px; height: 40px; object-fit: cover;">
                                        <a href="<?= $receipt['proof']; ?>" target="_blank" class="btn btn-link btn-sm ml-2">Abrir Imagem</a>
                                    </td>
                                    <td><?= $receipt['firstname'] . ' ' . $receipt['lastname']; ?></td>
                                    <td><?= $receipt['username']; ?></td>
                                    <td><?= $receipt['email']; ?></td>
                                    <td><?= $status_translation[$receipt['status']] ?? 'Desconhecido'; ?></td> <!-- Conversão de status -->
                                    <td><?= date('d/m/Y H:i', strtotime($receipt['creation_date'])); ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm" <?= ($receipt['status'] == 'approved') ? 'disabled' : 'onclick="approveReceipt(' . $receipt['id'] . ')"'; ?>>Aprovar</button>
                                        <button class="btn btn-danger btn-sm" <?= ($receipt['status'] == 'rejected') ? 'disabled' : 'onclick="rejectReceipt(' . $receipt['id'] . ')"'; ?>>Recusar</button>
                                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>comprovante?id=<?= $receipt['id']; ?>" class="btn btn-secondary btn-sm">Visualizar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="text-center">Nenhum comprovante encontrado.</td>
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
    function approveReceipt(id) {
        if (confirm("Tem certeza que deseja aprovar este comprovante?")) {
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/donation-receipt.php',
                method: 'POST',
                data: {
                    receipt_id: id,
                    action: 'approve-receipt'
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

    function rejectReceipt(id) {
        if (confirm("Tem certeza que deseja recusar este comprovante?")) {
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/donation-receipt.php',
                method: 'POST',
                data: {
                    receipt_id: id,
                    action: 'reject-receipt'
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
                    toastr.error('Ocorreu um erro ao recusar o comprovante.', 'Erro', {
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
        var table = $("#DonationReceipts").DataTable({
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
                .filter(row => row[6] === "Pendente").length;
            var countAprovado = table
                .rows()
                .data()
                .filter(row => row[6] === "Aprovado").length;
            var countRejeitado = table
                .rows()
                .data()
                .filter(row => row[6] === "Rejeitado").length;

            // Atualiza os textos dos botões
            $('.count-all').text(total);
            $('.count-pending').text(countPendente);
            $('.count-approved').text(countAprovado);
            $('.count-rejected').text(countRejeitado);
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