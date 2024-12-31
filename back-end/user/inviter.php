<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inviter_id'])) {
        $user_id = $_SESSION['user_id'];

        $inviter_id = filter_input(INPUT_POST, 'inviter_id', FILTER_SANITIZE_NUMBER_INT);

        // Consulta para buscar os dados do convidador
        $stmt = $conn->prepare("
            SELECT u.firstname, u.lastname, u.username, u.email, u.whatsapp, u.cpf, u.token, p.name AS plan, 
                CASE 
                    WHEN n.inviter_id = ? AND n.referrer_id IS NULL THEN 'direct'
                    WHEN n.referrer_id = ? THEN 'direct'
                    ELSE 'indirect' 
                END AS type, 
                CASE 
                    WHEN s.status = 'ACTIVE' THEN 'Ativo'
                    WHEN s.status = 'INACTIVE' THEN 'Inativo'
                    WHEN s.status = 'EXPIRED' THEN 'Expirado'
                    WHEN s.status = 'CANCELED' THEN 'Cancelado'
                END AS status
            FROM tb_users u
            LEFT JOIN tb_user_networks un ON u.id = un.user_id
            LEFT JOIN tb_subscriptions s ON u.id = s.user_id
            LEFT JOIN tb_plans p ON s.plan_id = p.id
            LEFT JOIN tb_networks n ON un.network_id = n.id
            WHERE u.id = ?
            ORDER BY s.status = 'ACTIVE' DESC, s.id DESC
            LIMIT 1
        ");
        $stmt->execute([$user_id, $user_id, $inviter_id]);
        $inviter = $stmt->fetch(PDO::FETCH_ASSOC);

        $inviter['fullname'] = $inviter['firstname'] . " " . $inviter['lastname'];

        $inviter['cpf'] = formatOcultCpf($inviter['cpf']);

        // if ($inviter['type'] == 'direct') {
        //     $inviter['phone'] = $inviter['whatsapp'];
        // } else {
        //     $inviter['phone'] = "(**) *****-****";
        //     $inviter['email'] = maskEmail($inviter['email']);
        // }

        $inviter['phone'] = $inviter['whatsapp'];
        unset($inviter['whatsapp']);

        if ($inviter) {
            echo json_encode(['status' => 'success', 'data' => $inviter]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Convidador não encontrado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Requisição inválida.']);
    }
?>