<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change-2fa') {
        $is_2fa_enabled = filter_input(INPUT_POST, 'is_2fa_enabled', FILTER_VALIDATE_INT);

        if ($is_2fa_enabled === null || ($is_2fa_enabled !== 0 && $is_2fa_enabled !== 1)) {
            echo json_encode(['status' => 'error', 'message' => 'Estado inválido para 2FA.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE tb_users SET 2fa = ? WHERE id = ?");
        $stmt->execute([$is_2fa_enabled, $_SESSION['user_id']]);

        if ($stmt->rowCount()) {
            echo json_encode(['status' => 'success', 'message' => '2FA atualizado com sucesso!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Nenhuma alteração foi feita.']);
        }
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
        exit;
    }
?>