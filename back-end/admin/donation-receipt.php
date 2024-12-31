<?php
    session_start();
    include('../../config.php');

    header('Content-Type: application/json');

    // Exemplo de código PHP para aprovar o comprovante
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve-receipt') {
        if (isset($_POST['receipt_id'])) {
            $receipt_id = $_POST['receipt_id'];

            // Iniciar uma transação para garantir a consistência entre as tabelas
            $conn->beginTransaction();

            try {
                // Buscar o user_id associado ao receipt_id
                $stmt = $conn->prepare("SELECT user_id FROM tb_donation_receipt WHERE id = :id");
                $stmt->bindParam(':id', $receipt_id);
                $stmt->execute();
                $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($receipt) {
                    $user_id = $receipt['user_id'];

                    // Atualize o status do comprovante para 'approved' na tabela tb_donation_receipt
                    $stmt = $conn->prepare("UPDATE tb_donation_receipt SET status = 'approved' WHERE id = :id");
                    $stmt->bindParam(':id', $receipt_id);
                    $stmt->execute();

                    // Atualize o status do usuário para 3 na tabela tb_users
                    $stmt = $conn->prepare("UPDATE tb_users SET status = 1 WHERE id = :user_id");
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();

                    // Confirmar a transação
                    $conn->commit();

                    // Defina a mensagem de sucesso na sessão
                    $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Comprovante aprovado com sucesso!');
                    $_SESSION['msg'] = $message;

                    // Resposta de sucesso
                    echo json_encode(['status' => 'success']);
                } else {
                    // Reverter a transação em caso de erro
                    $conn->rollBack();

                    // Erro ao mover o arquivo
                    echo json_encode(['status' => 'error', 'message' => 'Comprovante não encontrado.']);
                }
            } catch (Exception $e) {
                // Reverter a transação em caso de erro
                $conn->rollBack();

                // Erro ao mover o arquivo
                echo json_encode(['status' => 'error', 'message' => 'Erro ao aprovar o comprovante.']);
            }
        }
    }

    // Exemplo de código PHP para reprovar o comprovante
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reject-receipt') {
        if (isset($_POST['receipt_id'])) {
            $receipt_id = $_POST['receipt_id'];

            // Iniciar uma transação para garantir a consistência entre as tabelas
            $conn->beginTransaction();

            try {
                // Buscar o user_id associado ao receipt_id
                $stmt = $conn->prepare("SELECT user_id FROM tb_donation_receipt WHERE id = :id");
                $stmt->bindParam(':id', $receipt_id);
                $stmt->execute();
                $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($receipt) {
                    $user_id = $receipt['user_id'];

                    // Atualize o status do comprovante para 'rejected' na tabela tb_donation_receipt
                    $stmt = $conn->prepare("UPDATE tb_donation_receipt SET status = 'rejected' WHERE id = :id");
                    $stmt->bindParam(':id', $receipt_id);
                    $stmt->execute();

                    // Atualize o status do usuário para 3 na tabela tb_users
                    $stmt = $conn->prepare("UPDATE tb_users SET status = 3 WHERE id = :user_id");
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();

                    // Confirmar a transação
                    $conn->commit();

                    // Defina a mensagem de sucesso na sessão
                    $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Comprovante recusado com sucesso!');
                    $_SESSION['msg'] = $message;

                    // Resposta de sucesso
                    echo json_encode(['status' => 'success']);
                } else {
                    // Reverter a transação em caso de erro
                    $conn->rollBack();

                    // Erro ao mover o arquivo
                    echo json_encode(['status' => 'error', 'message' => 'Comprovante não encontrado.']);
                }
            } catch (Exception $e) {
                // Reverter a transação em caso de erro
                $conn->rollBack();

                // Erro ao mover o arquivo
                echo json_encode(['status' => 'error', 'message' => 'Erro ao rejeitar o comprovante.']);
            }
        }
    }