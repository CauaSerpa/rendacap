<?php
    session_start();
    include('./../../config.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title_id'])) {
        $title_id = $_POST['title_id'];
        $current_user_id = $_SESSION['user_id']; // Supondo que você tenha o ID do usuário na sessão

        $query = "
        SELECT 
            dtp.id, 
            g.id AS group_id, 
            g.group_code, 
            dp.name AS product, 
            dtp.series, 
            dtp.title, 
            dtp.operation_code, 
            dtp.dv, 
            dtp.lucky_number, 
            dtp.draw_date, 
            dtp.id AS title_id
        FROM tb_group_users gu
        JOIN tb_groups g ON gu.group_id = g.id
        JOIN tb_draw_titles dt ON g.title_id = dt.id
        LEFT JOIN tb_draw_title_products dtp ON dt.id = dtp.draw_title_id
        JOIN tb_draw_products dp ON dtp.product_id = dp.id
        WHERE dtp.id = ?
        ORDER BY dtp.draw_date DESC
        LIMIT 1
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$title_id]);
        $next_draw = $stmt->fetch(PDO::FETCH_ASSOC);

        // Consulta para listar os usuários do grupo
        $query = "
            SELECT s.user_id, s.plan_id, p.name AS plan_name, u.firstname, u.lastname, u.email, u.username, u.profile_image, a.city, a.state
            FROM tb_group_users gu
            JOIN tb_users u ON gu.user_id = u.id
            JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
            JOIN tb_plans p ON s.plan_id = p.id
            JOIN tb_address a ON u.id = a.user_id
            WHERE gu.group_id = ? 
            ORDER BY s.id ASC
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$next_draw['group_id']]);
        $sorted_participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formata a resposta
        $response = [
            'participants' => $sorted_participants,
            'next_draw' => $next_draw,
            'current_user_id' => $current_user_id,
        ];
    
        // Retorna os dados como JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }