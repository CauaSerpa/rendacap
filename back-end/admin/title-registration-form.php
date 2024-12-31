<?php
session_start();
include('./../../config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'title-registration-form') {

    if (isset($_SESSION['user_id'])) {
        $identifier = $_POST['identifier'];
        $responsible = $_POST['responsible'];
        $plan_id = $_POST['plan'];
        $group_creation_method = $_POST['group_creation_method'];
        $products = $_POST['products']; // Produtos vêm em um array com sub-arrays

        // Obter o primeiro draw_week dos produtos
        $first_product = reset($products);
        $first_draw_week = $first_product[0]['draw_date'];

        if (DateTime::createFromFormat('d/m/Y', $first_draw_week) !== false) {
            $draw_week_obj = DateTime::createFromFormat('d/m/Y', $first_draw_week);
            $first_draw_week_formatted = $draw_week_obj->format('Y-m-d');
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data inválida.']);
            exit;
        }

        try {
            // Iniciar transação
            $conn->beginTransaction();

            // Inserir o título na tabela tb_draw_titles (informações gerais do título)
            $stmt = $conn->prepare("
                INSERT INTO tb_draw_titles (identifier, responsible, plan_id)
                VALUES (:identifier, :responsible, :plan_id)
            ");

            $stmt->execute([
                ':identifier' => $identifier,
                ':responsible' => $responsible,
                ':plan_id' => $plan_id
            ]);

            // Obter o ID do título inserido
            $draw_title_id = $conn->lastInsertId();

            // Iterar sobre os diferentes tipos de produtos (telesena, viva_sorte, hiper_cap_brasil)
            foreach ($products as $product_type => $product_array) {
                foreach ($product_array as $product) {
                    $draw_date = $product['draw_date'];
                    $operation_code = $product['operation_code'];
                    $series = $product['series'];
                    $title = $product['title'];
                    $dv = $product['dv'];
                    $lucky_number = $product['lucky_number'];

                    // Consultar os produtos
                    $stmt = $conn->prepare("SELECT id FROM tb_draw_products WHERE slug = ?");
                    $stmt->execute([$product_type]);
                    $product_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

                    // Formatar a data e pegar a semana e ano
                    if (DateTime::createFromFormat('d/m/Y', $draw_date) !== false) {
                        $draw_date_obj = DateTime::createFromFormat('d/m/Y', $draw_date);
                        $week_number = $draw_date_obj->format('W'); // Semana do ano
                        $year_number = $draw_date_obj->format('y'); // Últimos 2 dígitos do ano
                        $draw_date_formatted = $draw_date_obj->format('Y-m-d');
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Data inválida.']);
                        exit;
                    }

                    // Determinar o prefixo baseado no produto e data (T, S, V, H)
                    $product_prefix = ($product_type === 'telesena') ? 'TS' : (($product_type === 'viva_sorte') ? 'VS' : 'HS');
                    $prefix = "{$product_prefix}_{$week_number}/{$year_number}_{$product_id}";

                    // Verificar quantos títulos com esse prefixo já existem para incrementar o número corretamente
                    $stmtCount = $conn->prepare("
                        SELECT COUNT(*) AS total FROM tb_draw_title_products WHERE title_id LIKE :prefix
                    ");
                    $stmtCount->execute([':prefix' => "{$prefix}%"]);
                    $count = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

                    // Criar o novo title_id com o formato correto
                    $new_title_id = $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT); // Ex: TS_44/24_10001

                    // Inserir o produto na tabela tb_draw_title_products (detalhes dos produtos)
                    $stmtProduct = $conn->prepare("
                        INSERT INTO tb_draw_title_products (draw_title_id, product_id, title_id, draw_date, operation_code, series, dv, title, lucky_number)
                        VALUES (:draw_title_id, :product_id, :title_id, :draw_date, :operation_code, :series, :dv, :title, :lucky_number)
                    ");

                    $stmtProduct->execute([
                        ':draw_title_id' => $draw_title_id,
                        ':product_id' => $product_id,
                        ':title_id' => $new_title_id, // Aqui usamos o novo title_id gerado
                        ':draw_date' => $draw_date_formatted,
                        ':operation_code' => $operation_code,
                        ':series' => $series,
                        ':dv' => $dv,
                        ':title' => $title,
                        ':lucky_number' => $lucky_number
                    ]);
                }
            }

            // Buscar usuários com o plano selecionado
            // $stmtUsers = $conn->prepare("
            //     SELECT u.id, u.firstname, COUNT(tg.id) AS title_count
            //     FROM tb_users u
            //     LEFT JOIN tb_group_users gu ON u.id = gu.user_id
            //     LEFT JOIN tb_groups tg ON gu.group_id = tg.id AND WEEK(tg.draw_week) = WEEK(:draw_date)
            //     INNER JOIN tb_subscriptions s ON u.id = s.user_id
            //     WHERE s.plan_id = :plan_id
            //     GROUP BY u.id
            //     HAVING title_count = 0
            //     ORDER BY title_count ASC
            //     LIMIT 4
            // ");
            $stmtUsers = $conn->prepare("
                SELECT 
                    u.id, 
                    u.firstname,
                    COUNT(DISTINCT dtp.id) AS title_count
                FROM 
                    tb_users u
                INNER JOIN 
                    tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
                LEFT JOIN 
                    tb_group_users gu ON u.id = gu.user_id
                LEFT JOIN 
                    tb_groups tg ON gu.group_id = tg.id
                LEFT JOIN 
                    tb_draw_titles dt ON tg.title_id = dt.id
                LEFT JOIN 
                    tb_draw_title_products dtp 
                    ON dt.id = dtp.draw_title_id 
                    AND WEEK(dtp.draw_date) = WEEK(:draw_date)
                WHERE 
                    s.plan_id = :plan_id
                GROUP BY 
                    u.id, u.firstname
                HAVING 
                    title_count = 0
                ORDER BY 
                    title_count ASC
                LIMIT 4
            ");
            $stmtUsers->execute([
                ':plan_id' => $plan_id,
                ':draw_date' => $first_draw_week_formatted
            ]);
            $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            // print_r($users);
            // exit;

            // Se menos de 4 usuários forem encontrados, retornar erro
            if (count($users) > 4) {
                throw new Exception("Muitos usuários foram selecionados com o plano selecionado.");
            }

            $plan_code = ($plan_id == 1) ? 'O' : (($plan_id == 2) ? 'P' : 'D');
            // Formatar o código do grupo conforme o padrão
            $group_code_prefix = "G{$plan_code} {$week_number}/{$year_number}_";

            // Verificar quantos títulos com esse prefixo já existem para incrementar o número corretamente
            $stmtCount = $conn->prepare("
                SELECT COUNT(*) AS total FROM tb_groups WHERE group_code LIKE :prefix
            ");
            $stmtCount->execute([':prefix' => "{$group_code_prefix}%"]);
            $count = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

            // Criar o novo title_id com o formato correto
            $group_code = $group_code_prefix . str_pad($count + 1, 8, '0', STR_PAD_LEFT); // Ex: GD 44/24_000000004

            // Inserir o título na tabela tb_draw_titles (informações gerais do título)
            $stmt = $conn->prepare("
                INSERT INTO tb_groups (title_id, group_code)
                VALUES (:title_id, :group_code)
            ");

            $stmt->execute([
                ':title_id' => $draw_title_id,
                ':group_code' => $group_code
            ]);

            // Obter o ID do título inserido
            $group_id = $conn->lastInsertId();

            // Criar o grupo conforme o método selecionado
            if ($group_creation_method === 'automatic') {
                // Lógica de criação automática do grupo e adição de usuários

                // Selecionar os 4 usuários com menos títulos na semana e atribuir o título
                foreach ($users as $user) {
                    $stmtAssign = $conn->prepare("
                        INSERT INTO tb_group_users (group_id, user_id)
                        VALUES (:group_id, :user_id)
                    ");
                    $stmtAssign->execute([
                        ':group_id' => $group_id,
                        ':user_id' => $user['id']
                    ]);
                }

            }

            // Commit na transação
            $conn->commit();

            $_SESSION['msg'] = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Título registrado e produtos associados com sucesso.');

            if ($group_creation_method === 'automatic') {
                echo json_encode(['status' => 'success', 'message' => 'Título registrado e produtos associados com sucesso.']);
            } else {
                echo json_encode(['status' => 'success', 'message' => 'Título registrado e produtos associados com sucesso.', 'redirect' => INCLUDE_PATH_DASHBOARD . "grupo?group_id=$group_id"]);
            }

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
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    exit;
}
?>