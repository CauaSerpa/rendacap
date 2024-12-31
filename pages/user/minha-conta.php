<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-user icon-gradient bg-warm-flame"></i>
            </div>
            <div>
                Minha Conta
                <div class="page-title-subheading">Aqui você pode visualizar e atualizar as informações da sua conta de forma prática e segura.</div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav.profile-nav-tabs {
        display: flex;
        flex-direction: column;
    }
    .nav.profile-nav-tabs a {
        height: 2.4rem;
        margin: .1rem 0;
        color: #343a40;
        display: flex;
        align-items: center;
    }
    .nav.profile-nav-tabs a:hover {
        background: #e0f3ff;
    }
    .nav.profile-nav-tabs a.active {
        color: #3f6ad8 !important;
        background: #e0f3ff !important;
        border-color: #e0f3ff !important;
        font-weight: bold;
    }

    .image-preview img {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        object-fit: cover;
    }
    .image-preview .camera-icon {
        position: absolute;
        right: 5px;
        bottom: 5px;
        width: 40px;
        height: 40px;
        border: 3px solid #fff;
        border-radius: 50%;
        background: blue;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .image-preview .camera-icon i.lnr-camera {
        font-size: 1.25rem;
        margin-left: 0.1rem;
        color: #fff;
    }
    @media only screen and (max-width: 600px) {
        #image {
            flex-direction: column;
            align-items: center;
        }
        .image-preview {
            margin-bottom: 1rem;
            width: max-content;
        }
        .image-preview img {
            width: 125px;
            height: 125px;
        }
    }
</style>

<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="nav profile-nav-tabs">
                    <a data-toggle="tab" href="#tab-animated-0" class="border-0 btn-transition btn-icon btn btn-transparent active" id="perfil-tab">
                        <i class="lnr-user btn-icon-wrapper"></i>
                        Perfil
                    </a>
                    <a data-toggle="tab" href="#tab-animated-1" class="border-0 btn-transition btn-icon btn btn-transparent" id="documentos-tab">
                        <i class="pe-7s-id btn-icon-wrapper"></i>
                        Documentos
                    </a>
                    <a data-toggle="tab" href="#tab-animated-2" class="border-0 btn-transition btn-icon btn btn-transparent" id="assinatura-tab">
                        <i class="pe-7s-wallet btn-icon-wrapper"></i>
                        Assinatura
                    </a>
                    <a data-toggle="tab" href="#tab-animated-3" class="border-0 btn-transition btn-icon btn btn-transparent" id="seguranca-tab">
                        <i class="lnr-lock btn-icon-wrapper"></i>
                        Segurança
                    </a>
                    <a data-toggle="tab" href="#tab-animated-4" class="border-0 btn-transition btn-icon btn btn-transparent text-danger mt-3" id="excluir-conta-tab">
                        <i class="lnr-trash btn-icon-wrapper"></i>
                        Excluir Conta
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="tab-content">
            <div class="tab-pane active" id="tab-animated-0" role="tabpanel">
                <div class="mb-3 card">
                    <div class="card-body">
                        <form id="profile-update-form" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="d-flex mb-5" id="image">
                                        <div class="image-preview position-relative mr-3">
                                            <img id="image-preview" src="<?= $user['avatar']; ?>" alt="Central Image" class="your-img img-fluid">
                                            <span class="camera-icon"><i class="lnr-camera"></i></span>
                                        </div>
                                        <div class="d-flex align-items-center ml-3">
                                            <input type="file" name="profile_image" id="profile_image" accept="image/*" class="d-none">
                                            <button type="button" class="btn btn-shadow btn-primary btn-lg mr-2" id="upload-btn">Carregar Nova Foto</button>
                                            <button type="button" class="btn btn-shadow btn-light btn-lg" id="delete-btn">Excluir Foto</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="firstname" class="">Primeiro Nome</label>
                                        <input name="firstname" id="firstname" placeholder="Primeiro Nome" type="text" class="form-control" value="<?= $user['firstname']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="lastname" class="">Sobrenome</label>
                                        <input name="lastname" id="lastname" placeholder="Sobrenome" type="text" class="form-control" value="<?= $user['lastname']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="phone" class="">Celular WhatsApp</label>
                                        <input name="phone" id="phone" placeholder="Celular WhatsApp" type="text" class="form-control" value="<?= $user['whatsapp']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="cpf" class="">CPF</label>
                                        <input name="cpf" id="cpf" placeholder="CPF" type="text" class="form-control" value="<?= $user['cpf']; ?>" readonly required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="birth_date" class="">Data de Aniversário</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend datepicker-trigger">
                                                <div class="input-group-text">
                                                    <i class="fa fa-calendar-alt"></i>
                                                </div>
                                            </div>
                                            <input name="birth_date" id="birth_date"
                                                placeholder="dd/mm/aaaa" type="text" class="form-control" data-toggle="datepicker-icon-year"
                                                autocomplete="off" value="<?= $user['birth_date']; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="rg" class="">RG</label>
                                        <input name="rg" id="rg" placeholder="RG" type="text" class="form-control" value="<?= $user['rg']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="pix_key_type" class="">Tipo de Chave Pix</label>
                                        <select name="pix_key_type" id="pix_key_type" class="form-control" required>
                                            <option value="">Selecione</option>
                                            <option value="CPF" <?= $user['pix_key_type'] === 'CPF' ? 'selected' : ''; ?>>CPF</option>
                                            <option value="CNPJ" <?= $user['pix_key_type'] === 'CNPJ' ? 'selected' : ''; ?>>CNPJ</option>
                                            <option value="Telefone" <?= $user['pix_key_type'] === 'Telefone' ? 'selected' : ''; ?>>Telefone</option>
                                            <option value="E-mail" <?= $user['pix_key_type'] === 'E-mail' ? 'selected' : ''; ?>>E-mail</option>
                                            <option value="Aleatória" <?= $user['pix_key_type'] === 'Aleatória' ? 'selected' : ''; ?>>Aleatória</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="pix_key" class="">Chave Pix</label>
                                        <input name="pix_key" id="pix_key" placeholder="Chave Pix" type="text" class="form-control" value="<?= $user['pix_key']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="position-relative form-group">
                                        <label for="birth_date" class="">Link de Convite</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="clipboard-token" value="<?= $user['invite_link']; ?>" readonly required>
                                            <div class="input-group-append">
                                                <button type="button" data-clipboard-target="#clipboard-token" class="btn-icon btn btn-primary clipboard-trigger">
                                                    <i class="pe-7s-copy-file btn-icon-wrapper"></i>
                                                    Copiar Link
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="mt-1 btn btn-primary" id="btnUpdProfile" style="width: 125px;">Salvar Alterações</button>
                            <button id="btnUpdProfileLoader" class="mt-1 btn btn-primary d-none" style="width: 125px;">
                                <div class="loader">
                                    <div class="ball-pulse">
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                    </div>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-animated-1" role="tabpanel">
                <div class="mb-3 card">
                    <div class="card-body">
                        <form id="document-upload-form" enctype="multipart/form-data">
                            <h5 class="card-title">Enviar Documentos</h5>
                            <div class="form-row">
                                <!-- Upload de Identidade -->
                                <div class="col-md-12 mb-3">
                                    <div class="position-relative form-group">
                                        <label for="identity_document" class="">Upload Identidade (Frente e Verso)</label>
                                        <input type="file" name="identity_document[]" id="identity_document" accept="image/*" class="form-control-file" multiple disabled>
                                    </div>
                                </div>
                                
                                <!-- Upload de RG -->
                                <div class="col-md-12 mb-3">
                                    <div class="position-relative form-group">
                                        <label for="rg_document" class="">Upload RG</label>
                                        <input type="file" name="rg_document" id="rg_document" accept="image/*" class="form-control-file" disabled>
                                    </div>
                                </div>

                                <!-- Upload de CPF -->
                                <div class="col-md-12 mb-3">
                                    <div class="position-relative form-group">
                                        <label for="cpf_document" class="">Upload CPF</label>
                                        <input type="file" name="cpf_document" id="cpf_document" accept="image/*" class="form-control-file" disabled>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="mt-1 btn btn-primary disabled" id="btnUploadDocuments" style="width: 150px;" disabled>Enviar Documentos</button>
                            <button id="btnUploadDocumentsLoader" class="mt-1 btn btn-primary d-none" style="width: 150px;">
                                <div class="loader">
                                    <div class="ball-pulse">
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                    </div>
                                </div>
                            </button>
                            <small class="form-text text-muted">
                                A imagem deve ser clara e legível, no formato JPG, PNG ou PDF, e o tamanho máximo é de 2MB.
                            </small>
                        </form>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-animated-2" role="tabpanel">
                <div class="mb-3 card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <h5 class="card-title">Informações do Seu Plano</h5>
                                <small class="form-text text-muted">Plano atual</small>
                                <h3 class="font-weight-bold mb-2"><?= $user['plan_name']; ?></h3>
                                <p class="fsize-1 font-weight-semibold mb-2 <?= ($user['plan_id'] == 3) ? "d-none" : ""; ?>"><?= $user['plan_description']; ?></p>
                                <?php if ($user['plan_id'] == 3) : ?>
                                    <small class="form-text text-muted">Sua próxima cobrança está agendada para <?= $user['due_date']; ?>. Para continuar usufruindo dos seus benefícios no próximo mês, é necessário selecionar um plano pago.</small>
                                <?php else : ?>
                                    <small class="form-text text-muted">Próxima cobrança: <?= $user['subs_price']; ?> em <?= $user['due_date']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 text-right">
                                <button class="btn btn-shadow btn-primary btn-lg mb-2" style="width: 250px;" data-toggle="modal" data-target="#changePlanModal">Alterar Plano</button>
                                <button class="btn btn-shadow btn-light btn-lg" style="width: 250px;" data-toggle="modal" data-target="#cancelSubscriptionModal" disabled>Cancelar Assinatura</button>
                            </div>
                        </div>
                        <div class="divider <?= ($user['plan_id'] == 3) ? "d-none" : ""; ?>"></div>
                        <div class="row <?= ($user['plan_id'] == 3) ? "d-none" : ""; ?>">
                            <div class="col-md-9 d-flex align-items-center justify-content-between">
                                <div class="left">
                                    <small class="form-text text-muted">Método de Pagamento</small>

                                    <div class="card-info d-flex align-items-center">
                                        <?= cardFlagSVG($user); ?>
                                    </div>
                                </div>
                                <!-- <div class="right d-flex align-items-center">
                                    <div class="btn btn-link fsize-1" data-toggle="modal" data-target="#removeCardModal">Remover</div>
                                </div> -->
                            </div>
                            <div class="col-md-3 d-flex align-items-center justify-content-end">
                                <button class="btn btn-shadow btn-light btn-lg" style="width: 250px;" data-toggle="modal" data-target="#updateCardModal" disabled>Alterar Cartão</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 card">
                    <div class="card-body">
                        <h5 class="card-title">Histórico de Pagamentos</h5>

                        <?php
                            // Query para buscar dados de pagamento
                            $stmt = $conn->prepare("
                                SELECT o.id, o.cpf, o.card_last_digits, o.total_amount AS price, o.plan_id, pl.name AS plan_name, o.order_date
                                FROM tb_orders o
                                JOIN tb_plans pl ON o.plan_id = pl.id
                                WHERE o.user_id = ?
                                ORDER BY o.created_at DESC
                            ");
                            // O parâmetro deve ser passado como array
                            $stmt->execute([$_SESSION['user_id']]);
                            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <div class="table-responsive">
                            <table id="billingHistory" class="mb-0 table table-borderless w-100">
                                <thead>
                                    <tr>
                                        <th>ID da Transação</th>
                                        <th>CPF</th>
                                        <th>Método de Pagamento</th>
                                        <th>Preço</th>
                                        <th>Plano</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($payments)): ?>
                                        <?php foreach ($payments as $payment): ?>
                                            <?php
                                                if (isset($payment['card_last_digits'])) {
                                                    $payment['method'] = "Crédito (Final ". $payment['card_last_digits'] . ")";
                                                } else if ($payment['plan_id'] == 3) {
                                                    $payment['method'] = "Plano Grátis";
                                                } else {
                                                    $payment['method'] = "PIX";
                                                }
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($payment['id']); ?></td>
                                                <td><?= formatOcultCpf($payment['cpf']) ?></td>
                                                <td><?= htmlspecialchars($payment['method']); ?></td>
                                                <td><?= ($payment['price'] != 0.00) ? formatToBRL($payment['price']) : "Grátis"; ?></td>
                                                <td><?= htmlspecialchars($payment['plan_name']); ?></td>
                                                <td><?= date('d/m/Y', strtotime($payment['order_date'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4">Nenhum pagamento encontrado</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-animated-3" role="tabpanel">
                <div class="mb-3 card">
                    <div class="card-body">
                        <form id="verify-email-form" class="mb-5">
                            <h5 class="card-title">Validar Email</h5>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="email" class="">Email Atual</label>
                                        <input name="email" id="email"
                                            placeholder="Email Atual" type="email" class="form-control" value="<?= $user['email']; ?>" <?= ($user['active_email'] == 1) ? "readonly" : ""; ?>>
                                    </div>
                                    <?php if($user['active_email'] !== 1) : ?>
                                    <button type="submit" class="mt-1 btn btn-primary" id="btnSendEmail" style="width: 97px;">Enviar Email</button>
                                    <button id="btnSendEmailLoader" class="mt-1 btn btn-primary d-none" style="width: 97px;">
                                        <div class="loader">
                                            <div class="ball-pulse">
                                                <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                                <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                                <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                            </div>
                                        </div>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                        <form id="password-form" class="mb-5">
                            <h5 class="card-title">Senha Para Saques</h5>

                            <div class="form-row">
                                <div class="col-md-4">
                                    <!-- Tooltip explicativo sobre a senha -->
                                    <div class="form-group">
                                        <label for="password" class="d-flex align-items-center">
                                            Senha
                                            <span class="ml-2" data-toggle="tooltip" data-placement="right" 
                                                title="Essa senha será necessária para confirmar futuras operações de saque. Ela garante maior segurança nas transações.">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                        </label>
                                        <input name="password" id="password" placeholder="0 - 0 - 0 - 0 - 0 - 0" 
                                            type="password" class="form-control" required maxlength="6" pattern="\d{6}" 
                                            title="A senha deve conter exatamente 6 números." 
                                            value="<?= (!empty($user['transaction_password'])) ? "000000" : ""; ?>" <?= (!empty($user['transaction_password'])) ? "readonly" : ""; ?>>
                                        <em id="password-error" class="error invalid-feedback">Por favor, insira uma senha de 6 números.</em>
                                    </div>
                                </div>
                            </div>

                            <?php if(empty($user['transaction_password'])) : ?>
                            
                            <div class="form-row">
                                <div class="col-md-4">
                                    <!-- Campo para confirmar a senha -->
                                    <div class="form-group">
                                        <label for="confirm-password">Confirmar Senha</label>
                                        <input name="confirm-password" id="confirm-password" placeholder="0 - 0 - 0 - 0 - 0 - 0" 
                                            type="password" class="form-control" required maxlength="6" pattern="\d{6}" 
                                            title="A confirmação deve corresponder à senha de 6 números.">
                                        <em id="confirm-password-error" class="error invalid-feedback">As senhas não correspondem.</em>
                                    </div>
                                </div>
                            </div>

                            <!-- Botão de envio -->
                            <button type="submit" class="mt-1 btn btn-primary" id="btnSubmit" style="width: 121px;">Cadastrar Senha</button>

                            <!-- Loader ao enviar o formulário -->
                            <button id="btnLoader" class="mt-1 btn btn-primary d-none" style="width: 121px;" disabled>
                                <div class="loader">
                                    <div class="ball-pulse">
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                    </div>
                                </div>
                            </button>

                            <?php endif; ?>

                        </form>
                        <form id="password-change-form" class="mb-5">
                            <h5 class="card-title">Alterar Senha</h5>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="current_password" class="">Senha Atual</label>
                                        <input name="current_password" id="current_password"
                                            placeholder="Senha Atual" type="password" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="new_password" class="">Nova Senha</label>
                                        <input name="new_password" id="new_password"
                                            placeholder="Nova Senha" type="password" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group mb-0">
                                        <label for="confirm_password" class="">Confirme a Nova Senha</label>
                                        <input name="confirm_password" id="confirm_password"
                                            placeholder="Confirme a Nova Senha" type="password" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-1 mb-3 position-relative form-check">
                                <input name="check" id="showPasswordToggle" type="checkbox" class="form-check-input">
                                <label for="showPasswordToggle" class="form-check-label">Exibir Senha</label>
                            </div>
                            <button type="submit" class="mt-1 btn btn-primary" id="btnUpdPassword" style="width: 125px;">Salvar Alterações</button>
                            <button id="btnUpdPasswordLoader" class="mt-1 btn btn-primary d-none" style="width: 125px;">
                                <div class="loader">
                                    <div class="ball-pulse">
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                        <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                    </div>
                                </div>
                            </button>
                        </form>
                        <style>
                            .toggle.btn-sm {
                                min-width: 59px;
                            }
                        </style>
                        <form>
                            <h5 class="card-title">Autenticação 2FA</h5>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group mb-0">
                                        <label for="current_email" class="">Ativo?</label>
                                        <div class="form-check p-0">
                                            <input type="checkbox" data-toggle="toggle" data-size="small" id="change-2fa"
                                                data-on="Sim" data-off="Não" data-onstyle="success" data-offstyle="danger" <?= ($user['2fa'] == 1) ? "checked" : "" ?>>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-animated-4" role="tabpanel">
                <div class="mb-3 card">
                    <div class="card-body">
                        <h5 class="card-title text-danger mb-1">Excluindo Conta</h5>
                        <p class="card-subtitle small text-danger mb-3">Por favor, tenha atenção ao acessar esta área!</p>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card-border card card-body border-danger widget-chart text-left align-items-start">
                                        <div class="icon-wrapper rounded-circle">
                                            <div class="icon-wrapper-bg bg-danger"></div>
                                            <i class="lnr-warning text-danger mb-1"></i>
                                        </div>
                                        <div class="widget-chart-content">
                                            <div class="widget-heading mb-3">Excluir sua conta removerá permanentemente todos os seus dados, incluindo a conta, redes e próximos saques. Esta ação é irreversível e você não poderá mais visualizar contas que faziam parte da sua rede.</div>
                                            <div class="widget-heading mb-3">Após clicar em "Deletar Minha Conta", enviaremos um e-mail de confirmação.</div>
                                            <div class="mb-3">
                                                <button class="btn-transition btn btn-outline-danger d-flex">Deletar Minha Conta</button>
                                            </div>
                                            <div class="widget-heading mb-3"><span class="font-weight-bolder">Observação:</span> Para excluir sua conta, acesse a seção  <a href="#" class="text-muted" target="_blank">Ajuda</a> > <a href="#" class="text-muted" target="_blank">Minha Conta</a> > <a href="#" class="text-muted" target="_blank">Deletar Minha Conta.</a></div>
                                            <div class="widget-heading">Etapas adicionais são necessárias para confirmar sua propriedade da conta e informar sobre os tipos de dados que serão apagados durante o processo.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Definindo um mapeamento entre as abas e as URLs
        const tabMap = {
            "tab-animated-0": "perfil",
            "tab-animated-1": "documentos",
            "tab-animated-2": "assinatura",
            "tab-animated-3": "seguranca",
            "tab-animated-4": "excluir-conta"
        };

        // Função para alterar a URL sem recarregar a página
        function changeUrl(tabId) {
            const url = "<?= INCLUDE_PATH_DASHBOARD; ?>";
            const baseUrl = url + "minha-conta/";
            const newUrl = baseUrl + tabMap[tabId];
            history.pushState(null, null, newUrl);  // Atualiza a URL
        }

        // Escutando o clique em cada aba
        document.querySelectorAll('.nav a[data-toggle="tab"]').forEach(function(tabLink) {
            tabLink.addEventListener('click', function(e) {
                const targetTabId = this.getAttribute('href').substring(1); // Obtém o ID da tab
                changeUrl(targetTabId);
            });
        });

        // Ativa a aba correta se a página for carregada com uma URL específica
        const currentUrl = window.location.pathname.split("/").pop();
        const matchingTabId = Object.keys(tabMap).find(key => tabMap[key] === currentUrl);

        if (matchingTabId) {
            const matchingTab = document.querySelector(`a[href="#${matchingTabId}"]`);
            if (matchingTab) {
                matchingTab.click(); // Ativa a aba correspondente
            }
        }
    });
</script>

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
            $('#current_password, #new_password, #confirm_password').attr('type', type);
        });
    });
</script>

<!-- Password -->
<script type="text/javascript">
    $(document).ready(function() {
        // Inicializa o tooltip
        $('[data-toggle="tooltip"]').tooltip();

        // Esconde as mensagens de erro inicialmente
        $('#password-error').hide();
        $('#confirm-password-error').hide();

        // Valida o formulário ao enviar
        $('#password-form').on('submit', function(event) {
            event.preventDefault(); // Impede o envio tradicional do formulário

            var password = $('#password').val();
            var confirmPassword = $('#confirm-password').val();
            var formValid = true;

            // Verifica se a senha contém exatamente 6 dígitos
            if (!/^\d{6}$/.test(password)) {
                $('#password-error').show();
                formValid = false;
            } else {
                $('#password-error').hide();
            }

            // Verifica se as senhas correspondem
            if (password !== confirmPassword) {
                $('#confirm-password-error').show();
                formValid = false;
            } else {
                $('#confirm-password-error').hide();
            }

            // Se o formulário estiver válido, faz o envio via AJAX
            if (formValid) {
                $('#btnSubmit').addClass('d-none');
                $('#btnLoader').removeClass('d-none');

                let formData = new FormData(this);
                formData.append('action', 'create-transaction-password'); // Adicionado 'action' diretamente ao FormData

                $.ajax({
                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/create-transaction-password.php', // URL do arquivo PHP para salvar a senha
                    type: 'POST',
                    data: formData, // Enviando FormData diretamente
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if(response.status == "success") {
                            // Exibe mensagens de sucesso
                            toastr.success(response.message, 'Sucesso', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 3000
                            });

                            $('#btnSubmit').removeClass('d-none');
                            $('#btnLoader').addClass('d-none');
                        } else {
                            // Exibe mensagens de sucesso
                            toastr.success(response.message, 'Erro', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 3000
                            });

                            $('#btnSubmit').removeClass('d-none');
                            $('#btnLoader').addClass('d-none');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        $('#btnSubmit').removeClass('d-none');
                        $('#btnLoader').addClass('d-none');
                    }
                });
            }
        });
    });
</script>

<!-- Update Profile -->
<script type="text/javascript">
    $(document).ready(function() {
        // Preview da imagem
        $('#upload-btn').click(function() {
            $('#profile_image').click();
        });

        $('#profile_image').change(function(e) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result); // Corrigido o ID para 'image-preview'
            };
            reader.readAsDataURL(this.files[0]);
        });

        $('#delete-btn').click(function() {
            $('#profile_image').val(''); // Corrigido para limpar o valor do input
            $('#image-preview').attr('src', '<?= INCLUDE_PATH_DASHBOARD; ?>files/profile/avatar/no-image.svg'); // Corrigido o ID para 'image-preview'
        });

        // Envio do formulário via AJAX
        $('#profile-update-form').submit(function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            btnUpdProfile = $("#btnUpdProfile");
            btnUpdProfileLoader = $("#btnUpdProfileLoader");

            // Desabilitar botão submit e habilitar loader
            btnUpdProfile.addClass("d-none");
            btnUpdProfileLoader.removeClass("d-none");

            let formData = new FormData(this);
            formData.append('action', 'update-profile'); // Adicionado 'action' diretamente ao FormData

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/update-profile.php',
                type: 'POST',
                data: formData, // Enviando FormData diretamente
                contentType: false,
                processData: false,
                success: function(response) {
                    if(response.status == "success") {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Sucesso', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        // Desabilitar loader e habilitar botão submit
                        btnUpdProfile.removeClass("d-none");
                        btnUpdProfileLoader.addClass("d-none");
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        // Desabilitar loader e habilitar botão submit
                        btnUpdProfile.removeClass("d-none");
                        btnUpdProfileLoader.addClass("d-none");
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
                    btnUpdProfile.removeClass("d-none");
                    btnUpdProfileLoader.addClass("d-none");
                }
            });
        });
    });
</script>

<!-- Upload Documents -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#document-upload-form').on('submit', function(e) {
            e.preventDefault(); // Impede o envio padrão do formulário

            var formData = new FormData(this); // Cria o FormData com os dados do formulário
            formData.append('action', 'upload-documents'); // Adicionado 'action' diretamente ao FormData

            // Define os botões como variáveis
            let btnUploadDocuments = $("#btnUploadDocuments");
            let btnUploadDocumentsLoader = $("#btnUploadDocumentsLoader");

            // Desabilitar botão submit e habilitar loader
            btnUploadDocuments.addClass("d-none");
            btnUploadDocumentsLoader.removeClass("d-none");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/upload-documents.php', // Arquivo PHP que processará o upload
                type: 'POST',
                data: formData,
                contentType: false, // Necessário para o envio de arquivos
                processData: false, // Necessário para o envio de arquivos
                success: function(response) {
                    if(response.status == "success") {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Sucesso', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    } else {
                        // Exibe mensagens de erro
                        $.each(response.message, function(key, value) {
                            toastr.error(value, 'Erro', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 3000
                            });
                        });
                    }

                    // Habilitar botão submit e desabilitar loader
                    btnUploadDocuments.removeClass("d-none");
                    btnUploadDocumentsLoader.addClass("d-none");
                },
                error: function(xhr, status, error) {
                    // Habilitar botão submit e desabilitar loader
                    btnUploadDocuments.removeClass("d-none");
                    btnUploadDocumentsLoader.addClass("d-none");
                }
            });
        });
    });
</script>

<!-- Update Password -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#verify-email-form').submit(function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            let btnSendEmail = $("#btnSendEmail");
            let btnSendEmailLoader = $("#btnSendEmailLoader");

            // Desabilitar botão submit e habilitar loader
            btnSendEmail.addClass("d-none");
            btnSendEmailLoader.removeClass("d-none");

            let formData = new FormData(this);
            formData.append('action', 'send-verify-email'); // Adiciona 'action' diretamente ao FormData

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/verify-email.php',
                type: 'POST',
                data: formData,
                processData: false, // Necessário para enviar FormData
                contentType: false, // Necessário para enviar FormData
                success: function(response) {
                    if(response.status == "success") {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Sucesso', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }

                    // Habilitar botão submit e desabilitar loader
                    btnSendEmail.removeClass("d-none");
                    btnSendEmailLoader.addClass("d-none");
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Habilitar botão submit e desabilitar loader
                    btnSendEmail.removeClass("d-none");
                    btnSendEmailLoader.addClass("d-none");
                }
            });
        });
    });
</script>

<!-- Update Password -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#password-change-form').submit(function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            let btnUpdPassword = $("#btnUpdPassword");
            let btnUpdPasswordLoader = $("#btnUpdPasswordLoader");

            // Desabilitar botão submit e habilitar loader
            btnUpdPassword.addClass("d-none");
            btnUpdPasswordLoader.removeClass("d-none");

            let formData = new FormData(this);
            formData.append('action', 'change-password'); // Adiciona 'action' diretamente ao FormData

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/change-password.php',
                type: 'POST',
                data: formData,
                processData: false, // Necessário para enviar FormData
                contentType: false, // Necessário para enviar FormData
                success: function(response) {
                    if(response.status == "success") {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Sucesso', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }

                    // Habilitar botão submit e desabilitar loader
                    btnUpdPassword.removeClass("d-none");
                    btnUpdPasswordLoader.addClass("d-none");
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Habilitar botão submit e desabilitar loader
                    btnUpdPassword.removeClass("d-none");
                    btnUpdPasswordLoader.addClass("d-none");
                }
            });
        });
    });
</script>

<!-- Change 2FA -->
<script type="text/javascript">
    $(document).ready(function() {
        $('input[type="checkbox"]#change-2fa').change(function() {
            let is2FAEnabled = $(this).prop('checked') ? 1 : 0;

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/change-2fa.php',
                type: 'POST',
                data: { 
                    action: 'change-2fa',
                    is_2fa_enabled: is2FAEnabled 
                },
                success: function(response) {
                    if(response.status == "success") {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Sucesso', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
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

<!-- Historico de pagamentos -->
<script>
    $(document).ready(() => {
        // Verifica a quantidade de registros na tabela (excluindo a linha do cabeçalho)
        const rowCount = $("#billingHistory tbody tr").length;

        // Se houver 10 ou mais registros, inicializa o DataTable
        if (rowCount >= 10) {
            $("#billingHistory").DataTable({
                ordering: false,
                language: {
                    "sProcessing": "Processando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "Nenhum registro encontrado",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                    "sInfoPostFix": "",
                    "sSearch": "Pesquisar:",
                    "sUrl": "",
                    "sInfoThousands": ".",
                    "sLoadingRecords": "Carregando...",
                    "oPaginate": {
                        "sFirst": "Primeiro",
                        "sPrevious": "Anterior",
                        "sNext": "Próximo",
                        "sLast": "Último"
                    },
                    "oAria": {
                        "sSortAscending": ": ativar para classificar a coluna em ordem crescente",
                        "sSortDescending": ": ativar para classificar a coluna em ordem decrescente"
                    }
                }
            });
        }
    });
</script>

<!-- Alterar Plano -->
<!-- <script>
    $(document).ready(function() {
        // Captura o clique nos botões de selecionar plano
        $('.select-plan').on('click', function() {
            // Obtém o valor do plano selecionado a partir do atributo data-plan
            var selectedPlan = $(this).data('plan');
            var $button = $(this);
            var $loader = $button.siblings('.loader-button');

            // Oculta o botão e mostra o loader durante a requisição
            $button.addClass('d-none');
            $loader.removeClass('d-none');

            // Coleta os dados do formulário
            var formData = $(this).serialize();

            // Coleta o tipo de pagamento
            var method = $('a[data-toggle="tab"].active').data('billing-type');

            var ajaxData = {
                action: 'update-plan',
                plan: selectedPlan
            };

            // Envia uma requisição AJAX para o servidor com o plano selecionado
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/billing/functions/update/update-subscription.php', // Substitua pelo endpoint correto
                method: 'POST',
                data: ajaxData,
                dataType: "json", // Espera uma resposta JSON do servidor
                success: function(response) {
                    // Requisição bem-sucedida
                    console.log('Plano alterado com sucesso!', response);
                    // alert('Plano ' + selectedPlan + ' selecionado com sucesso!');
                    if(response.status == "success") {
                        location.reload(); // Faz um refresh na página
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }

                    // Oculta o loader e volta a exibir o botão
                    $loader.addClass('d-none');
                    $button.removeClass('d-none');
                },
                error: function(xhr, status, error) {
                    // Em caso de erro
                    console.log('Erro ao alterar o plano:', error);
                    alert('Ocorreu um erro ao selecionar o plano.');

                    // Oculta o loader e volta a exibir o botão
                    $loader.addClass('d-none');
                    $button.removeClass('d-none');
                }
            });
        });
    });
</script> -->

<!-- Alterar Cartão de Crédito -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#update-card-form').submit(function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            let btnUpdCard = $("#btnUpdCard");
            let btnUpdCardLoader = $("#btnUpdCardLoader");

            // Desabilitar botão submit e habilitar loader
            btnUpdCard.addClass("d-none");
            btnUpdCardLoader.removeClass("d-none");

            // Coleta os dados do formulário
            var formData = $(this).serialize();

            // Coleta o tipo de pagamento
            var method = $('a[data-toggle="tab"].active').data('billing-type');

            var ajaxData = {
                action: 'update-card',
                params: btoa(formData)
            };

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/billing/functions/update/update-subscription.php',
                type: 'POST',
                data: ajaxData,
                dataType: "json", // Espera uma resposta JSON do servidor
                success: function(response) {
                    if(response.status == "success") {
                        location.reload(); // Faz um refresh na página
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }

                    // Habilitar botão submit e desabilitar loader
                    btnUpdCard.removeClass("d-none");
                    btnUpdCardLoader.addClass("d-none");
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Habilitar botão submit e desabilitar loader
                    btnUpdCard.removeClass("d-none");
                    btnUpdCardLoader.addClass("d-none");
                }
            });
        });
    });
</script>

<!-- Cancelar Assinatura -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#btnCancelSubscription').on('click', function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            let btnCancelSubscription = $("#btnCancelSubscription");
            let btnCancelSubscriptionLoader = $("#btnCancelSubscriptionLoader");

            // Desabilitar botão submit e habilitar loader
            btnCancelSubscription.addClass("d-none");
            btnCancelSubscriptionLoader.removeClass("d-none");

            // Coleta os dados do formulário
            var formData = $(this).serialize();

            // Coleta o tipo de pagamento
            var method = $('a[data-toggle="tab"].active').data('billing-type');

            var ajaxData = {
                action: 'cancel-subscription'
            };

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/billing/functions/update/cancel-subscription.php',
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    if(response.status == "success") {
                        location.reload(); // Faz um refresh na página
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }

                    // Habilitar botão submit e desabilitar loader
                    btnCancelSubscription.removeClass("d-none");
                    btnCancelSubscriptionLoader.addClass("d-none");
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Habilitar botão submit e desabilitar loader
                    btnCancelSubscription.removeClass("d-none");
                    btnCancelSubscriptionLoader.addClass("d-none");
                }
            });
        });
    });
</script>