<?php
session_start();
include('./../../config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search-draw-group') {

    // ID do usuário atual e o plano dele
    $current_user_id = (isset($_SESSION["user_id_signature"])) ? $_SESSION["user_id_signature"] : $_SESSION["user_id"]; // Substitua pelo ID do usuário atual
    $current_user_plan = $_POST['plan_id']; // Substitua pelo plano do usuário atual

    // Obter o horário atual
    $current_datetime = new DateTime();
    $current_day = $current_datetime->format('l'); // Dia da semana (ex: Monday)
    $current_time = $current_datetime->format('H:i'); // Hora atual (ex: 15:30)

    // Condicional para verificar se é antes ou depois das 18:00 de quarta-feira
    if ($current_day == 'Wednesday' && $current_time < '18:00') {
        // Antes das 18:00 de quarta-feira, busca o grupo da semana atual
        $date_condition = "BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 - WEEKDAY(CURDATE()) DAY)";
    } else {
        // Depois das 18:00 de quarta-feira, busca o grupo da próxima semana
        $date_condition = "BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 WEEK) AND DATE_ADD(CURDATE(), INTERVAL 7 + (6 - WEEKDAY(CURDATE())) DAY)";
    }

    // Consultar grupos com menos de 4 usuários, do mesmo plano do usuário, e títulos válidos
    $query = "
        SELECT 
            g.id AS group_id,
            g.group_code,
            dt.id AS title_id,
            dtp.draw_date,
            dp.name AS product,
            (
                SELECT COUNT(*) 
                FROM tb_group_users gu 
                WHERE gu.group_id = g.id
            ) AS user_count
        FROM tb_groups g
        JOIN tb_draw_titles dt ON g.title_id = dt.id
        JOIN tb_draw_title_products dtp ON dt.id = dtp.draw_title_id
        JOIN tb_draw_products dp ON dtp.product_id = dp.id
        WHERE dt.plan_id = :plan_id
        AND (
            SELECT COUNT(*) 
            FROM tb_group_users gu 
            WHERE gu.group_id = g.id
        ) < 4
        AND dtp.draw_date >= CURDATE() -- Apenas títulos futuros ou desta semana
        AND dtp.draw_date $date_condition
        ORDER BY dtp.draw_date ASC
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([':plan_id' => $current_user_plan]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($groups)) {
        foreach ($groups as $group) {
            // Verifica se o usuário já está no grupo
            $checkQuery = "
                SELECT COUNT(*) 
                FROM tb_group_users 
                WHERE group_id = :group_id AND user_id = :user_id
            ";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([':group_id' => $group['group_id'], ':user_id' => $current_user_id]);

            if ($checkStmt->fetchColumn() == 0) {
                // Usuário não está no grupo, insere
                $insertQuery = "
                    INSERT INTO tb_group_users (group_id, user_id) 
                    VALUES (:group_id, :user_id)
                ";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->execute([':group_id' => $group['group_id'], ':user_id' => $current_user_id]);

                if ($insertStmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Usuário adicionado ao grupo ' . htmlspecialchars($group['group_code']) . ' com sucesso.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Erro ao adicionar o usuário ao grupo ' . htmlspecialchars($group['group_code'])]);
                }
            } else {
                // Usuário já está no grupo
                echo json_encode(['status' => 'error', 'message' => 'Usuário já faz parte do grupo ' . htmlspecialchars($group['group_code'])]);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nenhum grupo disponível com as condições especificadas.']);
        exit;
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Requisição inválida.']);
    exit;
}
?>