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
                if (isset($_SESSION['user_id'])) {
                    // Verifica a permissão do usuário e define a path
                    foreach ($permissionsMap as $permission => $assignedPath) {
                        if (hasPermission($_SESSION['user_id'], $permission, $conn)) {
                            $path = $assignedPath;
                            break;
                        }
                    }
                } else {
                    // Usuário não está logado ou não tem permissões
                    header("Location: " . INCLUDE_PATH_AUTH . "login");
                    exit;
                }

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