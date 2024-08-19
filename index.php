<?php
    session_start();
    ob_start();
    include('./config.php');

    //Url Amigavel
    $url = isset($_GET['url']) ? $_GET['url'] : 'home';

    //Edita o escrito da url para ser colocado no title
    if ($url == "")
    {
        $title = "Painel";
    } else {
        $title = ucwords(str_replace("-", " ", $url));
    }

    $permissions = "";
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Language" content="en">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Register Boxed - ArchitectUI HTML Bootstrap 4 Dashboard Template</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
        <meta name="description" content="ArchitectUI HTML Bootstrap 4 Dashboard Template">

        <!-- Favicon Icon -->
        <link rel="shortcut icon" href="<?= INCLUDE_PATH_DASHBOARD; ?>images/favicon.png" type="image/x-icon">

        <!-- Disable tap highlight on IE -->
        <meta name="msapplication-tap-highlight" content="no">
        <link rel="stylesheet" href="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/@fortawesome/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/ionicons-npm/css/ionicons.css">
        <link rel="stylesheet" href="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/linearicons-master/dist/web-font/style.css">
        <link rel="stylesheet" href="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/pixeden-stroke-7-icon-master/pe-icon-7-stroke/dist/pe-icon-7-stroke.css">
        <link href="<?= INCLUDE_PATH_DASHBOARD; ?>styles/css/base.css" rel="stylesheet">

        <!-- plugin dependencies -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/jquery/dist/jquery.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/moment/moment.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/metismenu/dist/metisMenu.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/bootstrap4-toggle/js/bootstrap4-toggle.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/jquery-circle-progress/dist/circle-progress.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/perfect-scrollbar/dist/perfect-scrollbar.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/toastr/build/toastr.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/jquery.fancytree/dist/jquery.fancytree-all-deps.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/apexcharts/dist/apexcharts.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/@chenfengyuan/datepicker/dist/datepicker.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/daterangepicker/daterangepicker.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/countup.js/dist/countUp.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/chart.js/dist/Chart.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/datatables.net/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/bootstrap-table/dist/bootstrap-table.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
        <!-- custome.js -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/charts/apex-charts.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/circle-progress.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/demo.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/scrollbar.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/toastr.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/treeview.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/form-components/toggle-switch.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/app.js"></script>
        <!-- library includes -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/library-includes/jquery.mask.min.js"></script>
        <!-- added codes -->
        <!-- vendors -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/jquery-validation/dist/jquery.validate.min.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>vendors/clipboard/dist/clipboard.min.js"></script>
        <!-- js -->
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/form-components/form-validation.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/form-components/datepicker.js"></script>
        <script type="text/javascript" src="<?= INCLUDE_PATH_DASHBOARD; ?>js/form-components/clipboard.js"></script>

    </head>
    <body>
        <?php
            // Iniciando variável $tab
            $tab = "";

            // Verifica se a URL contém pelo menos uma barra
            if (strpos($url, '/') !== false) {
                // Conta quantas barras existem na URL
                $slashCount = substr_count($url, '/');

                if ($slashCount == 1) {
                    // Há apenas uma barra na URL
                    list($tab, $url) = explode('/', $url, 2);
                } elseif ($slashCount > 1) {
                    // Há mais de uma barra na URL
                    list($tab, $url, $token) = explode('/', $url, 3);
                }
            }

            if ($url == "404") {
                include('templates/404.php');
            } elseif ($tab == "auth" || $url == "auth") {
                include('templates/auth.php');
            } else {
                include('templates/dashboard.php');
            }
        ?>

        <?php
            // Se tiver um "$_SESSION['msg']" exibe a mensagem
            if (isset($_SESSION['msg'])) {
        ?>
            <script type="text/javascript">
                $(document).ready(function() {
                    // Exibe a mensagem usando toastr
                    toastr.<?= $_SESSION['msg']['status']; ?>('<?= $_SESSION['msg']['message']; ?>', '<?= $_SESSION['msg']['title']; ?>', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                });
            </script>
        <?php
                // Remove a mensagem da sessão após exibi-la
                unset($_SESSION['msg']);
            }
        ?>

    </body>
</html>