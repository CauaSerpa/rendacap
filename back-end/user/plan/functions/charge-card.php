<?php
    function createChargeCard($customer_id, $dataForm, $config, $conn) {

        // Configura o fuso horário para São Paulo, Brasil
        date_default_timezone_set('America/Sao_Paulo');
        $date = date("Y-m-d"); // Obtém a data atual no formato "aaaa-mm-dd"

		$expiry = explode("/", $dataForm["card_expiration"]);

		// Passando valor do conforme pedido
		$dataForm['cpfCnpj'] = $dataForm['cpf'];
		$dataForm["postalCode"] = $dataForm['cep'];
		$dataForm["addressNumber"] = $dataForm['address_number'];

        $curl = curl_init();

        $fields = [
            "customer" => $customer_id,
            "billingType" => "CREDIT_CARD",
            "dueDate" => date("Y-m-d"),
            "value" => $dataForm["price"],
			"description" => "Cobrança " . $dataForm['voucher_name'] . ", Cartão de Crédito.",
			"creditCard" => [
				"holderName" => $dataForm["card_holder"],
				"number" => $dataForm["card_number"],
				"expiryMonth" => trim($expiry[0]),
				"expiryYear" => trim($expiry[1]),
				"ccv" => $dataForm["card_cvv"]
			],
			"creditCardHolderInfo" => [
				"name" => $dataForm["name"],
				"email" => $dataForm["email"],
				"cpfCnpj" => $dataForm["cpfCnpj"],
				"postalCode" => $dataForm["postalCode"],
				"addressNumber" => $dataForm["addressNumber"],
				"phone" => $dataForm["phone"]
			]
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => $config['asaas_api_url'].'payments',
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
			// Verifica se há erros na resposta da API
			if (isset($retorno['errors']) && is_array($retorno['errors'])) {
				$errorMessage = $retorno['errors'][0]['description'] ?? 'Erro desconhecido';
				echo json_encode(['status' => 'error', 'message' => $errorMessage]);
				exit();
			}

			// Verifica se a assinatura foi criada com sucesso
			if (isset($retorno['object']) && $retorno['object'] == 'payment') {

				updateOrderCard($retorno, $dataForm, $config, $conn);

				return $retorno['id'];
			} else {
				// Em caso de erro na API, exibe a mensagem de erro
				$message = $retorno['description'] ?? 'Erro desconhecido';
				echo json_encode(['status' => 'error', 'message' => $message]);
				exit();
			}
		} else {
			// Se houver um erro na decodificação do JSON, exibe a resposta bruta
			// echo json_encode($response, true);
			// Em caso de erro na API, exibe a mensagem de erro
			echo json_encode(['status' => 'error', 'message' => $response]);
			exit();
		}
    }