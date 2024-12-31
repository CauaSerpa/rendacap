<?php
    session_start();
    include('../../../../config.php');

    header('Content-Type: application/json');

    //
    //
    //
    // Update Card
    //
    //
    //
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "update-card") {

        //Decodificando base64 e passando para $dataForm
        $dataForm = [];
        parse_str(base64_decode($_POST['params']), $dataForm);

        // Passa o user id na variavel
        $dataForm['user_id'] = $_SESSION["user_id"];

        // Informacoes do usuario
        $stmt = $conn->prepare("
            SELECT ci.*, s.subscription_id
            FROM tb_customer_info ci
            LEFT JOIN tb_subscriptions s ON ci.user_id = s.user_id
            WHERE ci.user_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$dataForm['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $dataForm['name'] = $user['name'];
        $dataForm['email'] = $user['email'];
        $dataForm['cpfCnpj'] = $user['cpf'];
        $dataForm['postalCode'] = $user['cep'];
        $dataForm['addressNumber'] = $user['number'];
        $dataForm['phone'] = $user['phone'];
        $dataForm['subscription_id'] = $user['subscription_id'];

        updateSubscriptionCard($dataForm, $config, $conn);

        // Defina a mensagem de sucesso na sessão
        $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Cartão alterado com sucesso.');
        $_SESSION['msg'] = $message;

        $response = array(
            'status' => 'success',
            'message' => 'Requisição processada com sucesso.'
        );

        echo json_encode($response);

    }

    function updateSubscriptionCard($dataForm, $config, $conn) {

        // Extrai o mês e ano de expiração do cartão
        $expiry = explode("/", $dataForm["card_expiration"]);

        // Inicia o CURL para fazer a requisição à API do Asaas
        $curl = curl_init();

        // Campos para enviar ao Asaas
        $fields = [
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
            CURLOPT_URL => $config['asaas_api_url'].'subscriptions/'.$dataForm["subscription_id"],
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

        // Executa a requisição e captura a resposta
        $response = curl_exec($curl);
        curl_close($curl);

        $retorno = json_decode($response, true);

        // Verifica se a resposta foi decodificada corretamente e se há erros
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($retorno['errors']) && is_array($retorno['errors'])) {
                $errorMessage = $retorno['errors'][0]['description'] ?? 'Erro desconhecido';
                echo json_encode(['status' => 'error', 'message' => $errorMessage]);
                exit();
            }

            // Verifica se o cartão foi atualizado com sucesso
            if (isset($retorno['object']) && $retorno['object'] == 'subscription') {
                // Atualiza as informações da assinatura no banco de dados
                $stmt = $conn->prepare("
                    UPDATE tb_subscriptions
                    SET card_last_digits = ?, card_brand = ?, due_date = ?
                    WHERE subscription_id = ?
                ");
                $stmt->execute([
                    substr($dataForm["card_number"], -4),
                    $retorno['creditCard']['creditCardBrand'],
                    $retorno['nextDueDate'],
                    $dataForm['subscription_id']
                ]);

                return $retorno['id'];
            } else {
                // Em caso de erro na resposta da API
                $message = $retorno['description'] ?? 'Erro desconhecido';
                echo json_encode(['status' => 'error', 'message' => $message]);
                exit();
            }
        } else {
            // Se houver erro na decodificação do JSON
            echo json_encode(['status' => 'error', 'message' => $response]);
            exit();
        }

    }

    //
    //
    //
    // Update Plan
    //
    //
    //
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "update-plan") {

        //Decodificando base64 e passando para $dataForm
        $dataForm = [];
        $dataForm['plan'] = $_POST['plan'];

        // Passa o user id na variavel
        $dataForm['user_id'] = $_SESSION["user_id"];

        // Informacoes do usuario
        $stmt = $conn->prepare("
            SELECT ci.*, s.subscription_id
            FROM tb_customer_info ci
            LEFT JOIN tb_subscriptions s ON ci.user_id = s.user_id
            WHERE ci.user_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$dataForm['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $dataForm['subscription_id'] = $user['subscription_id'];

        // Informacoes do plano
        $stmt = $conn->prepare("
            SELECT *
            FROM tb_plans
            WHERE slug = ?
            LIMIT 1
        ");
        $stmt->execute([$dataForm['plan']]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        $dataForm['plan_id'] = $plan['id'];
        $dataForm['value'] = $plan['price'];

        updatePlan($dataForm, $config, $conn);

        // Defina a mensagem de sucesso na sessão
        $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Cartão alterado com sucesso.');
        $_SESSION['msg'] = $message;

        $response = array(
            'status' => 'success',
            'message' => 'Requisição processada com sucesso.'
        );

        echo json_encode($response);

    }

    // Update Plan
    function updatePlan($dataForm, $config, $conn) {

        // Inicia o CURL para fazer a requisição à API do Asaas
        $curl = curl_init();

        // Campos para enviar ao Asaas
        $fields = [
            "value" => $dataForm['value']
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => $config['asaas_api_url'].'subscriptions/'.$dataForm["subscription_id"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'access_token: '.$config['asaas_api_key'],
                'User-Agent: '.$config['project_name']
            )
        ));

        // Executa a requisição e captura a resposta
        $response = curl_exec($curl);
        curl_close($curl);

        $retorno = json_decode($response, true);

        // Verifica se a resposta foi decodificada corretamente e se há erros
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($retorno['errors']) && is_array($retorno['errors'])) {
                $errorMessage = $retorno['errors'][0]['description'] ?? 'Erro desconhecido';
                echo json_encode(['status' => 'error', 'message' => $errorMessage]);
                exit();
            }

            // Verifica se o cartão foi atualizado com sucesso
            if (isset($retorno['object']) && $retorno['object'] == 'subscription') {
                // Atualiza as informações da assinatura no banco de dados
                $stmt = $conn->prepare("
                    UPDATE tb_subscriptions
                    SET price = ?
                    WHERE subscription_id = ?
                ");
                $stmt->execute([
                    $retorno['price'],
                    $dataForm['subscription_id']
                ]);

                return $retorno['id'];
            } else {
                // Em caso de erro na resposta da API
                $message = $retorno['description'] ?? 'Erro desconhecido';
                echo json_encode(['status' => 'error', 'message' => $message]);
                exit();
            }
        } else {
            // Se houver erro na decodificação do JSON
            echo json_encode(['status' => 'error', 'message' => $response]);
            exit();
        }

    }