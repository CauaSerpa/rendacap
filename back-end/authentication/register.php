<?php
session_start();
include('./../../config.php');

header('Content-Type: application/json');

function generateToken($length = 50) {
    return bin2hex(random_bytes($length));
}

function generateSequentialToken($user_id) {
    $formatted_sequence = str_pad($user_id, 6, '0', STR_PAD_LEFT);
    return '3005' . $formatted_sequence;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "register") {
    // Coleta de dados 
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $whatsapp = $_POST['phone'];
    $birth_date = DateTime::createFromFormat('d/m/Y', $_POST['birth_date']);
    $cep = $_POST['cep'];
    $address = $_POST['address'];
    $address_number = $_POST['address_number'] ?? 0;
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

    // Validação da data de nascimento
    if ($birth_date === false) {
        echo json_encode(['status' => 'error', 'message' => 'Data de nascimento inválida.']);
        exit;
    }
    $birthday = $birth_date->format('Y-m-d');

    try {
        // Iniciar transação
        if (!$conn) {
            throw new Exception("Conexão inválida com o banco de dados.");
        }
        $conn->beginTransaction();

        // Inserir dados do usuário
        $stmt = $conn->prepare("INSERT INTO tb_users (status, firstname, lastname, email, phone, whatsapp, birth_date, cpf, rg, username, password, active_token) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstname, $lastname, $email, $phone, $whatsapp, $birthday, $cpf, $rg, $username, $password, $active_token]);
        $user_id = $conn->lastInsertId();

        // Inserir endereço
        $stmt = $conn->prepare("INSERT INTO tb_address (user_id, cep, address, number, complement, neighborhood, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $cep, $address, $address_number, $complement, $neighborhood, $city, $state]);

        // Definir cargo
        $stmt = $conn->prepare("INSERT INTO tb_user_roles (user_id, role_id) VALUES (?, 3)");
        $stmt->execute([$user_id]);

        $token = generateSequentialToken($user_id);
        $update_stmt = $conn->prepare("UPDATE tb_users SET token = ? WHERE id = ?");
        $update_stmt->execute([$token, $user_id]);

        $users_with_less_than_5_invited = []; // Array global para armazenar IDs de usuários com menos de 5 convidados

        function list_network_with_less_than_5($conn, $networkIds) {
            global $users_with_less_than_5_invited; // Garantir que o array seja global

            foreach ($networkIds as $networkId) {
                // Consulta para obter todos os user_id associados à network
                $stmt = $conn->prepare("
                    SELECT user_id
                    FROM tb_user_networks
                    WHERE network_id = ?
                ");
                $stmt->execute([$networkId]);
                $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

                foreach ($user_ids as $subuser_id) {
                    // Verifica quantos usuários estão associados à network do subusuário
                    $stmt = $conn->prepare("
                        SELECT COUNT(*) AS user_count
                        FROM tb_user_networks
                        INNER JOIN tb_networks ON tb_user_networks.network_id = tb_networks.id
                        WHERE tb_networks.inviter_id = ?
                    ");
                    $stmt->execute([$subuser_id]);
                    $userCountResult = $stmt->fetch(PDO::FETCH_ASSOC);
                    $userCount = $userCountResult['user_count'];

                    // Se o subusuário tiver menos de 5 usuários na rede, adiciona ao array
                    if ($userCount < 5) {
                        $users_with_less_than_5_invited[] = $subuser_id;
                    }

                    // Verifica se o subusuário tem redes
                    $stmt = $conn->prepare("
                        SELECT id
                        FROM tb_networks
                        WHERE inviter_id = ?
                    ");
                    $stmt->execute([$subuser_id]);
                    $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    if (empty($networkIds)) {
                        // Se não há redes, adiciona ao array de usuários sem convidados
                        $users_with_less_than_5_invited[] = $subuser_id;
                    } else {
                        // Chama a função recursiva para continuar listando os convidados
                        list_network_with_less_than_5($conn, $networkIds);
                    }
                }
            }
        }

        // Busca o inviter e o referrer pelos tokens da sessão
        $inviter_token = $_SESSION['inviter'] ?? null; // Token do convidador
        $referrer_token = $_SESSION['referrer'] ?? null; // Token do referenciador

        $inviter_id = null;
        $referrer_id = null;

        // Busca o inviter_id pelo token
        if ($inviter_token) {
            $stmt = $conn->prepare("SELECT id FROM tb_users WHERE token = ?");
            $stmt->execute([$inviter_token]);
            $inviter = $stmt->fetch(PDO::FETCH_ASSOC);
            $inviter_id = $inviter['id'] ?? null;
        }

        // Busca o referrer_id pelo token
        if ($referrer_token) {
            $stmt = $conn->prepare("SELECT id FROM tb_users WHERE token = ?");
            $stmt->execute([$referrer_token]);
            $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
            $referrer_id = $referrer['id'] ?? null;
        }

        // Verifica se o inviter_id é nulo e pula a etapa de cadastro em uma rede se for
        if (!is_null($inviter_id)) {

            // Verifica quantas vezes o inviter_id aparece na tabela tb_networks
            $stmt = $conn->prepare("
                SELECT COUNT(*) AS count
                FROM tb_networks
                WHERE inviter_id = ?
            ");
            $stmt->execute([$inviter_id]);
            $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $inviterCount = $countResult['count'];

            // Consulta para obter todos os network_ids do usuário
            $stmt = $conn->prepare("
                SELECT id
                FROM tb_networks
                WHERE inviter_id = ?
            ");
            $stmt->execute([$inviter_id]);
            $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Se não houver networks, também queremos incluir usuários sem redes
            if (empty($networkIds)) {
                // Consulta para obter todos os usuários que não têm nenhuma rede cadastrada
                $stmt = $conn->prepare("
                    SELECT id
                    FROM tb_users
                    WHERE id NOT IN (SELECT user_id FROM tb_user_networks)
                ");
                $stmt->execute();
                $noNetworkUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Adiciona usuários sem rede ao array
                $users_with_less_than_5_invited = array_merge($users_with_less_than_5_invited, $noNetworkUsers);
            } else {
                // Listar os usuários na rede
                list_network_with_less_than_5($conn, $networkIds);
            }

            // Remove duplicatas do array, se houver
            $users_with_less_than_5_invited = array_unique($users_with_less_than_5_invited);

            // Ordena o array em ordem crescente
            sort($users_with_less_than_5_invited);

            if ($inviterCount < 5) {
                // Caso a rede do inviter tenha menos de 5 usuairos
                $stmt = $conn->prepare("INSERT INTO tb_networks (inviter_id, referrer_id) VALUES (?, ?)");
                $stmt->execute([$inviter_id, $referrer_id]);
            } else {
                // Tem mais, procura um usuario dentro da rede
                $new_inviter_id = $users_with_less_than_5_invited[0];

                // Inserir o novo usuário na tabela tb_networks com o novo inviter_id
                $stmt = $conn->prepare("INSERT INTO tb_networks (inviter_id, referrer_id) VALUES (?, ?)");
                $stmt->execute([$new_inviter_id, $inviter_id]);
            }

            $network_id = $conn->lastInsertId();

            // Adicionar o novo usuário à rede encontrada ou criada
            $stmt = $conn->prepare("INSERT INTO tb_user_networks (network_id, user_id) VALUES (?, ?)");
            $stmt->execute([$network_id, $user_id]);

        }

        // Enviar e-mail de verificação
        $verification_link = INCLUDE_PATH_DASHBOARD . "r/verificar-email/" . $active_token;
        $subject = "Bem-vindo ao $project_name";
        $content = array("layout" => "verify-email", "content" => array("firstname" => $firstname, "link" => $verification_link));
        sendMail($firstname, $email, $subject, $content);

        // Armazena o informacoes em uma session
        $_SESSION['user_id_signature'] = $user_id;
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
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollBack();

        // Registrar erro em um log
        error_log("Erro no registro do usuário: " . $e->getMessage());

        // Mensagem genérica para o usuário
        echo json_encode(['status' => 'error', 'message' => 'Ocorreu um erro ao processar seu registro. Tente novamente mais tarde.']);
        exit;
    }

    $stmt = null;
    $conn = null;
}
?>