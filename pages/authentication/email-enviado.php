<?php
    if (isset($_SESSION['email'])) {
        $email = $_SESSION['email'];
    } else {
        header('Location: ' . INCLUDE_PATH_AUTH . 'login');
    }

    // Opcional: Limpar o e-mail da sessão após o uso
    // unset($_SESSION['email']);
?>
<div class="modal-content">
    <div class="modal-body d-block text-center">
        <i class="fa fa-fw mb-3" style="font-size: 4rem;" aria-hidden="true" title="Copy to use inbox"></i>
        <h4 class="mb-2">Verificação de Email</h4>
        <h6>Identificamos que você está tentando acessar por um novo dispositivo ou localização. Para sua segurança, enviamos um link de confirmação para <span class="font-weight-bolder"><?= $email; ?></span> para que você autorize esse acesso.</h6>
    </div>
    <div class="modal-footer d-block text-center">
        <a href="<?= INCLUDE_PATH_AUTH; ?>login" class="btn-light btn-pill btn-shadow btn-hover-shine btn btn-primary btn-lg" style="width: 200px;">Ir para o Login</a>
    </div>
</div>