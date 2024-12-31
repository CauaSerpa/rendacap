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

<div class="modal-dialog w-100 mx-auto">
    <form id="invoiceInfoForm">
        <div class="modal-content">
            <div class="modal-body">
                <h5 class="modal-title">
                    <h4 class="mt-2">
                        <div>Informações de Pagamento</div>
                        <!-- <span>Leva apenas
                            <span class="text-success">alguns segundos</span> para criar sua conta</span>
                        <?php if (isset($inviter)) { ?>
                        <p class="small mt-2 mb-0">Convidado(a) de <b><?= $inviter_fullname; ?></b></p>
                        <?php } ?> -->
                    </h4>
                </h5>
                <div class="divider row"></div>

                <div class="mb-2 card text-dark card-border bg-light">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="font-weight-semibold mb-0" id="plan-price">R$ 50/mês</h6>
                            <p class="mb-0" id="plan-name">Plano Prata</p>
                        </div>
                        <a href="<?= INCLUDE_PATH_AUTH; ?>planos" class="btn btn-link fsize-1 p-0">Trocar</a>
                    </div>
                </div>

                <div class="card-header card-header-tab-animation mb-4">
                    <ul class="nav nav-justified">
                        <li class="nav-item">
                            <a data-toggle="tab" href="#tab-card" class="nav-link font-weight-semibold active" data-billing-type="card">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="1.5rem"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#000000" d="M512 80c8.8 0 16 7.2 16 16l0 32L48 128l0-32c0-8.8 7.2-16 16-16l448 0zm16 144l0 192c0 8.8-7.2 16-16 16L64 432c-8.8 0-16-7.2-16-16l0-192 480 0zM64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32zm56 304c-13.3 0-24 10.7-24 24s10.7 24 24 24l48 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-48 0zm128 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-112 0z"/></svg>
                                Cartão de Crédito
                            </a>
                        </li>
                        <li class="nav-item">
                            <a data-toggle="tab" href="#tab-pix" class="nav-link font-weight-semibold" data-billing-type="pix">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1.5rem"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#000000" d="M242.4 292.5C247.8 287.1 257.1 287.1 262.5 292.5L339.5 369.5C353.7 383.7 372.6 391.5 392.6 391.5H407.7L310.6 488.6C280.3 518.1 231.1 518.1 200.8 488.6L103.3 391.2H112.6C132.6 391.2 151.5 383.4 165.7 369.2L242.4 292.5zM262.5 218.9C256.1 224.4 247.9 224.5 242.4 218.9L165.7 142.2C151.5 127.1 132.6 120.2 112.6 120.2H103.3L200.7 22.8C231.1-7.6 280.3-7.6 310.6 22.8L407.8 119.9H392.6C372.6 119.9 353.7 127.7 339.5 141.9L262.5 218.9zM112.6 142.7C126.4 142.7 139.1 148.3 149.7 158.1L226.4 234.8C233.6 241.1 243 245.6 252.5 245.6C261.9 245.6 271.3 241.1 278.5 234.8L355.5 157.8C365.3 148.1 378.8 142.5 392.6 142.5H430.3L488.6 200.8C518.9 231.1 518.9 280.3 488.6 310.6L430.3 368.9H392.6C378.8 368.9 365.3 363.3 355.5 353.5L278.5 276.5C264.6 262.6 240.3 262.6 226.4 276.6L149.7 353.2C139.1 363 126.4 368.6 112.6 368.6H80.8L22.8 310.6C-7.6 280.3-7.6 231.1 22.8 200.8L80.8 142.7H112.6z"/></svg>
                                PIX
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <div class="tab-pane active" id="tab-card" role="tabpanel">

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
                    <div class="tab-pane" id="tab-pix" role="tabpanel">

                        <div class="form-row">
                            <div class="col-md-12">
                                <div class="position-relative form-group">
                                    <input name="name" id="name"
                                        placeholder="Nome" type="text" class="form-control" value="<?= $user['shortname']; ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="position-relative form-group">
                                    <input name="cpf" id="cpf"
                                        placeholder="CPF" type="text" class="form-control" value="<?= $user['cpf']; ?>">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="mt-2 mb-3 position-relative form-check">
                    <input name="agree" id="agree" type="checkbox" class="form-check-input">
                    <label for="agree" class="form-check-label">Aceite nossos
                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>termos-e-condicoes">Termos e Condições</a>.
                    </label>
                </div>

            </div>
            <div class="modal-footer d-block text-center">
                <button type="submit" id="btnInvoiceInfo" class="btn-wide btn-pill btn-shadow btn-hover-shine btn btn-primary btn-lg" style="width: 200px;">Pagar</button>
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
</div>

<!-- Máscaras -->
<script>
    // Máscara para Numero do Cartao
    $('#card_number').mask('0000 0000 0000 0000');

    // Máscara para Vencimento do Cartao
    $('#card_expiration').mask('00/00');

    // Máscara para CVV
    $('#card_cvv').mask('0000');

    // Máscara para CPF
    $('input[name="cpf"]').mask('000.000.000-00');
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // Definindo os planos em um array
        var plans = [
            { id: 1, slug: "gold", name: "Ouro", price: 100, cycle: "mês" },
            { id: 2, slug: "silver", name: "Prata", price: 50, cycle: "mês" },
            { id: 3, slug: "bronze", name: "Bronze", price: 0, cycle: "única" },
            { id: 4, slug: "diamond", name: "Diamante", price: 200, cycle: "mês" }
        ];

        // Recuperando o plano selecionado do sessionStorage
        var savedPlan = sessionStorage.getItem('chosenPlan');

        // Verifica se há um plano selecionado
        if (savedPlan) {
            // Encontrando o plano no array de planos
            var plan = plans.find(p => p.slug === savedPlan);

            // Exibindo os detalhes do plano
            if (plan) {
                $('#plan-name').text(`Plano ${plan.name}`);
                $('#plan-price').text(`R$ ${plan.price}/${plan.cycle}`);

                // Adiciona o ID do plano ao formData quando o formulário for enviado
                $("#invoiceInfoForm").submit(function(e) {
                    e.preventDefault(); // Evita o envio tradicional do formulário

                    // Define os botões como variáveis
                    var invoiceInfoButton = $("#btnInvoiceInfo");
                    var loaderButton = $("#btnLoader");

                    // Desabilitar botão submit e habilitar loader
                    invoiceInfoButton.addClass("d-none");
                    loaderButton.removeClass("d-none");

                    // Coleta os dados do formulário
                    var formData = $(this).serialize();

                    // Coleta o tipo de pagamento
                    var method = $('a[data-toggle="tab"].active').data('billing-type');

                    var ajaxData = {
                        action: 'invoice-info',
                        method: method,
                        plan_id: plan.id,
                        params: btoa(formData)
                    };

                    // Faz a requisição AJAX
                    $.ajax({
                        type: "POST",
                        url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/billing/subscription.php",
                        data: ajaxData,
                        dataType: "json", // Espera uma resposta JSON do servidor
                        success: function(response) {
                            if(response.status == "success") {
                                if (method == "card") {
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
                                    }); // ADicionar esse mesmo codigo em pagamento

                                    // Redireciona o usuário após o toastr desaparecer
                                    window.location.href = "<?= INCLUDE_PATH_AUTH; ?>";
                                } else {
                                    // Codificar o subscription ID usando btoa()
                                    var encodedPaymentId = btoa(response.code);

                                    // Armazenando a seleção no sessionStorage
                                    sessionStorage.setItem('paymentId', encodedPaymentId);

                                    // Codificar o subscription ID usando btoa()
                                    var encodedPaymentIdCode = btoa(response.paymentId);

                                    // Armazenando a seleção no sessionStorage
                                    sessionStorage.setItem('paymentIdCode', encodedPaymentIdCode);

                                    // Redireciona o usuário após o toastr desaparecer
                                    window.location.href = "<?= INCLUDE_PATH_AUTH; ?>pagamento";
                                }

                                // Desabilitar loader e habilitar botão submit
                                invoiceInfoButton.removeClass("d-none");
                                loaderButton.addClass("d-none");
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
                });
            } else {
                // Redireciona o usuário para a página de seleção de planos
                window.location.href = "<?= INCLUDE_PATH_AUTH; ?>planos";
            }
        } else {
            // Redireciona o usuário para a página de seleção de planos
            window.location.href = "<?= INCLUDE_PATH_AUTH; ?>planos";
        }
    });
</script>