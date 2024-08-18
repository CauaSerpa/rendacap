<?php
    // Verifica se o token foi definido
    if (isset($token) || isset($_SESSION['inviter'])) {
        // Salva o token caso o usuário saia da página sem querer
        $token = (!isset($token)) ? $_SESSION['inviter'] : $token;
        $_SESSION['inviter'] = $token;

        // Prepara a consulta para buscar o usuário que gerou o token
        $stmt = $conn->prepare("SELECT * FROM tb_users WHERE token = ?");
        $stmt->execute([$token]);
        $inviter = $stmt->fetch(PDO::FETCH_ASSOC);

        // Pega o nome completo do usuário
        $inviter_fullname = $inviter['firstname'] . " " . $inviter['lastname'];
    }
?>

<form id="signupForm">
    <div class="modal-content">
        <div class="modal-body">
            <h5 class="modal-title">
                <h4 class="mt-2">
                    <div>Bem-vindo(a),</div>
                    <span>Leva apenas
                        <span class="text-success">alguns segundos</span> para criar sua conta</span>
                    <?php if (isset($inviter)) { ?>
                    <p class="small mt-2 mb-0">Convidado(a) de <b><?= $inviter_fullname; ?></b></p>
                    <?php } ?>
                </h4>
            </h5>
            <div class="divider row"></div>
            <div class="form-row">
                <div class="col-md-6">
                    <div class="position-relative form-group">
                        <input name="firstname" id="firstname"
                            placeholder="Nome aqui..." type="text" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="position-relative form-group">
                        <input name="lastname" id="lastname"
                            placeholder="Sobrenome aqui..." type="text" class="form-control">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="position-relative form-group">
                        <input name="email" id="email"
                            placeholder="Email aqui..." type="email" class="form-control">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="position-relative form-group">
                        <input name="confirm_email" id="confirm_email"
                            placeholder="Repita o email aqui..." type="email" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="position-relative form-group">
                        <input name="phone" id="phone"
                            placeholder="Celular WhatsApp aqui..." type="text" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="position-relative form-group">
                        <div class="input-group">
                            <div class="input-group-prepend datepicker-trigger">
                                <div class="input-group-text">
                                    <i class="fa fa-calendar-alt"></i>
                                </div>
                            </div>
                            <input name="birth_date" id="birth_date"
                                placeholder="dd/mm/aaaa" type="text" class="form-control" data-toggle="datepicker-icon-year"
                                autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="position-relative form-group">
                        <input name="cpf" id="cpf"
                            placeholder="CPF aqui..." type="text" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="position-relative form-group">
                        <input name="rg" id="rg"
                            placeholder="RG aqui..." type="text" class="form-control">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="position-relative form-group">
                        <input name="username" id="username"
                            placeholder="Usuário aqui..." type="text" class="form-control">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="position-relative form-group">
                        <input name="password" id="password"
                            placeholder="Senha aqui..." type="password" class="form-control">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="position-relative form-group mb-0">
                        <input name="confirm_password" id="confirm_password"
                            placeholder="Repita a senha aqui..." type="password" class="form-control">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mt-1 position-relative form-check">
                        <input name="check" id="showPasswordToggle" type="checkbox" class="form-check-input">
                        <label for="showPasswordToggle" class="form-check-label">Exibir senha</label>
                    </div>
                </div>
            </div>
            <div class="mt-3 position-relative form-check">
                <input name="agree" id="agree" type="checkbox" class="form-check-input">
                <label for="agree" class="form-check-label">Aceite nossos
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>termos-e-condicoes">Termos e Condições</a>.
                </label>
            </div>
            <input type="hidden" name="token_used" value="<?= isset($token) ? $token : null; ?>">
            <input type="hidden" name="action" value="register">
            <div class="divider row"></div>
            <h6 class="mb-0">
                Já tem uma conta?
                <a href="<?= INCLUDE_PATH_AUTH; ?>login" class="text-primary">Entrar</a> |
                <a href="<?= INCLUDE_PATH_AUTH; ?>recuperar-senha" class="text-primary">Recuperar senha</a>
            </h6>
        </div>
        <div class="modal-footer d-block text-center">
            <button type="submit" id="btnRegister" class="btn-wide btn-pill btn-shadow btn-hover-shine btn btn-primary btn-lg" style="width: 200px;">Criar uma conta</button>
            <button id="btnLoader" class="btn-wide btn-pill btn-shadow btn-hover-shine btn btn-primary btn-lg d-none" style="width: 200px;">
                <div class="loader">
                    <div class="ball-pulse">
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                    </div>
                </div>
            </button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(() => {
        // Inicializa o datepicker
        $('[data-toggle="datepicker-icon-year"]').datepicker({
            format: 'dd/mm/yyyy', // Define o formato da data
            startView: 2,
            trigger: ".datepicker-trigger",
            startDate: '<?= date('d/m/Y', strtotime('-150 years')); ?>', // Data mínima
            endDate: '<?= date("d/m/Y"); ?>' // Data máxima
        });

        // Mostrar senha
        $('#showPasswordToggle').on('change', function() {
            const type = $(this).is(':checked') ? 'text' : 'password';
            $('#password, #confirm_password').attr('type', type);
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#signupForm").submit(function(e) {
            e.preventDefault(); // Evita o envio tradicional do formulário

            // Define os botões como variáveis
            registerButton = $("#btnRegister");
            loaderButton = $("#btnLoader");

            // Desabilitar botão submit e habilitar loader
            registerButton.addClass("d-none");
            loaderButton.removeClass("d-none");

            // Coleta os dados do formulário
            var formData = $(this).serialize();

            // Faz a requisição AJAX
            $.ajax({
                type: "POST",
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/authentication/register.php",
                data: formData,
                dataType: "json", // Espera uma resposta JSON do servidor
                success: function(response) {
                    if(response.status == "success") {
                        // Redireciona o usuário após o toastr desaparecer
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>email-enviado";

                        // Desabilitar loader e habilitar botão submit
                        registerButton.removeClass("d-none");
                        loaderButton.addClass("d-none");
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        // Desabilitar loader e habilitar botão submit
                        registerButton.removeClass("d-none");
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
                    registerButton.removeClass("d-none");
                    loaderButton.addClass("d-none");
                }
            });
        });
    });
</script>