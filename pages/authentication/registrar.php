<?php
    // Verifica se o token foi definido
    if (!isset($token) && !isset($_SESSION['inviter'])) {
        renderErrorModal(
            "Erro: Convite necessário",
            "Você precisa de um link de convite para se cadastrar no sistema.",
            INCLUDE_PATH_AUTH . "login"
        );
        exit;
    }

    // Salva o token e gerencia sessão
    $token = $token ?? $_SESSION['inviter'];

    // Verifica e processa o token com ou sem asterisco
    if (strpos($token, '*') !== false) {
        [$token, $referrer_token] = explode('*', $token) + [null, null];
        $_SESSION['inviter'] = $referrer_token;
        $_SESSION['referrer'] = $token;
    } else {
        $_SESSION['inviter'] = $token;
        $_SESSION['referrer'] = null;
    }

    // Busca o usuário que gerou o token
    $stmt = $conn->prepare("SELECT * FROM tb_users WHERE token = ?");
    $stmt->execute([$token]);
    $inviter = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inviter) {
        renderErrorModal(
            "Erro: Link de convite inválido",
            "O link de convite informado não pertence a nenhum usuário da plataforma.",
            INCLUDE_PATH_AUTH . "login"
        );
        session_unset();
        session_destroy();
        exit;
    }

    // Pega o nome completo do usuário que convidou
    $inviter_fullname = $inviter['firstname'] . " " . $inviter['lastname'];

    // Busca o referrer se existir
    if (isset($referrer_token)) {
        $stmt_referrer = $conn->prepare("SELECT * FROM tb_users WHERE token = ?");
        $stmt_referrer->execute([$referrer_token]);
        $referrer = $stmt_referrer->fetch(PDO::FETCH_ASSOC);
        $referrer_fullname = $referrer ? $referrer['firstname'] . " " . $referrer['lastname'] : null;
    }

    // Função para exibir erros em um modal
    function renderErrorModal($title, $message, $redirectUrl) {
?>
    <style>
        .app-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    <div class="modal-dialog w-100 mx-auto">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5 class="text-danger"><?= htmlspecialchars($title) ?></h5>
                <p><?= htmlspecialchars($message) ?></p>
                <a href="<?= htmlspecialchars($redirectUrl) ?>" class="btn-pill btn-hover-shine btn btn-primary btn-lg">Voltar</a>
            </div>
        </div>
    </div>
<?php
    }
?>

<div class="modal-dialog w-100 mx-auto">
    <form id="signupForm">
        <div class="modal-content">
            <div class="modal-body">
                <h5 class="modal-title">
                    <h4 class="mt-2">
                        <div>Bem-vindo(a),</div>
                        <span>Leva apenas
                            <span class="text-success">alguns segundos</span> para criar sua conta</span>
                        <?php if (isset($inviter) && $inviter) { ?>
                            <p class="small mt-2 mb-0">
                                Convidado(a) de <b><?= $inviter_fullname; ?></b>
                                <?php if (isset($referrer) && $referrer) { ?>
                                    e <b><?= $referrer_fullname; ?></b>
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </h4>
                </h5>
                <div class="divider row"></div>
                <h5 class="card-title">Seus dados</h5>
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="position-relative form-group">
                            <input name="firstname" id="firstname"
                                placeholder="Primeiro Nome" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="position-relative form-group">
                            <input name="lastname" id="lastname"
                                placeholder="Sobrenome" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="position-relative form-group">
                            <input name="email" id="email"
                                placeholder="Email" type="email" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="position-relative form-group">
                            <input name="confirm_email" id="confirm_email"
                                placeholder="Repita o email" type="email" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="position-relative form-group">
                            <input name="phone" id="phone"
                                placeholder="Celular WhatsApp" type="text" class="form-control">
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
                                placeholder="CPF" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="position-relative form-group">
                            <input name="rg" id="rg"
                                placeholder="RG" type="text" class="form-control">
                        </div>
                    </div>
                </div>
                <h5 class="card-title">Endereço</h5>
                <div class="form-row">
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <input name="cep" id="cep"
                                placeholder="CEP" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="position-relative form-group">
                            <input name="address" id="address"
                                placeholder="Endereço" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="position-relative form-group">
                            <input name="complement" id="complement"
                                placeholder="Complemento" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <input name="state" id="state"
                                placeholder="Estado" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <input name="address_number" id="address_number"
                                placeholder="Número" type="text" class="form-control">
                            <div class="position-relative form-check">
                                <input name="no_address_number" id="no_address_number" type="checkbox" class="form-check-input">
                                <label for="no_address_number" class="form-check-label small">Sem número</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <input name="neighborhood" id="neighborhood"
                                placeholder="Bairro" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <input name="city" id="city"
                                placeholder="Cidade" type="text" class="form-control">
                        </div>
                    </div>
                </div>
                <h5 class="card-title">Login</h5>
                <div class="form-row">
                    <div class="col-md-12">
                        <div class="position-relative form-group">
                            <input name="username" id="username"
                                placeholder="Usuário" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="position-relative form-group">
                            <input name="password" id="password"
                                placeholder="Senha" type="password" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="position-relative form-group mb-0">
                            <input name="confirm_password" id="confirm_password"
                                placeholder="Repita a senha" type="password" class="form-control">
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
                    <input name="agree" id="agree" type="checkbox" class="form-check-input" value="1">
                    <label for="agree" class="form-check-label">Aceite nossos
                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>images/pdf/termo_de_uso dez_24.pdf" target="_blank">Termos e Condições</a>.
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
                <button type="button" id="btnLoader" class="btn-wide btn-pill btn-shadow btn-hover-shine btn btn-primary btn-lg d-none" style="width: 200px;">
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
</div>

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

<script>
    $(document).ready(function() {
        // Máscara para CEP
        $('#cep').mask('00000-000');

        // Função para buscar o endereço com base no CEP
        $('#cep').on('blur', function() {
            var cep = $(this).val().replace(/\D/g, '');
            if (cep != "") {
                var validacep = /^[0-9]{8}$/;
                if (validacep.test(cep)) {
                    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                        if (!("erro" in dados)) {
                            // Desabilita os campos de endereço até que o CEP seja inserido
                            $('#address, #neighborhood, #city, #state').prop('readonly', true);

                            $('#address').val(dados.logradouro).removeClass('is-invalid').addClass('is-valid');
                            $('#neighborhood').val(dados.bairro).removeClass('is-invalid').addClass('is-valid');
                            $('#city').val(dados.localidade).removeClass('is-invalid').addClass('is-valid');
                            $('#state').val(dados.uf).removeClass('is-invalid').addClass('is-valid');
                        } else {
                            // Caso ocorra algum erro na requisição
                            toastr.error('CEP não encontrado.', 'Erro', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 3000
                            });

                            $('#address, #neighborhood, #city, #state').val('');
                        }
                    });
                } else {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Formato de CEP inválido.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Habilita os campos de endereço até que o CEP seja inserido
                    $('#address, #neighborhood, #city, #state').prop('readonly', false);
                    $('#address, #neighborhood, #city, #state').val('');
                }
            } else {
                // Habilita os campos de endereço até que o CEP seja inserido
                $('#address, #neighborhood, #city, #state').prop('readonly', false);
                $('#address, #neighborhood, #city, #state').val('');
            }
        });

        // Função para desabilitar o campo de número do endereço
        $('#no_address_number').change(function() {
            if ($(this).is(':checked')) {
                $('#address_number').val('').prop('disabled', true);
            } else {
                $('#address_number').prop('disabled', false);
            }
        });
    });
</script>

<!-- <script>
    $(document).ready(function() {
        function checkField(action, field, value) {
            $.ajax({
                type: "POST",
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/authentication/check-register-fields.php",
                data: { action: action, value: value },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'error') {
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }

                    console.log("TESTE " + field);
                    field.addClass('is-invalid').removeClass('is-valid');
                },
                error: function() {
                    toastr.error('Erro ao verificar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                }
            });
        }

        $('#email').on('input', function() {
            var field = $('#email');
            var email = $(this).val();
            checkField('check-email', field, email);
        });

        $('#cpf').on('input', function() {
            var cpf = $(this).val();
            checkField('check_cpf', cpf);
        });

        $('#rg').on('input', function() {
            var rg = $(this).val();
            checkField('check_rg', rg);
        });

        $('#username').on('input', function() {
            var username = $(this).val();
            checkField('check_username', username);
        });
    });
</script> -->

<script type="text/javascript">
    $(document).ready(function() {
        $("#signupForm").submit(function(e) {
            e.preventDefault(); // Evita o envio tradicional do formulário

            // Define os botões como variáveis
            registerButton = $("#btnRegister");
            loaderButton = $("#btnLoader");

            // Desabilitar botão submit e habilitar loader
            registerButton.addClass("d-none");
            registerButton.prop("disabled", true);

            loaderButton.removeClass("d-none");
            loaderButton.prop("disabled", true);

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
                        // window.location.href = "<?= INCLUDE_PATH_AUTH; ?>email-enviado"; // Desabilitado em fase de testes
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>planos";

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
                        registerButton.prop("disabled", false);

                        loaderButton.addClass("d-none");
                        loaderButton.prop("disabled", false);
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
                    registerButton.prop("disabled", false);

                    loaderButton.addClass("d-none");
                    loaderButton.prop("disabled", false);
                }
            });
        });
    });
</script>