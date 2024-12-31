<?php
    $_SESSION['user_id_signature'] = $_SESSION['user_id'];

    if (isset($_SESSION['user_id_signature'])) {
        // Prepara a consulta para buscar o usuário que gerou o token
        $stmt = $conn->prepare("SELECT * FROM tb_users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id_signature']]);

        if ($stmt->rowCount()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Pega o nome completo do usuário
            $user['surname'] = explode(' ', $user['lastname'])[0];
            $user['shortname'] = $user['firstname'] . " " . $user['surname'];
        } else {
            // Define a mensagem de erro para usuário não encontrado
            $message = array(
                'status' => 'error', 
                'title' => 'Usuário não encontrado', 
                'message' => 'Não conseguimos localizar sua conta. Faça login ou crie uma nova conta para continuar.'
            );
            $_SESSION['msg'] = $message;

            // Redireciona usuario
            header("Location: " . INCLUDE_PATH_AUTH);
            exit();
        }
    } else {
        // Define a mensagem com base no status de login do usuário
        $text_message = ($_SESSION['user_id'])
                        ? 'Você já possui uma assinatura. Para alterá-la, acesse Minha Conta > Assinatura.' // O usuário está logado, mas não tem 'user_id_signature'
                        : 'Por favor, faça login para acessar esta página.'; // O usuário não está logado

        // Define a mensagem de erro correspondente
        $message = array(
            'status' => 'error', 
            'title' => 'Acesso negado', 
            'message' => $text_message
        );
        $_SESSION['msg'] = $message;

        // Redireciona usuario
        header("Location: " . INCLUDE_PATH_AUTH);
        exit();
    }
?>

<style>
    .line-or {
        position: relative;
        width: 90%;
        height: 1px;
        background: #c4c4c4;
    }
    .line-or .or {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        color: #c4c4c4;
        background: white;
    }
</style>

<div class="modal-dialog w-100 mx-auto">
    <form id="invoiceInfoForm">
        <div class="modal-content">
            <div class="modal-body">
                <h5 class="modal-title">
                    <h4 class="mt-2">
                        <div>Escanear QR Code PIX</div>
                        <span>Escaneie esse código QR para efetuar o pagamento.</span>
                        <!-- <?php if (isset($inviter)) { ?>
                        <p class="small mt-2 mb-0">Convidado(a) de <b><?= $inviter_fullname; ?></b></p>
                        <?php } ?> -->
                    </h4>
                </h5>
                <div class="divider row"></div>

                <div class="d-flex flex-column align-items-center justify-content-center mb-3">
                    <p class="font-weight-semibold fsize-3 mb-0" id="pix-price">R$</p>

                    <img src="#" id="pix-encoded-image" alt="QR Code PIX" style="width: 250px;">

                    <div class="line-or mx-0 my-3">
                        <span class="or small px-2">ou copie o código abaixo</span>
                    </div>

                    <div class="input-group mt-3" style="width: 375px;">
                        <input type="text" class="form-control" id="pix-code" value="Example text to copy!" readonly>
                        <div class="input-group-append">
                            <button type="button" data-clipboard-target="#pix-code" class="btn btn-icon btn-primary clipboard-trigger">
                                <i class="fa fa-copy mr-1"></i>
                                Copiar
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        function printPaymentData(data) {
            var price = "R$ " + data.price.replace(".", ",");
            $("#pix-price").text(price);

            var pixEncodedImage = "data:image/png;base64," + data.pixEncodedImage;
            $("#pix-encoded-image").attr("src", pixEncodedImage);

            $("#pix-code").val(data.pixCode);
        }

        // Recuperando o plano selecionado do sessionStorage
        var paymentId = sessionStorage.getItem('paymentId');

        // Verifica se há um plano selecionado
        if (paymentId) {
            // Faz a requisição AJAX
            $.ajax({
                type: "POST",
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/billing/functions/list-qr-code-pix.php",
                data: { paymentId: paymentId },
                dataType: "json", // Espera uma resposta JSON do servidor
                success: function(response) {
                    if(response.status == "success") {
                        console.log("sucesso");
                        printPaymentData(response);
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        // Desabilitar loader e habilitar botão submit
                        invoiceInfoButton.removeClass("d-none");
                        loaderButton.addClass("d-none");
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
                    invoiceInfoButton.removeClass("d-none");
                    loaderButton.addClass("d-none");
                }
            });
        } else {
            // Redireciona o usuário para a página de seleção de planos
            window.location.href = "<?= INCLUDE_PATH_AUTH; ?>planos";
        }
    });

    function getPaymentStaus() {
        // Recuperando o plano selecionado do sessionStorage
        var paymentId = sessionStorage.getItem('paymentIdCode');

        // Enviar uma solicitação AJAX para verificar o pagamento
        $.ajax({
            type: 'POST',
            url: '<?php echo INCLUDE_PATH_DASHBOARD ?>back-end/billing/functions/payment-status.php',
            data: { paymentId: paymentId },
            dataType: 'JSON',
            success: function(response) {
                if (response.status == 'success') {
                    if (response.paymentStatus == 'pago') {








                        // Definindo os planos em um array
                        var plans = [
                            { id: 1, slug: "gold", name: "Ouro", price: 100, cycle: "mês" },
                            { id: 2, slug: "silver", name: "Prata", price: 50, cycle: "mês" },
                            { id: 3, slug: "bronze", name: "Bronze", price: 0, cycle: "única" },
                            { id: 4, slug: "diamond", name: "Diamante", price: 200, cycle: "mês" }
                        ];

                        // Recuperando o plano selecionado do sessionStorage
                        var savedPlan = sessionStorage.getItem('chosenPlan');

                        // Encontrando o plano no array de planos
                        var plan = plans.find(p => p.slug === savedPlan);

                        var newAjaxData = {
                            action: 'search-draw-group',
                            plan_id: plan.id,
                        };

                        // Faz a requisição AJAX
                        $.ajax({
                            type: "POST",
                            url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/search-draw-group.php",
                            data: newAjaxData,
                            dataType: "json", // Espera uma resposta JSON do servidor
                            success: function(response) {
                                console.log(response.message);
                            },
                            error: function() {
                                console.log('Ocorreu um erro ao enviar os dados. Tente novamente.');
                            }
                        });









                        // Redireciona o usuário para a página de seleção de planos
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>";
                    }
                }
            }
        });
    }

    // Guardar o ID do intervalo para poder pará-lo posteriormente
    setInterval(getPaymentStaus, 10000);
</script>