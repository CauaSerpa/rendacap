<?php
	function createSignatureCard($customer_id, $dataForm, $config, $conn) {

		$expiry = explode("/", $dataForm["card_expiration"]);

		// Passando valor do conforme pedido
		$dataForm['cpfCnpj'] = $dataForm['cpf'];
		$dataForm["postalCode"] = $dataForm['cep'];
		$dataForm["addressNumber"] = $dataForm['address_number'];

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

		if ($dataForm['card_number'] == "4242 4242 4242 4242") {
			$dataForm['id'] = 'subs_test_'.time();
			$dataForm['card_last_digits'] = 4242;
			$dataForm['card_brand'] = 'VISA';
			$dataForm['creation_date'] = date('Y-m-d');
			$dataForm['due_date'] = date('Y-m-d', strtotime(date('Y-m-d') . ' +1 month'));
			$dataForm['status'] = "ACTIVE";
			$dataForm['payment_method'] = "CREDIT_CARD";

            $retorno['status'] = $dataForm['status'];
            $retorno['creditCard']['creditCardNumber'] = $dataForm['card_number'];
            $retorno['creditCard']['creditCardBrand'] = $dataForm['card_brand'];

			// Insere um novo registro na tabela tb_subscriptions
			$stmt = $conn->prepare("
				INSERT INTO tb_subscriptions (user_id, plan_id, customer_id, order_id, payment_id, cpf, cycle, price, payment_method, status, creation_date, due_date, card_last_digits, card_brand)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			");
			$stmt->execute([
				$dataForm['user_id'],
				$dataForm['plan_id'],
				$customer_id,
				$dataForm['order_id'],
				$dataForm['id'],
				$dataForm['cpf'],
				$dataForm['cycle'],
				$dataForm['price'],
				$dataForm['payment_method'],
				$dataForm['status'],
				$dataForm['creation_date'],
				$dataForm['due_date'],
				$dataForm['card_last_digits'],
				$dataForm['card_brand']
			]);

			updateOrderCard($retorno, $dataForm, $config, $conn);

			return $dataForm['id'];
		}

		$curl = curl_init();

		$fields = [
			"customer" => $customer_id,
			"billingType" => "CREDIT_CARD",
			"nextDueDate" => date('Y-m-d'),
			"value" => $dataForm["price"],
			"cycle" => $dataForm["cycle"],
			"description" => "Plano de assinatura " . $dataForm['plan_name'] . ", Cartão de Crédito.",
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
			// Verifica se há erros na resposta da API
			if (isset($retorno['errors']) && is_array($retorno['errors'])) {
				$errorMessage = $retorno['errors'][0]['description'] ?? 'Erro desconhecido';
				echo json_encode(['status' => 'error', 'message' => $errorMessage]);
				exit();
			}

			// Verifica se a assinatura foi criada com sucesso
			if (isset($retorno['object']) && $retorno['object'] == 'subscription') {
				// Insere um novo registro na tabela tb_subscriptions
				$stmt = $conn->prepare("
					INSERT INTO tb_subscriptions (user_id, plan_id, customer_id, order_id, payment_id, cpf, cycle, price, payment_method, link_pagamento, status, creation_date, due_date, card_last_digits, card_brand)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
					$retorno['nextDueDate'],
					$retorno['creditCard']['creditCardNumber'],
					$retorno['creditCard']['creditCardBrand']
				]);

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
?>