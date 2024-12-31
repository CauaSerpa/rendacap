<?php
session_start();
include('./../../config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'title-editing-form') {

    if (isset($_SESSION['user_id'])) {
        $title_id = $_POST['title_id'];
        $identifier = $_POST['identifier'];
        $products = $_POST['products']; // Produtos vem em um array com sub-arrays
        $new_products = $_POST['new_products']; // Produtos vem em um array com sub-arrays

        try {
            // Iniciar transação
            $conn->beginTransaction();

            // Atualizar título principal
            $updateTitleSql = "UPDATE tb_draw_titles SET identifier = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateTitleSql);
            $updateStmt->execute([$identifier, $title_id]);

            // Iterar sobre os diferentes tipos de produtos (telesena, viva_sorte, hiper_cap_brasil)
            foreach ($products as $slug => $product_array) {
                foreach ($product_array as $product) {
                    $id = $product['id'];
                    $draw_date = $product['draw_date'];
                    $operation_code = $product['operation_code'];
                    $series = $product['series'];
                    $title = $product['title'];
                    $dv = $product['dv'];
                    $lucky_number = $product['lucky_number'];

                    // Formatar a data
                    if (DateTime::createFromFormat('d/m/Y', $draw_date) !== false) {
                        $draw_date_formatted = date("Y-m-d", strtotime(str_replace('/', '-', $draw_date)));
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Data inválida.']);
                        exit;
                    }

                    // Aqui você deve fazer o update na tabela que relaciona os produtos ao título
                    $updateProductSql = "UPDATE tb_draw_title_products SET draw_date = ?, title = ?, operation_code = ?, series = ?, dv = ?, lucky_number = ? WHERE id = ? AND draw_title_id = ? AND product_id = (SELECT id FROM tb_draw_products WHERE slug = ?)";
                    $updateProductStmt = $conn->prepare($updateProductSql);
                    $updateProductStmt->execute([$draw_date_formatted, $title, $operation_code, $series, $dv, $lucky_number, $id, $title_id, $slug]);
                }
            }

            // Adiciona produtos novos
            if ($new_products) {
                foreach ($new_products as $product_type => $product_array) {
                    foreach ($product_array as $new_product) {
                        $draw_date = $new_product['draw_date'];
                        $operation_code = $new_product['operation_code'];
                        $series = $new_product['series'];
                        $title = $new_product['title'];
                        $dv = $new_product['dv'];
                        $lucky_number = $new_product['lucky_number'];
    
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
                            ':draw_title_id' => $title_id,
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
            }

            // Commit na transação
            $conn->commit();

            $_SESSION['msg'] = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Título atualizado e produtos associados com sucesso.');
            echo json_encode(['status' => 'success', 'message' => 'Título atualizado e produtos associados com sucesso.']);
            exit;
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Erro: ' . $e->getMessage()]);
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