<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    // Processa o formulário de pagamento
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process-payment') {
        $withdrawal_id = $_POST['withdrawal_id'];
        $admin_note = $_POST['admin_note'];

        // Verifica se um comprovante foi enviado
        if (!empty($_FILES['receipt']['name'])) {
            $receipt_name = $_FILES['receipt']['name'];
            $receipt_tmp_name = $_FILES['receipt']['tmp_name'];

            // Define o caminho principal de uploads
            $base_folder = '../../files/uploads/comprovantes/';

            // Cria a subpasta com o ID do saque (withdrawal_id)
            $withdrawal_folder = $base_folder . $withdrawal_id . '/';

            // Verifica se a pasta já existe, caso contrário, cria a pasta
            if (!is_dir($withdrawal_folder)) {
                if (!mkdir($withdrawal_folder, 0777, true)) {
                    echo json_encode(['status' => 'error', 'message' => 'Erro ao criar diretório para o comprovante.']);
                    exit();
                }
            }

            // Define o caminho completo para o arquivo dentro da subpasta do saque
            $proof_of_transfer_folder = $withdrawal_folder . $receipt_name;
            $proof_of_transfer = INCLUDE_PATH_DASHBOARD . "files/uploads/comprovantes/" . $withdrawal_id . '/' . $receipt_name;

            // Move o arquivo para o diretório de uploads
            if (move_uploaded_file($receipt_tmp_name, $proof_of_transfer_folder)) {
                // Atualiza o comprovante no banco de dados
                $query = "UPDATE tb_withdrawals SET proof_of_transfer = :proof_of_transfer WHERE id = :withdrawal_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':proof_of_transfer', $proof_of_transfer);
                $stmt->bindParam(':withdrawal_id', $withdrawal_id);
                $stmt->execute();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao mover o arquivo para o diretório de uploads.']);
                exit();
            }
        }

        // Atualiza o status, a nota do administrador e a data de pagamento
        $status = 'paid';
        $payment_date = date('Y-m-d H:i:s');
        $query = "UPDATE tb_withdrawals SET status = :status, admin_notes = :admin_notes, payment_date = :payment_date WHERE id = :withdrawal_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':admin_notes', $admin_note);
        $stmt->bindParam(':payment_date', $payment_date);
        $stmt->bindParam(':withdrawal_id', $withdrawal_id);

        if ($stmt->execute()) {
            // Resposta de sucesso
            echo json_encode(['status' => 'success', 'message' => 'Saque atualizado com sucesso.']);
            exit();
        } else {
            // Resposta de erro
            echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o saque.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
        exit();
    }
?>