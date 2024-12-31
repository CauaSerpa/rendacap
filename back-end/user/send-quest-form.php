<?php
session_start();
include('./../../config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send-quest-form') {

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $contactSubject = $_POST['subject'];
        $contactMessage = $_POST['message'];

        $fullname = $firstname . " " . $lastname;

        try {
            // Iniciar transação
            $conn->beginTransaction();

            // Inserir notificação na tabela tb_contact_messages
            $stmt = $conn->prepare("
                INSERT INTO tb_contact_messages (user_id, firstname, lastname, email, phone, subject, message)
                VALUES (:user_id, :firstname, :lastname, :email, :phone, :subject, :message)
            ");
            $stmt->execute([
                ':user_id' => $user_id,
                ':firstname' => $firstname,
                ':lastname' => $lastname,
                ':email' => $email,
                ':phone' => $phone,
                ':subject' => $contactSubject,
                ':message' => $contactMessage,
            ]);

            // Obter o ID da notificação inserida
            $quest_id = $conn->lastInsertId();

            // Aqui você pode usar a função `sendMail`
            $subject = "$fullname está com dúvidas!";
            $content = array("layout" => "quest-email", "content" => array("quest" => $quest_id, "fullname" => $fullname, "email" => $email, "phone" => $phone, "subject" => $contactSubject, "message" => $contactMessage));
            sendMail($firstname, 'contato@rendacap.com.br', $subject, $content);

            // Commit na transação
            $conn->commit();

            echo json_encode(['status' => 'success', 'message' => 'Mensagem enviada com sucesso! Aguarde até que os administradores entrem em contato com você por e-mail.']);
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