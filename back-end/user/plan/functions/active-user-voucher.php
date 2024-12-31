<?php
session_start();
include_once('../../../../config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'active-user-voucher') {
    // Supondo que você tenha uma conexão com o banco já configurada em $conn
    $userId = $_POST['userId']; // ID do usuário

    // Consulta para pegar o último payment_id do usuário na tabela tb_subscriptions
    $stmt = $conn->prepare("SELECT payment_id FROM tb_subscriptions WHERE user_id = ? ORDER BY status = 'INACTIVE' DESC, id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $lastPayment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lastPayment) {
        echo json_encode(['status' => 'error', 'message' => 'Nenhum pagamento encontrado para o usuário.']);
        exit();
    }

    // Pega o payment_id do último pagamento
    $paymentId = $lastPayment['payment_id'];

    // Inicializa o cURL para fazer a requisição GET no endpoint de cobranças
    $curl = curl_init();

    // Configura a requisição cURL para chamar o endpoint de listagem de pagamentos
    curl_setopt_array($curl, array(
        CURLOPT_URL => $config['asaas_api_url']."subscriptions/$paymentId/payments", // Substitua pelo id correto da assinatura
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET', // Método GET
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json', // Define o tipo de conteúdo como JSON
            'access_token: ' . $config['asaas_api_key'], // Substitua com sua chave de acesso
            'User-Agent: ' . $config['project_name'] // Identificador do seu projeto (opcional)
        ),
    ));

    // Executa a requisição e captura a resposta
    $response = curl_exec($curl);

    // Verifica se ocorreu algum erro com o cURL
    if(curl_errno($curl)) {
        echo 'Error:' . curl_error($curl);
    }

    // Fecha a conexão cURL
    curl_close($curl);

    // Decodifica a resposta JSON
    $responseDecoded = json_decode($response, true);

    // Verifica se a resposta foi bem-sucedida
    if (json_last_error() === JSON_ERROR_NONE) {
        // Verifica se há cobranças na resposta
        if (isset($responseDecoded['data']) && is_array($responseDecoded['data'])) {
            // Percorre as cobranças e atualiza o status
            foreach ($responseDecoded['data'] as $payment) {
                // Verifica se a cobrança não está paga ou está vencida
                if ($payment['status'] != 'PAID' || $payment['status'] != 'RECEIVED_IN_CASH') {
                    // Atualiza o status para "pago em dinheiro"
                    $paymentFields = [
                        "paymentDate" => date('Y-m-d'), // Data do recebimento
                        "value" => $payment['value'], // Valor recebido
                    ];

                    // Inicializa o cURL para chamar o endpoint de atualização de status de pagamento
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $config['asaas_api_url']."payments/{$payment['id']}/receiveInCash", // Substitua pelo id correto do pagamento
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST', // Método POST
                        CURLOPT_POSTFIELDS => json_encode($paymentFields), // Passando os dados no formato JSON
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json', // Define o tipo de conteúdo como JSON
                            'access_token: ' . $config['asaas_api_key'], // Substitua com sua chave de acesso
                            'User-Agent: ' . $config['project_name'] // Identificador do seu projeto (opcional)
                        ),
                    ));

                    // Executa a requisição e captura a resposta
                    $response = curl_exec($curl);

                    // Verifica se ocorreu algum erro com o cURL
                    if(curl_errno($curl)) {
                        echo 'Error:' . curl_error($curl);
                    }

                    // Fecha a conexão cURL
                    curl_close($curl);

                    // Decodifica a resposta JSON
                    $responseDecoded = json_decode($response, true);

                    // Verifica se a resposta foi bem-sucedida
                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (isset($responseDecoded['status']) && $responseDecoded['status'] !== 'RECEIVED_IN_CASH') {
                            echo json_encode(['status' => 'error', 'message' => 'Falha ao confirmar o pagamento.']);
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Erro ao processar a resposta da API.']);
                        exit();
                    }
                }
            }

            // Atualiza o status da assinatura na tabela tb_subscriptions
            $updateStmt = $conn->prepare("UPDATE tb_subscriptions SET status = 'ACTIVE' WHERE payment_id = ?");
            $updateStmt->execute([$paymentId]);

            // Preparar a consulta
            $stmt = $conn->prepare("UPDATE tb_voucher_orders SET used = 1 WHERE user_id = ? AND status = 'completed' AND used = 0 ORDER BY id ASC LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);

            echo json_encode(['status' => 'success', 'message' => 'Usuário ativado com sucesso.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Nenhuma cobrança encontrada.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao processar a resposta da API.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método inválido.']);
    exit();
}
?>