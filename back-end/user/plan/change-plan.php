<?php
    session_start();
    include_once('../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change-plan') {

        //Decodificando base64 e passando para $dataForm
        $dataForm = [];
        if (isset($_POST['params'])) {
            parse_str(base64_decode($_POST['params']), $dataForm);
        }

        changePlan($dataForm, $config, $conn);

        $response = array(
            'status' => 'success',
            'message' => 'Requisição processada com sucesso.'
        );

        return json_encode($response);

    }

    function changePlan($dataForm, $config, $conn){

        if(isset($_POST)) {

            // Passa o user id na variavel
            $dataForm['user_id'] = !empty($_POST['user_id']) && isset($_POST['user_id'])
                                    ? $_POST['user_id'] 
                                    : $_SESSION['user_id'];
            $dataForm['method'] = $_POST['method'];

            //
            //
            //
            //  Alterar pela tabela tb_customer_info
            //
            //
            // Informacoes do usuario
            $stmt = $conn->prepare("
                SELECT *
                FROM tb_users
                WHERE id = ?
            ");
            $stmt->execute([$dataForm['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $dataForm['name'] = $user['firstname'] . " " . $user['lastname'];
            $dataForm['email'] = $user['email'];
            $dataForm['phone'] = $user['whatsapp'];
            if ($dataForm['method'] == 'voucher') {
                $dataForm['cpf'] = $user['cpf'];
            }

            // Informacoes de endereco do usuario
            $stmt = $conn->prepare("
                SELECT *
                FROM tb_address
                WHERE user_id = ?
            ");
            $stmt->execute([$dataForm['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $dataForm['cep'] = $user['cep'];
            $dataForm['address'] = $user['address'];
            $dataForm['address_number'] = $user['number'];
            $dataForm['complement'] = $user['complement'];
            $dataForm['neighborhood'] = $user['neighborhood'];
            $dataForm['city'] = $user['city'];
            $dataForm['state'] = $user['state'];
            $dataForm['country'] = $user['country'];

            // Informacoes do plano
            $stmt = $conn->prepare("
                SELECT *
                FROM tb_plans
                WHERE id = ?
            ");
            $stmt->execute([$_POST["plan"]]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);

            $dataForm['plan_id'] = $plan['id'];
            $dataForm['plan_name'] = $plan['name'];
            $dataForm['plan_slug'] = $plan['slug'];
            $dataForm['price'] = $plan['price'];
            $dataForm['cycle'] = $plan['cycle'];

            

            // // Informacoes do voucher
            // $stmt = $conn->prepare("
            //     SELECT *
            //     FROM tb_vouchers
            //     WHERE slug = ?
            // ");
            // $stmt->execute([$_POST["voucher"]]);
            // $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

            // $dataForm['voucher_id'] = $voucher['id'];
            // $dataForm['voucher_name'] = $voucher['name'];
            // $dataForm['price'] = $voucher['price'];


            // Verificar se a variável 'plan' foi enviada via POST
            if ($_POST['method'] == 'voucher') {
                // SQL para contar os vouchers ativos por plano
                $sql = "
                    SELECT 
                        COUNT(vo.id) AS count_vouchers
                    FROM 
                        tb_voucher_orders vo
                    JOIN 
                        tb_vouchers v ON vo.voucher_id = v.id
                    WHERE 
                        vo.user_id = :user_id 
                        AND vo.status = 'completed' 
                        AND vo.used = 0 
                        AND v.slug = :plan
                ";

                // Preparar a consulta
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $dataForm['user_id']);
                $stmt->bindParam(':plan', $dataForm['plan_slug']);

                // Executar a consulta
                $stmt->execute();

                // Obter os resultados
                $vouchers = $stmt->fetch(PDO::FETCH_ASSOC);

                $dataForm['count_vouchers'] = $vouchers ? $vouchers['count_vouchers'] : 0;

                if ($dataForm['count_vouchers'] < 1) {
                    echo json_encode(['status' => 'error', 'message' => 'Vouchers insuficiente para transação']);
                    exit;
                }
            }

            //
            //
            //
            //  Colocar codigo function em pagina separada
            //
            //
            // Pega IP do usuario que esta fazendo a compra
            function getUserIP() {
                if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                    return $_SERVER['HTTP_CLIENT_IP'];
                } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    return $_SERVER['REMOTE_ADDR'];
                }
            }

            $dataForm['ip'] = getUserIP();

            // echo "<pre>";
            // print_r($dataForm);
            // echo "</pre>";
            // exit;

            include_once('./functions/create-client.php');
            include_once('./functions/order.php');

            include_once('./functions/signature-card.php');
            include_once('./functions/signature-pix.php');

            include_once('./functions/charge-card.php');
            include_once('./functions/charge-pix.php');

            include_once('./functions/get-payment-id.php');
            include_once('./functions/get-qr-code-pix.php');

            include_once('./functions/cancel-old-charges.php');

            include_once('./functions/subs-free-plan.php');

            switch($_POST["method"]) {
                case 'card':
                    $customer_id = createClient($dataForm, $config, $conn);
                    $dataForm['order_id'] = createOrder($customer_id, $dataForm, $config, $conn);
                    $subscription_id = createSignatureCard($customer_id, $dataForm, $config, $conn);

                    cancelOldSubscriptions($dataForm, $subscription_id, $config, $conn);

                    // Envia resposta
                    echo json_encode(["status"=>'success', 'message' => 'Pagamento feito com sucesso!', "code"=>$subscription_id, "id"=>$customer_id]);

                    // Finaliza o codigo
                    break;
                case 'pix':
                    $customer_id = createClient($dataForm, $config, $conn);
                    $dataForm['order_id'] = createOrder($customer_id, $dataForm, $config, $conn);
                    $subscription_id = createSignaturePix($customer_id, $dataForm, $config, $conn);
                    $payment_id = getPaymentID($subscription_id, $config, $conn);
                    $pix = getQRCodePix($subscription_id, $payment_id, $config, $conn);

                    cancelOldSubscriptions($dataForm, $subscription_id, $config, $conn);

                    // Envia resposta
                    echo json_encode(["status"=>'success', 'message' => 'Pedido criado com sucesso!', "code"=>$subscription_id, "paymentId"=>$payment_id, "pix_image"=>$pix['pix_image'], "pix_code"=>$pix['pix_code'], "id"=>$customer_id]);

                    // Finaliza o codigo
                    break;
                case 'voucher':
                    $customer_id = createClient($dataForm, $config, $conn);
                    // $dataForm['order_id'] = createOrderVoucher($customer_id, $dataForm, $config, $conn);
                    $subscription_id = subsFreePlan($customer_id, $dataForm, $config, $conn);

                    cancelOldSubscriptions($dataForm, $subscription_id, $config, $conn);

                    // Envia resposta
                    echo json_encode(["status"=>'success', 'message' => 'Pagamento feito com sucesso!', "code"=>$subscription_id, "id"=>$customer_id]);

                    // Finaliza o codigo
                    break;
                default:
                    echo json_encode(['status' => 'error', 'message' => 'Método de pagamento inválido!']);
                    break;
            }
        
        }
    }
?>