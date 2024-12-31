<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-chat icon-gradient bg-deep-blue"></i>
            </div>
            <div>
                Suporte
                <div class="page-title-subheading">Aqui você pode tirar dúvidas ou entrar em contato com o suporte da <?= $project_name; ?>.</div>
            </div>
        </div>
    </div>
</div>








<div class="text-center mb-5 mt-5">
    <h5 class="menu-header-title text-capitalize mb-3 fsize-3">Suporte RendaCAP Brasil</h5>
</div>

<div class="row mb-5">
    <div class="row">
    <div class="col-lg-7">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <form id="send-quest-form">
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="position-relative form-group">
                                <label for="firstname" class="">Primeiro Nome</label>
                                <input name="firstname" id="firstname" placeholder="Primeiro Nome" type="text" class="form-control" value="<?= $user['firstname']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="position-relative form-group">
                                <label for="lastname" class="">Sobrenome</label>
                                <input name="lastname" id="lastname" placeholder="Sobrenome" type="text" class="form-control" value="<?= $user['lastname']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="position-relative form-group">
                                <label for="email" class="">Email</label>
                                <input name="email" id="email" placeholder="Email" type="email" class="form-control" value="<?= $user['email']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="position-relative form-group">
                                <label for="phone" class="">Celular WhatsApp</label>
                                <input name="phone" id="phone" placeholder="Celular WhatsApp" type="text" class="form-control" value="<?= $user['whatsapp']; ?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="position-relative form-group">
                                <label for="subject" class="">Assunto</label>
                                <input name="subject" id="subject" placeholder="Explique com suas próprias palavras o assunto do contato." type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="position-relative form-group">
                                <label for="message" class="">Mensagem</label>
                                <textarea name="message" id="message" placeholder="Por favor, explique o porquê do seu contato." class="form-control" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="mt-1 btn btn-primary" id="btnSubmit" style="width: 130px;">Enviar Mensagem</button>
                    <button id="btnLoader" class="mt-1 btn btn-primary d-none" style="width: 130px;">
                        <div class="loader">
                            <div class="ball-pulse">
                                <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                                <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                            </div>
                        </div>
                    </button>
                </form>

                <!-- Div para exibir a mensagem de sucesso -->
                <div id="success-message" class="alert alert-success text-center p-4 d-none">
                    <i class="fa fa-check-circle fa-3x mb-3" aria-hidden="true"></i>
                    <p class="fsize-2">Obrigado pelo envio, em até 48 horas o suporte do RendaCAP irá te responder. Caso a dúvida seja urgente, entre em contato com o seu patrocinador ou no grupo do WhatsApp. Obrigado!</p>
                </div>

            </div>
        </div>
    </div>


    <?php
        $user['sponsor_id'] = !empty($user['referrer_id']) 
            ? $user['referrer_id'] 
            : (!empty($user['inviter_id']) ? $user['inviter_id'] : null);

        $sponsor = null;
        if ($user['sponsor_id']) {
            $stmt = $conn->prepare("
                SELECT u.*, a.city, a.state
                FROM tb_users u
                LEFT JOIN tb_address a ON a.user_id = u.id
                WHERE u.id = ?
            ");
            $stmt->execute([$user['sponsor_id']]);

            if ($stmt->rowCount()) {
                $sponsor = $stmt->fetch(PDO::FETCH_ASSOC);
                $sponsor['fullname'] = $sponsor['firstname'] . " " . $sponsor['lastname'];
                $sponsor['surname'] = explode(' ', $sponsor['lastname'])[0];
                $sponsor['shortname'] = $sponsor['firstname'] . " " . $sponsor['surname'];
            }
        }
    ?>

    <div class="col-lg-5">
        <div class="main-card card">
            <div class="card-body">

                <h5 class="card-title">Ainda tem alguma dúvida?</h5>
                <p>Você pode mandar uma mensagem para o suporte do RendaCAP Brasil que iremos responder em até 48 horas, se for urgente entrar em contato com o seu patrocinador.</p>    

                <?php if ($sponsor): ?>
                    <div class="sponsor-info">
                        <h5 class="card-title mt-4">Informações do Patrocinador:</h5>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Nome:</strong> <?= htmlspecialchars($sponsor['shortname']) ?></li>
                            <li><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($sponsor['email']) ?>"><?= htmlspecialchars($sponsor['email']) ?></a></li>
                            <li><strong>Telefone:</strong> <?= htmlspecialchars($sponsor['whatsapp']) ?></li>
                            <li><strong>Localização:</strong> <?= htmlspecialchars($sponsor['city'] . ', ' . $sponsor['state']) ?></li>
                        </ul>
                    </div><br>
                <?php endif; ?>
				
				<h5 class="card-title"><center><b>Ainda tem alguma dúvida?</h5</b></center>
                <p><center>Entrar no grupo do whatsapp do suporte do RendaCAP Brasil</center></p> 
				<center><img src="<?= INCLUDE_PATH_DASHBOARD; ?>files/graduacao/qrcode.png" alt="Sem Imagem" class="mb-2" style="width: 50%; object-fit: contain;"></center>
				<center><a href=https://chat.whatsapp.com/FH9CiSquWtU5nqHEpoKVz2 target=”_blank” class="theme-btn red-color">Clicar no link para entrar no grupo do Suporte RendaCAP Brasil</a></center>
            </div>
        </div>
    </div>

</div>

<!-- Accordion -->
<script>
    $(document).ready(function() {
        // Para todos os botões que controlam o collapse no accordion
        $('#accordion').on('click', 'button', function() {
            // Encontra o ícone dentro do botão clicado
            var icon = $(this).find('i.icon');

            // Alterna entre os ícones de adicionar e remover
            if (icon.hasClass('ion-android-add')) {
                icon.removeClass('ion-android-add').addClass('ion-android-remove');
            } else {
                icon.removeClass('ion-android-remove').addClass('ion-android-add');
            }
        });

        // Para garantir que os ícones dos outros painéis do accordion sejam redefinidos
        $('#accordion').on('hidden.bs.collapse', function() {
            $('#accordion .collapse').each(function() {
                if (!$(this).hasClass('show')) {
                    $(this).prev().find('i.icon').removeClass('ion-android-remove').addClass('ion-android-add');
                }
            });
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#send-quest-form').on('submit', function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            btnSubmit = $("#btnSubmit");
            btnLoader = $("#btnLoader");

            // Desabilitar botão submit e habilitar loader
            btnSubmit.addClass("d-none");
            btnLoader.removeClass("d-none");

            let formData = new FormData(this);
            formData.append('action', 'send-quest-form'); // Adicionado 'action' diretamente ao FormData

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/send-quest-form.php', // URL do arquivo PHP para salvar a senha
                type: 'POST',
                data: formData, // Enviando FormData diretamente
                contentType: false,
                processData: false,
                success: function(response) {
                    if(response.status == "success") {
                        // Reseta o formulario
                        $('#send-quest-form')[0].reset();

                        // Desabilitar loader e habilitar botão submit
                        btnSubmit.removeClass("d-none");
                        btnLoader.addClass("d-none");

                        // Ocultar o formulário e exibir a mensagem de sucesso
                        $('#send-quest-form').addClass('d-none');
                        $('#success-message').removeClass('d-none');
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        // Desabilitar loader e habilitar botão submit
                        btnSubmit.removeClass("d-none");
                        btnLoader.addClass("d-none");
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
                    btnSubmit.removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        });
    });
</script>