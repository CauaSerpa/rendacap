<?php
    if (isset($_SESSION['user_id'])) {
        if (isset($_GET['title_id'])) {
            $title_id = intval($_GET['title_id']); // Obter o ID do título a ser deletado

            try {
                // Iniciar transação
                $conn->beginTransaction();

                // Primeiro, deletar os produtos associados ao título
                $deleteProductsSql = "DELETE FROM tb_draw_title_products WHERE draw_title_id = ?";
                $deleteProductsStmt = $conn->prepare($deleteProductsSql);
                $deleteProductsStmt->execute([$title_id]);

                // Em seguida, deletar o título
                $deleteTitleSql = "DELETE FROM tb_draw_titles WHERE id = ?";
                $deleteTitleStmt = $conn->prepare($deleteTitleSql);
                $deleteTitleStmt->execute([$title_id]);

                // Commit na transação
                $conn->commit();

                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'success', 'title' => 'Sucesso', 'message' => 'Título e produtos associados deletados com sucesso.');
                $_SESSION['msg'] = $message;

                // Redirecionar para a página de listagem de títulos com uma mensagem de sucesso
                header("Location: " . INCLUDE_PATH_DASHBOARD . "titulos");
                exit();
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Defina a mensagem de sucesso na sessão
                $message = array('status' => 'error', 'title' => 'Erro', 'message' => 'Erro ao deletar título: ' . $e->getMessage());
                $_SESSION['msg'] = $message;
    
                // Redirecionar para a página de listagem de títulos com uma mensagem de sucesso
                header("Location: " . INCLUDE_PATH_DASHBOARD . "titulos");
                exit;
            }
        } else {
            // Reverter a transação em caso de falha na exclusão
            $conn->rollBack();

            // Defina a mensagem de sucesso na sessão
            $message = array('status' => 'error', 'title' => 'Sucesso', 'message' => 'ID do título não fornecido.');
            $_SESSION['msg'] = $message;

            // Redirecionar para a página de listagem de títulos com uma mensagem de sucesso
            header("Location: " . INCLUDE_PATH_DASHBOARD . "titulos");
            exit();
        }
    } else {
        // Defina a mensagem de sucesso na sessão
        $message = array('status' => 'error', 'title' => 'Sucesso', 'message' => 'Usuário não autenticado.');
        $_SESSION['msg'] = $message;

        // Redirecionar para a página de listagem de títulos com uma mensagem de sucesso
        header("Location: " . INCLUDE_PATH_AUTH);
        exit;
    }