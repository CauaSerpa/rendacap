<?php
	function createClient($dataForm, $config, $conn) {

		// Passando valor do conforme pedido
		$dataForm['cpfCnpj'] = $dataForm['cpf'];
		$dataForm["mobilePhone"] = $dataForm['phone'];
		$dataForm["postalCode"] = $dataForm['cep'];
		$dataForm["addressNumber"] = $dataForm['address_number'];
		$dataForm["province"] = $dataForm['neighborhood'];
		$dataForm["externalReference"] = $dataForm['user_id'];
		// $dataForm["groupName"] = 'RendaCap';

		// Cria um array com os dados a serem enviados para a API
		$fields = [
			"name" => $dataForm["name"],
			"cpfCnpj" => $dataForm["cpfCnpj"],
			"email" => $dataForm["email"],
			"mobilePhone" => $dataForm["mobilePhone"],
			"postalCode" => $dataForm["postalCode"],
			"address" => $dataForm["address"],
			"addressNumber" => $dataForm["addressNumber"],
			"complement" => $dataForm["complement"],
			"province" => $dataForm["province"],
			"externalReference" => $dataForm["externalReference"],
			// "groupName" => $dataForm["groupName"]
		];

		// Verifica se há um cliente existente na tabela tb_customer_info
		$stmt = $conn->prepare("
			SELECT customer_id
			FROM tb_customer_info
			WHERE user_id = ?
		");
		$stmt->execute([$dataForm['user_id']]);
		$customer = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($customer && isset($customer['customer_id'])) {
			// Se já existe, retorna o customer_id
			return $customer['customer_id'];
		} else {
			// Se não existe, faz a requisição à API para criar um novo cliente
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $config['asaas_api_url'].'customers',
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
	
			if ($retorno['object'] === 'customer') {
				// Verifica se o usuário já está registrado na tabela tb_customer_info
				if ($customer) {
					// Atualiza o registro existente
					$stmt = $conn->prepare("
						UPDATE tb_customer_info 
						SET customer_id = ? 
						WHERE user_id = ?
					");
					$stmt->execute([$retorno['id'], $dataForm['user_id']]);
				} else {
					// Busca os dados do usuário na tabela tb_users
					$stmt = $conn->prepare("
						SELECT firstname, lastname, email, phone, cpf
						FROM tb_users
						WHERE id = ?
					");
					$stmt->execute([$dataForm['user_id']]);
					$user = $stmt->fetch(PDO::FETCH_ASSOC);
	
					if ($user) {
						// Concatena o nome completo
						$user['fullname'] = $user['firstname'] . " " . $user['lastname'];
	
						// Insere um novo registro na tabela tb_customer_info
						$stmt = $conn->prepare("
							INSERT INTO tb_customer_info (user_id, customer_id, name, email, phone, cpf)
							VALUES (?, ?, ?, ?, ?, ?)
						");
						$stmt->execute([
							$dataForm['user_id'],
							$retorno['id'],
							$user['fullname'],
							$user['email'],
							$user['phone'],
							$user['cpf']
						]);
					} else {
						// Se não encontrou o usuário na tabela tb_users
						echo json_encode(['status' => 'error', 'message' => 'Por favor, faça login para continuar']);
						exit;
					}
				}
	
				return $retorno['id'];
			} else {
				// Em caso de erro na API, exibe a mensagem de erro
				echo json_encode(['status' => 'error', 'message' => $response]);
				exit;
			}
		}
	}
?>