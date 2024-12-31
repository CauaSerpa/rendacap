<?php
    session_start();
    include_once('../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'invoice-info') {

        //Decodificando base64 e passando para $dataForm
        $dataForm = [];
        if (isset($_POST['params'])) {
            parse_str(base64_decode($_POST['params']), $dataForm);
        }

        createSubscription($dataForm, $config, $conn);

        $response = array(
            'status' => 'success',
            'message' => 'Requisição processada com sucesso.'
        );

        return json_encode($response);

    }

    function createSubscription($dataForm, $config, $conn){

        if(isset($_POST)) {

            // Passa o user id na variavel
            $dataForm['user_id'] = (isset($_SESSION["user_id_signature"])) ? $_SESSION["user_id_signature"] : $_SESSION["user_id"];
            $dataForm['method'] = $_POST['method'] ?? "free_plan";

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
            $dataForm['cpf'] = $user['cpf'];

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
            $stmt->execute([$_POST["plan_id"]]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);

            $dataForm['plan_id'] = $plan['id'];
            $dataForm['plan_name'] = $plan['name'];
            $dataForm['price'] = $plan['price'];
            $dataForm['cycle'] = $plan['cycle'];

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

            // if ($plan["plan_id"] == "monthly") {
            //     $dataForm["period"] = "MONTHLY";
            // } else {
            //     $plan["period"] = "only";
            // }

            // Iniciando variavel "$subscription_id"
            // $subscription_id = null;

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

            if ($dataForm['plan_id'] == 3) {
                $customer_id = createClient($dataForm, $config, $conn);
                $payment_id = subsFreePlan($customer_id, $dataForm, $config, $conn);
                cancelOldSubscriptions($dataForm, $payment_id, $config, $conn);

                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Pagamento feito com sucesso!');
                $_SESSION['msg'] = $message;

                // Armazena o informacoes em uma session
                $_SESSION['user_id'] = $dataForm['user_id'];
                $_SESSION['email'] = $dataForm['email'];

                // Limpar o código 2FA da sessão e do banco de dados
                unset($_SESSION['user_id_signature']);
                unset($_SESSION['user_email_signature']);

                // Envia resposta
                echo json_encode(["status"=>'success', "code"=>$payment_id, "id"=>$customer_id]);

                // Finaliza o codigo
                exit;
            }

            switch($_POST["method"]) {
                case 'card':
                    $customer_id = createClient($dataForm, $config, $conn);
                    $dataForm['order_id'] = createOrder($customer_id, $dataForm, $config, $conn);
                    $payment_id = createSignatureCard($customer_id, $dataForm, $config, $conn);
                    cancelOldSubscriptions($dataForm, $payment_id, $config, $conn);

                    // Defina a mensagem de sucesso na sessão
                    $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Pagamento feito com sucesso!');
                    $_SESSION['msg'] = $message;

                    // Armazena o informacoes em uma session
                    $_SESSION['user_id'] = $dataForm['user_id'];
                    $_SESSION['email'] = $dataForm['email'];

                    // Limpar o código 2FA da sessão e do banco de dados
                    unset($_SESSION['user_id_signature']);
                    unset($_SESSION['user_email_signature']);

                    // Envia resposta
                    echo json_encode(["status"=>'success', "code"=>$payment_id, "id"=>$customer_id]);

                    // Finaliza o codigo
                    break;
                case 'pix':
                    $customer_id = createClient($dataForm, $config, $conn);
                    $dataForm['order_id'] = createOrder($customer_id, $dataForm, $config, $conn);
                    $subscription_id = createSignaturePix($customer_id, $dataForm, $config, $conn);
                    $payment_id = getPaymentID($subscription_id, $config, $conn);
                    getQRCodePix($subscription_id, $payment_id, $config, $conn);
                    cancelOldSubscriptions($dataForm, $subscription_id, $config, $conn);

                    // Defina a mensagem de sucesso na sessão
                    $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Pedido criado com sucesso!');
                    $_SESSION['msg'] = $message;

                    // Armazena o informacoes em uma session
                    $_SESSION['user_id'] = $dataForm['user_id'];
                    $_SESSION['email'] = $dataForm['email'];

                    // Limpar o código 2FA da sessão e do banco de dados
                    unset($_SESSION['user_id_signature']);
                    unset($_SESSION['user_email_signature']);

                    // Envia resposta
                    echo json_encode(["status"=>'success', "code"=>$subscription_id, "paymentId"=>$payment_id, "id"=>$customer_id]);

                    // Finaliza o codigo
                    break;
                default:
                    echo json_encode(['status' => 'error', 'message' => 'Método de pagamento inválido!']);
                    break;
            }
        
        }
    }
?>