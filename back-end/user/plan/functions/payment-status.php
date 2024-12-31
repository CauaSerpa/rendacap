<?php
    session_start();
    include('../../../../config.php');

    $id = base64_decode($_POST['payment_id']);
    $subs_id = base64_decode($_POST['subs_id']);

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
            atualizarBancoDeDados($subs_id, 'paid', 'ACTIVE', $conn);

            $paymentStatus = 'paid'; // Atualiza o status para 'pago'
        }
    }

    function atualizarBancoDeDados($subs_id, $order_status, $subs_status, $conn) {
        // Durante o processo de login
        $stmt = $conn->prepare("
            SELECT *
            FROM tb_subscriptions
            WHERE payment_id = ?
        ");
        $stmt->execute([$subs_id]);

        if ($stmt->rowCount()) {
            $subs = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("UPDATE tb_orders SET order_status = ?, payment_status = ? WHERE id = ?");
            $stmt->execute([$order_status, $subs_status, $subs['order_id']]);

            $stmt = $conn->prepare("UPDATE tb_subscriptions SET status = ?, payment_status = ? WHERE payment_id = ?");
            $stmt->execute([$subs_status, $subs_status, $subs_id]);
        }
    }

    // Responda ao cliente
    echo json_encode(['status' => 'success', 'message' => 'Pagamento feito com sucesso!', 'paymentStatus' => $paymentStatus]);