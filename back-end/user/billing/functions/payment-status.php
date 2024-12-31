<?php
    session_start();
    include('../../../../config.php');

    $id = base64_decode($_POST['payment_id']);

    $paymentStatus = "";

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $config['asaas_api_url']."payments?invoice={$id}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'access_token: ' . $config['asaas_api_key'],
            'User-Agent: '.$config['project_name']
        )
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $payments = json_decode($response, true);

    // print_r($payments);
    foreach ($payments['data'] as $payment) {
        $asaas_id = $payment['id'];
        $status = $payment['status'];

        // Verificar se o pagamento foi concluído
        if ($asaas_id == $id && $status == 'RECEIVED') {
            // O pagamento foi recebido, você pode prosseguir com a atualização no banco de dados
            // Chame uma função para atualizar o banco de dados com o status do pagamento
            atualizarBancoDeDados($id, 'completed', $conn);

            $paymentStatus = 'paid'; // Atualiza o status para 'paid'
        }
    }

    function atualizarBancoDeDados($id, $status, $conn) {
        $stmt = $conn->prepare("UPDATE tb_voucher_orders SET status = ? WHERE payment_id = ?");
        $stmt->execute([$status, $id]);
    }

    // Responda ao cliente
    echo json_encode(['status' => 'success', 'message' => 'Pagamento feito com sucesso!', 'paymentStatus' => $paymentStatus]);