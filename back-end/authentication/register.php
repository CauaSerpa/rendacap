<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    function generateSequentialToken($user_id) {
        // Formatar o número de sequência com zeros à esquerda
        $formatted_sequence = str_pad($user_id, 6, '0', STR_PAD_LEFT);
        $token = '3005' . $formatted_sequence;

        return $token;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "register") {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $whatsapp = $_POST['phone'];

        // Altera data para padrao do banco de dados
        $birth_date = $_POST['birth_date'];
        $birth_date = DateTime::createFromFormat('d/m/Y', $birth_date);
        $birthday = $birth_date->format('Y-m-d');

        $cpf = $_POST['cpf'];
        $rg = $_POST['rg'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $active_token = generateToken();
        $token_used = !empty($_POST['token_used']) ? $_POST['token_used'] : null;
        $status = 0; // Inativo até que o e-mail seja confirmado

        $stmt = $conn->prepare("INSERT INTO tb_users (firstname, lastname, email, whatsapp, birth_date, cpf, rg, username, password, active_token, token_used, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstname, $lastname, $email, $whatsapp, $birthday, $cpf, $rg, $username, $password, $active_token, $token_used, $status]);

        // Recupere o ID do último registro inserido
        $user_id = $conn->lastInsertId();

        $token = generateSequentialToken($user_id); // Gere o token sequencial

        // Insere token na tabela
        $update_stmt = $conn->prepare("UPDATE tb_users SET token = ? WHERE id = ?");
        $update_stmt->execute([$token, $user_id]);

        // Define permissao para usuario
        $role_id = 3; // Padrao para usuarios
        $permission_stmt = $conn->prepare("INSERT INTO tb_user_roles (user_id, role_id) VALUES (?, ?)");
        $permission_stmt->execute([$user_id, $role_id]);

        if ($stmt->rowCount()) {
            // Enviar e-mail de verificação
            $verification_link = INCLUDE_PATH_DASHBOARD . "back-end/authentication/verify.php?token=" . $active_token;
            $subject = "Confirme seu email";
            $content = array("layout" => "verify-email", "content" => array("firstname" => $firstname, "link" => $verification_link));
            sendMail($firstname, $email, $subject, $content);

            // Armazena o email em uma session
            $_SESSION['email'] = $email;

            // Retorna um status de sucesso
            $response = array('status' => 'success');

            // Defina a mensagem de sucesso na sessão
            $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Registro realizado com sucesso! Por favor, verifique seu e-mail.');
            $_SESSION['msg'] = $message;
        } else {
            $response = array('status' => 'error', 'message' => 'Erro ao registrar. Por favor, tente novamente.');
        }

        // Fechar a conexão
        $stmt = null;
        $conn = null;
    } else {
        $response = array('status' => 'error', 'message' => 'Erro ao registrar. Por favor, tente novamente.');
    }

    // Enviar a resposta em JSON
    echo json_encode($response);
?>