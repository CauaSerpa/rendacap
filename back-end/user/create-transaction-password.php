<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create-transaction-password') {

        // Obtém a senha enviada via POST
        $password = $_POST['password'];

        // Criptografa a senha para segurança
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Consulta para atualizar a senha do usuário
        $stmt = $conn->prepare("UPDATE tb_users SET transaction_password = :transaction_password WHERE id = :user_id");
        $stmt->bindParam(':transaction_password', $hashedPassword);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Senha cadastrada com sucesso!']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar a senha. Tente novamente.']);
            exit;
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
        exit;
    }
?>