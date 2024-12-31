<?php
    session_start();
    include('../../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "cancel-subscription") {

        // Passa o user id na variavel
        $dataForm['user_id'] = $_SESSION["user_id"];

        // Informacoes do usuario
        $stmt = $conn->prepare("
            SELECT subscription_id
            FROM tb_subscriptions
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$dataForm['user_id']]);
        $dataForm['subscription_id'] = $stmt->fetch(PDO::FETCH_ASSOC)['subscription_id'];

        cancelSubscription($dataForm, $config);

        // Defina a mensagem de sucesso na sessão
        $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Assinatura cancelada com sucesso.');
        $_SESSION['msg'] = $message;

        $response = array(
            'status' => 'success',
            'message' => 'Requisição processada com sucesso.'
        );

        echo json_encode($response);

    }

    function cancelSubscription($dataForm, $config) {
        $curl = curl_init();

        // Campos para enviar ao Asaas
        $fields = [
            "status" => "INACTIVE"
        ];

        // Configurando a requisição para a API do Asaas
        curl_setopt_array($curl, array(
            CURLOPT_URL => $config['asaas_api_url'].'subscriptions/'.$dataForm['subscription_id'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT', // Alterado para PUT
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'access_token: '.$config['asaas_api_key'],
                'User-Agent: '.$config['project_name']
            ),
        ));

        // Executando a requisição
        $response = curl_exec($curl);
        curl_close($curl);

        // Decodificando a resposta da API
        $retorno = json_decode($response, true);

        // Verificando erros na resposta da API
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($retorno['errors']) && is_array($retorno['errors'])) {
                $errorMessage = $retorno['errors'][0]['description'] ?? 'Erro desconhecido';
                echo json_encode(['status' => 'error', 'message' => $errorMessage]);
                exit();
            }

            // Se a assinatura foi cancelada com sucesso
            if (isset($retorno['status']) && $retorno['status'] == 'CANCELLED') {
                // Atualiza as informações da assinatura no banco de dados
                $stmt = $conn->prepare("
                    UPDATE tb_subscriptions
                    SET status = 'INACTIVE'
                    WHERE subscription_id = ?
                ");
                $stmt->execute([$dataForm['subscription_id']]);

                echo json_encode(['status' => 'success', 'message' => 'Assinatura desativada com sucesso.']);
                return true;
            } else {
                // Caso a assinatura não tenha sido desativada corretamente
                $message = $retorno['description'] ?? 'Erro desconhecido';
                echo json_encode(['status' => 'error', 'message' => $message]);
                exit();
            }
        } else {
            // Caso a resposta não tenha sido um JSON válido
            echo json_encode(['status' => 'error', 'message' => $response]);
            exit();
        }
    }