<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    // Verificação do código 2FA
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['two_factor_code'])) {
        $twoFactorCode = $_POST['two_factor_code'];

        if (isset($_SESSION['user_id'])) {
            $stmt = $conn->prepare("SELECT * FROM tb_users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($_SESSION['two_factor_code'] == $twoFactorCode) {
                // Código 2FA está correto, permitir login
                // Limpar o código 2FA da sessão e do banco de dados
                unset($_SESSION['two_factor_code']);

                // Lógica de "lembrar-me"
                $rememberMe = isset($_SESSION['remember_me']) ? $_SESSION['remember_me'] : false;
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
                echo json_encode(["status" => "error", "message" => "Código 2FA incorreto."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Sessão expirada. Faça login novamente."]);
        }
    }
?>