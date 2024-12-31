<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "login") {
        $login = trim($_POST['login']);
        $password = $_POST['password'];
        $rememberMe = isset($_POST['remember_me']) ? $_POST['remember_me'] : false;
        $http_referer = isset($_POST['http_referer']) ? $_POST['http_referer'] : null;

        // Prepare a consulta para buscar o usuário pelo email ou username
        $stmt = $conn->prepare("
            SELECT u.*, ur.role_id
            FROM tb_users u
            LEFT JOIN tb_user_roles ur ON u.id = ur.user_id
            WHERE u.email = ? OR u.username = ?
        ");
        $stmt->execute([$login, $login]);

        if ($stmt->rowCount()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Consulta por uma assinatura existente
            $subsStmt = $conn->prepare("SELECT * FROM tb_subscriptions WHERE user_id = ? AND status = 'ACTIVE'");
            $subsStmt->execute([$user['id']]);

            // Armazena o resultado do rowCount() em uma variável
            $subsCount = $subsStmt->rowCount();

            if ($user['role_id'] == 1) {
                $subsCount = 1;
            }

            // Alterar para logica caso o usuario nao tenha comprado nenhum plano
            if ($user['status'] == 0) {
                // Verifica se a sessão existe antes de tentar removê-la
                if (isset($_SESSION['user_id'])) {
                    unset($_SESSION['user_id']);
                    unset($_SESSION['email']);
                }

                // Armazenar o código 2FA na sessão e no banco de dados
                $_SESSION['user_id_finalize_registration'] = $user['id'];
                $_SESSION['user_email_finalize_registration'] = $user['email'];

                // Retorna para o ajax que o código 2FA foi enviado por email
                echo json_encode(["status" => "finalize-registration"]);

                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Por favor finalize o cadastro da sua conta para continuar.');
                $_SESSION['msg'] = $message;
            } else if ($user['status'] == 3) {
                // Verifica se a sessão existe antes de tentar removê-la
                if (isset($_SESSION['user_id'])) {
                    unset($_SESSION['user_id']);
                    unset($_SESSION['email']);
                }

                // Armazenar o código 2FA na sessão e no banco de dados
                $_SESSION['user_id_send_new_proof'] = $user['id'];
                $_SESSION['user_email_send_new_proof'] = $user['email'];

                // Retorna para o ajax que o código 2FA foi enviado por email
                echo json_encode(["status" => "send-new-proof"]);

                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Por favor envie um novo comprovante para acessar sua conta.');
                $_SESSION['msg'] = $message;
            } else if (!$subsCount) {
                // Verifica se a sessão existe antes de tentar removê-la
                if (isset($_SESSION['user_id'])) {
                    unset($_SESSION['user_id']);
                    unset($_SESSION['email']);
                }

                // Armazenar o código 2FA na sessão e no banco de dados
                $_SESSION['user_id_signature'] = $user['id'];
                $_SESSION['user_email_signature'] = $user['email'];

                if ($user['active_email'] == 0) {
                    // Retorna para o ajax que o código 2FA foi enviado por email
                    echo json_encode(["status" => "email-verification"]);
    
                    // Defina a mensagem de sucesso na sessão
                    // $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Por favor verifique seu email.'); // Desabilitado em fase de testes
                    $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Por favor finalize o cadastro da sua conta para continuar.');
                    $_SESSION['msg'] = $message;
                    exit;
                }

                // Retorna para o ajax que o código 2FA foi enviado por email
                echo json_encode(["status" => "create-signature"]);

                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Por favor finalize o cadastro da sua conta para continuar.');
                $_SESSION['msg'] = $message;
            } else {
                // Verificar a senha
                if (password_verify($password, $user['password'])) {
                    if ($user['2fa'] == 0) {
                        // Codigo 2fa nao ativo, faz login normalmente

                        // Armazenar o código 2FA na sessão e no banco de dados
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['email'] = $user['email'];

                        // Lógica de "lembrar-me"
                        if ($rememberMe) {
                            // Cria um array com o user_id
                            $data = array('remember_me' => $user['id']);
                            
                            // Codifica o array em JSON
                            $jsonData = json_encode($data);
                            
                            // Codifica o JSON em Base64
                            $base64Data = base64_encode($jsonData);
                            
                            // Define o cookie
                            setcookie("remember_me", $base64Data, time() + (86400 * 30), "/"); // Cookie válido por 30 dias
                        }

                        // Retorna uma mensagem de sucesso
                        echo json_encode(["status" => "success"]);

                        // Defina a mensagem de sucesso na sessão
                        $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Login bem-sucedido.');
                        $_SESSION['msg'] = $message;
                    } else {
                        // Codigo 2fa ativo
                        // Gerar código 2FA
                        $twoFactorCode = rand(100000, 999999);

                        // Armazenar o código 2FA na sessão e no banco de dados
                        $_SESSION['two_factor_code'] = $twoFactorCode;
                        $_SESSION['user_id_2fa'] = $user['id'];
                        $_SESSION['user_email_2fa'] = $user['email'];
                        $_SESSION['remember_me'] = $rememberMe;

                        // Enviar o código 2FA para o e-mail do usuário (ou via SMS, etc.)
                        // Aqui você pode usar a função `sendMail` que você já criou para enviar o código 2FA
                        $subject = "Seu código é $twoFactorCode";
                        $content = array("layout" => "2fa-email", "content" => array("firstname" => $user['firstname'], "code" => $twoFactorCode));
                        sendMail($user['firstname'], $user['email'], $subject, $content);

                        // Redefine session http_referer
                        $_SESSION['http_referer'] = $http_referer;

                        // Retorna para o ajax que o código 2FA foi enviado por email
                        echo json_encode(["status" => "2fa-required"]);

                        // Defina a mensagem de sucesso na sessão
                        $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Código 2FA enviado. Verifique seu e-mail.');
                        $_SESSION['msg'] = $message;
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Email ou senha incorretos."]);
                }
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Email ou senha incorretos."]);
        }
    }
?>