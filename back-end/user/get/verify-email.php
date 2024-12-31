<?php
    // Verifica se o token foi passado na URL
    if (isset($token) && !empty($token)) {

        // Consulta para verificar se o token existe no banco de dados
        $stmt = $conn->prepare("SELECT id, email, new_email FROM tb_users WHERE active_token = ? AND active_email = 0");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (!is_null($user['new_email']) && $user['email'] !== $user['new_email']) {
                // Se o token for válido, atualize o campo active_email
                $updateStmt = $conn->prepare("UPDATE tb_users SET email = ?, new_email = NULL, active_email = 1, active_token = NULL, 2fa = 1 WHERE id = ?");
                $updateStmt->execute([$user['new_email'], $user['id']]);
            } else {
                // Se o token for válido, atualize o campo active_email
                $updateStmt = $conn->prepare("UPDATE tb_users SET active_email = 1, active_token = NULL, 2fa = 1 WHERE id = ?");
                $updateStmt->execute([$user['id']]);
            }

            if ($updateStmt->rowCount() > 0) {
                $title = "Sucesso!";
                $content = "Seu novo email foi verificado com sucesso.";
            } else {
                $title = "Erro!";
                $content = "Não foi possível verificar seu email. Tente novamente.";
            }
        } else {
            $title = "Erro!";
            $content = "Este link de verificação é inválido ou já foi utilizado.";
        }
    } else {
        $title = "Erro!";
        $content = "Token de verificação não encontrado.";
    }
?>

<?php if ($url == "registrar" || $url == "finalizar-cadastro") { ?>
<style>
    .background {
        position: fixed;
        width: 100%;
    }
    .app-content {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        padding: 1.75rem 0;
        width: 100%;
        height: 100%;
        overflow-y: auto;
    }
</style>
<?php } else { ?>
<style>
    .background {
        position: fixed;
        width: 100%;
    }
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
<?php } ?>

<div class="app-container app-theme-white body-tabs-shadow">
    <div class="app-container">
        <div class="background h-100 bg-premium-dark">
            <div class="app-content">
                <div class="mx-auto app-login-box col-md-8">
                <div class="app-logo-inverse mx-auto mb-3"></div> <!-- logo -->

                    <!-- page content -->
                    <div class="modal-dialog w-100 mx-auto">
                        <div class="modal-content">
                            <div class="modal-body d-block text-center">
                                <i class="fa fa-fw mb-3" style="font-size: 4rem;" aria-hidden="true" title="Copy to use inbox"></i>
                                <h4 class="mb-2"><?= $title; ?></h4>
                                <h6><?= $content; ?></h6>
                            </div>
                            <div class="modal-footer d-block text-center">
                                <a href="<?= INCLUDE_PATH_AUTH; ?>login" class="btn-light btn-pill btn-shadow btn-hover-shine btn btn-primary btn-lg" style="width: 200px;">Ir para o Login</a>
                                <!-- <a href="<?= INCLUDE_PATH_AUTH; ?>planos" class="btn-light btn-pill btn-shadow btn-hover-shine btn btn-primary btn-lg" style="width: 200px;">Pular</a> -->
                            </div>
                        </div>
                    </div>
                    <!-- end page content -->

                    <div class="text-center text-white opacity-8 mt-3">Direitos autorais &copy; <?= $project_name; ?> <?= date("Y"); ?></div> <!-- copyright -->
                </div>
            </div>
        </div>
    </div>
</div>