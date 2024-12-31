<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit-notification-form') {
        if (isset($_SESSION['user_id'])) {
            $notification_id = $_POST['notification_id'];
            $title = $_POST['title'];
            $content = $_POST['content'];
            $type = $_POST['type'];

            // Inicializa variáveis opcionais
            $plan_users = isset($_POST['plan_users']) ? $_POST['plan_users'] : null;
            $selected_users = isset($_POST['selected_users']) ? explode(',', $_POST['selected_users']) : [];
            $removed_users = isset($_POST['removed_users']) ? explode(',', $_POST['removed_users']) : [];

            try {
                // Iniciar transação
                $conn->beginTransaction();

                // Consulta o type atual da notificacao
                $query = "
                    SELECT n.*
                    FROM tb_notifications n
                    WHERE n.id = ?
                ";
                $stmt = $conn->prepare($query);
                $stmt->execute([$notification_id]);
                $notification = $stmt->fetch(PDO::FETCH_ASSOC);

                // Atualizar a notificação na tabela tb_notifications
                $stmt = $conn->prepare("
                    UPDATE tb_notifications
                    SET title = :title, content = :content, type = :type, plan = :plan, updated_at = NOW()
                    WHERE id = :notification_id
                ");
                $stmt->execute([
                    ':title' => $title,
                    ':content' => $content,
                    ':type' => $type,
                    ':plan' => $plan_users,
                    ':notification_id' => $notification_id,
                ]);

                // Se o type for diferente do type atual
                if ($notification['type'] !== $type) {
                    // Remover associações de usuários anteriores
                    $delete_recipients_stmt = $conn->prepare("DELETE FROM tb_notification_recipients WHERE notification_id = ?");
                    $delete_recipients_stmt->execute([$notification_id]);
                }

                // Gerenciar usuários de acordo com o tipo
                if ($type === 'all') {
                    // Selecionar todos os usuários ativos
                    $stmt_all = $conn->query("
                        SELECT u.id
                        FROM tb_users u
                        LEFT JOIN tb_notification_recipients nr ON u.id = nr.user_id
                        WHERE nr.user_id IS NULL
                    ");
                    $all_users = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($all_users as $user) {
                        $insert_stmt = $conn->prepare("INSERT INTO tb_notification_recipients (notification_id, user_id) VALUES (?, ?)");
                        $insert_stmt->execute([$notification_id, $user['id']]);
                    }
                } elseif ($type === 'plan_users' && !empty($plan_users)) {
                    // Busca todas as notificações
                    $query = "
                        SELECT n.*
                        FROM tb_notifications n
                        WHERE n.id = ?
                    ";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$notification_id]);
                    $notification = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($plan_users !== $notification['plan']) {
                        // Remover usuários associados ao plano antigo
                        $removeQuery = "DELETE FROM tb_notification_recipients WHERE notification_id = ?";
                        $stmt = $conn->prepare($removeQuery);
                        $stmt->execute([$notification_id]);

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
                    } else {
                        // Verifica usuarios com o mesmo plano selecionado que nao estao cadastrados na tabela tb_notification_recipients
                        $query = "
                            SELECT u.id AS user_id
                            FROM tb_users u
                            JOIN tb_subscriptions s ON u.id = s.user_id
                            LEFT JOIN tb_notification_recipients nr ON u.id = nr.user_id
                            WHERE s.plan_id = ?
                            AND nr.user_id IS NULL
                            AND nr.notification_id = ?
                        ";
                        $stmt = $conn->prepare($query);
                        $stmt->execute([$plan_users, $notification_id]);
                        $usersToInsert = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $insertQuery = "INSERT INTO tb_notification_recipients (user_id, notification_id) VALUES (:user_id, :notification_id)";
                        $stmt = $conn->prepare($insertQuery);

                        foreach ($usersToInsert as $userId) {
                            $stmt->execute(['user_id' => $userId, 'notification_id' => $notification_id]);
                        }
                    }
                } elseif ($type === 'selecteds') {
                    // Adicionar novos usuários selecionados
                    if (!empty($selected_users)) {
                        foreach ($selected_users as $user_id) {
                            // Verificar se o usuário já foi cadastrado para esta notificação
                            $check_stmt = $conn->prepare("
                                SELECT COUNT(*) 
                                FROM tb_notification_recipients 
                                WHERE notification_id = ? AND user_id = ?
                            ");
                            $check_stmt->execute([$notification_id, $user_id]);
                            $exists = $check_stmt->fetchColumn();

                            // Inserir somente se o registro não existir
                            if ($exists == 0) {
                                $insert_stmt = $conn->prepare("
                                    INSERT INTO tb_notification_recipients (notification_id, user_id) 
                                    VALUES (?, ?)
                                ");
                                $insert_stmt->execute([$notification_id, $user_id]);
                            }
                        }
                    }

                    // Remover usuários não mais associados
                    if (!empty($removed_users)) {
                        foreach ($removed_users as $user_id) {
                            $delete_stmt = $conn->prepare("DELETE FROM tb_notification_recipients WHERE notification_id = ? AND user_id = ?");
                            $delete_stmt->execute([$notification_id, $user_id]);
                        }
                    }
                }

                // Confirmar transação
                $conn->commit();

                $_SESSION['msg'] = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Notificação editada com sucesso.');
                echo json_encode(['status' => 'success', 'message' => 'Notificação editada com sucesso.']);
            } catch (Exception $e) {
                // Reverter transação em caso de erro
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Erro ao editar a notificação: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Requisição inválida.']);
    }