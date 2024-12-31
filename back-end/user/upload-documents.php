<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload-documents') {
        // Configurações
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $uploadDir = __DIR__ . '/../../files/profile/documents/' . $_SESSION['user_id'] . '/';
        $response = [];

        // Criação do diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Função para salvar detalhes no banco de dados
        function saveDocumentToDB($conn, $userId, $documentType, $fileName, $fileMimeType) {
            $stmt = $conn->prepare("INSERT INTO tb_user_documents (user_id, document_type, file_name, mime_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $documentType, $fileName, $fileMimeType]);
        }

        // Função para processar upload de arquivos
        function processUpload($file, $documentType, $uploadDir, $conn) {
            global $allowedTypes, $max_file_size, $response;

            $fileMimeType = mime_content_type($file['tmp_name']);
            $fileSize = $file['size'];

            if (in_array($fileMimeType, $allowedTypes)) {
                if ($fileSize <= $max_file_size) {
                    $fileName = basename($file['name']);
                    $filePath = $uploadDir . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);

                    if (move_uploaded_file($file['tmp_name'], $filePath)) {
                        saveDocumentToDB($conn, $_SESSION['user_id'], $documentType, $fileName, $fileMimeType);
                    } else {
                        $response[$documentType] = 'Falha ao enviar ' . $documentType . ': ' . $fileName;
                    }
                } else {
                    $response[$documentType] = 'O arquivo ' . $fileName . ' excede o tamanho máximo permitido de 2MB.';
                }
            } else {
                $response[$documentType] = 'Tipo de arquivo não permitido para ' . $documentType . ': ' . $fileName;
            }
        }

        // Upload da Identidade
        if (isset($_FILES['identity_document'])) {
            foreach ($_FILES['identity_document']['tmp_name'] as $key => $tmp_name) {
                $file = [
                    'tmp_name' => $_FILES['identity_document']['tmp_name'][$key],
                    'name' => $_FILES['identity_document']['name'][$key],
                    'size' => $_FILES['identity_document']['size'][$key],
                ];
                processUpload($file, 'identity', $uploadDir, $conn);
            }
        }

        // Upload do RG
        if (isset($_FILES['rg_document'])) {
            $file = $_FILES['rg_document'];
            processUpload($file, 'rg', $uploadDir, $conn);
        }

        // Upload do CPF
        if (isset($_FILES['cpf_document'])) {
            $file = $_FILES['cpf_document'];
            processUpload($file, 'cpf', $uploadDir, $conn);
        }

        if (!empty($response)) {
            // Retorna a resposta com mensagens de erro ou sucesso
            echo json_encode(['status' => 'error', 'message' => $response]);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Documentos enviados com sucesso.']);
        }
    }
?>