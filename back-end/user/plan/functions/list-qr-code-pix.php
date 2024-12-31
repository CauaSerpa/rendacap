<?php
    include('../../../config.php');

    $payment_id = base64_decode($_POST['paymentId']);

    $stmt = $conn->prepare("SELECT * FROM tb_subscriptions WHERE payment_id = ?");
    $stmt->execute([$payment_id]);

    if ($stmt->rowCount()) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            "status" => "success",
            "price" => $result['price'],
            "pixEncodedImage" => $result['pix_encoded_image'],
            "pixCode" => $result['pix_code'],
            "pixExpirationDate" => $result['pix_expiration_date'] ? date("d/m/Y", strtotime($result['pix_expiration_date'])) : null,
            "dueDate" => $result['due_date'] ? date("d/m/Y", strtotime($result['due_date'])) : null,
            "creationDate" => $result['creation_date'] ? date("d/m/Y", strtotime($result['creation_date'])) : null
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nenhum resultado encontrado.']);
    }
