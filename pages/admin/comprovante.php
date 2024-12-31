<?php
// Consultar o comprovante pelo ID
if (isset($_GET['id'])) {
    $receipt_id = $_GET['id']; // ID do comprovante a ser visualizado
    $stmt = $conn->prepare("
        SELECT dr.*, u.firstname, u.lastname, u.username, u.email
        FROM tb_donation_receipt dr
        JOIN tb_users u ON u.id = dr.user_id
        WHERE dr.id = ?
    ");
    $stmt->execute([$receipt_id]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

    // Array para conversão de status
    $status_translation = [
        'pending' => 'Pendente',
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado',
    ];
?>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-albums icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Visualizar Comprovante
                <div class="page-title-subheading">Aqui você pode visualizar os detalhes do comprovante.</div>
            </div>
        </div>
    </div>
</div>

<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Detalhes do Comprovante</h5>

        <div class="row">
            <div class="col-md-6">
                <div class="position-relative form-group">
                    <label>ID do Comprovante</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($receipt['id']); ?>" readonly>
                </div>
                <div class="position-relative form-group">
                    <label>Usuário</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($receipt['firstname'] . ' ' . $receipt['lastname']); ?>" readonly>
                </div>
                <div class="position-relative form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($receipt['email']); ?>" readonly>
                </div>
                <div class="position-relative form-group">
                    <label>Status</label>
                    <input type="text" class="form-control" value="<?= $status_translation[$receipt['status']] ?? 'Desconhecido'; ?>" readonly>
                </div>
                <div class="position-relative form-group">
                    <label>Data de Envio</label>
                    <input type="text" class="form-control" value="<?= date('d/m/Y H:i', strtotime($receipt['creation_date'])); ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="position-relative form-group">
                    <h5 class="card-title">Comprovante</h5>
                    <div class="container-image">
                        <img src="<?= $receipt['proof']; ?>" alt="Comprovante <?= $receipt['id']; ?>" class="img-fluid mb-3" style="max-height: 300px; object-fit: contain;">
                    </div>
                    <a href="<?= $receipt['proof']; ?>" target="_blank" class="btn btn-link btn-sm">Abrir Imagem em Nova Guia</a>
                </div>
            </div>
        </div>

        <hr>

        <div class="d-flex align-items-center justify-content-between">
            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>comprovantes" class="btn btn-light">Voltar</a>

            <div class="actions-buttons">
                <button class="btn btn-success btn-sm" <?= ($receipt['status'] == 'approved') ? 'disabled' : 'onclick="approveReceipt(' . $receipt['id'] . ')"'; ?>>Aprovar</button>
                <button class="btn btn-danger btn-sm ml-2" <?= ($receipt['status'] == 'rejected') ? 'disabled' : 'onclick="rejectReceipt(' . $receipt['id'] . ')"'; ?>>Recusar</button>
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
<?php
    } else {
        // Defina a mensagem de erro na sessão
        $message = array('status' => 'error', 'title' => 'Erro', 'message' => 'ID do comprovante não fornecido.');
        $_SESSION['msg'] = $message;

        // Redirecionar para a página de listagem de comprovantes com uma mensagem de erro
        header("Location: " . INCLUDE_PATH_DASHBOARD . "comprovantes");
        exit();
    }
?>