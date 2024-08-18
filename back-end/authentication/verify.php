<?php
    include('./../../config.php'); // Inclua a conexão com o banco de dados

    function verificarToken($token) {
        global $conn; // Usa a conexão com o banco de dados

        // Prepara a consulta para verificar se o token existe
        $stmt = $conn->prepare("SELECT id, email FROM tb_users WHERE active_token = ?");
        $stmt->execute([$token]);

        // Retorna os dados do usuário se o token for encontrado
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function ativarConta($userId) {
        global $conn;

        // Prepara a consulta para ativar a conta
        $stmt = $conn->prepare("UPDATE tb_users SET status = 1, active_token = NULL WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    // Obtém o token do GET
    if (isset($_GET['token'])) {
        $token = $_GET['token'];

        // Verifica se o token é válido
        $user = verificarToken($token);

        if ($user) {
            // Token válido, ativa a conta
            if (ativarConta($user['id'])) {
                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Conta ativada com sucesso para o email: ' . htmlspecialchars($user['email']));
                $_SESSION['msg'] = $message;

                header("Location: " . INCLUDE_PATH_AUTH . "login");
            } else {
                echo "Erro ao ativar a conta.";
            }
        } else {
            echo "Token inválido ou expirado.";
        }
    } else {
        echo "Nenhum token fornecido.";
    }
?>