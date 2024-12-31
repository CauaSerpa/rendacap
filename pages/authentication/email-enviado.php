<?php
    if (isset($_SESSION['email']) || isset($_SESSION['user_email_signature'])) {
        $email = isset($_SESSION['email']) ? $_SESSION['email'] : $_SESSION['user_email_signature'];
    } else {
        // Define a mensagem de erro para o usuário que já validou o email
        $message = array(
            'status' => 'error',
            'title' => 'Email já verificado',
            'message' => 'Você já verificou seu email.'
        );
        $_SESSION['msg'] = $message;

        // Redireciona o usuário para o login
        header("Location: " . INCLUDE_PATH_AUTH . "login");
        exit();
    }
?>

<div class="modal-dialog w-100 mx-auto">
    <div class="modal-content">
        <div class="modal-body text-center">
            <i class="fa fa-envelope mb-3" style="font-size: 4rem;" aria-hidden="true"></i>
            <h4 class="mb-2">Validação de Email Necessária</h4>
            <h6>
                Para continuar, confirme seu endereço de email. 
                Enviamos um link de validação para 
                <span class="font-weight-bold"><?= htmlspecialchars($email); ?></span>. 
                Por favor, verifique sua caixa de entrada (e a pasta de spam, se necessário) e clique no link para validar seu email.
            </h6>
        </div>
        <!-- <div class="modal-footer text-center">
            <a href="<?= INCLUDE_PATH_AUTH; ?>reenviar-email" class="btn btn-primary btn-pill btn-shadow btn-hover-shine btn-lg" style="width: 200px;">
                Reenviar Email
            </a>
        </div> -->
    </div>
</div>