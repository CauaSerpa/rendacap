<?php
    session_start();
    ob_start();
    include('./config.php');

    //Url Amigavel
    $url = isset($_GET['url']) ? $_GET['url'] : 'home';

    //Edita o escrito da url para ser colocado no title
    if ($url == "")
    {
        $title = "Painel";
    } else {
        $title = ucwords(str_replace("-", " ", $url));
    }

    $permissions = "";
?>
<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Language" content="pt-BR">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Deshboard RendaCAP Brasil</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
        <meta name="description" content="Deshboard RendaCAP Brasil">

        <!-- Google Icons -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />

        <!-- Favicon Icon -->
        <link rel="shortcut icon" href="<?= INCLUDE_PATH_DASHBOARD; ?>images/favicon.png" type="image/x-icon">

        <!-- Disable tap highlight on IE -->
        <meta name="msapplication-tap-highlight" content="no">
        <link rel="stylesheet" href="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/@fortawesome/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/ionicons-npm/css/ionicons.css">
        <link rel="stylesheet" href="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/linearicons-master/dist/web-font/style.css">
        <link rel="stylesheet" href="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/pixeden-stroke-7-icon-master/pe-icon-7-stroke/dist/pe-icon-7-stroke.css">
        <link href="<?= INCLUDE_PATH_DASHBOARD; ?>styles/css/base.css" rel="stylesheet">

        <!-- plugin dependencies -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/jquery/dist/jquery.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/moment/moment.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/metismenu/dist/metisMenu.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/bootstrap4-toggle/js/bootstrap4-toggle.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/jquery-circle-progress/dist/circle-progress.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/perfect-scrollbar/dist/perfect-scrollbar.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/toastr/build/toastr.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/jquery.fancytree/dist/jquery.fancytree-all-deps.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/apexcharts/dist/apexcharts.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/@chenfengyuan/datepicker/dist/datepicker.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/daterangepicker/daterangepicker.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/countup.js/dist/countUp.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/chart.js/dist/Chart.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/datatables.net/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/bootstrap-table/dist/bootstrap-table.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
        <!-- custome.js -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/charts/apex-charts.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/circle-progress.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/demo.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/scrollbar.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/toastr.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/treeview.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/form-components/toggle-switch.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/app.js"></script>
        <!-- library includes -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/library-includes/jquery.mask.min.js"></script>
        <!-- added codes -->
        <!-- vendors -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/jquery-validation/dist/jquery.validate.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/clipboard/dist/clipboard.min.js"></script>
        <!-- js -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/form-components/form-validation.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/form-components/datepicker.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/form-components/clipboard.js"></script>

    </head>
    <body>
        <style>
            /* Animação de pulsação */
            @keyframes pulsate {
                0% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.1);
                }
                100% {
                    transform: scale(1);
                }
            }

            /* Classe personalizada para o botão pulsante */
            .btn-pulse {
                animation: pulsate 1.5s ease-in-out infinite;
            }
        </style>

        <?php
            // Iniciando variável $tab
            $tab = "";

            // Verifica se a URL contém pelo menos uma barra
            if (strpos($url, '/') !== false) {
                // Conta quantas barras existem na URL
                $slashCount = substr_count($url, '/');

                if ($slashCount == 1) {
                    // Há apenas uma barra na URL
                    list($tab, $url) = explode('/', $url, 2);
                } elseif ($slashCount > 1) {
                    // Há mais de uma barra na URL
                    list($tab, $url, $token) = explode('/', $url, 3);
                }
            }

            // Se for a página de verificação de e-mail
            if ($tab == "r" && $url == "verificar-email" && !empty($token)) {
                // Inclua o arquivo de verificação de e-mail e passe o token
                include('back-end/user/get/verify-email.php');
            } elseif ($url == "404") {
                include('templates/404.php');
            } elseif ($tab == "auth" || $url == "auth") {
                include('templates/auth.php');
            } else {
                include('templates/dashboard.php');
            }
        ?>

        <?php
            // Se tiver um "$_SESSION['msg']" exibe a mensagem
            if (isset($_SESSION['msg'])) {
        ?>
            <script type="text/javascript">
                $(document).ready(function() {
                    // Exibe a mensagem usando toastr
                    toastr.<?= $_SESSION['msg']['status']; ?>('<?= $_SESSION['msg']['message']; ?>', '<?= $_SESSION['msg']['title']; ?>', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                });
            </script>
        <?php
                // Remove a mensagem da sessão após exibi-la
                unset($_SESSION['msg']);
            }
        ?>

    </body>
</html>

<!-- Modal de Saque -->
<div class="modal fade" id="withdrawModal" tabindex="-1" role="dialog" aria-labelledby="withdrawModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!-- Cabeçalho do Modal -->
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawModalLabel">Saque de Seus Cashbacks</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Corpo do Modal -->
            <div class="modal-body">
                <!-- Passo 1: Informações sobre vouchers -->
                <div id="voucher-info">
                    <p>Solicitação de terça-feira das 08:00h até quinta-feira as 18:00h.</p>
                    <small>A partir de R$ 25,00</small>
                    <hr>
                    <p>Comprar Voucher (Plano de Assinaturas)</p>
                    <div class="d-flex flex-column justify-content-center">
                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>vouchers" class="btn btn-shadow btn-success btn-lg" id="buy-voucher">SIM, QUERO COMPRAR VOUCHERS</a>
                        <button type="button" class="btn btn-outline-link small" id="no-voucher">NÃO, Continuar com Saque</button>
                    </div>
                </div>

                <!-- Passo 2: Formulário de Saque -->
                <div id="withdraw-form" class="d-none">
                    <form id="withdrawRequestForm">
                        <label for="withdraw-password">Senha de Saque</label>
                        <div class="form-row">
                            <div class="col-md-9">
                                <div class="form-group mb-0">
                                    <input type="password" class="form-control" id="withdraw-password" name="withdraw-password" 
                                        placeholder="Digite sua senha de saque" maxlength="6" pattern="\d{6}" required>
                                </div>
                                <small>Valor total disponível: R$ <span id="withdrawAmount"><?= number_format($networkCashbackData['totalCashbackAvailable'], 2, ',', '.'); ?></span></small>
                            </div>
                            <input type="hidden" class="form-control" id="withdraw-amount" name="withdraw-amount" value="<?= $networkCashbackData['totalCashbackAvailable'] ?? 0; ?>">
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary btn-lg btn-block mt-0" id="btnWithdrawRequest">Sacar</button>

                                <!-- Loader ao enviar o formulário -->
                                <button id="btnWithdrawRequestLoader" class="btn btn-primary btn-lg btn-block mt-0 d-none" disabled>
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
                        <em id="password-error" class="error invalid-feedback">Por favor, insira uma senha numérica válida de 6 dígitos</em>
                    </form>
                </div>

                <!-- Passo 3: Mensagem de saque em andamento -->
                <div id="withdraw-processing" class="d-none text-center">
                    <span class="material-icons" style="font-size: 48px;">schedule</span>
                    <p>Seu saque está sendo processado. Em até 24 horas o valor estará disponível em sua conta.</p>
                </div>
            </div>

            <!-- Rodapé do Modal -->
            <div class="modal-footer justify-content-start">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let withdrawSuccess = false; // Variável para armazenar o status do saque
        const isWithdrawAvailable = <?= ($isButtonEnabled) ? 'true' : 'false'; ?>;
        const totalCashback = <?= $networkCashbackData['totalCashbackAvailable'] ?? 0; ?>;

        // Permite apenas números no campo de senha
        $('#withdraw-password').on('input', function() {
            this.value = this.value.replace(/\D/g, ''); // Remove qualquer caractere que não seja numérico
        });

        // Esconde o erro da senha
        $('#password-error').hide();

        // Ao clicar em "Não, continuar para saque"
        $('#no-voucher').click(function() {
            $('#voucher-info').addClass('d-none'); // Esconde a mensagem sobre vouchers
            $('#withdraw-form').removeClass('d-none'); // Exibe o formulário de saque
        });

        // Manipula o envio do formulário de saque
        $('#withdrawRequestForm').submit(function(e) {
            e.preventDefault(); // Evita o envio do formulário padrão

            // Define os botões como variáveis
            let btnWithdrawRequest = $("#btnWithdrawRequest");
            let btnWithdrawRequestLoader = $("#btnWithdrawRequestLoader");

            // Desabilitar botão submit e habilitar loader
            btnWithdrawRequest.addClass("d-none");
            btnWithdrawRequestLoader.removeClass("d-none");

            // Captura a senha digitada
            const password = $('#withdraw-password').val();
            const valorSaque = totalCashback;

            // Verifica se a senha foi preenchida corretamente
            if (password.length !== 6) {
                $('#password-error').show();

                // Desabilitar loader e habilitar botão submit
                btnWithdrawRequest.removeClass("d-none");
                btnWithdrawRequestLoader.addClass("d-none");

                return;
            }

            let formData = new FormData(this);
            formData.append('action', 'process-withdraw'); // Adiciona a ação 'process-withdraw' ao FormData
            formData.append('withdraw-amount', valorSaque);

            // Faz a requisição AJAX para processar o saque
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/process-withdraw.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status === 'success') {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Sucesso', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        withdrawSuccess = true; // Marca que o saque foi realizado com sucesso

                        // Exibe o estado de saque em andamento
                        $('#withdraw-form').addClass('d-none');
                        $('#withdraw-processing').removeClass('d-none');

                        // Desabilitar loader e habilitar botão submit
                        btnWithdrawRequest.removeClass("d-none");
                        btnWithdrawRequestLoader.addClass("d-none");
                    } else if (response.status === 'invalid-password') {
                        $('#password-error').text('Senha incorreta. Por favor, tente novamente.').show();

                        // Desabilitar loader e habilitar botão submit
                        btnWithdrawRequest.removeClass("d-none");
                        btnWithdrawRequestLoader.addClass("d-none");
                    } else {
                        // Exibe mensagens de sucesso
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        // Desabilitar loader e habilitar botão submit
                        btnWithdrawRequest.removeClass("d-none");
                        btnWithdrawRequestLoader.addClass("d-none");
                    }
                },
                error: function() {
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Desabilitar loader e habilitar botão submit
                    btnWithdrawRequest.removeClass("d-none");
                    btnWithdrawRequestLoader.addClass("d-none");
                }
            });
        });

        // Quando o modal for fechado, redefina para o estado inicial ao abrir de novo
        $('#withdrawModal').on('hidden.bs.modal', function () {
            if (!withdrawSuccess) {
                // Caso o saque não tenha sido realizado com sucesso, retorna à primeira etapa
                $('#voucher-info').removeClass('d-none');
                $('#withdraw-form').addClass('d-none');
                $('#withdraw-processing').addClass('d-none');
                $('#password-error').hide();
                $('#withdraw-password').val('');
            }
        });

        // Quando o modal for reaberto
        $('#withdrawModal').on('shown.bs.modal', function () {
            if (withdrawSuccess) {
                // Se o saque já foi realizado, mostra a mensagem de saque em andamento
                $('#withdraw-form').addClass('d-none');
                $('#withdraw-processing').removeClass('d-none');
            }
        });
    });
</script>

<!-- Informações do Convidado -->
<div class="modal fade" id="inviterInfoModal" tabindex="-1" aria-labelledby="inviterInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inviterInfoModalLabel">Informações do Usuário</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="d-flex align-items-center justify-content-between"><strong>Nome Completo:</strong> <span id="inviter-fullname"></span></p>
                <p class="d-flex align-items-center justify-content-between">
                    <strong>Username:</strong> 
                    <span>
                        <i class="fas fa-copy clipboard-trigger mr-1" data-clipboard-target="#inviter-username" style="cursor: pointer;"></i>
                        <span id="inviter-username"></span>
                    </span>
                </p>
                <p class="d-flex align-items-center justify-content-between"><strong>Email:</strong> <span id="inviter-email"></span></p>
                <p class="d-flex align-items-center justify-content-between"><strong>Celular WhatsApp:</strong> <span id="inviter-phone"></span></p>
                <p class="d-flex align-items-center justify-content-between inviter-cpf"><strong>CPF:</strong> <span id="inviter-cpf"></span></p>
                <p class="d-flex align-items-center justify-content-between"><strong>Plano:</strong> <span id="inviter-plan"></span></p>
                <p class="d-flex align-items-center justify-content-between"><strong>Status do Plano:</strong> <span id="inviter-status"></span></p>
                <p class="d-flex align-items-center justify-content-between inviter-link">
                    <strong>Link de Convite:</strong> 
                    <span>
                        <i class="fas fa-copy clipboard-trigger mr-1" data-clipboard-target="#inviter-link" style="cursor: pointer;"></i>
                        <span id="inviter-link"></span>
                    </span>
                </p>
                <div id="voucher-content" class="d-none">
                    <hr>
                    <h4 class="mb-2">Ativação com Voucher</h4>
                    <p>
                        <strong>Atenção:</strong> Você possui <strong id="subuserVouchersCount">0</strong> voucher(s) disponível(is) da plataforma. 
                        Este usuário está atualmente com o plano inativo. Você pode ativá-lo utilizando um voucher adquirido na plataforma. 
                        Ao prosseguir, o voucher será debitado imediatamente e não poderá ser reembolsado após a ativação. 
                        Por favor, certifique-se de que deseja continuar antes de confirmar.
                    </p>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="creditVoucherAgreement" required>
                            <label class="form-check-label" for="creditVoucherAgreement">
                                Confirmo que li e concordo com os termos acima.
                            </label>
                        </div>
                        <div class="d-flex">
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>vouchers" class="btn btn-link mr-2 d-none" id="purchaseVoucherButton">Adquirir Voucher</a>
                            <button class="btn btn-success disabled" id="btnActiveUser" data-toggle="tooltip" title="Você não possui vouchers para ativar este usuário." style="width: 108px;" disabled>Ativar Usuário</button>
                            <button id="btnLoaderActiveUser" class="btn btn-success disabled d-none" style="width: 108px;" disabled>
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
        </div>
    </div>
</div>

<style>
    .modal-x-lg {
        max-width: 1000px !important;
    }
</style>



















<!-- Modal para alterar plano -->
<div class="modal fade" id="changePlanModal" tabindex="-1" role="dialog" aria-labelledby="changePlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-x-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePlanModalLabel">Alterar Plano de Assinatura</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="step step-plan">
                    <div class="row">
                        <div class="col-md-4 d-grid">
                            <div class="card-shadow-secondary border card card-body border-secondary">
                                <h5 class="card-title">Plano Diamante</h5>
                                <div class="d-flex align-items-baseline">
                                    <small class="opacity-5 pr-1">R$</small>
                                    <h3 class="count-up font-weight-semibold" data-value="200.00">200,00</h3>
                                    <small class="opacity-5 pl-1">Mensal</small>
                                </div>

                                <div class="divider mt-3"></div>
                                <p>12 títulos de Capitalização DIGITAL</p>
                                <ul>
                                    <li>4 Tele Sena Semanal</li>
                                    <li>4 HiperCap Brasil</li>
                                    <li>4 Viva Sorte</li>
                                </ul>
                                <p class="text-center">12 títulos de Capitalização DIGITAL /por semana</p>
                                <p class="text-center">Totalizando 48 títulos em 30 dias</p>
                                <button type="button" class="select-plan btn-pill btn-hover-shine btn btn-primary btn-lg" data-step="payment" data-plan="diamond" data-id="4">Comprar Plano</button>
                                <button class="loader-button btn-pill btn-hover-shine btn btn-primary btn-lg d-none">
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
                        <div class="col-md-4 d-grid">
                            <div class="card-shadow-secondary border card card-body border-secondary">
                                <h5 class="card-title">Plano Ouro</h5>
                                <div class="d-flex align-items-baseline">
                                    <small class="opacity-5 pr-1">R$</small>
                                    <h3 class="count-up font-weight-semibold" data-value="100.00">100,00</h3>
                                    <small class="opacity-5 pl-1">Mensal</small>
                                </div>

                                <div class="divider mt-3"></div>
                                <p>6 títulos de Capitalização DIGITAL</p>
                                <ul>
                                    <li>2 Tele Sena Semanal</li>
                                    <li>2 HiperCap Brasil</li>
                                    <li>2 Viva Sorte</li>
                                </ul>
                                <p class="text-center"> 6 títulos de Capitalização DIGITAL /por semana</p>
                                <p class="text-center">Totalizando 24 títulos em 30 dias</p>
                                <button type="button" class="select-plan btn-pill btn-hover-shine btn btn-primary btn-lg" data-step="payment" data-plan="gold" data-id="1">Comprar Plano</button>
                                <button class="loader-button btn-pill btn-hover-shine btn btn-primary btn-lg d-none">
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
                        <div class="col-md-4 d-grid">
                            <div class="dark-card card-shadow-secondary card card-body" style="background-color: rgb(51, 51, 51); border-color: rgb(51, 51, 51); color: white !important;">
                                <h5 class="card-title text-white d-flex align-items-center justify-content-between">
                                    Plano Prata
                                    <div class="badge badge-pill badge-primary">Popular</div>
                                </h5>
                                <div class="d-flex align-items-baseline">
                                    <small class="opacity-5 pr-1">R$</small>
                                    <h3 class="count-up font-weight-semibold" data-value="50.00">50,00</h3>
                                    <small class="opacity-5 pl-1">Mensal</small>
                                </div>

                                <div class="divider mt-3"></div>
                                <p>3 títulos de Capitalização DIGITAL</p>
                                <ul>
                                    <li>1 Tele Sena Semanal</li>
                                    <li>1 HiperCap Brasil</li>
                                    <li>1 Viva Sorte</li>
                                </ul>
                                <p class="text-center">3 títulos de Capitalização DIGITAL /por semana</p>
                                <p class="text-center">Totalizando 12 títulos em 30 dias</p>
                                <button type="button" class="select-plan btn-pill btn-hover-shine btn btn-primary btn-lg" data-step="payment" data-plan="silver" data-id="2">Comprar Plano</button>
                                <button class="loader-button btn-pill btn-hover-shine btn btn-primary btn-lg d-none">
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
                    </div>
                </div>

                <!-- Etapa 2: Seleção do método de pagamento -->
                <div class="step step-payment-method d-none">
                    <ul class="list-group">
                        <button class="list-group-item-action list-group-item payment-method" data-step="voucher">
                            Crédito em Voucher
                        </button>
                        <button class="list-group-item-action list-group-item payment-method" data-step="pix">
                            PIX
                        </button>
                        <button class="list-group-item-action list-group-item payment-method" data-step="credit-card">
                            Cartão de Crédito
                        </button>
                    </ul>
                </div>

                <!-- Etapa 3: Voucher -->
                <div class="step step-voucher text-center d-none">
                    <i class="pe-7s-star text-secondary mb-3" style="font-size: 4rem;" aria-hidden="true"></i>
                    <h4 class="mb-2">Voucher</h4>
                    <p>
                        <strong>Atenção:</strong> Você possui <strong id="vouchersCount">0</strong> voucher da plataforma. 
                        Este voucher será debitado imediatamente e não será reembolsado após a confirmação da compra. 
                        Certifique-se de que está de acordo antes de prosseguir.
                    </p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="creditAgreement" required>
                        <label class="form-check-label" for="creditAgreement">
                            Eu concordo com os termos acima.
                        </label>
                    </div>
                </div>

                <!-- Etapa 3: Pix -->
                <div class="step step-pix d-none">
                    <div id="pixFormContent">
                        <p>Por favor, insira seu Nome e CPF para gerar o código PIX.</p>
                        <form id="changePlanPixForm" class="form-group">
                            <input type="text" name="name" id="pixName" class="form-control mb-3" placeholder="Nome Completo" required>
                            <input type="text" name="cpf" id="pixCpf" class="form-control mb-3" placeholder="CPF" required>
                        </form>
                    </div>
                    <div id="pixCodeContainer" class="text-center" style="display: none;">
                        <p>Use o QR Code ou o código PIX abaixo para realizar o pagamento.</p>
                        <img src="#" alt="QR Code PIX" class="img-fluid mb-3" style="max-width: 200px;" id="pixQrCode">
                        <div class="position-relative form-group px-5">
                            <label for="pixCode" class="">Código PIX:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="pixCode" readonly>
                                <div class="input-group-append">
                                    <button type="button" data-clipboard-target="#pixCode" class="btn-icon btn btn-primary clipboard-trigger">
                                        <i class="pe-7s-copy-file btn-icon-wrapper"></i>
                                        Copiar Link
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Etapa 3: Cartão de Crédito -->
                <div class="step step-credit-card d-none">
                    <h5>Pagamento via Cartão de Crédito</h5>
                    <form id="changePlanCreditCardForm">
                        <div class="form-row">
                            <div class="col-md-12">
                                <input name="card_number" id="card_number" autocomplete="cc-number" placeholder="Número do Cartão" type="text" class="form-control mb-3">
                            </div>
                            <div class="col-md-6">
                                <input name="card_expiration" id="card_expiration" autocomplete="cc-exp" placeholder="Vencimento (MM/AA)" type="text" class="form-control mb-3">
                            </div>
                            <div class="col-md-6">
                                <input name="card_cvv" id="card_cvv" autocomplete="cc-csc" placeholder="CVV" type="text" class="form-control mb-3">
                            </div>
                            <div class="col-md-12">
                                <input name="card_holder" id="card_holder" autocomplete="cc-name" placeholder="Titular do Cartão" type="text" class="form-control mb-3">
                            </div>
                            <div class="col-md-12">
                                <input name="cpf" id="cpf" autocomplete="cpf" placeholder="CPF" type="text" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Etapa 4: Sucesso -->
                <div class="step step-success d-none text-center">
                    <i class="pe-7s-check text-success mb-3" style="font-size: 4rem;" aria-hidden="true"></i>
                    <h4 class="mb-2">Pagamento Realizado com Sucesso!</h4>
                    <p>O pagamento foi processado e seu plano foi alterado com sucesso.</p>
                </div>

            </div>
            <div class="modal-footer d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-light" data-dismiss="modal">Fechar</button>
                <div>
                    <button type="button" class="btn btn-link btn-sm btn-back" style="display: none;">Voltar</button>
                    <button type="button" class="btn btn-primary" id="confirmChangePlanPayment" style="display: none;">Confirmar Pagamento</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        let currentStep = 1;

        // Ao abrir o modal, exibir a etapa de seleção dos planos
        $('#changePlanModal').on('show.bs.modal', function () {
            $('.step').addClass("d-none");
            $('.step-plan').removeClass("d-none");
            $('.btn-back').hide();
            $('#confirmChangePlanPayment').hide();
        });

        // Navegação entre as etapas
        $(".select-plan").on("click", function () {
            currentStep = 2;

            $(".select-plan").removeClass("selected");
            $(this).addClass("selected");

            $(".step").addClass("d-none");
            $(".step-payment-method").removeClass("d-none");
            $('.btn-back').show();
            $('#confirmChangePlanPayment').hide();

            // Recupera o ID do plano selecionado
            let plan = $(this).data("plan");

            // Requisição AJAX para obter o número de vouchers
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/plan/functions/get-voucher-count.php', // Ajuste conforme sua rota
                method: 'POST',
                data: { plan: plan }, // Envia o ID do plano
                success: function(response) {
                    if(response.status === "success") {
                        // Exibe o número de vouchers no elemento #vouchersCount
                        $('#vouchersCount').text(response.vouchersCount);
                    }
                },
            });
        });

        $(".payment-method").on("click", function () {
            let method = $(this).data("step");

            $(".payment-method").removeClass("active");
            $(this).addClass("active");

            $(".step").addClass("d-none");
            $(`.step-${method}`).removeClass("d-none");

            // Exibe o botão "Voltar" nas etapas intermediárias
            if (currentStep === 2) {
                $('.btn-back').show();
                $('#confirmChangePlanPayment').hide();
            }

            // Se for a etapa de Pix ou Cartão de Crédito, mostra o botão "Confirmar Pagamento"
            if (method === "credit-card" || method === "voucher" || method === "pix") {
                $('#confirmChangePlanPayment').show();
            }
        });

        // Ação do botão "Voltar"
        $('.btn-back').on("click", function () {
            currentStep--;
            if (currentStep === 1) {
                $(".step").addClass("d-none");
                $(".step-plan").removeClass("d-none");
                $('.btn-back').hide();
                $('#confirmChangePlanPayment').hide();
            } else if (currentStep === 2) {
                $(".step").addClass("d-none");
                $(".step-payment-method").removeClass("d-none");
                $('#confirmChangePlanPayment').hide();
            }
        });

        // Confirmar pagamento com envio via AJAX
        $('#confirmChangePlanPayment').on("click", function () {
            let paymentMethod = $('.payment-method.active').data("step"); // Método de pagamento selecionado
            let planId = $('.select-plan.selected').data("id"); // ID do plano selecionado
            let ajaxData = {};

            if (paymentMethod === 'pix') {
                // Coleta os dados do formulário
                var params = $('#changePlanPixForm').serialize();

                ajaxData = { action: 'change-plan', method: 'pix', plan: planId, params: btoa(params) };
            } else if (paymentMethod === 'voucher') {
                ajaxData = { action: 'change-plan', method: 'voucher', plan: planId };
            } else if (paymentMethod === 'credit-card') {
                // Coleta os dados do formulário
                var params = $('#changePlanCreditCardForm').serialize();

                ajaxData = { action: 'change-plan', method: 'card', plan: planId, params: btoa(params) };
            }

            // Requisição AJAX (pré-pronta para adicionar lógica)
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/plan/change-plan.php', // Ajuste conforme sua rota
                method: 'POST',
                data: ajaxData,
                success: function(response) {
                    if(response.status == "success") {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Sucesso', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        if (paymentMethod === 'pix') {
                            // Adiciona o prefixo adequado ao Base64 recebido do backend
                            const base64Image = `data:image/png;base64,${response.pix_image}`;

                            // Atualiza o src da imagem do QR Code
                            $('#pixQrCode').attr('src', base64Image);

                            $('#pixCode').val(response.pix_code); // Código do PIX

                            $('#pixCodeContainer').show(); // Exibe o container do QR Code e código
                            $('#pixFormContent').hide(); // Exibe o container do QR Code e código
                            $('#confirmChangePlanPayment').hide(); // Ocultar o botão de confirmação

                            // Verifica o status do pagamento a cada 10 segundos (se necessário)
                            var intervalId = setInterval(function() {
                                $.ajax({
                                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/plan/functions/payment-status.php', // Nova rota para verificar status
                                    method: 'POST',
                                    data: { subs_id: btoa(response.code), payment_id: btoa(response.paymentId) }, // Passa o código do PIX para o backend verificar o status
                                    dataType: 'JSON',
                                    success: function(checkResponse) {
                                        if (checkResponse.status === 'success' && checkResponse.paymentStatus === 'paid') {
                                            // Se o pagamento for confirmado
                                            clearInterval(intervalId); // Para o intervalo
                                            toastr.success(checkResponse.message, 'Sucesso', {
                                                closeButton: true,
                                                progressBar: true,
                                                timeOut: 3000
                                            });





















                                            let planId = $('.select-plan.selected').data("id"); 

                                            var newAjaxData = {
                                                action: 'search-draw-group',
                                                plan_id: planId,
                                            };

                                            // Faz a requisição AJAX
                                            $.ajax({
                                                type: "POST",
                                                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/search-draw-group.php",
                                                data: newAjaxData,
                                                dataType: "json", // Espera uma resposta JSON do servidor
                                                success: function(response) {
                                                    console.log(response.message);






                                                    // Esconde todas as seções e exibe o conteúdo de sucesso
                                                    $('.step, .step-plan, .payment-section').addClass("d-none");
                                                    $('.step-success').removeClass("d-none");
                                                    $('.btn-back').hide();
                                                    $('#confirmChangePlanPayment').hide();

                                                    // Atraso de 3 segundos antes de recarregar a página
                                                    setTimeout(function() {
                                                        // Exibe mensagens de alerta
                                                        toastr.info('A página será recarregada automaticamente', 'Alerta', {
                                                            closeButton: true,
                                                            progressBar: true,
                                                            timeOut: 3000, // Tempo antes de recarregar
                                                            onHidden: function () {
                                                                // Recarrega a página após a exibição do toastr
                                                                location.reload();
                                                            }
                                                        });
                                                    }, 1500); // 1500 milissegundos = 1.5 segundos







                                                },
                                                error: function() {
                                                    console.log('Ocorreu um erro ao enviar os dados. Tente novamente.');
                                                }
                                            });





















                                            
                                        } else if (checkResponse.status === 'error') {
                                            // Se houver erro no status
                                            clearInterval(intervalId); // Para o intervalo
                                            toastr.error('Erro ao verificar pagamento, tente novamente mais tarde.', 'Erro', {
                                                closeButton: true,
                                                progressBar: true,
                                                timeOut: 3000
                                            });
                                        }
                                    },
                                    error: function() {
                                        // Caso ocorra algum erro na verificação do status
                                        toastr.error('Erro ao verificar o status do pagamento.', 'Erro', {
                                            closeButton: true,
                                            progressBar: true,
                                            timeOut: 3000
                                        });
                                    }
                                });
                            }, 10000); // Intervalo de 10 segundos
                        } else if (paymentMethod === 'voucher' || paymentMethod === 'credit-card') {







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






                                    // Esconde todas as seções e exibe o conteúdo de sucesso
                                    $('.step, .step-plan, .payment-section').addClass("d-none");
                                    $('.step-success').removeClass("d-none");
                                    $('.btn-back').hide();
                                    $('#confirmChangePlanPayment').hide();

                                    // Atraso de 3 segundos antes de recarregar a página
                                    setTimeout(function() {
                                        // Exibe mensagens de alerta
                                        toastr.info('A página será recarregada automaticamente', 'Alerta', {
                                            closeButton: true,
                                            progressBar: true,
                                            timeOut: 3000, // Tempo antes de recarregar
                                            onHidden: function () {
                                                // Recarrega a página após a exibição do toastr
                                                location.reload();
                                            }
                                        });
                                    }, 1500); // 1500 milissegundos = 1.5 segundos







                                },
                                error: function() {
                                    console.log('Ocorreu um erro ao enviar os dados. Tente novamente.');
                                }
                            });






                        }
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
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                }
            });
        });
    });
</script>

<style>
    .input-number,
    .input-cvv {
        position: relative;
    }
    .input-number input#card_number,
    .input-cvv input#cvv {
        padding-right: 2.5rem;
    }
    .input-number::before {
        position: absolute;
        right: .75rem;
        top: 50%;
        transform: translateY(-50%);
        font-family: "Material Symbols Outlined";
        content: "\e870";
        color: #000000;
        font-size: 1.25rem;
        pointer-events: none;
    }
    .input-cvv .btn-icon {
        position: absolute;
        right: 0;
        top: 50%;
        padding: 0 .75rem;
        transform: translateY(-50%);
        display: flex;
        align-items: center;
    }
    .input-cvv .btn-icon .icon {
        color: #000000;
        font-size: 1.25rem;
    }
</style>

<!-- Modal para alterar cartão -->
<div class="modal fade" id="updateCardModal" tabindex="-1" role="dialog" aria-labelledby="updateCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateCardModalLabel">Alterar Cartão de Crédito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="update-card-form">
                <div class="modal-body">

                    <div class="form-row">
                        <div class="col-md-12">
                            <div class="position-relative form-group input-number">
                                <input name="card_number" id="card_number" autocomplete="cc-number"
                                    placeholder="Número do Cartão" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="position-relative form-group">
                                <input name="card_expiration" id="card_expiration" autocomplete="cc-exp"
                                    placeholder="Vencimento do Cartão" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="position-relative form-group input-cvv">
                                <input name="card_cvv" id="card_cvv" autocomplete="cc-csc"
                                    placeholder="CVV" type="text" class="form-control">
                                <button type="button" class="btn-icon btn-icon-only btn-pill btn btn-outline-link" data-toggle="modal" data-target="#cvvModal">
                                    <span class="icon material-symbols-outlined">help</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="position-relative form-group">
                                <input name="card_holder" id="card_holder" autocomplete="cc-name"
                                    placeholder="Titular do Cartão" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="position-relative form-group">
                                <input name="cpf" id="cpf" autocomplete="cpf"
                                    placeholder="CPF" type="text" class="form-control" value="<?= $user['cpf']; ?>">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-shadow btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-shadow btn-primary" id="btnUpdCard">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #cancelSubscriptionModal .modal-dialog,
    #removeCardModal .modal-dialog {
        box-shadow: none;
    }
</style>

<!-- Modal para cancelar assinatura -->
<div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" role="dialog" aria-labelledby="cancelSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelSubscriptionModalLabel">Remover Cartão de Crédito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/remove-payment-method" method="post">
                <div class="modal-body">
                    <p>Tem certeza de que deseja cancelar está assinatura?</p>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-shadow btn-light" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-shadow btn-danger" id="btnCancelSubscription">Cancelar Assinatura</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para remover cartão -->
<div class="modal fade" id="removeCardModal" tabindex="-1" role="dialog" aria-labelledby="removeCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeCardModalLabel">Remover Cartão de Crédito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/remove-payment-method" method="post">
                <div class="modal-body">
                    <p>Tem certeza de que deseja remover este cartão de crédito? Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-shadow btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-shadow btn-danger">Remover Cartão</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Exemplo código CVV -->
<div class="modal fade" id="cvvModal" tabindex="-1" aria-labelledby="cvvModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cvvModalLabel">O que é CVV?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>O CVV (Código de Verificação de Cartão) é um código de 3 ou 4 dígitos, normalmente localizado no verso do seu cartão de crédito ou débito. Ele é utilizado para aumentar a segurança das transações online.</p>
                <div class="row">
                    <div class="col-md-6">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/payment/visa_cvv.png" class="img-fluid" alt="Cartão mostrando CVV">
                    </div>
                    <div class="col-md-6">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/payment/amex_cvv.png" class="img-fluid" alt="Cartão mostrando CVV">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Comprar Vouchers -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Selecione a Forma de Pagamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Opções de pagamento -->
                <ul class="list-group">
                    <button class="list-group-item-action list-group-item voucher-payment-method" data-type="credit">
                        Crédito da Plataforma
                    </button>
                    <button class="list-group-item-action list-group-item voucher-payment-method" data-type="pix">
                        PIX
                    </button>
                    <button class="list-group-item-action list-group-item voucher-payment-method" data-type="card">
                        Cartão de Crédito
                    </button>
                </ul>

                <!-- Conteúdo dinâmico -->
                <div id="paymentContent">
                    <!-- Crédito da Plataforma -->
                    <div id="creditContent" class="payment-section text-center p-3" style="display: none;">
                        <i class="pe-7s-wallet text-secondary mb-3" style="font-size: 4rem;" aria-hidden="true"></i>
                        <h4 class="mb-2">Crédito da Plataforma</h4>
                        <p>
                            <strong>Atenção:</strong> Você está utilizando o crédito da plataforma. 
                            Este valor será debitado imediatamente e não será reembolsado após a confirmação da compra. 
                            Certifique-se de que está de acordo antes de prosseguir.
                        </p>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="creditAgreement" required>
                            <label class="form-check-label" for="creditAgreement">
                                Eu concordo com os termos acima.
                            </label>
                        </div>
                    </div>

                    <!-- PIX -->
                    <div id="pixContent" class="payment-section" style="display: none;">
                        <div id="pixFormContent">
                            <p>Por favor, insira seu Nome e CPF para gerar o código PIX.</p>
                            <form id="pixForm" class="form-group">
                                <input type="text" name="name" id="pixName" class="form-control mb-3" placeholder="Nome Completo" required>
                                <input type="text" name="cpf" id="pixCpf" class="form-control mb-3" placeholder="CPF" required>
                            </form>
                        </div>
                        <div id="pixCodeContainer" class="text-center" style="display: none;">
                            <p>Use o QR Code ou o código PIX abaixo para realizar o pagamento.</p>
                            <img src="#" alt="QR Code PIX" class="img-fluid mb-3" style="max-width: 200px;" id="pixQrCode">
                            <div class="position-relative form-group px-5">
                                <label for="pixCode" class="">Código PIX:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="pixCode" readonly>
                                    <div class="input-group-append">
                                        <button type="button" data-clipboard-target="#pixCode" class="btn-icon btn btn-primary clipboard-trigger">
                                            <i class="pe-7s-copy-file btn-icon-wrapper"></i>
                                            Copiar Link
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cartão de Crédito -->
                    <div id="cardContent" class="payment-section" style="display: none;">
                        <form id="creditCardForm">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <input name="card_number" id="card_number" autocomplete="cc-number" placeholder="Número do Cartão" type="text" class="form-control mb-3">
                                </div>
                                <div class="col-md-6">
                                    <input name="card_expiration" id="card_expiration" autocomplete="cc-exp" placeholder="Vencimento (MM/AA)" type="text" class="form-control mb-3">
                                </div>
                                <div class="col-md-6">
                                    <input name="card_cvv" id="card_cvv" autocomplete="cc-csc" placeholder="CVV" type="text" class="form-control mb-3">
                                </div>
                                <div class="col-md-12">
                                    <input name="card_holder" id="card_holder" autocomplete="cc-name" placeholder="Titular do Cartão" type="text" class="form-control mb-3">
                                </div>
                                <div class="col-md-12">
                                    <input name="cpf" id="cpf" autocomplete="cpf" placeholder="CPF" type="text" class="form-control">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Conteúdo de Sucesso -->
                    <div id="successContent" class="payment-section text-center p-3" style="display: none;">
                        <i class="pe-7s-check text-success mb-3" style="font-size: 4rem;" aria-hidden="true"></i>
                        <h4 class="mb-2">Pagamento Realizado com Sucesso!</h4>
                        <p>Obrigado por sua compra. O pagamento foi processado com sucesso.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-light" data-dismiss="modal">Fechar</button>
                <div>
                    <button type="button" class="mr-2 btn btn-link btn-sm btn-back" style="display: none;">Voltar</button>
                    <button type="button" class="btn btn-primary" id="confirmPayment" style="display: none;">Confirmar Pagamento</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Evento para abrir o modal e salvar o voucher selecionado
        $('.select-voucher').on('click', function () {
            const selectedPlan = $(this).data('plan'); // Obtém o plano do botão clicado
            $('#paymentModal').data('selectedPlan', selectedPlan); // Salva no modal

            // Restaurar o estado inicial do modal
            $('.voucher-payment-method').removeClass('selected').show(); // Mostrar os botões de forma de pagamento
            $('.payment-section').hide(); // Esconder as seções de pagamento
            $('#confirmPayment').hide(); // Ocultar o botão de confirmação
            $('#pixFormContent').show(); // Exibe o container do QR Code e código
            $('.btn-back').hide(); // Ocultar o botão "Voltar"

            $('#paymentModal').modal('show'); // Abre o modal
        });

        // Alternar conteúdo dinâmico do modal e ocultar botões
        $('.voucher-payment-method').on('click', function () {
            const selectedMethod = $(this).data('type');
            
            // Oculta os botões de seleção e exibe o conteúdo correspondente
            $('.voucher-payment-method').hide();
            $('.payment-section').hide();
            $('#confirmPayment').show();
            $('.btn-back').show();

            $('.voucher-payment-method').removeClass('selected'); // Remover selected de qualquer outro botao
            $(this).addClass('selected'); // Adicionar selected no botao especifico

            if (selectedMethod === 'credit') {
                $('#creditContent').show();
            } else if (selectedMethod === 'pix') {
                $('#pixContent').show();
            } else if (selectedMethod === 'card') {
                $('#cardContent').show();
            }
        });

        // Botão "Voltar" para retornar à seleção de métodos de pagamento
        $('.btn-back').on('click', function () {
            $('.payment-section').hide(); // Oculta as seções de pagamento
            $('.voucher-payment-method').removeClass('selected').show(); // Exibe os botões de método de pagamento
            $('#confirmPayment').hide(); // Oculta o botão de confirmação
            $(this).hide(); // Oculta o botão "Voltar"
        });

        // Botão de confirmação de pagamento
        $('#confirmPayment').on('click', function () {
            const selectedMethod = $('.voucher-payment-method.selected').data('type');
            const selectedPlan = $('#paymentModal').data('selectedPlan');
            let ajaxData = {};

            if (selectedMethod === 'pix') {
                // Coleta os dados do formulário
                var params = $('#pixForm').serialize();

                ajaxData = { action: 'buy-voucher', method: 'pix', voucher: selectedPlan, params: btoa(params) };
            } else if (selectedMethod === 'credit') {
                ajaxData = { action: 'buy-voucher', method: 'credit', voucher: selectedPlan, availableCredit: <?= $networkCashbackData['totalCashbackAvailable'] ?? 0; ?> };
            } else if (selectedMethod === 'card') {
                // Coleta os dados do formulário
                var params = $('#creditCardForm').serialize();

                ajaxData = { action: 'buy-voucher', method: 'card', voucher: selectedPlan, params: btoa(params) };
            }

            // Requisição AJAX (pré-pronta para adicionar lógica)
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/billing/process-payment.php', // Ajuste conforme sua rota
                method: 'POST',
                data: ajaxData,
                success: function(response) {
                    if(response.status == "success") {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Sucesso', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        if (selectedMethod === 'pix') {
                            // Adiciona o prefixo adequado ao Base64 recebido do backend
                            const base64Image = `data:image/png;base64,${response.pix_image}`;

                            // Atualiza o src da imagem do QR Code
                            $('#pixQrCode').attr('src', base64Image);

                            $('#pixCode').val(response.pix_code); // Código do PIX

                            $('#pixCodeContainer').show(); // Exibe o container do QR Code e código
                            $('#pixFormContent').hide(); // Exibe o container do QR Code e código
                            $('#confirmPayment').hide(); // Ocultar o botão de confirmação

                            // Verifica o status do pagamento a cada 10 segundos (se necessário)
                            var intervalId = setInterval(function() {
                                $.ajax({
                                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/billing/functions/payment-status.php', // Nova rota para verificar status
                                    method: 'POST',
                                    data: { payment_id: btoa(response.code) }, // Passa o código do PIX para o backend verificar o status
                                    dataType: 'JSON',
                                    success: function(checkResponse) {
                                        if (checkResponse.status === 'success' && checkResponse.paymentStatus === 'paid') {
                                            // Se o pagamento for confirmado
                                            clearInterval(intervalId); // Para o intervalo
                                            toastr.success(checkResponse.message, 'Sucesso', {
                                                closeButton: true,
                                                progressBar: true,
                                                timeOut: 3000
                                            });

                                            // Faça algo após confirmação do pagamento, por exemplo, redirecionar ou exibir um sucesso
                                            $('.payment-section').hide();
                                            $('#successContent').show();
                                            $('#confirmPayment').hide();
                                            $('.btn-back').hide();
                                            $('.btn-back').hide();
                                        } else if (checkResponse.status === 'error') {
                                            // Se houver erro no status
                                            clearInterval(intervalId); // Para o intervalo
                                            toastr.error('Erro ao verificar pagamento, tente novamente mais tarde.', 'Erro', {
                                                closeButton: true,
                                                progressBar: true,
                                                timeOut: 3000
                                            });
                                        }
                                    },
                                    error: function() {
                                        // Caso ocorra algum erro na verificação do status
                                        toastr.error('Erro ao verificar o status do pagamento.', 'Erro', {
                                            closeButton: true,
                                            progressBar: true,
                                            timeOut: 3000
                                        });
                                    }
                                });
                            }, 10000); // Intervalo de 10 segundos
                        } else if (selectedMethod === 'card') {
                            // Esconde todas as seções e exibe o conteúdo de sucesso
                            $('.payment-section').hide();
                            $('#successContent').show();
                            $('#confirmPayment').hide();
                            $('.btn-back').hide();
                            $('.btn-back').hide();
                        } else if (selectedMethod === 'credit') {
                            // Esconde todas as seções e exibe o conteúdo de sucesso
                            $('.payment-section').hide();
                            $('#successContent').show();
                            $('#confirmPayment').hide();
                            $('.btn-back').hide();
                            $('.btn-back').hide();
                        }
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
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                }
            });
        });
    });
</script>

<!-- Máscaras -->
<script>
    // Máscara para Numero do Cartao
    $('input[name="card_number"]').mask('0000 0000 0000 0000');

    // Máscara para Vencimento do Cartao
    $('input[name="card_expiration"]').mask('00/00');

    // Máscara para CVV
    $('input[name="card_cvv"]').mask('0000');

    // Máscara para CPF
    $('input[name="cpf"]').mask('000.000.000-00');
</script>

<!-- Modal Plano Bronze -->
<div id="bronzeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="bronzeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="bronzeModalLabel">Plano Bronze</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body - Etapa 1 -->
            <div class="modal-body step1">
                <h5 class="mb-3">Seja Bem Vindo!</h5>
                <ul>
                    <li>Você terá acesso LIMITADO ao Programa de Remuneração da Renda CAP Brasil.</li>
                    <li>O Plano Bronze libera somente Cashback da sua 1ª geração.</li>
                </ul>
                <p class="text-danger font-weight-semibold mb-0">OBS: ATENÇÃO</p>
                <p class="text-danger font-weight-semibold">Esse Plano não dá direito á Títulos de Capitalização.</p>
                <!-- <p class="text-info font-weight-semibold">OBS: Você precisará realizar uma doação de R$ 20,00 para o Hospital do Câncer Infantil.</p> -->
                <button class="btn btn-success btn-pulse btnSelectOtherPlan ml-2">Selecionar Outro Plano</button>
                <button id="btnNextStep" class="btn btn-link">Continuar</button>
            </div>

            <!-- Modal Body - Etapa 2 -->
            <div class="modal-body step2 d-none">
                <h5>Tem certeza que deseja continuar com o Plano Bronze?</h5>
                <p>Você pode alterar seu plano a qualquer momento, mas recomendamos aproveitar os benefícios dos planos pagos!</p>
                <button class="btn btn-light btnSelectOtherPlan">Selecionar Outro Plano</button>
                <button id="btnNextStep2" class="btn btn-primary">Continuar</button>
            </div>

            <!-- Modal Body - Etapa 3 (Nova Etapa) -->
            <div class="modal-body step3 d-none">
                <h5>Realize a doação para o Hospital do Câncer Infantil</h5>
                <p>Para confirmar seu plano, realize a doação de R$ 20,00 utilizando o código QR ou a chave Pix abaixo:</p>
                <div class="text-center">
                    <!-- QR Code Pix (Exemplo) -->
                    <img alt="QR Code Pix" class="img-fluid mb-3" src="data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjEwIDIxMCIgd2lkdGg9IjIxMCIgaGVpZ2h0PSIyMTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgICAgIAogICAgPHN2ZyB4PSIwIiB5PSIwIiB3aWR0aD0iMjEwIiBoZWlnaHQ9IjIxMCIgc3R5bGU9IiI+CiAgICAgIDxzdmcgd2lkdGg9IjIxMCIgaGVpZ2h0PSIyMTAiIHNoYXBlLXJlbmRlcmluZz0iY3Jpc3BFZGdlcyI+PGcgaWQ9ImNsaXAtcGF0aC1kb3QtY29sb3IiIGZpbGw9IiMwMDAwMDAiPjxyZWN0IHg9IjMiIHk9IjU5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYuNSw2Mi41KSI+PC9yZWN0PjxyZWN0IHg9IjMiIHk9IjgwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYuNSw4My41KSI+PC9yZWN0PjxyZWN0IHg9IjMiIHk9Ijg3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYuNSw5MC41KSI+PC9yZWN0PjxyZWN0IHg9IjMiIHk9Ijk0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYuNSw5Ny41KSI+PC9yZWN0PjxyZWN0IHg9IjMiIHk9IjExNSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsMTE4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMyIgeT0iMTI5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYuNSwxMzIuNSkiPjwvcmVjdD48cmVjdCB4PSIzIiB5PSIxMzYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNi41LDEzOS41KSI+PC9yZWN0PjxyZWN0IHg9IjMiIHk9IjE0MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsMTQ2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTAiIHk9IjY2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzLjUsNjkuNSkiPjwvcmVjdD48cmVjdCB4PSIxMCIgeT0iNzMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTMuNSw3Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjEwIiB5PSI4NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMy41LDkwLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTAiIHk9IjEwMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMy41LDEwNC41KSI+PC9yZWN0PjxyZWN0IHg9IjEwIiB5PSIxMDgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTMuNSwxMTEuNSkiPjwvcmVjdD48cmVjdCB4PSIxMCIgeT0iMTE1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzLjUsMTE4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTAiIHk9IjEyOSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMy41LDEzMi41KSI+PC9yZWN0PjxyZWN0IHg9IjEwIiB5PSIxNDMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTMuNSwxNDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxNyIgeT0iNjYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjAuNSw2OS41KSI+PC9yZWN0PjxyZWN0IHg9IjE3IiB5PSI3MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMC41LDc2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTciIHk9IjEwMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMC41LDEwNC41KSI+PC9yZWN0PjxyZWN0IHg9IjE3IiB5PSIxMDgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjAuNSwxMTEuNSkiPjwvcmVjdD48cmVjdCB4PSIxNyIgeT0iMTM2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDIwLjUsMTM5LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTciIHk9IjE0MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMC41LDE0Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjI0IiB5PSI2NiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyNy41LDY5LjUpIj48L3JlY3Q+PHJlY3QgeD0iMjQiIHk9IjczIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDI3LjUsNzYuNSkiPjwvcmVjdD48cmVjdCB4PSIyNCIgeT0iODAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjcuNSw4My41KSI+PC9yZWN0PjxyZWN0IHg9IjI0IiB5PSI4NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyNy41LDkwLjUpIj48L3JlY3Q+PHJlY3QgeD0iMjQiIHk9IjExNSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyNy41LDExOC41KSI+PC9yZWN0PjxyZWN0IHg9IjI0IiB5PSIxMjIiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjcuNSwxMjUuNSkiPjwvcmVjdD48cmVjdCB4PSIyNCIgeT0iMTQzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDI3LjUsMTQ2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMzEiIHk9IjU5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDM0LjUsNjIuNSkiPjwvcmVjdD48cmVjdCB4PSIzMSIgeT0iNjYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMzQuNSw2OS41KSI+PC9yZWN0PjxyZWN0IHg9IjMxIiB5PSI4NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwzNC41LDkwLjUpIj48L3JlY3Q+PHJlY3QgeD0iMzEiIHk9IjExNSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwzNC41LDExOC41KSI+PC9yZWN0PjxyZWN0IHg9IjMxIiB5PSIxMzYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMzQuNSwxMzkuNSkiPjwvcmVjdD48cmVjdCB4PSIzMSIgeT0iMTQzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDM0LjUsMTQ2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMzgiIHk9IjU5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQxLjUsNjIuNSkiPjwvcmVjdD48cmVjdCB4PSIzOCIgeT0iNjYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDEuNSw2OS41KSI+PC9yZWN0PjxyZWN0IHg9IjM4IiB5PSI4MCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0MS41LDgzLjUpIj48L3JlY3Q+PHJlY3QgeD0iMzgiIHk9Ijk0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQxLjUsOTcuNSkiPjwvcmVjdD48cmVjdCB4PSIzOCIgeT0iMTAxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQxLjUsMTA0LjUpIj48L3JlY3Q+PHJlY3QgeD0iMzgiIHk9IjEyMiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0MS41LDEyNS41KSI+PC9yZWN0PjxyZWN0IHg9IjM4IiB5PSIxMjkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDEuNSwxMzIuNSkiPjwvcmVjdD48cmVjdCB4PSIzOCIgeT0iMTQzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQxLjUsMTQ2LjUpIj48L3JlY3Q+PHJlY3QgeD0iNDUiIHk9IjU5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQ4LjUsNjIuNSkiPjwvcmVjdD48cmVjdCB4PSI0NSIgeT0iNzMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDguNSw3Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjQ1IiB5PSI4NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0OC41LDkwLjUpIj48L3JlY3Q+PHJlY3QgeD0iNDUiIHk9IjEwMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0OC41LDEwNC41KSI+PC9yZWN0PjxyZWN0IHg9IjQ1IiB5PSIxMTUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDguNSwxMTguNSkiPjwvcmVjdD48cmVjdCB4PSI0NSIgeT0iMTI5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQ4LjUsMTMyLjUpIj48L3JlY3Q+PHJlY3QgeD0iNDUiIHk9IjE0MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0OC41LDE0Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjUyIiB5PSI2NiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw1NS41LDY5LjUpIj48L3JlY3Q+PHJlY3QgeD0iNTIiIHk9IjczIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDU1LjUsNzYuNSkiPjwvcmVjdD48cmVjdCB4PSI1MiIgeT0iOTQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNTUuNSw5Ny41KSI+PC9yZWN0PjxyZWN0IHg9IjU5IiB5PSIxMCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2Mi41LDEzLjUpIj48L3JlY3Q+PHJlY3QgeD0iNTkiIHk9IjE3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYyLjUsMjAuNSkiPjwvcmVjdD48cmVjdCB4PSI1OSIgeT0iMjQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjIuNSwyNy41KSI+PC9yZWN0PjxyZWN0IHg9IjU5IiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2Mi41LDM0LjUpIj48L3JlY3Q+PHJlY3QgeD0iNTkiIHk9IjM4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYyLjUsNDEuNSkiPjwvcmVjdD48cmVjdCB4PSI1OSIgeT0iNDUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjIuNSw0OC41KSI+PC9yZWN0PjxyZWN0IHg9IjU5IiB5PSI1MiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2Mi41LDU1LjUpIj48L3JlY3Q+PHJlY3QgeD0iNTkiIHk9IjY2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYyLjUsNjkuNSkiPjwvcmVjdD48cmVjdCB4PSI1OSIgeT0iODciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjIuNSw5MC41KSI+PC9yZWN0PjxyZWN0IHg9IjU5IiB5PSI5NCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2Mi41LDk3LjUpIj48L3JlY3Q+PHJlY3QgeD0iNTkiIHk9IjEwOCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2Mi41LDExMS41KSI+PC9yZWN0PjxyZWN0IHg9IjU5IiB5PSIxMjIiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjIuNSwxMjUuNSkiPjwvcmVjdD48cmVjdCB4PSI1OSIgeT0iMTI5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYyLjUsMTMyLjUpIj48L3JlY3Q+PHJlY3QgeD0iNTkiIHk9IjEzNiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2Mi41LDEzOS41KSI+PC9yZWN0PjxyZWN0IHg9IjU5IiB5PSIxNDMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjIuNSwxNDYuNSkiPjwvcmVjdD48cmVjdCB4PSI1OSIgeT0iMTY0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYyLjUsMTY3LjUpIj48L3JlY3Q+PHJlY3QgeD0iNTkiIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2Mi41LDE3NC41KSI+PC9yZWN0PjxyZWN0IHg9IjU5IiB5PSIxOTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjIuNSwyMDIuNSkiPjwvcmVjdD48cmVjdCB4PSI2NiIgeT0iMyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2OS41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSI2NiIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjkuNSwyMC41KSI+PC9yZWN0PjxyZWN0IHg9IjY2IiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2OS41LDM0LjUpIj48L3JlY3Q+PHJlY3QgeD0iNjYiIHk9IjU5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDY5LjUsNjIuNSkiPjwvcmVjdD48cmVjdCB4PSI2NiIgeT0iNjYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjkuNSw2OS41KSI+PC9yZWN0PjxyZWN0IHg9IjY2IiB5PSI3MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2OS41LDc2LjUpIj48L3JlY3Q+PHJlY3QgeD0iNjYiIHk9IjgwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDY5LjUsODMuNSkiPjwvcmVjdD48cmVjdCB4PSI2NiIgeT0iODciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjkuNSw5MC41KSI+PC9yZWN0PjxyZWN0IHg9IjY2IiB5PSIxMDEiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjkuNSwxMDQuNSkiPjwvcmVjdD48cmVjdCB4PSI2NiIgeT0iMTA4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDY5LjUsMTExLjUpIj48L3JlY3Q+PHJlY3QgeD0iNjYiIHk9IjEzNiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2OS41LDEzOS41KSI+PC9yZWN0PjxyZWN0IHg9IjY2IiB5PSIxNTAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjkuNSwxNTMuNSkiPjwvcmVjdD48cmVjdCB4PSI2NiIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDY5LjUsMTYwLjUpIj48L3JlY3Q+PHJlY3QgeD0iNjYiIHk9IjE2NCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2OS41LDE2Ny41KSI+PC9yZWN0PjxyZWN0IHg9IjY2IiB5PSIxNzEiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjkuNSwxNzQuNSkiPjwvcmVjdD48cmVjdCB4PSI2NiIgeT0iMTc4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDY5LjUsMTgxLjUpIj48L3JlY3Q+PHJlY3QgeD0iNjYiIHk9IjE4NSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2OS41LDE4OC41KSI+PC9yZWN0PjxyZWN0IHg9IjY2IiB5PSIxOTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNjkuNSwyMDIuNSkiPjwvcmVjdD48cmVjdCB4PSI3MyIgeT0iMyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw3Ni41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSI3MyIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNzYuNSwyMC41KSI+PC9yZWN0PjxyZWN0IHg9IjczIiB5PSIyNCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw3Ni41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iNzMiIHk9IjM4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDc2LjUsNDEuNSkiPjwvcmVjdD48cmVjdCB4PSI3MyIgeT0iNDUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNzYuNSw0OC41KSI+PC9yZWN0PjxyZWN0IHg9IjczIiB5PSIxNTAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNzYuNSwxNTMuNSkiPjwvcmVjdD48cmVjdCB4PSI3MyIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDc2LjUsMTYwLjUpIj48L3JlY3Q+PHJlY3QgeD0iNzMiIHk9IjE2NCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw3Ni41LDE2Ny41KSI+PC9yZWN0PjxyZWN0IHg9IjczIiB5PSIxNzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNzYuNSwxODEuNSkiPjwvcmVjdD48cmVjdCB4PSI4MCIgeT0iMjQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsODMuNSwyNy41KSI+PC9yZWN0PjxyZWN0IHg9IjgwIiB5PSI1MiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw4My41LDU1LjUpIj48L3JlY3Q+PHJlY3QgeD0iODAiIHk9IjU5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDgzLjUsNjIuNSkiPjwvcmVjdD48cmVjdCB4PSI4MCIgeT0iNjYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsODMuNSw2OS41KSI+PC9yZWN0PjxyZWN0IHg9IjgwIiB5PSIxNDMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsODMuNSwxNDYuNSkiPjwvcmVjdD48cmVjdCB4PSI4MCIgeT0iMTUwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDgzLjUsMTUzLjUpIj48L3JlY3Q+PHJlY3QgeD0iODAiIHk9IjE1NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw4My41LDE2MC41KSI+PC9yZWN0PjxyZWN0IHg9IjgwIiB5PSIxNjQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsODMuNSwxNjcuNSkiPjwvcmVjdD48cmVjdCB4PSI4MCIgeT0iMTc4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDgzLjUsMTgxLjUpIj48L3JlY3Q+PHJlY3QgeD0iODAiIHk9IjE5MiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw4My41LDE5NS41KSI+PC9yZWN0PjxyZWN0IHg9Ijg3IiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDkwLjUsNi41KSI+PC9yZWN0PjxyZWN0IHg9Ijg3IiB5PSI0NSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw5MC41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iODciIHk9IjUyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDkwLjUsNTUuNSkiPjwvcmVjdD48cmVjdCB4PSI4NyIgeT0iNTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsOTAuNSw2Mi41KSI+PC9yZWN0PjxyZWN0IHg9Ijg3IiB5PSIxNDMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsOTAuNSwxNDYuNSkiPjwvcmVjdD48cmVjdCB4PSI4NyIgeT0iMTUwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDkwLjUsMTUzLjUpIj48L3JlY3Q+PHJlY3QgeD0iODciIHk9IjE2NCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw5MC41LDE2Ny41KSI+PC9yZWN0PjxyZWN0IHg9Ijg3IiB5PSIxODUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsOTAuNSwxODguNSkiPjwvcmVjdD48cmVjdCB4PSI4NyIgeT0iMTkyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDkwLjUsMTk1LjUpIj48L3JlY3Q+PHJlY3QgeD0iODciIHk9IjE5OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw5MC41LDIwMi41KSI+PC9yZWN0PjxyZWN0IHg9Ijk0IiB5PSIxNyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw5Ny41LDIwLjUpIj48L3JlY3Q+PHJlY3QgeD0iOTQiIHk9IjMxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDk3LjUsMzQuNSkiPjwvcmVjdD48cmVjdCB4PSI5NCIgeT0iMzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsOTcuNSw0MS41KSI+PC9yZWN0PjxyZWN0IHg9Ijk0IiB5PSI1MiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw5Ny41LDU1LjUpIj48L3JlY3Q+PHJlY3QgeD0iOTQiIHk9IjY2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDk3LjUsNjkuNSkiPjwvcmVjdD48cmVjdCB4PSI5NCIgeT0iMTM2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDk3LjUsMTM5LjUpIj48L3JlY3Q+PHJlY3QgeD0iOTQiIHk9IjE0MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw5Ny41LDE0Ni41KSI+PC9yZWN0PjxyZWN0IHg9Ijk0IiB5PSIxNTAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsOTcuNSwxNTMuNSkiPjwvcmVjdD48cmVjdCB4PSI5NCIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDk3LjUsMTYwLjUpIj48L3JlY3Q+PHJlY3QgeD0iOTQiIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw5Ny41LDE3NC41KSI+PC9yZWN0PjxyZWN0IHg9Ijk0IiB5PSIxNzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsOTcuNSwxODEuNSkiPjwvcmVjdD48cmVjdCB4PSI5NCIgeT0iMTkyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDk3LjUsMTk1LjUpIj48L3JlY3Q+PHJlY3QgeD0iOTQiIHk9IjE5OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw5Ny41LDIwMi41KSI+PC9yZWN0PjxyZWN0IHg9IjEwMSIgeT0iMTAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTA0LjUsMTMuNSkiPjwvcmVjdD48cmVjdCB4PSIxMDEiIHk9IjI0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEwNC41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTAxIiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMDQuNSwzNC41KSI+PC9yZWN0PjxyZWN0IHg9IjEwMSIgeT0iMzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTA0LjUsNDEuNSkiPjwvcmVjdD48cmVjdCB4PSIxMDEiIHk9IjQ1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEwNC41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTAxIiB5PSI1OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMDQuNSw2Mi41KSI+PC9yZWN0PjxyZWN0IHg9IjEwMSIgeT0iMTQzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEwNC41LDE0Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjEwMSIgeT0iMTY0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEwNC41LDE2Ny41KSI+PC9yZWN0PjxyZWN0IHg9IjEwOCIgeT0iNjYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTExLjUsNjkuNSkiPjwvcmVjdD48cmVjdCB4PSIxMDgiIHk9IjE0MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMTEuNSwxNDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxMDgiIHk9IjE1MCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMTEuNSwxNTMuNSkiPjwvcmVjdD48cmVjdCB4PSIxMDgiIHk9IjE1NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMTEuNSwxNjAuNSkiPjwvcmVjdD48cmVjdCB4PSIxMDgiIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMTEuNSwxNzQuNSkiPjwvcmVjdD48cmVjdCB4PSIxMDgiIHk9IjE3OCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMTEuNSwxODEuNSkiPjwvcmVjdD48cmVjdCB4PSIxMDgiIHk9IjE5MiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMTEuNSwxOTUuNSkiPjwvcmVjdD48cmVjdCB4PSIxMDgiIHk9IjE5OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMTEuNSwyMDIuNSkiPjwvcmVjdD48cmVjdCB4PSIxMTUiIHk9IjMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTE4LjUsNi41KSI+PC9yZWN0PjxyZWN0IHg9IjExNSIgeT0iMTAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTE4LjUsMTMuNSkiPjwvcmVjdD48cmVjdCB4PSIxMTUiIHk9IjI0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDExOC41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTE1IiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMTguNSwzNC41KSI+PC9yZWN0PjxyZWN0IHg9IjExNSIgeT0iNDUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTE4LjUsNDguNSkiPjwvcmVjdD48cmVjdCB4PSIxMTUiIHk9IjUyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDExOC41LDU1LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTE1IiB5PSIxMzYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTE4LjUsMTM5LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTE1IiB5PSIxNDMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTE4LjUsMTQ2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTE1IiB5PSIxNzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTE4LjUsMTgxLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTE1IiB5PSIxODUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTE4LjUsMTg4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTE1IiB5PSIxOTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTE4LjUsMjAyLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTIyIiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxMjIiIHk9IjEwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDEzLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTIyIiB5PSIyNCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMjUuNSwyNy41KSI+PC9yZWN0PjxyZWN0IHg9IjEyMiIgeT0iNTIiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTI1LjUsNTUuNSkiPjwvcmVjdD48cmVjdCB4PSIxMjIiIHk9IjU5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDYyLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTIyIiB5PSI2NiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMjUuNSw2OS41KSI+PC9yZWN0PjxyZWN0IHg9IjEyMiIgeT0iMTM2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDEzOS41KSI+PC9yZWN0PjxyZWN0IHg9IjEyMiIgeT0iMTUwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDE1My41KSI+PC9yZWN0PjxyZWN0IHg9IjEyMiIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDE2MC41KSI+PC9yZWN0PjxyZWN0IHg9IjEyMiIgeT0iMTcxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDE3NC41KSI+PC9yZWN0PjxyZWN0IHg9IjEyMiIgeT0iMTc4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDE4MS41KSI+PC9yZWN0PjxyZWN0IHg9IjEyMiIgeT0iMTg1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDE4OC41KSI+PC9yZWN0PjxyZWN0IHg9IjEyMiIgeT0iMTkyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDE5NS41KSI+PC9yZWN0PjxyZWN0IHg9IjEyMiIgeT0iMTk5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEyNS41LDIwMi41KSI+PC9yZWN0PjxyZWN0IHg9IjEyOSIgeT0iMyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzIuNSw2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTI5IiB5PSIxNyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzIuNSwyMC41KSI+PC9yZWN0PjxyZWN0IHg9IjEyOSIgeT0iMjQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTMyLjUsMjcuNSkiPjwvcmVjdD48cmVjdCB4PSIxMjkiIHk9IjMxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzMi41LDM0LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTI5IiB5PSI0NSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzIuNSw0OC41KSI+PC9yZWN0PjxyZWN0IHg9IjEyOSIgeT0iNTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTMyLjUsNjIuNSkiPjwvcmVjdD48cmVjdCB4PSIxMjkiIHk9IjE0MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzIuNSwxNDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxMjkiIHk9IjE1NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzIuNSwxNjAuNSkiPjwvcmVjdD48cmVjdCB4PSIxMjkiIHk9IjE2NCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzIuNSwxNjcuNSkiPjwvcmVjdD48cmVjdCB4PSIxMjkiIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzIuNSwxNzQuNSkiPjwvcmVjdD48cmVjdCB4PSIxMjkiIHk9IjE3OCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzIuNSwxODEuNSkiPjwvcmVjdD48cmVjdCB4PSIxMjkiIHk9IjE4NSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzIuNSwxODguNSkiPjwvcmVjdD48cmVjdCB4PSIxMzYiIHk9IjMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTM5LjUsNi41KSI+PC9yZWN0PjxyZWN0IHg9IjEzNiIgeT0iMzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTM5LjUsNDEuNSkiPjwvcmVjdD48cmVjdCB4PSIxMzYiIHk9IjUyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzOS41LDU1LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTM2IiB5PSI3MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMzkuNSw3Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjEzNiIgeT0iMTA4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzOS41LDExMS41KSI+PC9yZWN0PjxyZWN0IHg9IjEzNiIgeT0iMTM2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzOS41LDEzOS41KSI+PC9yZWN0PjxyZWN0IHg9IjEzNiIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzOS41LDE2MC41KSI+PC9yZWN0PjxyZWN0IHg9IjEzNiIgeT0iMTc4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzOS41LDE4MS41KSI+PC9yZWN0PjxyZWN0IHg9IjEzNiIgeT0iMTkyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzOS41LDE5NS41KSI+PC9yZWN0PjxyZWN0IHg9IjEzNiIgeT0iMTk5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzOS41LDIwMi41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNDYuNSw2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTQzIiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNDYuNSwzNC41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTQ2LjUsNDEuNSkiPjwvcmVjdD48cmVjdCB4PSIxNDMiIHk9IjQ1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTQzIiB5PSI1MiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNDYuNSw1NS41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iODAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTQ2LjUsODMuNSkiPjwvcmVjdD48cmVjdCB4PSIxNDMiIHk9Ijg3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDkwLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTQzIiB5PSI5NCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNDYuNSw5Ny41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTAxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDEwNC41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTA4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDExMS41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTIyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDEyNS41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTQzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDE0Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTUwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDE1My41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDE2MC41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTY0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDE2Ny41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTcxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDE3NC41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTg1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDE4OC41KSI+PC9yZWN0PjxyZWN0IHg9IjE0MyIgeT0iMTk5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE0Ni41LDIwMi41KSI+PC9yZWN0PjxyZWN0IHg9IjE1MCIgeT0iNTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsNjIuNSkiPjwvcmVjdD48cmVjdCB4PSIxNTAiIHk9IjY2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE1My41LDY5LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTUwIiB5PSI3MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNTMuNSw3Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjE1MCIgeT0iODAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsODMuNSkiPjwvcmVjdD48cmVjdCB4PSIxNTAiIHk9Ijk0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE1My41LDk3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTUwIiB5PSIxMDEiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsMTA0LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTUwIiB5PSIxMTUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsMTE4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTUwIiB5PSIxMjkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsMTMyLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTUwIiB5PSIxNDMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsMTQ2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTUwIiB5PSIxNzEiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsMTc0LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTUwIiB5PSIxNzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsMTgxLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTUwIiB5PSIxOTIiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsMTk1LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTUwIiB5PSIxOTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTUzLjUsMjAyLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTU3IiB5PSI1OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjAuNSw2Mi41KSI+PC9yZWN0PjxyZWN0IHg9IjE1NyIgeT0iNjYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTYwLjUsNjkuNSkiPjwvcmVjdD48cmVjdCB4PSIxNTciIHk9IjgwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2MC41LDgzLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTU3IiB5PSI4NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjAuNSw5MC41KSI+PC9yZWN0PjxyZWN0IHg9IjE1NyIgeT0iMTA4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2MC41LDExMS41KSI+PC9yZWN0PjxyZWN0IHg9IjE1NyIgeT0iMTM2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2MC41LDEzOS41KSI+PC9yZWN0PjxyZWN0IHg9IjE1NyIgeT0iMTQzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2MC41LDE0Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjE1NyIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2MC41LDE2MC41KSI+PC9yZWN0PjxyZWN0IHg9IjE1NyIgeT0iMTcxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2MC41LDE3NC41KSI+PC9yZWN0PjxyZWN0IHg9IjE1NyIgeT0iMTkyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2MC41LDE5NS41KSI+PC9yZWN0PjxyZWN0IHg9IjE2NCIgeT0iNTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTY3LjUsNjIuNSkiPjwvcmVjdD48cmVjdCB4PSIxNjQiIHk9IjEwOCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjcuNSwxMTEuNSkiPjwvcmVjdD48cmVjdCB4PSIxNjQiIHk9IjExNSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjcuNSwxMTguNSkiPjwvcmVjdD48cmVjdCB4PSIxNjQiIHk9IjEyOSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjcuNSwxMzIuNSkiPjwvcmVjdD48cmVjdCB4PSIxNjQiIHk9IjEzNiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjcuNSwxMzkuNSkiPjwvcmVjdD48cmVjdCB4PSIxNjQiIHk9IjE0MyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjcuNSwxNDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxNjQiIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjcuNSwxNzQuNSkiPjwvcmVjdD48cmVjdCB4PSIxNjQiIHk9IjE3OCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjcuNSwxODEuNSkiPjwvcmVjdD48cmVjdCB4PSIxNzEiIHk9IjU5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE3NC41LDYyLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSI2NiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNzQuNSw2OS41KSI+PC9yZWN0PjxyZWN0IHg9IjE3MSIgeT0iNzMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsNzYuNSkiPjwvcmVjdD48cmVjdCB4PSIxNzEiIHk9IjgwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE3NC41LDgzLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIxMDgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMTExLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIxMjkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMTMyLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIxNDMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMTQ2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIxNTAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMTUzLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIxNTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMTYwLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIxNjQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMTY3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIxNzEiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMTc0LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIxNzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMTgxLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIxODUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMTg4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTc4IiB5PSI1OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxODEuNSw2Mi41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iNzMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTgxLjUsNzYuNSkiPjwvcmVjdD48cmVjdCB4PSIxNzgiIHk9IjgwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDgzLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTc4IiB5PSI4NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxODEuNSw5MC41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTAxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDEwNC41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTA4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDExMS41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTE1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDExOC41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTIyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDEyNS41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTI5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDEzMi41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTM2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDEzOS41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTQzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDE0Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTUwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDE1My41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTY0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDE2Ny41KSI+PC9yZWN0PjxyZWN0IHg9IjE4NSIgeT0iNTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsNjIuNSkiPjwvcmVjdD48cmVjdCB4PSIxODUiIHk9IjczIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4OC41LDc2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSI4MCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxODguNSw4My41KSI+PC9yZWN0PjxyZWN0IHg9IjE4NSIgeT0iODciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsOTAuNSkiPjwvcmVjdD48cmVjdCB4PSIxODUiIHk9Ijk0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4OC41LDk3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIxMjIiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMTI1LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIxMzYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMTM5LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIxNDMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMTQ2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIxNTAiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMTUzLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIxNTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMTYwLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIxNjQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMTY3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIxNzEiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMTc0LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIxODUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMTg4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIxOTIiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMTk1LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTkyIiB5PSI1OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxOTUuNSw2Mi41KSI+PC9yZWN0PjxyZWN0IHg9IjE5MiIgeT0iNjYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTk1LjUsNjkuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTIiIHk9IjgwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDgzLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTkyIiB5PSI4NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxOTUuNSw5MC41KSI+PC9yZWN0PjxyZWN0IHg9IjE5MiIgeT0iMTAxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDEwNC41KSI+PC9yZWN0PjxyZWN0IHg9IjE5MiIgeT0iMTE1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDExOC41KSI+PC9yZWN0PjxyZWN0IHg9IjE5MiIgeT0iMTM2IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDEzOS41KSI+PC9yZWN0PjxyZWN0IHg9IjE5MiIgeT0iMTQzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDE0Ni41KSI+PC9yZWN0PjxyZWN0IHg9IjE5MiIgeT0iMTUwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDE1My41KSI+PC9yZWN0PjxyZWN0IHg9IjE5MiIgeT0iMTcxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDE3NC41KSI+PC9yZWN0PjxyZWN0IHg9IjE5MiIgeT0iMTc4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDE4MS41KSI+PC9yZWN0PjxyZWN0IHg9IjE5MiIgeT0iMTkyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDE5NS41KSI+PC9yZWN0PjxyZWN0IHg9IjE5OSIgeT0iNjYiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjAyLjUsNjkuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTkiIHk9IjczIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDIwMi41LDc2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTk5IiB5PSI4MCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMDIuNSw4My41KSI+PC9yZWN0PjxyZWN0IHg9IjE5OSIgeT0iODciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjAyLjUsOTAuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTkiIHk9IjEyMiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMDIuNSwxMjUuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTkiIHk9IjE1MCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMDIuNSwxNTMuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTkiIHk9IjE1NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMDIuNSwxNjAuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTkiIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMDIuNSwxNzQuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTkiIHk9IjE4NSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMDIuNSwxODguNSkiPjwvcmVjdD48L2c+PGcgaWQ9ImNsaXAtcGF0aC1jb3JuZXJzLXNxdWFyZS1jb2xvci0wLTAiIGZpbGw9IiMwMDAwMDAiPjxyZWN0IHg9IjMiIHk9IjMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNi41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIzIiB5PSIxMCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsMTMuNSkiPjwvcmVjdD48cmVjdCB4PSIzIiB5PSIxNyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsMjAuNSkiPjwvcmVjdD48cmVjdCB4PSIzIiB5PSIyNCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsMjcuNSkiPjwvcmVjdD48cmVjdCB4PSIzIiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsMzQuNSkiPjwvcmVjdD48cmVjdCB4PSIzIiB5PSIzOCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsNDEuNSkiPjwvcmVjdD48cmVjdCB4PSIzIiB5PSI0NSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsNDguNSkiPjwvcmVjdD48cmVjdCB4PSIxMCIgeT0iMyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMy41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxMCIgeT0iNDUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTMuNSw0OC41KSI+PC9yZWN0PjxyZWN0IHg9IjE3IiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDIwLjUsNi41KSI+PC9yZWN0PjxyZWN0IHg9IjE3IiB5PSI0NSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMC41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMjQiIHk9IjMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjcuNSw2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMjQiIHk9IjQ1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDI3LjUsNDguNSkiPjwvcmVjdD48cmVjdCB4PSIzMSIgeT0iMyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwzNC41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIzMSIgeT0iNDUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMzQuNSw0OC41KSI+PC9yZWN0PjxyZWN0IHg9IjM4IiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQxLjUsNi41KSI+PC9yZWN0PjxyZWN0IHg9IjM4IiB5PSI0NSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0MS41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iNDUiIHk9IjMiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDguNSw2LjUpIj48L3JlY3Q+PHJlY3QgeD0iNDUiIHk9IjEwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQ4LjUsMTMuNSkiPjwvcmVjdD48cmVjdCB4PSI0NSIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDguNSwyMC41KSI+PC9yZWN0PjxyZWN0IHg9IjQ1IiB5PSIyNCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0OC41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iNDUiIHk9IjMxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQ4LjUsMzQuNSkiPjwvcmVjdD48cmVjdCB4PSI0NSIgeT0iMzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDguNSw0MS41KSI+PC9yZWN0PjxyZWN0IHg9IjQ1IiB5PSI0NSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0OC41LDQ4LjUpIj48L3JlY3Q+PC9nPjxnIGlkPSJjbGlwLXBhdGgtY29ybmVycy1kb3QtY29sb3ItMC0wIiBmaWxsPSIjMDAwMDAwIj48cmVjdCB4PSIxNyIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjAuNSwyMC41KSI+PC9yZWN0PjxyZWN0IHg9IjE3IiB5PSIyNCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMC41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTciIHk9IjMxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDIwLjUsMzQuNSkiPjwvcmVjdD48cmVjdCB4PSIyNCIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjcuNSwyMC41KSI+PC9yZWN0PjxyZWN0IHg9IjI0IiB5PSIyNCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyNy41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMjQiIHk9IjMxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDI3LjUsMzQuNSkiPjwvcmVjdD48cmVjdCB4PSIzMSIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMzQuNSwyMC41KSI+PC9yZWN0PjxyZWN0IHg9IjMxIiB5PSIyNCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwzNC41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMzEiIHk9IjMxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDM0LjUsMzQuNSkiPjwvcmVjdD48L2c+PGcgaWQ9ImNsaXAtcGF0aC1jb3JuZXJzLXNxdWFyZS1jb2xvci0xLTAiIGZpbGw9IiMwMDAwMDAiPjxyZWN0IHg9IjE1NyIgeT0iMyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjAuNSw2LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTU3IiB5PSIxMCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjAuNSwxMy41KSI+PC9yZWN0PjxyZWN0IHg9IjE1NyIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTYwLjUsMjAuNSkiPjwvcmVjdD48cmVjdCB4PSIxNTciIHk9IjI0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2MC41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTU3IiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNjAuNSwzNC41KSI+PC9yZWN0PjxyZWN0IHg9IjE1NyIgeT0iMzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTYwLjUsNDEuNSkiPjwvcmVjdD48cmVjdCB4PSIxNTciIHk9IjQ1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2MC41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTY0IiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2Ny41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxNjQiIHk9IjQ1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE2Ny41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE3NC41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxNzEiIHk9IjQ1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE3NC41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTc4IiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxNzgiIHk9IjQ1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4OC41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxODUiIHk9IjQ1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4OC41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTkyIiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTIiIHk9IjQ1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE5NS41LDQ4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTk5IiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDIwMi41LDYuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTkiIHk9IjEwIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDIwMi41LDEzLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTk5IiB5PSIxNyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMDIuNSwyMC41KSI+PC9yZWN0PjxyZWN0IHg9IjE5OSIgeT0iMjQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjAyLjUsMjcuNSkiPjwvcmVjdD48cmVjdCB4PSIxOTkiIHk9IjMxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDIwMi41LDM0LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTk5IiB5PSIzOCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMDIuNSw0MS41KSI+PC9yZWN0PjxyZWN0IHg9IjE5OSIgeT0iNDUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjAyLjUsNDguNSkiPjwvcmVjdD48L2c+PGcgaWQ9ImNsaXAtcGF0aC1jb3JuZXJzLWRvdC1jb2xvci0xLTAiIGZpbGw9IiMwMDAwMDAiPjxyZWN0IHg9IjE3MSIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTc0LjUsMjAuNSkiPjwvcmVjdD48cmVjdCB4PSIxNzEiIHk9IjI0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE3NC41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTcxIiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxNzQuNSwzNC41KSI+PC9yZWN0PjxyZWN0IHg9IjE3OCIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTgxLjUsMjAuNSkiPjwvcmVjdD48cmVjdCB4PSIxNzgiIHk9IjI0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4MS41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTc4IiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxODEuNSwzNC41KSI+PC9yZWN0PjxyZWN0IHg9IjE4NSIgeT0iMTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMTg4LjUsMjAuNSkiPjwvcmVjdD48cmVjdCB4PSIxODUiIHk9IjI0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDE4OC41LDI3LjUpIj48L3JlY3Q+PHJlY3QgeD0iMTg1IiB5PSIzMSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxODguNSwzNC41KSI+PC9yZWN0PjwvZz48ZyBpZD0iY2xpcC1wYXRoLWNvcm5lcnMtc3F1YXJlLWNvbG9yLTAtMSIgZmlsbD0iIzAwMDAwMCI+PHJlY3QgeD0iMyIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYuNSwxNjAuNSkiPjwvcmVjdD48cmVjdCB4PSIzIiB5PSIxNjQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNi41LDE2Ny41KSI+PC9yZWN0PjxyZWN0IHg9IjMiIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsMTc0LjUpIj48L3JlY3Q+PHJlY3QgeD0iMyIgeT0iMTc4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYuNSwxODEuNSkiPjwvcmVjdD48cmVjdCB4PSIzIiB5PSIxODUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNi41LDE4OC41KSI+PC9yZWN0PjxyZWN0IHg9IjMiIHk9IjE5MiIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw2LjUsMTk1LjUpIj48L3JlY3Q+PHJlY3QgeD0iMyIgeT0iMTk5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDYuNSwyMDIuNSkiPjwvcmVjdD48cmVjdCB4PSIxMCIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDEzLjUsMTYwLjUpIj48L3JlY3Q+PHJlY3QgeD0iMTAiIHk9IjE5OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwxMy41LDIwMi41KSI+PC9yZWN0PjxyZWN0IHg9IjE3IiB5PSIxNTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjAuNSwxNjAuNSkiPjwvcmVjdD48cmVjdCB4PSIxNyIgeT0iMTk5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDIwLjUsMjAyLjUpIj48L3JlY3Q+PHJlY3QgeD0iMjQiIHk9IjE1NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyNy41LDE2MC41KSI+PC9yZWN0PjxyZWN0IHg9IjI0IiB5PSIxOTkiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjcuNSwyMDIuNSkiPjwvcmVjdD48cmVjdCB4PSIzMSIgeT0iMTU3IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDM0LjUsMTYwLjUpIj48L3JlY3Q+PHJlY3QgeD0iMzEiIHk9IjE5OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwzNC41LDIwMi41KSI+PC9yZWN0PjxyZWN0IHg9IjM4IiB5PSIxNTciIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDEuNSwxNjAuNSkiPjwvcmVjdD48cmVjdCB4PSIzOCIgeT0iMTk5IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQxLjUsMjAyLjUpIj48L3JlY3Q+PHJlY3QgeD0iNDUiIHk9IjE1NyIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0OC41LDE2MC41KSI+PC9yZWN0PjxyZWN0IHg9IjQ1IiB5PSIxNjQiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDguNSwxNjcuNSkiPjwvcmVjdD48cmVjdCB4PSI0NSIgeT0iMTcxIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQ4LjUsMTc0LjUpIj48L3JlY3Q+PHJlY3QgeD0iNDUiIHk9IjE3OCIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0OC41LDE4MS41KSI+PC9yZWN0PjxyZWN0IHg9IjQ1IiB5PSIxODUiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsNDguNSwxODguNSkiPjwvcmVjdD48cmVjdCB4PSI0NSIgeT0iMTkyIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDQ4LjUsMTk1LjUpIj48L3JlY3Q+PHJlY3QgeD0iNDUiIHk9IjE5OSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCw0OC41LDIwMi41KSI+PC9yZWN0PjwvZz48ZyBpZD0iY2xpcC1wYXRoLWNvcm5lcnMtZG90LWNvbG9yLTAtMSIgZmlsbD0iIzAwMDAwMCI+PHJlY3QgeD0iMTciIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyMC41LDE3NC41KSI+PC9yZWN0PjxyZWN0IHg9IjE3IiB5PSIxNzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjAuNSwxODEuNSkiPjwvcmVjdD48cmVjdCB4PSIxNyIgeT0iMTg1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDIwLjUsMTg4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMjQiIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwyNy41LDE3NC41KSI+PC9yZWN0PjxyZWN0IHg9IjI0IiB5PSIxNzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMjcuNSwxODEuNSkiPjwvcmVjdD48cmVjdCB4PSIyNCIgeT0iMTg1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDI3LjUsMTg4LjUpIj48L3JlY3Q+PHJlY3QgeD0iMzEiIHk9IjE3MSIgd2lkdGg9IjciIGhlaWdodD0iNyIgdHJhbnNmb3JtPSJyb3RhdGUoMCwzNC41LDE3NC41KSI+PC9yZWN0PjxyZWN0IHg9IjMxIiB5PSIxNzgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciIHRyYW5zZm9ybT0icm90YXRlKDAsMzQuNSwxODEuNSkiPjwvcmVjdD48cmVjdCB4PSIzMSIgeT0iMTg1IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB0cmFuc2Zvcm09InJvdGF0ZSgwLDM0LjUsMTg4LjUpIj48L3JlY3Q+PC9nPjxpbWFnZSB4PSI3NCIgeT0iNzQiIHdpZHRoPSI2MXB4IiBoZWlnaHQ9IjYxcHgiIGhyZWY9ImRhdGE6aW1hZ2Uvc3ZnK3htbDtiYXNlNjQsUENFdExTQlFhWGdnTFMwK0RRbzhjM1puSUhodGJHNXpQU0pvZEhSd09pOHZkM2QzTG5jekxtOXlaeTh5TURBd0wzTjJaeUlnZDJsa2RHZzlJalUwSWlCb1pXbG5hSFE5SWpVMElpQjJhV1YzUW05NFBTSXdJREFnTlRRZ05UUWlJR1pwYkd3OUltNXZibVVpUGcwS0lDQWdJRHh3WVhSb0lHUTlJazB4TlM0NU1EZ3lJREUyTGpBd01ERklNVFV1TWpnME9Vd3lNaTQyTlRRNUlEZ3VOak13TURkRE1qVXVNRFUyTmlBMkxqSXpOelUzSURJNExqazBNeklnTmk0eU16YzFOeUF6TVM0ek5EUTVJRGd1TmpNd01EZE1Nemd1TnpFME9TQXhOaTR3TURBeFNETTRMakE1TVRaRE16WXVOakkwT1NBeE5pNHdNREF4SURNMUxqSTBNRGNnTVRZdU5UWTROQ0F6TkM0eU1EUTVJREUzTGpZeE16Uk1NamN1T1RjeE5pQXlNeTQ0TVRreVF6STNMalF6TURjZ01qUXVNell3TVNBeU5pNDFOamt4SURJMExqTTJNREVnTWpZdU1ESTRNaUF5TXk0NE1Ua3lUREU1TGpjNU5Ea2dNVGN1TmpFek5FTXhPQzQzTlRreElERTJMalUyT0RRZ01UY3VNemMwT1NBeE5pNHdNREF4SURFMUxqa3dPRElnTVRZdU1EQXdNVm9pSUdacGJHdzlJaU0wUkVJMlFVTWlMejQ4Y0dGMGFDQmtQU0pOTXpndU1Ea3hOaUF6Tnk0NU9UazVTRE00TGpjeE5EbE1NekV1TXpRME9TQTBOUzR6TmprNVF6STRMamswTXpJZ05EY3VOell5TkNBeU5TNHdOVFkySURRM0xqYzJNalFnTWpJdU5qVTBPU0EwTlM0ek5qazVUREUxTGpJNE5Ea2dNemN1T1RrNU9VZ3hOUzQ1TURneVF6RTNMak0zTkRrZ016Y3VPVGs1T1NBeE9DNDNOVGt4SURNM0xqUXpNVFlnTVRrdU56azBPU0F6Tmk0ek9EWTJUREkyTGpBeU9ESWdNekF1TVRnd09FTXlOaTQxTmpreElESTVMall6T1RrZ01qY3VORE13TnlBeU9TNDJNems1SURJM0xqazNNVFlnTXpBdU1UZ3dPRXd6TkM0eU1EUTVJRE0yTGpNNE5qWkRNelV1TWpRd055QXpOeTQwTXpFMklETTJMall5TkRrZ016Y3VPVGs1T1NBek9DNHdPVEUySURNM0xqazVPVGxhSWlCbWFXeHNQU0lqTkVSQ05rRkRJaTgrUEhCaGRHZ2daRDBpVFRRMUxqTTNNREVnTXpFdU16UTBPVXcwTUM0MU5EZzBJRE0yTGpFMk5qWklNemd1TURreE4wTXpOeTR4TVRBNUlETTJMakUyTmpZZ016WXVNVGswTWlBek5TNDNPREUySURNMUxqUTVOellnTXpVdU1EazBNVXd5T1M0eU5qUXlJREk0TGpnM09URkRNamd1TURFM05pQXlOeTQyTXpJMElESTFMams0TWpZZ01qY3VOak15TkNBeU5DNDNNelU1SURJNExqZzNPVEZNTVRndU5UQXlOaUF6TlM0d09UUXhRekUzTGpnd05Ua2dNelV1TnpneE5pQXhOaTQ0T0RreUlETTJMakUyTmpZZ01UVXVPVEE0TkNBek5pNHhOalkyU0RFekxqUTFNVGRNT0M0Mk16QXdOeUF6TVM0ek5EUTVRell1TWpNM05UY2dNamd1T1RRek15QTJMakl6TnpVM0lESTFMakExTmpZZ09DNDJNekF3TnlBeU1pNDJOVFE1VERFekxqUTFNVGNnTVRjdU9ETXpNMGd4TlM0NU1EZzBRekUyTGpnNE9USWdNVGN1T0RNek15QXhOeTQ0TURVNUlERTRMakl4T0RNZ01UZ3VOVEF5TmlBeE9DNDVNRFU0VERJMExqY3pOVGtnTWpVdU1USXdPRU15TlM0ek5Ua3lJREkxTGpjME5ERWdNall1TVRnME1pQXlOaTR3TlRVNElESTNMakF3TURFZ01qWXVNRFUxT0VNeU55NDRNVFU1SURJMkxqQTFOVGdnTWpndU5qUXdPU0F5TlM0M05EUXhJREk1TGpJMk5ESWdNalV1TVRJd09Fd3pOUzQwT1RjMklERTRMamt3TlRoRE16WXVNVGswTWlBeE9DNHlNVGd6SURNM0xqRXhNRGtnTVRjdU9ETXpNeUF6T0M0d09URTNJREUzTGpnek16TklOREF1TlRRNE5FdzBOUzR6TnpBeElESXlMalkxTkRsRE5EY3VOell5TmlBeU5TNHdOVFkySURRM0xqYzJNallnTWpndU9UUXpNeUEwTlM0ek56QXhJRE14TGpNME5EbGFJaUJtYVd4c1BTSWpORVJDTmtGRElpOCtEUW84TDNOMlp6ND0iPjwvaW1hZ2U+PC9zdmc+CiAgICA8L3N2Zz4KICAKICAgIDwvc3ZnPg==">
                    <p class="mb-3">Chave Pix: <strong>doacaohospital@rendacap.com.br</strong></p>
                </div>
                <p>Após realizar a doação, envie o comprovante abaixo:</p>
                <!-- Input para enviar comprovante -->
                <input type="file" id="comprovantePix" class="form-control mb-3" accept="image/*,application/pdf">
                <button id="btnConfirmDonation" class="btn btn-primary">Enviar Comprovante e Confirmar</button>
                <button id="btnLoaderConfirmBronze" class="loader-button btn-pill btn-hover-shine btn btn-primary btn-lg d-none">
                    <div class="loader">
                        <div class="ball-pulse">
                            <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                            <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                            <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                        </div>
                    </div>
                </button>
            </div>

            <!-- Modal Body - Etapa Carregamento -->
            <div class="modal-body step-loading d-none text-center">
                <div class="spinner-border text-primary mt-2" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
                <p class="mt-3">Estamos processando seu plano. Por favor, aguarde...</p>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal's Administradores -->

<!-- Modal para selecionar usuários -->
<div class="modal fade" id="selectUsersModal" tabindex="-1" role="dialog" aria-labelledby="selectUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="selectUsersModalLabel">Selecionar Usuários</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Campo de busca -->
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <label for="search-input" class="form-label">Buscar Usuários</label>
                        <input type="text" id="search-input" class="form-control" placeholder="Digite para buscar...">
                    </div>
                </div>

                <!-- Lista de usuários -->
                <table class="table" style="width: 100%;" id="tableTitleUsersAssociated">
                    <thead>
                        <tr>
                            <th>Selecionar</th>
                            <th class="w-100">Usuário</th>
                            <th>Plano</th>
                        </tr>
                    </thead>
                    <tbody id="userList">
                        <!-- Usuários serão listados aqui via AJAX -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="confirmSelection">Confirmar Seleção</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão de Titulo -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmação de Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir este título?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Deletar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal confirmar exclusao de usuario -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Você está prestes a excluir o usuário <strong id="userToDelete"></strong>. Esta ação é irreversível.</p>
                <div class="form-group">
                    <label for="adminPassword">Senha do Administrador:</label>
                    <input type="password" class="form-control" id="adminPassword" placeholder="Digite sua senha">
                </div>
                <input type="hidden" id="userIdToDelete">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUser">Confirmar Exclusão</button>
            </div>
        </div>
    </div>
</div>