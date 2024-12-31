<?php if ($url == "registrar" || $url == "finalizar-cadastro") { ?>
<style>
    .background {
        position: fixed;
        width: 100%;
    }
    .app-content {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        padding: 1.75rem 0;
        width: 100%;
        height: 100%;
        overflow-y: auto;
    }
</style>
<?php } elseif ($url == "planos") { ?>
<style>
    .background {
        position: fixed;
        width: 100%;
    }
    .app-content {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        padding: 1.75rem 0;
        width: 100%;
        height: 100%;
        overflow-y: auto;
    }
    @media (min-width: 900px) {
        .app-content {
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }
</style>
<?php } else { ?>
<style>
    .background {
        position: fixed;
        width: 100%;
    }
    .app-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
<?php } ?>

<div class="app-container app-theme-white body-tabs-shadow">
    <div class="app-container">
        <div class="background h-100 bg-premium-dark">
            <div class="app-content">
                <div class="mx-auto app-login-box col-md-8">
                <div class="app-logo-inverse mx-auto mb-3"></div> <!-- logo -->

                    <!-- page content -->
                    <?php
                        // Se a url for ex.: "auth" sem barra ou se estiver vazia ex.: "auth/", Adiciona login ex.: "auth/login"
                        $url = ($url == "auth" || empty($url)) ? "login" : $url;

                        if (file_exists('pages/authentication/' . $url . '.php')) {
                            include('pages/authentication/' . $url . '.php');
                        } else {
                            // A página não existe
                            header('Location: ' . INCLUDE_PATH_DASHBOARD . '404');
                            exit;
                        }
                    ?>
                    <!-- end page content -->

                    <div class="text-center text-white opacity-8 mt-3">Direitos autorais &copy; <?= $project_name; ?> <?= date("Y"); ?></div> <!-- copyright -->
                </div>
            </div>
        </div>
    </div>
</div>