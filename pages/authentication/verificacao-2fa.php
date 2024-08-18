<?php
    if (!isset($_SESSION['two_factor_code'])) {
        header('Location: ' . INCLUDE_PATH_AUTH . 'login');
    }
?>
<form id="twoFactorForm">
    <div class="modal-content">
        <div class="modal-body">
            <div class="h5 modal-title text-center">
                <h4 class="mt-2">
                    <div>Autenticação 2FA</div>
                    <span>Insira o código enviado pelo Email.</span>
                </h4>
            </div>
            <div class="form-row">
                <div class="col-md-12">
                    <div class="position-relative form-group mb-0">
                        <input name="two_factor_code" id="two_factor_code"
                            placeholder="Código 2FA aqui..." type="text" class="form-control" autocomplete="off">
                        <button type="button" id="resendCode" class="btn btn-link mt-2 px-0">Reenviar Código...</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer clearfix">
            <div class="float-left">
                <a href="<?= INCLUDE_PATH_AUTH; ?>recuperar-senha" class="btn-pill btn-hover-shine btn btn-link btn-lg">Recuperar senha</a>
            </div>
            <div class="float-right">
                <button type="submit" id="btnLogin" class="btn-pill btn-hover-shine btn btn-primary btn-lg" style="width: 72px;">Entrar</button>
                <button id="btnLoader" class="btn-pill btn-hover-shine btn btn-primary btn-lg d-none" style="width: 72px;">
                    <div class="loader">
                        <div class="ball-pulse">
                            <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                            <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                            <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(() => {
        // formata o campo '#two_factor_code'
        $('#two_factor_code').mask('000000');
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#twoFactorForm").submit(function(e) {
            e.preventDefault(); // Evita o envio tradicional do formulário

            // Define os botões como variáveis
            loginButton = $("#btnLogin");
            loaderButton = $("#btnLoader");

            // Desabilitar botão submit e habilitar loader
            loginButton.addClass("d-none");
            loaderButton.removeClass("d-none");

            // Coleta os dados do formulário
            var formData = $(this).serialize();

            // Faz a requisição AJAX
            $.ajax({
                type: "POST",
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/authentication/verify-2fa.php",
                data: formData,
                dataType: "json", // Espera uma resposta JSON do servidor
                success: function(response) {
                    if(response.status == "success") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>";

                        // Desabilitar loader e habilitar botão submit
                        loginButton.removeClass("d-none");
                        loaderButton.addClass("d-none");
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        // Desabilitar loader e habilitar botão submit
                        loginButton.removeClass("d-none");
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
                    loginButton.removeClass("d-none");
                    loaderButton.addClass("d-none");
                }
            });
        });

        // Reenviar código 2FA
        $('#resendCode').on('click', function() {
            $.ajax({
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/authentication/resend-2fa.php",
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    // Exibe uma mensagem de sucesso usando toastr
                    toastr.success(response.message, 'Sucesso', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000,
                        onHidden: function() {
                            // Desabilitar loader e habilitar botão submit
                            loginButton.removeClass("d-none");
                            loaderButton.addClass("d-none");
                        }
                    });
                }
            });
        });
    });
</script>