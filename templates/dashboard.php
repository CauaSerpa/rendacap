<?php
    // TEMPORARIO, CRIAR MAIN PARA PAGINAS, EX.: MAIN PARA PAGINAS DE LOGIN, MAIN PARA PAGINAS DO PAINEL
    // Durante o processo de login
    // $stmt = $conn->prepare("
    //     SELECT u.*, un.network_id, i.id AS inviter_id
    //     FROM tb_users u
    //     LEFT JOIN tb_user_networks un ON u.id = un.user_id
    //     LEFT JOIN tb_networks n ON un.network_id = n.id
    //     LEFT JOIN tb_users i ON n.inviter_id = i.id
    //     WHERE u.id = ?
    // ");
    if (isset($_SESSION['user_id'])) {
        // Verifica a permissão do usuário e define a path
        foreach ($permissionsMap as $permission => $assignedPath) {
            if (hasPermission($_SESSION['user_id'], $permission, $conn)) {
                $user_role = $assignedPath;
                break;
            }
        }
    } else {
        // Salva pagina que o usuario estava anteriormente
        $_SESSION['http_referer'] = $url;

        echo json_encode(['status' => 'error', 'message' => 'Por favor faça login para acessar essa página.']);

        // Usuário não está logado ou não tem permissões
        header("Location: " . INCLUDE_PATH_AUTH);
        exit;
    }

    // $stmt = $conn->prepare("
    //     SELECT u.*, a.city, a.state, un.network_id, i.id AS inviter_id, s.id AS subscription_id, s.card_last_digits, s.card_brand, s.created_at, s.due_date, p.id AS plan_id, p.name AS plan_name, p.price AS plan_price, p.item_description AS plan_description
    //     FROM tb_users u
    //     LEFT JOIN tb_user_networks un ON u.id = un.user_id
    //     LEFT JOIN tb_address a ON a.user_id = u.id
    //     LEFT JOIN tb_networks n ON un.network_id = n.id
    //     LEFT JOIN tb_users i ON n.inviter_id = i.id
    //     LEFT JOIN tb_subscriptions s ON u.id = s.user_id
    //     LEFT JOIN tb_plans p ON s.plan_id = p.id
    //     WHERE u.id = ?
    //     ORDER BY s.created_at DESC
    //     LIMIT 1
    // ");
    $stmt = $conn->prepare("
        SELECT u.*, a.city, a.state, un.network_id, i.id AS inviter_id, n.referrer_id, 
            s.id AS subscription_id, s.payment_method, s.card_last_digits, s.card_brand, s.created_at, s.due_date, 
            p.id AS plan_id, p.name AS plan_name, p.price AS plan_price, p.item_description AS plan_description
        FROM tb_users u
        LEFT JOIN tb_user_networks un ON u.id = un.user_id
        LEFT JOIN tb_address a ON a.user_id = u.id
        LEFT JOIN tb_networks n ON un.network_id = n.id
        LEFT JOIN tb_users i ON n.inviter_id = i.id
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE u.id = ?
        ORDER BY s.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);

    if ($stmt->rowCount()) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_role !== 'admin') {
            $stmt = $conn->prepare("
                SELECT *
                FROM tb_subscriptions
                WHERE user_id = ? 
                AND (
                    (status = 'ACTIVE' AND payment_method = 'CREDIT_CARD')
                    OR (status = 'ACTIVE' AND payment_method = 'PIX')
                    OR (status = 'ACTIVE' AND payment_method = 'FREE_PLAN')
                    OR (status = 'ACTIVE' AND payment_method = 'VOUCHER')
                )
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $subs = $stmt->fetch(PDO::FETCH_ASSOC);
    
            $subs['creation_date'] = (new DateTime($subs['creation_date']))->format('d/m/Y');
            $subs['created_at'] = (new DateTime($subs['created_at']))->format('d/m/Y');
            $subs['due_date'] = (new DateTime($subs['due_date']))->format('d/m/Y');
        } else {
            $user['plan_name'] = 'ativo';
        }

        // Define variáveis ​​com nomes diferentes
        $user['fullname'] = $user['firstname'] . " " . $user['lastname'];
        $user['surname'] = explode(' ', $user['lastname'])[0];
        $user['shortname'] = $user['firstname'] . " " . $user['surname'];

        $user['birth_date'] = (new DateTime($user['birth_date']))->format('d/m/Y');

        $user['invite_link'] = INCLUDE_PATH_AUTH . 'registrar/' . $user['token'];

        $no_image_src = INCLUDE_PATH_DASHBOARD . 'images/avatars/' . strtolower($user['plan_name']) . ".png";
        $avatar_src = INCLUDE_PATH_DASHBOARD . 'files/profile/avatar/' . $user['id'] . '/' . $user['profile_image'];

        $user['avatar'] = (($user['profile_image'] !== 'no-image.svg') ? $avatar_src : $no_image_src);

        // Altera o formato da data de vencimento de 0000-00-00 para 00 de xxxx de 0000
        $user['due_date'] = (new DateTime($user['due_date']))->format('d') . " de " . $month_names[(int)(new DateTime($user['due_date']))->format('m')] . " de " . (new DateTime($user['due_date']))->format('Y');

        $user['subs_price'] = formatToBRL($user['plan_price']);
    }
?>

<div class="app-container app-theme-white body-tabs-shadow fixed-header fixed-sidebar">

    <!-- header -->
    <?php
        include('template-parts/header.php');
    ?>
    <!-- end header -->

    <div class="app-main">

        <!-- sidebar -->
        <?php
            include('template-parts/sidebar.php');
        ?>
        <!-- end sidebar -->

        <div class="app-main__outer">
            <div class="app-main__inner">

            <!-- page content -->
            <?php
                if (!empty($tab)) {
                    $tabPanel = $url;
                    $url = $tab;
                }

                // Passa role do usuario
                $path = $user_role;

                // Inclui página
                if (file_exists('pages/' . $path . '/' . $url . '.php')) {
                    include('pages/' . $path . '/' . $url . '.php');
                } else {
                    // A página não existe
                    header('Location: ' . INCLUDE_PATH_DASHBOARD . '404');
                    exit;
                }
            ?>
            <!-- end page content -->

            </div>
        </div>

    </div>
</div>