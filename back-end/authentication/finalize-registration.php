<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    function generateSequentialToken($user_id) {
        // Formatar o número de sequência com zeros à esquerda
        $formatted_sequence = str_pad($user_id, 6, '0', STR_PAD_LEFT);
        $token = '3005' . $formatted_sequence;

        return $token;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "finalize-registration") {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $whatsapp = $_POST['phone'];

        // Altera data para padrao do banco de dados
        $birth_date = $_POST['birth_date'];
        $birth_date = DateTime::createFromFormat('d/m/Y', $birth_date);
        $birthday = $birth_date->format('Y-m-d');

        // Endereco
        $cep = $_POST['cep'];
        $address = $_POST['address'];
        $address_number = (!isset($_POST['no_address_number'])) ? $_POST['address_number'] : 0;
        $complement = $_POST['complement'];
        $neighborhood = $_POST['neighborhood'];
        $city = $_POST['city'];
        $state = $_POST['state'];

        $cpf = $_POST['cpf'];
        $rg = $_POST['rg'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $active_token = generateToken();
        $agree = isset($_POST['agree']) ? $_POST['agree'] : null;
    
        // Validação se o campo 'agree' está marcado
        if (!$agree) {
            echo json_encode(['status' => 'error', 'message' => 'Você deve concordar com os termos para continuar.']);
            exit;
        }

        try {
            // Iniciar transação
            if (!$conn) {
                throw new Exception("Conexão inválida com o banco de dados.");
            }
            $conn->beginTransaction();

            $stmt = $conn->prepare("UPDATE tb_users SET
                                        status = 1,
                                        firstname = ?,
                                        lastname = ?,
                                        email = ?,
                                        phone = ?,
                                        whatsapp = ?,
                                        birth_date = ?,
                                        cpf = ?,
                                        rg = ?,
                                        username = ?,
                                        password = ?,
                                        active_token = ?
                                    WHERE id = ?");
            $stmt->execute([$firstname, $lastname, $email, $phone, $whatsapp, $birthday, $cpf, $rg, $username, $password, $active_token, $_SESSION['user_id_finalize_registration']]);

            $stmt = $conn->prepare("UPDATE tb_address SET
                                        cep = ?,
                                        address = ?,
                                        number = ?,
                                        complement = ?,
                                        neighborhood = ?,
                                        city = ?,
                                        state = ?
                                    WHERE user_id = ?");
            $stmt->execute([$cep, $address, $address_number, $complement, $neighborhood, $city, $state, $_SESSION['user_id_finalize_registration']]);

            $fullname = $firstname . " " . $lastname;
            $stmt = $conn->prepare("INSERT INTO tb_customer_info (user_id, name, email, phone, cpf, cep, address, number, complement, neighborhood, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id_finalize_registration'], $fullname, $email, $phone, $cpf, $cep, $address, $address_number, $complement, $neighborhood, $city, $state]);

            if ($stmt->rowCount()) {
                // Enviar e-mail de verificação
                $verification_link = INCLUDE_PATH_DASHBOARD . "r/verificar-email/" . $active_token;
                $subject = "Bem-vindo ao $project_name";
                $content = array("layout" => "verify-email", "content" => array("firstname" => $firstname, "link" => $verification_link));
                sendMail($firstname, $email, $subject, $content);

                // Armazena o informacoes em uma session
                $_SESSION['user_id_signature'] = $_SESSION['user_id_finalize_registration'];
                $_SESSION['user_email_signature'] = $email;

                // Limpar o código 2FA da sessão e do banco de dados
                unset($_SESSION['user_id']);
                unset($_SESSION['email']);
                unset($_SESSION['user_id_finalize_registration']);
                unset($_SESSION['user_email_finalize_registration']);

                // Commit na transação
                $conn->commit();

                // Retorna um status de sucesso
                echo json_encode(['status' => 'success']);        

                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Registro realizado com sucesso! Por favor, verifique seu e-mail.');
                $_SESSION['msg'] = $message;
                exit;
            } else {
                throw new Exception("Nenhuma linha afetada no banco de dados.");
            }
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Erro: ' . $e->getMessage()]);
            exit;
        }

        // Fechar a conexão
        $stmt = null;
        $conn = null;
    } else {
        $response = array('status' => 'error', 'message' => 'Erro ao registrar. Por favor, tente novamente.');
    }

    // Enviar a resposta em JSON
    echo json_encode($response);
?>