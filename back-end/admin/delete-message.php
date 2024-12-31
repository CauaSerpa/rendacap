<?php
    session_start();
    include('../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete-message') {
        if (isset($_POST['message_id'])) {
            $message_id = $_POST['message_id'];

            // Iniciar uma transação para garantir a consistência entre as tabelas
            $conn->beginTransaction();

            try {
                // Atualize o status do mensagem para 'approved' na tabela tb_donation_message
                $stmt = $conn->prepare("DELETE FROM tb_contact_messages WHERE id = :id");
                $stmt->bindParam(':id', $message_id);
                $stmt->execute();

                // Confirmar a transação
                $conn->commit();

                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Mensagem deletada com sucesso!');
                $_SESSION['msg'] = $message;

                // Resposta de sucesso
                echo json_encode(['status' => 'success']);
            } catch (Exception $e) {
                // Reverter a transação em caso de erro
                $conn->rollBack();

                // Erro ao mover o arquivo
                echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar a mensagem.']);
            }
        }
    }