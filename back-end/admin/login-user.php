<?php
    session_start();
    include('./../../config.php');

    // Verifica se os dados necessários foram enviados
    if (isset($_POST['user_id']) && isset($_POST['user_email'])) {
        $userId = $_POST['user_id'];
        $userEmail = $_POST['user_email'];

        // Armazena os dados na sessão
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $userEmail;

        $_SESSION['msg'] = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Login efetuado com sucesso.');
        header("Location: " . INCLUDE_PATH_DASHBOARD);
        exit;
    } else {
        // Caso os dados não sejam enviados corretamente
        echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
    }