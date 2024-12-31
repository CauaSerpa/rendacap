<?php
function createSignaturePix($customer_id, $dataForm, $config, $conn) {

    // Array com ciclos
    $cycleArray = [
        'monthly' => 'MONTHLY',
        'only' => 'ONLY'
    ];

    // Verifica se o ID existe no array e pega o texto
    if (array_key_exists($dataForm['cycle'], $cycleArray)) {
        $dataForm['cycle'] = $cycleArray[$dataForm['cycle']];
    } else {
        // Em caso de erro, exibe a mensagem de erro
        echo json_encode(['status' => 'error', 'message' => 'Ciclo não informado.']);
    }

    $curl = curl_init();

    $fields = [
        "customer" => $customer_id,
        "billingType" => "PIX",
        "description" => "Plano de assinatura " . $dataForm['plan_name'] . ", PIX.",
        "value" => $dataForm["price"],
        "nextDueDate" => date('Y-m-d'),
        "cycle" => $dataForm["cycle"]
    ];

    curl_setopt_array($curl, array(
        CURLOPT_URL => $config['asaas_api_url'].'subscriptions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($fields),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'access_token: '.$config['asaas_api_key'],
            'User-Agent: '.$config['project_name']
        )
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $retorno = json_decode($response, true);

	// Verifica se a resposta foi decodificada corretamente e se a chave 'object' existe
    if (json_last_error() === JSON_ERROR_NONE) {
        if (isset($retorno['object']) && $retorno['object'] == 'subscription') {
            // Insere um novo registro na tabela tb_subscriptions
            $stmt = $conn->prepare("
                INSERT INTO tb_subscriptions (user_id, plan_id, customer_id, order_id, payment_id, cpf, cycle, price, payment_method, link_pagamento, status, creation_date, due_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $dataForm['user_id'],
                $dataForm['plan_id'],
                $customer_id,
                $dataForm['order_id'],
                $retorno['id'],
                $dataForm['cpf'],
                $retorno['cycle'],
                $retorno['value'],
                $retorno['billingType'],
                $retorno['paymentLink'],
                $retorno['status'],
                $retorno['dateCreated'],
                $retorno['nextDueDate']
            ]);

            return $retorno['id'];
        } else {
            // Se a chave 'object' não existir ou não for 'subscription', exibe a resposta
            echo json_encode($retorno, true);
            exit();
        }
    } else {
        // Se houver um erro na decodificação do JSON, exibe a resposta bruta
        echo json_encode($response, true);
        exit();
    }
}