<?php
    if (isset($_SESSION['user_id'])) {
        header("Location: " . INCLUDE_PATH_DASHBOARD);
        exit();
    } elseif (isset($_COOKIE['remember_me']) && !isset($_SESSION['user_id'])) {
        // Decodifica o valor do cookie de Base64 para JSON
        $base64Data = $_COOKIE['remember_me'];
        $jsonData = base64_decode($base64Data);

        // Decodifica o JSON para um array
        $data = json_decode($jsonData, true);

        if (isset($data['remember_me'])) {
            $user_id = $data['remember_me'];

            // Consulta para verificar se o cookie existe no banco de dados
            $stmt = $conn->prepare("SELECT * FROM tb_users WHERE id = ?");
            $stmt->execute([$user_id]);

            if ($stmt->rowCount()) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user['status'] == 1) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['username'] = $user['username'];

                    header("Location: " . INCLUDE_PATH_DASHBOARD);
                    exit();
                }
            }
        }
    }
?>

<div class="modal-dialog w-100 mx-auto">
    <form id="signinForm">
        <div class="modal-content">
            <div class="modal-body">
                <div class="h5 modal-title text-center">
                    <h4 class="mt-2">
                        <div>Bem vindo(a) de volta,</div>
                        <span>Não tem uma conta?
                            <a href="<?= INCLUDE_PATH_AUTH; ?>registrar" class="text-primary">Inscreva-se agora</a>
                        </span>
                    </h4>
                </div>
                <div class="form-row">
                    <div class="col-md-12">
                        <div class="position-relative form-group">
                            <input name="login" id="login"
                                placeholder="Email ou Username" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="position-relative form-group mb-0">
                            <input name="password" id="password"
                                placeholder="Senha" type="password" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mt-1 position-relative form-check">
                            <input name="check" id="showPasswordToggle" type="checkbox" class="form-check-input">
                            <label for="showPasswordToggle" class="form-check-label">Exibir senha</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="position-relative form-check">
                        <input name="remember_me" id="remember_me" type="checkbox" class="form-check-input">
                        <label for="remember_me" class="form-check-label">Mantenha-me conectado</label>
                    </div>
                </div>
                <input type="hidden" name="http_referer" value="<?= isset($_SESSION['http_referer']) ? $_SESSION['http_referer'] : null; ?>">
                <input type="hidden" name="action" value="login">
                <div class="d-flex align-items-center">
                    <div>
                        <a href="<?= INCLUDE_PATH_AUTH; ?>recuperar-senha" class="btn-pill btn-hover-shine btn btn-link btn-lg">Recuperar senha</a>
                    </div>
                    <div>
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
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(() => {
        // Mostrar senha
        $('#showPasswordToggle').on('change', function() {
            const type = $(this).is(':checked') ? 'text' : 'password';
            $('#password, #confirm_password').attr('type', type);
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#signinForm").submit(function(e) {
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
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/authentication/login.php",
                data: formData,
                dataType: "json", // Espera uma resposta JSON do servidor
                success: function(response) {
                    if(response.status == "success") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD . ((isset($_SESSION['http_referer']) ? $_SESSION['http_referer'] : null)); ?>";

                        // Desabilitar loader e habilitar botão submit
                        loginButton.removeClass("d-none");
                        loaderButton.addClass("d-none");
                    } else if (response.status == "2fa-required") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>verificacao-2fa";

                        // Desabilitar loader e habilitar botão submit
                        loginButton.removeClass("d-none");
                        loaderButton.addClass("d-none");
                    } else if (response.status == "create-signature") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>planos";

                        // Desabilitar loader e habilitar botão submit
                        loginButton.removeClass("d-none");
                        loaderButton.addClass("d-none");
                    } else if (response.status == "email-verification") {
                        // Redireciona o usuário após o toastr desaparecer
                        // window.location.href = "<?= INCLUDE_PATH_AUTH; ?>email-enviado"; // Desabilitado em fase de testes
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>planos";

                        // Desabilitar loader e habilitar botão submit
                        loginButton.removeClass("d-none");
                        loaderButton.addClass("d-none");
                    } else if (response.status == "finalize-registration") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>finalizar-cadastro";

                        // Desabilitar loader e habilitar botão submit
                        loginButton.removeClass("d-none");
                        loaderButton.addClass("d-none");
                    } else if (response.status == "send-new-proof") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>enviar-novo-comprovante";

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