<?php
session_start();
include('./../../config.php');

try {
    // Consulta para obter os dados
    $stmt = $conn->prepare("
        SELECT u.firstname, 
               u.lastname, 
               u.username, 
               p.name AS plan_name, 
               (SELECT COUNT(*) 
                FROM tb_user_networks un
                INNER JOIN tb_networks n ON un.network_id = n.id
                WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id)) AS total_diretos,
               (SELECT COUNT(*) 
                FROM tb_user_networks un
                INNER JOIN tb_networks n ON un.network_id = n.id
                INNER JOIN tb_subscriptions s ON un.user_id = s.user_id
                WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id) AND s.plan_id = 4 AND s.status = 'ACTIVE') AS total_diamante,
               (SELECT COUNT(*) 
                FROM tb_user_networks un
                INNER JOIN tb_networks n ON un.network_id = n.id
                INNER JOIN tb_subscriptions s ON un.user_id = s.user_id
                WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id) AND s.plan_id = 1 AND s.status = 'ACTIVE') AS total_ouro,
               (SELECT COUNT(*) 
                FROM tb_user_networks un
                INNER JOIN tb_networks n ON un.network_id = n.id
                INNER JOIN tb_subscriptions s ON un.user_id = s.user_id
                WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id) AND s.plan_id = 2 AND s.status = 'ACTIVE') AS total_prata,
               (SELECT COUNT(*) 
                FROM tb_user_networks un
                INNER JOIN tb_networks n ON un.network_id = n.id
                INNER JOIN tb_subscriptions s ON un.user_id = s.user_id
                WHERE ((n.inviter_id = u.id AND n.referrer_id IS NULL) OR n.referrer_id = u.id) AND s.plan_id = 3 AND s.status = 'ACTIVE') AS total_bronze,
               u.date_create
        FROM tb_users u
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE u.status = 1
        GROUP BY u.id
        ORDER BY total_diretos DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cabeçalhos para download do arquivo CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=usuarios-com-mais-diretos.csv');

    // Criação do arquivo CSV
    $output = fopen('php://output', 'w');

    // Cabeçalho das colunas
    fputcsv($output, ['Nome', 'Login', 'Plano', 'Total Diretos', 'Diamante', 'Ouro', 'Prata', 'Bronze', 'Data Entrada']);

    // Adiciona os dados no CSV
    foreach ($users as $user) {
        fputcsv($output, [
            $user['firstname'] . ' ' . ($user['lastname'] ?? '-- Indefinido --'),
            $user['username'] ?? '-- Indefinido --',
            $user['plan_name'] ?? 'Nenhum',
            $user['total_diretos'] ?? 0,
            $user['total_diamante'] ?? 0,
            $user['total_ouro'] ?? 0,
            $user['total_prata'] ?? 0,
            $user['total_bronze'] ?? 0,
            $user['date_create'] ? date('d/m/Y', strtotime($user['date_create'])) : '--'
        ]);
    }

    // Fecha o recurso do arquivo
    fclose($output);
    exit;
} catch (Exception $e) {
    echo "Erro ao gerar o CSV: " . $e->getMessage();
    exit;
}