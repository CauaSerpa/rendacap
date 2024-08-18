<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    // Gerar e reenviar código 2FA
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Gere um novo código 2FA
        $twoFactorCode = rand(100000, 999999);
        $_SESSION['two_factor_code'] = $twoFactorCode;

        $email = $_SESSION['user_email']; // Assumindo que o e-mail do usuário está salvo na sessão

        // Durante o processo de login
        $stmt = $conn->prepare("SELECT * FROM tb_users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Enviar o código 2FA para o e-mail do usuário (ou via SMS, etc.)
            // Aqui você pode usar a função `sendMail` que você já criou para enviar o código 2FA
            $subject = "Seu código é $twoFactorCode";
            $message = "Olá " . $user['firstname'] . ",<br>Seu código de autenticação de dois fatores é: $twoFactorCode";
            sendMail($user['firstname'], $email, $subject, $message);

            echo json_encode(['status' => 'success', 'message' => 'Código 2FA reenviado com sucesso!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    }
?>
