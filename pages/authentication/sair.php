<?php
    session_unset();
    session_destroy();

    if (isset($_COOKIE['remember_me'])) {
        // Remove o cookie do navegador
        setcookie("remember_me", "", time() - 3600, "/");
    }

    // Defina a mensagem de sucesso na sessão
    $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Deslogado com sucesso.');
    $_SESSION['msg'] = $message;

    header("Location: " . INCLUDE_PATH_AUTH);
    exit();
?>