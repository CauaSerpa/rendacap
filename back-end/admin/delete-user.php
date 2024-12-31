<?php
    session_start();
    include('../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete-user') {
        if (isset($_SESSION['user_id'])) {
            if (isset($_POST['user_id']) && isset($_POST['admin_password'])) {
                $user_id = $_POST['user_id'];
                $admin_password = $_POST['admin_password'];
                $admin_id = $_SESSION['user_id'];

                // Iniciar uma transação
                $conn->beginTransaction();

                try {
                    // Verificar a senha do administrador
                    $stmt = $conn->prepare("SELECT password FROM tb_users WHERE id = :id");
                    $stmt->bindParam(':id', $admin_id);
                    $stmt->execute();
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($admin && password_verify($admin_password, $admin['password'])) {
                        // Tabelas a serem afetadas
                        $tables = [
                            'tb_address',
                            'tb_contact_messages',
                            'tb_customer_info',
                            'tb_donation_receipt',
                            'tb_group_users',
                            'tb_networks', // Tabela específica tratada separadamente
                            'tb_notification_recipients',
                            'tb_orders',
                            'tb_subscriptions',
                            'tb_title_users',
                            'tb_user_documents',
                            'tb_user_networks',
                            'tb_user_roles',
                            'tb_voucher_orders',
                            'tb_withdrawals',
                            'tb_users'
                        ];

                        // Deletar dados de cada tabela
                        foreach ($tables as $table) {
                            if ($table === 'tb_networks') {
                                // Atualizar referrer_id para NULL, se aplicável
                                $updateStmt = $conn->prepare("UPDATE tb_networks SET referrer_id = NULL WHERE referrer_id = :user_id");
                                $updateStmt->bindParam(':user_id', $user_id);
                                $updateStmt->execute();

                                // Passo 1: Identificar os network_id associados ao inviter_id (user_id)
                                $selectStmt = $conn->prepare("SELECT id FROM tb_networks WHERE inviter_id = :user_id");
                                $selectStmt->bindParam(':user_id', $user_id);
                                $selectStmt->execute();
                                $networkIds = $selectStmt->fetchAll(PDO::FETCH_COLUMN);

                                // Passo 2: Excluir os registros da tabela tb_user_networks com esses network_id
                                if (!empty($networkIds)) {
                                    $placeholders = implode(',', array_fill(0, count($networkIds), '?')); // Cria placeholders para a consulta IN
                                    $deleteUserNetworksStmt = $conn->prepare("DELETE FROM tb_user_networks WHERE network_id IN ($placeholders)");
                                    $deleteUserNetworksStmt->execute($networkIds);
                                }

                                // Passo 3: Excluir os registros da tabela tb_networks com inviter_id igual ao user_id
                                $deleteStmt = $conn->prepare("DELETE FROM tb_networks WHERE inviter_id = :user_id");
                                $deleteStmt->bindParam(':user_id', $user_id);
                                $deleteStmt->execute();
                            } else {
                                $column = ($table === 'tb_users') ? 'id' : 'user_id'; // Ajuste para a tabela principal

                                // Verificar se há registros antes de deletar
                                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE $column = :user_id");
                                $checkStmt->bindParam(':user_id', $user_id);
                                $checkStmt->execute();

                                if ($checkStmt->fetchColumn() > 0) {
                                    $stmt = $conn->prepare("DELETE FROM $table WHERE $column = :user_id");
                                    $stmt->bindParam(':user_id', $user_id);
                                    $stmt->execute();
                                }
                            }
                        }

                        // Confirmar a transação
                        $conn->commit();

                        // Mensagem de sucesso
                        $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Usuário deletado com sucesso!');
                        $_SESSION['msg'] = $message;

                        echo json_encode(['status' => 'success']);
                        exit;
                    } else {
                        // Reverter a transação
                        $conn->rollBack();
                        echo json_encode(['status' => 'error', 'message' => 'Credenciais inválidas.']);
                        exit;
                    }
                } catch (Exception $e) {
                    // Reverter a transação em caso de erro
                    $conn->rollBack();
                    echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar o usuário. Erro: ' . $e->getMessage()]);
                    exit;
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Parâmetros inválidos.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Parâmetros inválidos.']);
        exit;
    }