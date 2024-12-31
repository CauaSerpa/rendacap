<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verifica o token
    $stmt = $conn->prepare("SELECT id FROM tb_users WHERE recup_password = ? AND recup_password_expiry > NOW()");
    $stmt->execute([$token]);

    if ($stmt->rowCount()) {
        // Token válido
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password === $confirm_password) {
                // Atualiza a senha do usuário
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE tb_users SET password = ?, recup_password = NULL, recup_password_expiry = NULL WHERE recup_password = ?");
                $update_stmt->execute([$hashedPassword, $token]);

                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Senha atualizada com sucesso.');
                $_SESSION['msg'] = $message;

                // Redireciona o usuário para login
                header("Location: " . INCLUDE_PATH_AUTH . "login");
                exit;
            } else {
                // Defina a mensagem de erro na sessão
                $message = array('status' => 'error', 'title' => 'Erro', 'message' => 'As senhas não coincidem.');
                $_SESSION['msg'] = $message;
            }
        }
    } else {
        // Defina a mensagem de erro na sessão
        $message = array('status' => 'error', 'title' => 'Erro', 'message' => 'Token inválido ou expirado.');
        $_SESSION['msg'] = $message;

        // Redireciona o usuário para recuperar-senha
        header("Location: " . INCLUDE_PATH_AUTH . "recuperar-senha");
        exit;
    }
} else {
    // Defina a mensagem de erro na sessão
    $message = array('status' => 'error', 'title' => 'Erro', 'message' => 'Token não fornecido.');
    $_SESSION['msg'] = $message;

    // Redireciona o usuário para recuperar-senha
    header("Location: " . INCLUDE_PATH_AUTH . "recuperar-senha");
    exit;
}
?>

<div class="modal-dialog w-100 mx-auto">
    <form id="resetPassForm" method="POST">
        <div class="modal-content">
            <div class="modal-header">
                <div class="h5 modal-title">
                    Esqueceu sua senha?
                    <h6 class="mt-1 mb-0 opacity-8">
                        <span>Utilize o formulário abaixo para recuperá-la.</span>
                    </h6>
                </div>
            </div>
            <div class="modal-body">
                <div>
                    <div class="form-row">
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
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <div class="d-flex align-items-center">
                    <a href="<?= INCLUDE_PATH_AUTH; ?>login" class="btn-pill btn-hover-shine btn btn-link btn-lg px-0">Faça login na conta existente</a>
                </div>
                <div class="d-flex align-items-center">
                    <button type="submit" id="btnLogin" class="btn-pill btn-hover-shine btn btn-primary btn-lg" style="width: 135px;">Recuperar senha</button>
                    <button id="btnLoader" class="btn-pill btn-hover-shine btn btn-primary btn-lg d-none" style="width: 135px;">
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
    </form>
</div>

<script type="text/javascript">
    $(document).ready(() => {
        // Mostrar senha
        $('#showPasswordToggle').on('change', function() {
            const type = $(this).is(':checked') ? 'text' : 'password';
            $('#password, #confirm_password').attr('type', type);
        });
    });
</script>