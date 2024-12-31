<?php
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $response = array('status' => 'success', 'message' => 'Valor válido.');

        $action = $_POST['action'];
        $value = $_POST['value'];

        switch ($action) {
            case 'check-email':
                $stmt = $conn->prepare("SELECT id FROM tb_users WHERE email = ?");
                break;
            case 'check-cpf':
                $stmt = $conn->prepare("SELECT id FROM tb_users WHERE cpf = ?");
                break;
            case 'check-rg':
                $stmt = $conn->prepare("SELECT id FROM tb_users WHERE rg = ?");
                break;
            case 'check-username':
                $stmt = $conn->prepare("SELECT id FROM tb_users WHERE username = ?");
                break;
            default:
                $response['status'] = 'error';
                $response['message'] = 'Ação não reconhecida.';
                echo json_encode($response);
                exit;
        }

        $stmt->execute([$value]);
        if ($stmt->rowCount() > 0) {
            $response['status'] = 'error';
            $response['message'] = ucfirst(str_replace('check-', '', $action)) . ' já cadastrado.';
        }

        echo json_encode($response);
    }
?>