<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    // Verifica o dia e horário permitidos para saque
    $hoje = date('w'); // 2 = Terça-feira, 4 = Quinta-feira
    $horaAtual = date('H:i');

    $diaPermitido = ($hoje >= 2 && $hoje <= 4);  // Apenas terça e quinta
    $horarioPermitido = ($horaAtual >= '08:00' && $horaAtual <= '18:00');

    // Verifica se é um pedido POST e a ação de saque foi enviada
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process-withdraw') {
        $password = $_POST['withdraw-password'];
        $amount = floatval($_POST['withdraw-amount']); // Valor do saque

        // Verifica se o usuário está logado
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];

            // Verifica se o valor do saque é maior que o mínimo permitido
            if ($amount < 50) {
                echo json_encode(['status' => 'error', 'message' => 'O valor mínimo para saque é de R$ 25,00.']);
                exit;
            }

            // Verifica se o dia e horário estão dentro do permitido
            if (!$diaPermitido || !$horarioPermitido) {
                echo json_encode(['status' => 'error', 'message' => 'Saques permitidos apenas de terça às 08:00 até quinta às 18:00.']);
                exit;
            }

            // Consulta o banco de dados para buscar a senha de transação
            $stmt = $conn->prepare("SELECT transaction_password FROM tb_users WHERE id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $hashed_password = $result['transaction_password'];

                // Verifica se a senha informada corresponde à hash no banco de dados
                if (password_verify($password, $hashed_password)) {
                    // Insere o pedido de saque na tabela tb_withdrawals
                    $stmt = $conn->prepare("INSERT INTO tb_withdrawals (user_id, amount, request_date, status) VALUES (?, ?, NOW(), 'pending')");
                    $stmt->execute([$user_id, $amount]);

                    // Sucesso
                    echo json_encode(['status' => 'success', 'message' => 'Saque processado com sucesso!']);
                    exit;
                } else {
                    // Senha inválida
                    echo json_encode(['status' => 'invalid-password', 'message' => 'Senha de saque inválida.']);
                    exit;
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado.']);
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