<?php
    if (isset($_SESSION['user_id_signature'])) {
        // Prepara a consulta para buscar o usuário que gerou o token
        $stmt = $conn->prepare("SELECT * FROM tb_subscriptions WHERE user_id = ? AND status = 'ACTIVE'");
        $stmt->execute([$_SESSION['user_id_signature']]);

        if (!$stmt->rowCount()) {
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
            // Define a mensagem de erro para usuário com assinatura existente
            $message = array(
                'status' => 'error', 
                'title' => 'Assinatura já existente', 
                'message' => 'Você já possui uma assinatura ativa. Para alterá-la, acesse Minha Conta > Assinatura.'
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
    #card-bronze .btn {
        position: absolute;
        left: 1.25rem;
        bottom: 1.25rem;
        width: calc(100% - 1.25rem - 1.25rem);
    }
</style>

<style>
    ul {
        padding: 0;
    }
    ul li {
        position: relative;
        list-style: none;
        font-weight: 600;
        padding-left: 1.25rem;
    }
    ul li::before {
        position: absolute;
        left: 0;
        top: 2px;
        font-family: "Material Symbols Outlined";
        content: "\e5ca";
        color: #000000;
        font-size: 0.88rem;
    }
    .dark-card ul li::before {
        color: #FFFFFF;
    }
</style>

<style>
    .modal-x-lg {
        max-width: 1000px !important;
    }
</style>

<div class="modal-lg modal-x-lg w-100 mx-auto">
    <div class="row">
        <div class="col-md-3 d-grid mb-3" id="card-diamond">
            <div class="card-shadow-secondary border card card-body border-secondary">
                <h5 class="card-title">Plano Diamante</h5>
                <div class="d-flex align-items-baseline">
                    <small class="opacity-5 pr-1">R$</small>
                    <h3 class="count-up font-weight-semibold" data-value="200.00">200,00</h3>
                    <small class="opacity-5 pl-1">Mensal</small>
                </div>

                <div class="divider mt-3"></div>
                <p>9 títulos de Capitalização DIGITAL</p>
                <ul>
                    <li>3 Tele Sena Semanal</li>
                    <li>3 HiperCap Brasil</li>
                    <li>3 Viva Sorte</li>
                </ul>
                <p class="text-center">4 semanas com 48 títulos 30 dias de assinatura</p>
                <button type="button" class="select-plan btn-pill btn-hover-shine btn btn-primary btn-lg" data-plan="diamond">Comprar Plano</button>
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
        <div class="col-md-3 d-grid mb-3" id="card-gold">
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
                <p class="text-center">4 semanas com 24 títulos 30 dias de assinatura</p>
                <button type="button" class="select-plan btn-pill btn-hover-shine btn btn-primary btn-lg" data-plan="gold">Comprar Plano</button>
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
        <div class="col-md-3 d-grid mb-3" id="card-silver">
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
                <p class="text-center">4 semanas com 12 títulos 30 dias de assinatura</p>
                <button type="button" class="select-plan btn-pill btn-hover-shine btn btn-primary btn-lg" data-plan="silver">Comprar Plano</button>
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
        <div class="col-md-3 d-grid mb-3" id="card-bronze">
            <div class="card-shadow-secondary border card card-body border-secondary">
                <h5 class="card-title">Plano Bronze</h5>
				<h5 class="card-title">Pré Cadastro</h5>
                <div class="d-flex align-items-baseline">
                    <small class="opacity-5 pr-1">R$</small>
                    <h3 class="count-up font-weight-semibold" data-value="20.00">Zero</h3>
                    <small class="opacity-5 pl-1"></small>
                </div>

                <div class="divider mt-3"></div>
                <p>Período de 01/12/2024 a 14/01/2025</p>
                <ul>
                    <li>1 Cupom da Sorte Valendo R$75k + R$25k </li>
                    <li>Sorteio dia 15/01/25 ás 19:00 horas</li>
					<li>Leteria Federal</li>
                </ul>
                <button type="button" class="btn-pill btn-hover-shine btn btn-primary btn-lg" data-plan="bronze">Entrar</button>
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
<div class="text-center text-danger opacity-8 mt-3"><b>Observação: Todos os títulos são compartilhados coletivamente entre grupos de 5 participantes.</b></div> <!-- observação -->

<script>
    // Codigo temporario para adesao
    $(document).ready(function() {
        // Abrir modal ao clicar no botão do plano bronze
        $('[data-plan="bronze"]').on('click', function() {
            $('#bronzeModal').modal('show');
        });

        // Avançar para a segunda etapa do modal
        $('#btnNextStep').on('click', function() {
            $('.step1').addClass('d-none');
            $('.step-loading').removeClass('d-none');

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/billing/subscription.php',
                method: 'POST',
                data: {
                    action: 'invoice-info',
                    plan_id: 3
                },
                success: function(response) {
                    // Redireciona para a página
                    window.location.href = "<?= INCLUDE_PATH_AUTH; ?>";

                    $('#bronzeModal').modal('hide');

                    // Volta ao estado original se houver erro
                    $loaderButton.addClass('d-none');
                    $button.removeClass('d-none');
                },
                error: function() {
                    // Exibe mensagens de erro
                    toastr.error('Ocorreu um erro ao selecionar o plano. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Volta ao estado original se houver erro
                    $loaderButton.addClass('d-none');
                    $button.removeClass('d-none');
                }
            });
        });

        // Retornar para a primeira etapa quando o modal for fechado
        $('#bronzeModal').on('hidden.bs.modal', function () {
            // Resetando para a primeira etapa
            $('.step1').removeClass('d-none');
            $('.step2, .step3, .step-loading').addClass('d-none');
        });
    });
</script>

<script>
    // Codigo principal com doacao
    // $(document).ready(function() {
    //     // Abrir modal ao clicar no botão do plano bronze
    //     $('[data-plan="bronze"]').on('click', function() {
    //         $('#bronzeModal').modal('show');
    //     });

    //     // Avançar para a segunda etapa do modal
    //     $('#btnNextStep').on('click', function() {
    //         $('.step1').addClass('d-none');
    //         $('.step2').removeClass('d-none');
    //     });

    //     // Avançar para a terceira etapa do modal
    //     $('#btnNextStep2').on('click', function() {
    //         $('.step2').addClass('d-none');
    //         $('.step3').removeClass('d-none');
    //     });

    //     // Fechar o modal se o usuário cancelar
    //     $('#btnCancelBronze').on('click', function() {
    //         $('#bronzeModal').modal('hide');
    //     });

    //     // Selecionar outro plano
    //     $('.btnSelectOtherPlan').on('click', function() {
    //         $('#bronzeModal').modal('hide');
    //         // Lógica para redirecionar ou selecionar outro plano
    //     });

    //     // Confirmar doação e enviar comprovante
    //     $('#btnConfirmDonation').on('click', function() {
    //         // Após salvar o comprovante, executa o próximo AJAX
    //         var $button = $('#btnConfirmDonation');
    //         var $loaderButton = $('#btnLoaderConfirmBronze');

    //         var comprovante = $('#comprovantePix').prop('files')[0];

    //         if (comprovante) {
    //             var formData = new FormData();
    //             formData.append('comprovante', comprovante);
    //             formData.append('action', 'save-donation-receipt');

    //             $.ajax({
    //                 url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/billing/functions/save-donation-receipt.php', // Endpoint para salvar o comprovante
    //                 method: 'POST',
    //                 data: formData,
    //                 processData: false,
    //                 contentType: false,
    //                 success: function(response) {
    //                     if(response.status == "success") {
    //                         $.ajax({
    //                             url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/billing/subscription.php',
    //                             method: 'POST',
    //                             data: {
    //                                 action: 'invoice-info',
    //                                 plan_id: 3
    //                             },
    //                             success: function(response) {
    //                                 // Redireciona para a página
    //                                 window.location.href = "<?= INCLUDE_PATH_AUTH; ?>";

    //                                 $('#bronzeModal').modal('hide');

    //                                 // Volta ao estado original se houver erro
    //                                 $loaderButton.addClass('d-none');
    //                                 $button.removeClass('d-none');
    //                             },
    //                             error: function() {
    //                                 // Exibe mensagens de erro
    //                                 toastr.error('Ocorreu um erro ao selecionar o plano. Tente novamente.', 'Erro', {
    //                                     closeButton: true,
    //                                     progressBar: true,
    //                                     timeOut: 3000
    //                                 });

    //                                 // Volta ao estado original se houver erro
    //                                 $loaderButton.addClass('d-none');
    //                                 $button.removeClass('d-none');
    //                             }
    //                         });
    //                     } else {
    //                         // Exibe mensagens de erro
    //                         toastr.error(response.message, 'Erro', {
    //                             closeButton: true,
    //                             progressBar: true,
    //                             timeOut: 3000
    //                         });

    //                         // Volta ao estado original se houver erro
    //                         $loaderButton.addClass('d-none');
    //                         $button.removeClass('d-none');
    //                     }
    //                 },
    //                 error: function() {
    //                     toastr.error('Ocorreu um erro ao enviar o comprovante. Tente novamente.', 'Erro', {
    //                         closeButton: true,
    //                         progressBar: true,
    //                         timeOut: 3000
    //                     });

    //                     // Volta ao estado original se houver erro
    //                     $loaderButton.addClass('d-none');
    //                     $button.removeClass('d-none');
    //                 }
    //             });
    //         } else {
    //             toastr.warning('Por favor, envie o comprovante da doação.', 'Atenção', {
    //                 closeButton: true,
    //                 progressBar: true,
    //                 timeOut: 3000
    //             });
    //         }
    //     });

    //     // Retornar para a primeira etapa quando o modal for fechado
    //     $('#bronzeModal').on('hidden.bs.modal', function () {
    //         // Resetando para a primeira etapa
    //         $('.step1').removeClass('d-none');
    //         $('.step2, .step3').addClass('d-none');
    //     });
    // });
</script>

<!-- Salva o plano clicado -->
<script>
    $(document).ready(function() {
        // Quando o botão de seleção de plano for clicado
        $('.select-plan').on('click', function() {
            var plan = $(this).data('plan');
            var $button = $(this);
            var $loaderButton = $button.next('.loader-button');

            // Esconde o botão de compra e mostra o loader
            $button.addClass('d-none');
            $loaderButton.removeClass('d-none');

            // Armazenando a seleção no sessionStorage
            sessionStorage.setItem('chosenPlan', plan);

            // Verifica se a sessão foi criada com sucesso
            if (sessionStorage.getItem('chosenPlan') !== null) {
                // Redireciona para a página de pagamento
                window.location.href = "<?= INCLUDE_PATH_AUTH; ?>detalhes-do-pagamento";

                // Volta ao estado original se houver erro
                $loaderButton.addClass('d-none');
                $button.removeClass('d-none');
            } else {
                // Exibe mensagens de erro
                toastr.error('Ocorreu um erro ao salvar o plano. Por favor, tente novamente.', 'Erro', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 3000
                });

                // Volta ao estado original se houver erro
                $loaderButton.addClass('d-none');
                $button.removeClass('d-none');
            }
        });
    });
</script>