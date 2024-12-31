<?php
	function createOrder($customer_id, $dataForm, $config, $conn) {

        $order['status'] = "pending";

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("
            INSERT INTO tb_voucher_orders (user_id, customer_id, cpf, voucher_id, payment_method, price)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dataForm['user_id'],
            $customer_id,
            $dataForm['cpf'],
            $dataForm['voucher_id'],
            $_POST['method'],
            $dataForm['price']
        ]);

        // Recupere o ID do último registro inserido
        $dataForm['order_id'] = $conn->lastInsertId();

        return $dataForm['order_id'];

	}

	function updateOrderCard($retorno, $dataForm, $config, $conn) {

        $order['status'] = "completed";

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("UPDATE tb_voucher_orders SET status = ?, payment_date = NOW(), card_last_digits = ?, card_brand = ? WHERE id = ?");
        $stmt->execute([
            $order['status'],
            $retorno['creditCard']['creditCardNumber'],
            $retorno['creditCard']['creditCardBrand'],
            $dataForm['order_id']
        ]);

	}

	function updateOrderPix($retorno, $dataForm, $config, $conn) {

        $order['status'] = "completed";

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("UPDATE tb_voucher_orders SET status = ?, payment_id = ?, payment_date = NOW(), link_pagamento = ? WHERE id = ?");
        $stmt->execute([
            $retorno['status'],
            $retorno['id'],
            $retorno['paymentLink'],
            $dataForm['order_id']
        ]);

	}

	function createOrderCredit($dataForm, $config, $conn) {

        if ($_POST['availableCredit'] < $dataForm['price']) {
            echo json_encode(['status' => 'error', 'message' => 'Crédito insuficiente para transação']);
            exit;
        }

        $order['status'] = "completed";

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("
            INSERT INTO tb_voucher_orders (user_id, cpf, voucher_id, payment_method, price)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dataForm['user_id'],
            $dataForm['cpf'],
            $dataForm['voucher_id'],
            $_POST['method'],
            $dataForm['price']
        ]);

        // Recupere o ID do último registro inserido
        $dataForm['order_id'] = $conn->lastInsertId();

        return $dataForm['order_id'];

	}
?>