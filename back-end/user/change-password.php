<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change-password') {

        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['status' => 'error', 'message' => 'As senhas não coincidem.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT password FROM tb_users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($currentPassword, $user['password'])) {
            $newPasswordHashed = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE tb_users SET password = ? WHERE id = ?");
            $stmt->execute([$newPasswordHashed, $_SESSION['user_id']]);

            if ($stmt->rowCount()) {
                echo json_encode(['status' => 'success', 'message' => 'Senha alterada com sucesso!']);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Nenhuma alteração foi feita.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'A senha atual está incorreta.']);
            exit;
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
        exit;
    }
?>