<?php
    session_start();
    include('../../../config.php');

    header('Content-Type: application/json');

    // Processa o formulário de pagamento
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save-donation-receipt') {
        $userId = (isset($_SESSION["user_id_signature"])) ? $_SESSION["user_id_signature"] : $_SESSION["user_id"]; // Pega o ID do usuário enviado via POST
        $comprovante = $_FILES['comprovante'];

        // Verifica se houve algum erro no upload do arquivo
        if ($comprovante['error'] === UPLOAD_ERR_OK) {
            // Define o diretório de upload
            $uploadDir = '../../../files/uploads/comprovante-doacoes/' . $userId . '/'; // Ajuste o caminho do diretório de uploads
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true); // Cria o diretório, se não existir
            }

            // Gera um nome único para o arquivo, incluindo o ID do usuário
            $fileExtension = pathinfo($comprovante['name'], PATHINFO_EXTENSION);
            $newFileName = 'comprovante_' . uniqid() . '.' . $fileExtension;
            $uploadFilePath = $uploadDir . $newFileName;
            $fileLink = INCLUDE_PATH_DASHBOARD . "files/uploads/comprovante-doacoes/$userId/$newFileName";

            // Move o arquivo para o diretório de uploads
            if (move_uploaded_file($comprovante['tmp_name'], $uploadFilePath)) {
                // Salva o caminho do arquivo no banco de dados
                $sql = "INSERT INTO tb_donation_receipt (user_id, proof) VALUES (:user_id, :proof)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':proof', $fileLink);

                if ($stmt->execute()) {
                    // Resposta de sucesso
                    echo json_encode(['status' => 'success', 'message' => 'Comprovante salvo com sucesso.']);
                } else {
                    // Erro ao salvar no banco de dados
                    echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar comprovante no banco de dados.']);
                }
            } else {
                // Erro ao mover o arquivo
                echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o comprovante no servidor.']);
            }
        } else {
            // Erro no upload do arquivo
            echo json_encode(['status' => 'error', 'message' => 'Erro no upload do arquivo.']);
        }
    }

    // Processa o formulário de pagamento
    else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend-donation-receipt') {
        $userId = (isset($_SESSION["user_id_send_new_proof"])) ? $_SESSION["user_id_send_new_proof"] : $_SESSION["user_id"]; // Pega o ID do usuário enviado via POST
        if (!empty($userId)) {
            $comprovante = $_FILES['comprovante'];
    
            // Verifica se houve algum erro no upload do arquivo
            if ($comprovante['error'] === UPLOAD_ERR_OK) {
                // Define o diretório de upload
                $uploadDir = '../../../files/uploads/comprovante-doacoes/' . $userId . '/'; // Ajuste o caminho do diretório de uploads
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true); // Cria o diretório, se não existir
                }
    
                // Gera um nome único para o arquivo, incluindo o ID do usuário
                $fileExtension = pathinfo($comprovante['name'], PATHINFO_EXTENSION);
                $newFileName = 'comprovante_' . uniqid() . '.' . $fileExtension;
                $uploadFilePath = $uploadDir . $newFileName;
                $fileLink = INCLUDE_PATH_DASHBOARD . "files/uploads/comprovante-doacoes/$userId/$newFileName";
    
                // Move o arquivo para o diretório de uploads
                if (move_uploaded_file($comprovante['tmp_name'], $uploadFilePath)) {
                    // Salva o caminho do arquivo no banco de dados
                    $sql = "INSERT INTO tb_donation_receipt (user_id, proof) VALUES (:user_id, :proof)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':proof', $fileLink);
                    $stmt->execute();
    
                    // Atualize o status do usuário para 3 na tabela tb_users
                    $stmt = $conn->prepare("UPDATE tb_users SET status = 1 WHERE id = :user_id");
                    $stmt->bindParam(':user_id', $userId);
    
                    if ($stmt->execute()) {
                        // Armazenar o código 2FA na sessão e no banco de dados
                        $_SESSION['user_id'] = $_SESSION['user_id_send_new_proof'];
                        $_SESSION['email'] = $_SESSION['user_email_send_new_proof'];
    
                        // Verifica se a sessão existe antes de tentar removê-la
                        if (isset($_SESSION['user_id_send_new_proof'])) {
                            unset($_SESSION['user_id_send_new_proof']);
                            unset($_SESSION['user_email_send_new_proof']);
                        }
    
                        // Resposta de sucesso
                        echo json_encode(['status' => 'success', 'message' => 'Comprovante salvo com sucesso.']);
                    } else {
                        // Erro ao salvar no banco de dados
                        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar comprovante no banco de dados.']);
                    }
                } else {
                    // Erro ao mover o arquivo
                    echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o comprovante no servidor.']);
                }
            } else {
                // Erro no upload do arquivo
                echo json_encode(['status' => 'error', 'message' => 'Erro no upload do arquivo.']);
            }
        } else {
            // Erro no upload do arquivo
            echo json_encode(['status' => 'error', 'message' => 'Você precisa estar autenticado para realizar esta operação.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    }
?>