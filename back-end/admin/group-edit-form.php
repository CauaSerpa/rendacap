<?php
session_start();
include('./../../config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'group-edit') {

    if (isset($_SESSION['user_id'])) {
        $group_id = $_POST['group_id'];
        $identifier = $_POST['identifier'];
        $selected_users = isset($_POST['selected_users']) ? $_POST['selected_users'] : ''; // Exemplo de array com IDs de usuários a serem adicionados
        $removed_users = isset($_POST['removed_users']) ? $_POST['removed_users'] : ''; // Exemplo de array com IDs de usuários a serem removidos

        try {
            // Iniciar transação
            $conn->beginTransaction();

            // Atualizar título principal
            $updateSql = "UPDATE tb_groups SET identifier = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([$identifier, $group_id]);

            // Adicionar usuários ao grupo
            if (!empty($selected_users)) {
                $selected_users_array = explode(',', $selected_users); // Converte os IDs dos usuários para um array
                foreach ($selected_users_array as $user_id) {
                    // Verificar se o usuário já está associado ao grupo
                    $check_stmt = $conn->prepare("SELECT * FROM tb_group_users WHERE group_id = ? AND user_id = ?");
                    $check_stmt->execute([$group_id, $user_id]);
                    $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);

                    // Se o usuário não estiver no grupo, adiciona
                    if (!$existing_user) {
                        $insert_stmt = $conn->prepare("INSERT INTO tb_group_users (group_id, user_id) VALUES (?, ?)");
                        $insert_stmt->execute([$group_id, $user_id]);
                    }
                }
            }

            // Remover usuários do grupo
            if (!empty($removed_users)) {
                $removed_users_array = explode(',', $removed_users); // Converte os IDs dos usuários a serem removidos para um array
                foreach ($removed_users_array as $user_id) {
                    // Verificar se o usuário está associado ao grupo
                    $check_stmt = $conn->prepare("SELECT * FROM tb_group_users WHERE group_id = ? AND user_id = ?");
                    $check_stmt->execute([$group_id, $user_id]);
                    $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);

                    // Se o usuário existir no grupo, remove
                    if ($existing_user) {
                        $delete_stmt = $conn->prepare("DELETE FROM tb_group_users WHERE group_id = ? AND user_id = ?");
                        $delete_stmt->execute([$group_id, $user_id]);
                    }
                }
            }

            // Commit na transação
            $conn->commit();

            $_SESSION['msg'] = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Grupo editado com sucesso.');
            echo json_encode(['status' => 'success', 'message' => 'Grupo editado com sucesso.']);
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
?>