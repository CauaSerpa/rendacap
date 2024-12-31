<?php
	function subsFreePlan($customer_id, $dataForm, $config, $conn) {
        $order['status'] = "paid";
        $dataForm['payment_status'] = "ACTIVE";
        $dataForm['cycle'] = 'only';

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("
            INSERT INTO tb_orders (user_id, customer_id, plan_id, cpf, order_status, total_amount, payment_method, payment_status, cep, address, number, complement, neighborhood, city, state)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dataForm['user_id'],
            $customer_id,
            $dataForm['plan_id'],
            $dataForm['cpf'],
            $order['status'],
            $dataForm['price'],
            $dataForm['cycle'],
            $dataForm['payment_status'],
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

        // Gera a assinatura
        // $dataForm['id'] = 'free_subs_'.time();
        $dataForm['id'] = 'subs_'.time();
        $dataForm['card_last_digits'] = 4242;
        $dataForm['card_brand'] = 'VISA';
        $dataForm['creation_date'] = date('Y-m-d');
        $dataForm['due_date'] = date('Y-m-d', strtotime(date('Y-m-d') . ' +1 month'));
        $dataForm['payment_date'] = date('Y-m-d');
        $dataForm['status'] = "ACTIVE";
        $dataForm['payment_method'] = "VOUCHER";
        $dataForm['payment_status'] = "PAID";

        $retorno['status'] = $dataForm['status'];

        // Insere um novo registro na tabela tb_subscriptions
        $stmt = $conn->prepare("
            INSERT INTO tb_subscriptions (user_id, plan_id, customer_id, order_id, payment_id, cpf, cycle, price, payment_method, payment_status, status, creation_date, due_date, payment_date)
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
            $dataForm['payment_status'],
            $dataForm['status'],
            $dataForm['creation_date'],
            $dataForm['due_date'],
            $dataForm['payment_date'],
        ]);

        $stmt = $conn->prepare("UPDATE tb_voucher_orders SET used = 1 WHERE user_id = ? AND status = 'completed' AND used = 0 ORDER BY id ASC LIMIT 1");
        $stmt->execute([$dataForm['user_id']]);

        return $dataForm['id'];
	}
?>