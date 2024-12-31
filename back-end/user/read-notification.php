<?php
session_start();
include('./../../config.php');

header('Content-Type: application/json');

// Verifica se o usuário está autenticado e se a ação é válida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'read-notification') {
    if (isset($_SESSION['user_id'])) {
        try {
            // Iniciar transação
            $conn->beginTransaction();

            // Marcar todas as notificações como lidas
            $update_stmt = $conn->prepare("
                UPDATE tb_notification_recipients
                SET is_read = 1
                WHERE user_id = ? AND is_read = 0
            ");
            $update_stmt->execute([$_SESSION['user_id']]);

            // Commit na transação
            $conn->commit();

            echo json_encode(['status' => 'success', 'message' => 'Notificações marcadas como lidas.']);
            exit;
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Requisição inválida.']);
    exit;
}