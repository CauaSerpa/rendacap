<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    // Verifica se a requisição veio via POST e se a ação é a esperada
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send-verify-email') {

        // Obtém o email atual do usuário da requisição
        $email = trim($_POST['email']);

        // Verifica se o email foi preenchido
        if (empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'O email não pode estar vazio.']);
            exit();
        }

        // Faz uma busca pelo usuario
        $stmt = $conn->prepare("
            SELECT *
            FROM tb_users
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);

        if ($stmt->rowCount()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Gera um token de verificação único
            $verification_token = generateToken();

            // Enviar e-mail de verificação
            $verification_link = INCLUDE_PATH_DASHBOARD . "r/verificar-email/" . $verification_token;
            $subject = "Bem-vindo ao $project_name";
            $content = array("layout" => "verify-email", "content" => array("firstname" => $user['firstname'], "link" => $verification_link));

            // Enviar o e-mail
            sendMail($user['firstname'], $email, $subject, $content);

            if ($email !== $user['email']) {
                // Atualiza o banco de dados com o novo email e o token de verificação
                $stmt = $conn->prepare("UPDATE tb_users SET new_email = ?, active_token = ?, active_email = 0 WHERE id = ?");
                $stmt->execute([$email, $verification_token, $_SESSION['user_id']]);
            } else {
                // Atualiza o banco de dados com o token de verificação (exemplo de atualização)
                $stmt = $conn->prepare("UPDATE tb_users SET active_token = ?, active_email = 0 WHERE id = ?");
                $stmt->execute([$verification_token, $_SESSION['user_id']]);
            }

            // Verifica se a atualização foi bem-sucedida
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Email de verificação enviado com sucesso!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o token de verificação.']);
            }

        } else {
            // Se a consulta falhar
            echo json_encode(['status' => 'error', 'message' => 'Erro ao encontrar o perfil.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Requisição inválida.']);
    }
?>