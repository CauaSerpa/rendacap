<?php
    session_start();
    include('../../../../config.php');

    header('Content-Type: application/json');

    // Verificar se a variável 'plan' foi enviada via POST
    if (isset($_POST['plan']) && isset($_SESSION['user_id'])) {
        // SQL para contar os vouchers ativos por plano
        $sql = "
            SELECT 
                COUNT(vo.id) AS count_vouchers
            FROM 
                tb_voucher_orders vo
            JOIN 
                tb_vouchers v ON vo.voucher_id = v.id
            WHERE 
                vo.user_id = :user_id 
                AND vo.status = 'completed' 
                AND vo.used = 0 
                AND v.slug = :plan
        ";

        // Preparar a consulta
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':plan', $_POST['plan']);

        // Executar a consulta
        $stmt->execute();

        // Obter os resultados
        $vouchers = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se houver resultados, retorne o número de vouchers, caso contrário, 0
        $response = [
            'status' => 'success',
            'vouchersCount' => $vouchers ? $vouchers['count_vouchers'] : 0
        ];
    } else {
        // Caso os parâmetros não sejam passados corretamente
        $response = [
            'status' => 'error',
            'message' => 'Plano ou usuário não encontrado.'
        ];
    }

    // Retorna o resultado como JSON
    echo json_encode($response);
?>