<?php

function getQRCodePix($subscription_id, $payment_id, $config, $conn) {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $config["asaas_api_url"]."payments/$payment_id/pixQrCode",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'access_token: '.$config["asaas_api_key"],
            'User-Agent: '.$config['project_name']
        )
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $retorno = json_decode($response, true);

    if($retorno["success"] == true) {

        $stmt = $conn->prepare("UPDATE tb_subscriptions SET pix_expiration_date = ?, pix_encoded_image = ?, pix_code = ? WHERE payment_id = ?");
        $stmt->execute([
            $retorno['expirationDate'],
            $retorno['encodedImage'],
            $retorno['payload'],
            $subscription_id
        ]);

        $pix = [];
        $pix['pix_image'] = $retorno['encodedImage'];
        $pix['pix_code'] = $retorno['payload'];

        return $pix;

    } else {
        echo $response;
        exit();
    }
}