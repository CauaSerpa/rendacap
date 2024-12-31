<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send-response-form') {

        if (isset($_SESSION['user_id'])) {
            $message_id = $_POST['message_id'];
            $response = $_POST['response'];

            try {
                // Iniciar transação
                $conn->beginTransaction();

                $stmt = $conn->prepare("
                    SELECT cm.id, u.username, u.firstname, u.lastname, cm.email, cm.phone, cm.subject, cm.message, cm.status, cm.created_at
                    FROM tb_contact_messages cm
                    JOIN tb_users u ON u.id = cm.user_id
                    WHERE cm.id = :id
                ");
                $stmt->bindParam(':id', $message_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = $stmt->fetch(PDO::FETCH_ASSOC);
                $message['fullname'] = $message['firstname'] . " " . $message['lastname'];
                $message['sent_in'] = date('d/m/Y H:i', strtotime($message['created_at']));

                // Atualizar a notificação na tabela tb_contact_messages
                $stmt = $conn->prepare("
                    UPDATE tb_contact_messages
                    SET status = :status, admin_response = :admin_response, answered_in = NOW()
                    WHERE id = :message_id
                ");
                $stmt->execute([
                    ':status' => 'answered',
                    ':admin_response' => $response,
                    ':message_id' => $message_id,
                ]);

                // Aqui você pode usar a função `sendMail`
                $subject = $message['firstname'] . ", aqui estão as resposta à sua dúvida!";
                $content = array("layout" => "response-email", "content" => array("message" => $message['id'], "fullname" => $message['fullname'], "firstname" => $message['firstname'], "email" => $message['email'], "phone" => $message['phone'], "message" => $message['message'], "sent_in" => $message['sent_in'], "response" => $response));
                sendMail($message['firstname'], 'contato@rendacap.com.br', $subject, $content);    

                // Commit na transação
                $conn->commit();

                $_SESSION['msg'] = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Resposta enviada com sucesso!');
                echo json_encode(['status' => 'success', 'message' => 'Resposta enviada com sucesso!']);
                exit;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Erro: ' . $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
        exit;
    }