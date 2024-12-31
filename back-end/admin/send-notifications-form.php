<?php
session_start();
include('./../../config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send-notifications-form') {

    if (isset($_SESSION['user_id'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $type = $_POST['type'];

        // Inicializa variáveis opcionais
        $plan_users = isset($_POST['plan_users']) ? $_POST['plan_users'] : null;
        $selected_users = isset($_POST['selected_users']) ? explode(',', $_POST['selected_users']) : [];

        try {
            // Iniciar transação
            $conn->beginTransaction();

            // Inserir notificação na tabela tb_notifications
            $stmt = $conn->prepare("
                INSERT INTO tb_notifications (title, content, type, plan, created_at)
                VALUES (:title, :content, :type, :plan, NOW())
            ");
            $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':type' => $type,
                ':plan' => $plan_users,
            ]);

            // Obter o ID da notificação inserida
            $notification_id = $conn->lastInsertId();

            // Processar usuários de acordo com o tipo
            if ($type === 'all') {
                // Selecionar todos os usuários ativos do sistema
                $stmt_all = $conn->query("
                                        SELECT u.id 
                                        FROM tb_users u
                                        JOIN tb_user_roles r ON u.id = r.user_id
                                        WHERE u.status = 1 AND r.role_id = 3
                                    ");
                $all_users = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

                foreach ($all_users as $user) {
                    $insert_stmt = $conn->prepare("INSERT INTO tb_notification_recipients (notification_id, user_id) VALUES (?, ?)");
                    $insert_stmt->execute([$notification_id, $user['id']]);
                }
            } elseif ($type === 'plan_users' && !empty($plan_users)) {
                // Selecionar usuários com planos específicos
                $stmt_plan = $conn->prepare("
                                        SELECT u.id
                                        FROM tb_users u
                                        JOIN tb_subscriptions s ON u.id = s.user_id
                                        JOIN tb_plans p ON s.plan_id = p.id
                                        JOIN tb_user_roles r ON u.id = r.user_id
                                        WHERE u.status = 1 AND s.plan_id = ? AND r.role_id = 3
                                    ");
                $stmt_plan->execute([$plan_users]);
                $plan_users_list = $stmt_plan->fetchAll(PDO::FETCH_ASSOC);

                if ($plan_users_list) {
                    foreach ($plan_users_list as $user) {
                        $insert_stmt = $conn->prepare("INSERT INTO tb_notification_recipients (notification_id, user_id) VALUES (?, ?)");
                        $insert_stmt->execute([$notification_id, $user['id']]);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Não existem usuários com o plano selecionado.']);
                    exit;
                }
            } elseif ($type === 'selecteds' && !empty($selected_users)) {
                // Adicionar usuários selecionados
                foreach ($selected_users as $user_id) {
                    $insert_stmt = $conn->prepare("INSERT INTO tb_notification_recipients (notification_id, user_id) VALUES (?, ?)");
                    $insert_stmt->execute([$notification_id, $user_id]);
                }
            }

            // Commit na transação
            $conn->commit();

            $_SESSION['msg'] = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Notificação enviada com sucesso.');
            echo json_encode(['status' => 'success', 'message' => 'Notificação enviada com sucesso.']);
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