<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-wallet icon-gradient bg-plum-plate"></i>
            </div>
            <div>
                Financeiro / Saques / Pagar
                <div class="page-title-subheading">Aqui você pode pagar o saque do usuário.</div>
            </div>
        </div>
    </div>
</div>

<?php
    // Array para conversão de status
    $status_translation = [
        'pending' => 'Pendente',
        'cancelled' => 'Cancelado',
        'paid' => 'Pago',
    ];

    // Verifica o ID do saque
    $withdrawal_id = $_GET['id'] ?? null;

    if ($withdrawal_id) {
        // Busca as informações do saque e do usuário
        $stmt = $conn->prepare("
            SELECT w.id, u.firstname, u.lastname, u.email, u.pix_key_type, u.pix_key, w.amount, w.status, w.admin_notes, w.proof_of_transfer 
            FROM tb_withdrawals w 
            JOIN tb_users u ON w.user_id = u.id
            WHERE w.id = ?
        ");
        $stmt->execute([$withdrawal_id]);
        $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Função para verificar se o arquivo é uma imagem
    function isImage($filePath) {
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']);
    }

    $withdrawal['fullname'] = $withdrawal['firstname'] . " " . $withdrawal['lastname'];
    $withdrawal['surname'] = explode(' ', $withdrawal['lastname'])[0];
    $withdrawal['shortname'] = $withdrawal['firstname'] . " " . $withdrawal['surname'];
?>

<div class="col-lg-12">
    <div class="main-card mb-3 card">
        <div class="card-body">
            <h5 class="card-title">Pagamento de Saque - ID #<?= $withdrawal['id']; ?></h5>

            <form id="payment-form" enctype="multipart/form-data">
                <div class="form-row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nome do Usuário</label>
                            <input type="text" class="form-control" value="<?= $withdrawal['shortname']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" value="<?= $withdrawal['email']; ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo de Chave Pix</label>
                            <input type="text" class="form-control" value="<?= $withdrawal['pix_key_type']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Chave Pix</label>
                            <input type="text" class="form-control" value="<?= $withdrawal['pix_key']; ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Valor do Saque</label>
                            <input type="text" class="form-control" value="R$ <?= number_format($withdrawal['amount'], 2, ',', '.'); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" disabled>
                                <option value="pending" <?= $withdrawal['status'] == 'pending' ? 'selected' : ''; ?>><?= $status_translation['pending']; ?></option>
                                <option value="paid" <?= $withdrawal['status'] == 'paid' ? 'selected' : ''; ?>><?= $status_translation['paid']; ?></option>
                                <option value="cancelled" <?= $withdrawal['status'] == 'cancelled' ? 'selected' : ''; ?>><?= $status_translation['cancelled']; ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Comprovante</label>
                    <input type="file" class="form-control-file mb-3" name="receipt" id="receipt-input" <?= (!empty($withdrawal['proof_of_transfer'])) ? "disabled" : ""; ?>>
                    <div id="receipt-preview" class="mb-3">
                        <?php if (!empty($withdrawal['proof_of_transfer'])): ?>
                            <?php if (isImage($withdrawal['proof_of_transfer'])): ?>
                                <div>
                                    <img src="<?= $withdrawal['proof_of_transfer']; ?>" alt="Comprovante" class="img-fluid mb-2" style="max-width: 300px;">
                                    <br>
                                    <a href="<?= $withdrawal['proof_of_transfer']; ?>" class="btn btn-secondary" download>Baixar Comprovante</a>
                                </div>
                            <?php else: ?>
                                <p><a href="<?= $withdrawal['proof_of_transfer']; ?>" target="_blank" class="btn btn-secondary">Baixar Comprovante (PDF)</a></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p>Nenhum comprovante enviado.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nota do Administrador</label>
                    <textarea class="form-control" name="admin_note" rows="3"><?= @$withdrawal['admin_note']; ?></textarea>
                </div>

                <hr>

                <input type="hidden" name="withdrawal_id" value="<?= $withdrawal['id']; ?>">

                <div class="d-flex align-items-center justify-content-between">
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>saques" class="btn btn-light">Voltar</a>

                    <?php if ($withdrawal['status'] == 'pending'): ?>
                        <button type="submit" class="btn btn-success">Definir Como Pago</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Função para verificar se o arquivo é uma imagem
    function isImage(fileType) {
        return fileType.startsWith('image/');
    }

    // Função para exibir pré-visualização do comprovante
    $('#receipt-input').on('change', function(event) {
        var file = event.target.files[0];
        var fileReader = new FileReader();

        if (file) {
            if (isImage(file.type)) {
                // Se o arquivo for uma imagem, exibe a pré-visualização
                fileReader.onload = function(e) {
                    $('#receipt-preview').html(
                        '<div>' +
                        '<img src="' + e.target.result + '" alt="Comprovante" class="img-fluid mb-2" style="max-width: 300px;">' +
                        '<br><a href="' + e.target.result + '" class="btn btn-secondary" download>Baixar Comprovante</a>' +
                        '</div>'
                    );
                };
                fileReader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                // Se for PDF, exibe um link de download
                fileReader.onload = function(e) {
                    $('#receipt-preview').html(
                        '<p><a href="' + e.target.result + '" class="btn btn-secondary" download="comprovante.pdf">Baixar Comprovante (PDF)</a></p>'
                    );
                };
                fileReader.readAsDataURL(file);
            } else {
                $('#receipt-preview').html('<p>Formato de arquivo não suportado.</p>');
            }
        }
    });
</script>

<!-- Alterar Cartão de Crédito -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#payment-form').on('submit', function(e) {
            e.preventDefault(); // Impede o envio padrão do formulário

            var formData = new FormData(this); // Cria o FormData com os dados do formulário
            formData.append('action', 'process-payment'); // Adicionado 'action' diretamente ao FormData

            // Define os botões como variáveis
            let btnSubmit = $("#btnSubmit");
            let btnSubmitLoader = $("#btnSubmitLoader");

            // Desabilitar botão submit e habilitar loader
            btnSubmit.addClass("d-none");
            btnSubmitLoader.removeClass("d-none");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/process-payment.php',
                type: 'POST',
                data: formData,
                contentType: false, // Necessário para o envio de arquivos
                processData: false, // Necessário para o envio de arquivos
                success: function(response) {
                    if(response.status == "success") {
                        location.reload(); // Faz um refresh na página
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }

                    // Habilitar botão submit e desabilitar loader
                    btnSubmit.removeClass("d-none");
                    btnSubmitLoader.addClass("d-none");
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Habilitar botão submit e desabilitar loader
                    btnSubmit.removeClass("d-none");
                    btnSubmitLoader.addClass("d-none");
                }
            });
        });
    });
</script>