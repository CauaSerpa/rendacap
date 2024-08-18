<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $rememberMe = isset($_POST['remember_me']) ? $_POST['remember_me'] : false;

        // Durante o processo de login
        $stmt = $conn->prepare("SELECT * FROM tb_users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['status'] == 0) {
                echo json_encode(["status" => "error", "message" => "Por favor, verifique seu e-mail para ativar sua conta."]);
            } else {
                // Verificar a senha
                if (password_verify($password, $user['password'])) {
                    if ($user['2fa'] == 0) {
                        // Codigo 2fa nao ativo, faz login normalmente
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
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['remember_me'] = $rememberMe;

                        // Enviar o código 2FA para o e-mail do usuário (ou via SMS, etc.)
                        // Aqui você pode usar a função `sendMail` que você já criou para enviar o código 2FA
                        $subject = "Seu código é $twoFactorCode";
                        $content = array("layout" => "2fa-email", "content" => array("firstname" => $user['firstname'], "code" => $twoFactorCode));
                        sendMail($user['firstname'], $user['email'], $subject, $content);

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