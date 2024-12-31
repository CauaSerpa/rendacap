<div class="modal-dialog modal-lg w-100 mx-auto">
    <form id="recupPassForm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="h5 modal-title">
                    Esqueceu sua senha?
                    <h6 class="mt-1 mb-0 opacity-8">
                        <span>Utilize o formulário abaixo para recuperá-la.</span>
                    </h6>
                </div>
            </div>
            <div class="modal-body">
                <div>
                    <div class="form-row">
                        <div class="col-md-12">
                            <div class="position-relative form-group my-2">
                                <input name="email" id="email"
                                    placeholder="Email" type="email" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <div class="d-flex align-items-center">
                    <a href="<?= INCLUDE_PATH_AUTH; ?>login" class="btn-pill btn-hover-shine btn btn-link btn-lg px-0">Faça login na conta existente</a>
                </div>
                <div class="d-flex align-items-center">
                    <button type="submit" id="btnLogin" class="btn-pill btn-hover-shine btn btn-primary btn-lg" style="width: 135px;">Recuperar senha</button>
                    <button id="btnLoader" class="btn-pill btn-hover-shine btn btn-primary btn-lg d-none" style="width: 135px;">
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
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#recupPassForm").submit(function(e) {
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
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/authentication/recup-password.php",
                data: formData,
                dataType: "json", // Espera uma resposta JSON do servidor
                success: function(response) {
                    if(response.status == "success") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>email-de-recuperacao-enviado";

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
    });
</script>