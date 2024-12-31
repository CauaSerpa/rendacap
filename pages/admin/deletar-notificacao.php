<?php
    // Verifica se o ID da notificação foi passado e se o usuário está autenticado
    if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
        $notificationId = intval($_GET['id']);

        try {
            // Iniciar transação
            $conn->beginTransaction();

            // Deletar os destinatários associados à notificação
            $stmtRecipients = $conn->prepare("
                DELETE FROM tb_notification_recipients
                WHERE notification_id = ?
            ");
            $stmtRecipients->execute([$notificationId]);

            // Deletar a notificação
            $stmtNotification = $conn->prepare("
                DELETE FROM tb_notifications
                WHERE id = ?
            ");
            $stmtNotification->execute([$notificationId]);

            // Confirmar transação
            $conn->commit();

            // Mensagem de sucesso
            $_SESSION['msg'] = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Notificação deletada com sucesso.');
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();
            $_SESSION['msg'] = array('status' => 'error', 'title' => 'Erro', 'message' => 'Erro ao deletar a notificação: ' . $e->getMessage());
        }
    } else {
        $_SESSION['msg'] = array('status' => 'error', 'title' => 'Erro', 'message' => 'Parâmetros inválidos ou usuário não autenticado.');
    }

    // Redirecionar para a página de listagem de notificações
    header("Location: " . INCLUDE_PATH_DASHBOARD . "notificacoes");
    exit;