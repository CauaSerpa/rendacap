<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update-profile') {

            // Sanitize input
            $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
            $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
            $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
            $whatsapp = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);
            $rg = filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_STRING);
            $pix_key_type = filter_input(INPUT_POST, 'pix_key_type', FILTER_SANITIZE_STRING);
            $pix_key = filter_input(INPUT_POST, 'pix_key', FILTER_SANITIZE_STRING);
            $birth_date = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_STRING);

            // Verifica se a data foi fornecida e está no formato esperado
            if ($birth_date && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $birth_date)) {
                $date_object = DateTime::createFromFormat('d/m/Y', $birth_date);
                if ($date_object) {
                    $birth_date = $date_object->format('Y-m-d');
                } else {
                    throw new Exception('Data de nascimento inválida.');
                }
            } else {
                throw new Exception('Por favor, insira uma data válida no formato DD/MM/AAAA.');
            }

            // Fetch current profile image from database
            $stmt = $conn->prepare("SELECT profile_image FROM tb_users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_image_path = $user['profile_image'] ?? 'no-image.svg';

            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_image']['tmp_name'];
                $fileName = $_FILES['profile_image']['name'];
                $fileSize = $_FILES['profile_image']['size'];
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];

                if ($fileSize > $max_file_size) {
                    throw new Exception('O arquivo de imagem é muito grande. O tamanho máximo permitido é de 2MB.');
                }

                if (in_array($fileExtension, $allowedfileExtensions)) {
                    $newFileName = uniqid() . '.' . $fileExtension;
                    $uploadFileDir = __DIR__ . '/../../files/profile/avatar/' . $_SESSION['user_id'] . '/';
                    $dest_path = $uploadFileDir . $newFileName;

                    if (!is_dir($uploadFileDir) && !mkdir($uploadFileDir, 0777, true)) {
                        throw new Exception('Erro ao criar o diretório de upload.');
                    }

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $stmt = $conn->prepare("UPDATE tb_users SET profile_image = ? WHERE id = ?");
                        $stmt->execute([$newFileName, $_SESSION['user_id']]);

                        if ($current_image_path && $current_image_path !== 'no-image.svg') {
                            $current_image_full_path = __DIR__ . '/../../' . $current_image_path;
                            if (file_exists($current_image_full_path)) {
                                unlink($current_image_full_path);
                            }
                        }
                    } else {
                        throw new Exception('Erro ao mover o arquivo para o diretório de upload.');
                    }
                } else {
                    throw new Exception('Tipo de arquivo não permitido.');
                }
            }

            $stmt = $conn->prepare("
                UPDATE tb_users SET
                firstname = ?, lastname = ?, phone = ?, whatsapp = ?, cpf = ?, rg = ?, pix_key_type = ?, pix_key = ?, birth_date = ?
                WHERE id = ?
            ");
            $stmt->execute([$firstname, $lastname, $phone, $whatsapp, $cpf, $rg, $pix_key_type, $pix_key, $birth_date, $_SESSION['user_id']]);

            if ($stmt->rowCount()) {
                echo json_encode(['status' => 'success', 'message' => 'Perfil atualizado com sucesso!']);
            } else {
                throw new Exception('Nenhuma alteração foi feita.');
            }

        } else {
            throw new Exception('Método de requisição inválido.');
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
?>