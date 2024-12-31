<?php
	function createOrder($customer_id, $dataForm, $config, $conn) {

        $order['status'] = "pending";

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("
            INSERT INTO tb_orders (user_id, customer_id, plan_id, cpf, order_status, total_amount, payment_method, cep, address, number, complement, neighborhood, city, state)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dataForm['user_id'],
            $customer_id,
            $dataForm['plan_id'],
            $dataForm['cpf'],
            $order['status'],
            $dataForm['price'],
            $dataForm['method'],
            $dataForm['cep'],
            $dataForm['address'],
            $dataForm['address_number'],
            $dataForm['complement'],
            $dataForm['neighborhood'],
            $dataForm['city'],
            $dataForm['state']
        ]);

        // Recupere o ID do último registro inserido
        $dataForm['order_id'] = $conn->lastInsertId();

        return $dataForm['order_id'];

	}

	function updateOrderCard($retorno, $dataForm, $config, $conn) {

        $order['status'] = "paid";

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("UPDATE tb_orders SET payment_status = ?, card_last_digits = ?, card_brand = ? WHERE id = ?");
        $stmt->execute([
            $retorno['status'],
            $retorno['creditCard']['creditCardNumber'],
            $retorno['creditCard']['creditCardBrand'],
            $dataForm['order_id']
        ]);

	}

	function updateOrderPix($retorno, $dataForm, $config, $conn) {

        $order['status'] = "paid";

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("UPDATE tb_orders SET payment_status = ? WHERE id = ?");
        $stmt->execute([
            $retorno['status'],
            $dataForm['order_id']
        ]);

	}

	function createOrderVoucher($customer_id, $dataForm, $config, $conn) {

        $order['status'] = "paid";

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("
            INSERT INTO tb_orders (user_id, customer_id, plan_id, cpf, order_status, total_amount, payment_method, cep, address, number, complement, neighborhood, city, state)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dataForm['user_id'],
            $customer_id,
            $dataForm['plan_id'],
            $dataForm['cpf'],
            $order['status'],
            $dataForm['price'],
            $dataForm['cycle'],
            $dataForm['cep'],
            $dataForm['address'],
            $dataForm['address_number'],
            $dataForm['complement'],
            $dataForm['neighborhood'],
            $dataForm['city'],
            $dataForm['state']
        ]);

        // Recupere o ID do último registro inserido
        $dataForm['order_id'] = $conn->lastInsertId();

        return $dataForm['order_id'];

	}
?>