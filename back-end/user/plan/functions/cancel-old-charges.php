<?php
function cancelOldSubscriptions($dataForm, $subscription_id, $config, $conn) {

    // Nome da tabela para a busca
    $tabela = 'tb_subscriptions';

    // Consulta para buscar todas as assinaturas ativas ou recebidas do usuário
    $sql = "SELECT * FROM $tabela WHERE status IN (:status1, :status2) AND payment_method != :payment_method AND user_id = :user_id AND payment_id != :current_subscription ORDER BY id DESC";

    // Preparar e executar a consulta
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':status1', 'ACTIVE');
    $stmt->bindValue(':status2', 'INACTIVE');
    $stmt->bindValue(':payment_method', 'FREE_PLAN');
    $stmt->bindParam(':user_id', $dataForm["user_id"]);
    $stmt->bindParam(':current_subscription', $subscription_id);
    $stmt->execute();

    // Se existirem assinaturas ativas ou recebidas
    if ($stmt->rowCount() > 0) {
        
        // Recupera todas as assinaturas
        $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Percorre todas as assinaturas encontradas
        foreach ($subs as $sub) {

            // Verifica se a assinatura contém 'sub_' mas não 'free_subs_'
			if (strpos($sub['payment_id'], 'sub_') !== false) {
                $subs_id = $sub['payment_id'];
            } else if (strpos($sub['subscription_id'], 'sub_') !== false) {
                $subs_id = $sub['subscription_id'];
            } else {
                continue;
            }

            // Inicializa a requisição cURL
            $curl = curl_init();

            // Configuração da requisição cURL para cancelar a assinatura
            curl_setopt_array($curl, array(
                CURLOPT_URL => $config['asaas_api_url'] . 'subscriptions/' . $subs_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'access_token: ' . $config['asaas_api_key'],
                    'User-Agent: ' . $config['project_name']
                )
            ));

            // Executa a requisição
            $response = curl_exec($curl);

            // Fecha a conexão cURL
            curl_close($curl);

            // Decodifica a resposta JSON
            $retorno = json_decode($response, true);

        }

    }

    // Nome da tabela para a busca
    $tabela = 'tb_subscriptions';
    $sql = "UPDATE $tabela SET status = :status WHERE user_id = :user_id AND payment_id != :current_subscription";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':status', 'INACTIVE');
    $stmt->bindParam(':user_id', $dataForm["user_id"]);
    $stmt->bindParam(':current_subscription', $subscription_id);
    $stmt->execute();

}