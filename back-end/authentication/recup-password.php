<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $_SESSION['email'] = $email;

        // Verifica se o email existe
        $stmt = $conn->prepare("SELECT id, firstname, email FROM tb_users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Gera um token de recuperação de senha
            $token = bin2hex(random_bytes(16)); // Gera um token aleatório

            // Armazena o token na tabela de usuários (ou em uma tabela separada)
            $update_stmt = $conn->prepare("UPDATE tb_users SET recup_password = ?, recup_password_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
            $update_stmt->execute([$token, $user['id']]);

            // Envia um e-mail com o link de recuperação
            $resetLink = INCLUDE_PATH_AUTH . "atualizar-senha?token=" . $token;
            $subject = "Recuperação de Senha";
            $content = array("layout" => "recup-password", "content" => array("firstname" => $user['firstname'], "link" => $resetLink));
            sendMail($user['firstname'], $email, $subject, $content);

            // Retorna uma mensagem de sucesso
            echo json_encode(['status' => 'success']);

            // Defina a mensagem de sucesso na sessão
            $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'O e-mail de recuperação de senha foi enviado com sucesso.');
            $_SESSION['msg'] = $message;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'O e-mail fornecido não está registrado.']);
        }
    }
?>