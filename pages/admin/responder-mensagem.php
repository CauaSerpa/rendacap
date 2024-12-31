<?php
// Recuperar a mensagem pelo ID
$messageId = $_GET['id'] ?? null;
if ($messageId) {
    $stmt = $conn->prepare("
        SELECT cm.id, u.username, u.firstname, u.lastname, cm.email, cm.phone, cm.subject, cm.message, cm.status, cm.admin_response, cm.created_at
        FROM tb_contact_messages cm
        JOIN tb_users u ON u.id = cm.user_id
        WHERE cm.id = :id
    ");
    $stmt->bindParam(':id', $messageId, PDO::PARAM_INT);
    $stmt->execute();
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    // Array para conversão de status
    $status_translation = [
        'pending' => 'Pendente',
        'answered' => 'Respondida',
    ];
    
    if (!$message) {
        echo "<div class='alert alert-danger'>Mensagem não encontrada.</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-danger'>ID da mensagem não especificado.</div>";
    exit;
}
?>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-comment icon-gradient bg-mean-fruit"></i>
            </div>
            <div>
                Responder Mensagem
                <div class="page-title-subheading">Aqui você pode visualizar a dúvida do cliente e enviar uma resposta.</div>
            </div>
        </div>
    </div>
</div>

<?php if ($message['status'] == "answered") : ?>
<div class="alert alert-success" role="alert">
    A pergunta já foi respondida!
</div>
<?php endif; ?>

<div class="main-card mb-3 card">
    <div class="card-body">
        <form id="send-response-form">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title">Informações da Mensagem</h5>
                    <p><strong>Nome:</strong> <?= htmlspecialchars($user['firstname'] . " " . $user['lastname']); ?></p>
                    <p><strong>Email:</strong> 
                        <a href="mailto:<?= htmlspecialchars($message['email']); ?>">
                            <?= htmlspecialchars($message['email']); ?>
                        </a>
                    </p>
                    <p><strong>Telefone:</strong> 
                        <a href="tel:<?= htmlspecialchars($message['phone']); ?>">
                            <?= htmlspecialchars($message['phone']); ?>
                        </a>
                    </p>
                    <p><strong>Status:</strong> <?= $status_translation[$message['status']] ?? 'Desconhecido'; ?></p>
                    <p><strong>Enviada em:</strong> <?= date('d/m/Y H:i', strtotime($message['created_at'])); ?></p>
                    <p><strong>Assunto:</strong> <?= htmlspecialchars($message['subject']); ?></p>
                    <p><strong>Mensagem:</strong></p>
                    <blockquote class="blockquote"><?= nl2br(htmlspecialchars($message['message'])); ?></blockquote>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="response">Resposta</label>
                        <textarea name="response" id="response" rows="12" class="form-control" required><?= $message['admin_response'] ?? ""; ?></textarea>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex align-items-center justify-content-between">
                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>mensagens" class="btn btn-light">Voltar</a>

                <button type="submit" id="btnSubmit" class="btn btn-primary" style="width: <?= (empty($message['admin_response'])) ? "117px" : "132px"; ?>;"><?= (empty($message['admin_response'])) ? "Enviar Resposta" : "Reenviar Resposta"; ?></button>
                <button id="btnLoader" class="btn btn-primary d-none" style="width: <?= (empty($message['admin_response'])) ? "117px" : "132px"; ?>;" disabled>
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

<script type="text/javascript">
    $(document).ready(function() {
        $('#send-response-form').on('submit', function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            btnSubmit = $("#btnSubmit");
            btnLoader = $("#btnLoader");

            // Desabilitar botão submit e habilitar loader
            btnSubmit.addClass("d-none");
            btnLoader.removeClass("d-none");

            let formData = new FormData(this);
            formData.append('action', 'send-response-form'); // Adicionado 'action' diretamente ao FormData
            formData.append('message_id', <?= $message['id']; ?>); // Adiciona 'message_id' diretamente ao FormData

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/send-response-form.php', // URL do arquivo PHP para salvar a senha
                type: 'POST',
                data: formData, // Enviando FormData diretamente
                contentType: false,
                processData: false,
                success: function(response) {
                    if(response.status == "success") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>mensagens";

                        // Reseta o formulario
                        $('#send-response-form')[0].reset();

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