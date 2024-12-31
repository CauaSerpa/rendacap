<?php
    //Funcao '.active' Sidebar
    function activeSidebarLink($par) {
        $url = explode('/',@$_GET['url'])[0];
        if ($url == $par)
        {
            echo "mm-active";
        }
    }

    //Funcao '.active' Sidebar
    function activeSidebarShow($par) {
        $url = explode('/',@$_GET['url'])[0];
        if ($url == $par)
        {
            echo "mm-show";
        }
    }
?>

<div class="app-sidebar sidebar-shadow">
    <div class="app-header__logo">
        <div class="logo-src"></div>
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                <li class="app-sidebar__heading">Menu</li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>home" class="<?= activeSidebarLink('home'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-home"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">home</span>
                        Início
                    </a>
                </li>

                <?php if ($user_role == "admin") : ?>

                <li class="<?= activeSidebarLink('titulos'); ?> <?= activeSidebarLink('grupos'); ?>">
                    <a href="#" class="<?= activeSidebarLink('titulos'); ?> <?= activeSidebarLink('grupos'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-wallet"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">conveyor_belt</span>
                        Produtos
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul class="<?= activeSidebarShow('titulos'); ?> <?= activeSidebarShow('grupos'); ?>">
                        <li>
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>titulos" class="<?= activeSidebarLink('titulos'); ?>">
                                <i class="metismenu-icon"></i>
                                Títulos
                            </a>
                        </li>
                        <li>
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>grupos" class="<?= activeSidebarLink('grupos'); ?>">
                                <i class="metismenu-icon"></i>
                                Grupos
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastros" class="<?= activeSidebarLink('cadastros'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-albums"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">person_add</span>
                        Cadastros
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>usuarios-sem-patrocinador" class="<?= activeSidebarLink('usuarios-sem-patrocinador'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-albums"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">no_accounts</span>
                        Usuários sem Patrocinador
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>comprovantes" class="<?= activeSidebarLink('comprovantes'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-albums"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">receipt_long</span>
                        Comprovante Doações
                    </a>
                </li>
                <li class="<?= activeSidebarLink('assinaturas'); ?> <?= activeSidebarLink('saques'); ?> <?= activeSidebarLink('pagar-saque'); ?>">
                    <a href="#" class="<?= activeSidebarLink('assinaturas'); ?> <?= activeSidebarLink('saques'); ?> <?= activeSidebarLink('pagar-saque'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-wallet"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">account_balance</span>
                        Financeiro
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul class="<?= activeSidebarShow('assinaturas'); ?> <?= activeSidebarShow('saques'); ?> <?= activeSidebarShow('pagar-saque'); ?>">
                        <li>
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>assinaturas" class="<?= activeSidebarLink('assinaturas'); ?>">
                                <i class="metismenu-icon"></i>
                                Assinaturas
                            </a>
                        </li>
                        <li>
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>saques" class="<?= activeSidebarLink('saques'); ?> <?= activeSidebarLink('pagar-saque'); ?>">
                                <i class="metismenu-icon"></i>
                                Saques
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>login-rede" class="<?= activeSidebarLink('login-rede'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-albums"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">network_node</span>
                        Login/Rede
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>usuarios-diretos" class="<?= activeSidebarLink('usuarios-diretos'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-albums"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">link</span>
                        Usuários/Diretos
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>controle-produtos" class="<?= activeSidebarLink('controle-produtos'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-albums"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">inventory</span>
                        Controle de Produtos
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>notificacoes" class="<?= activeSidebarLink('notificacoes'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-albums"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">notifications_active</span>
                        Notificações
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>mensagens" class="<?= activeSidebarLink('mensagens'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-albums"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">forum</span>
                        Suporte
                    </a>
                </li>

                <?php elseif ($user_role == "user") : ?>

                <?php if ($user['plan_id'] != 3) : ?>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>meus-titulos" class="<?= activeSidebarLink('meus-titulos'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-albums"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">description</span>
                        Meus Títulos
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>meu-grupo" class="<?= activeSidebarLink('meu-grupo'); ?>">
                        <!-- <i class="metismenu-icon lnr-users"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">groups</span>
                        Meu Grupo (5 Participantes)
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>meus-premios" class="<?= activeSidebarLink('meus-premios'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-users"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">workspace_premium</span>
                        Meus Prêmios
                    </a>
                </li>

                <?php endif; ?>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastros-pendentes" class="<?= activeSidebarLink('cadastros-pendentes'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-users"></i> -->
                        <!-- <span class="metismenu-icon material-symbols-rounded">network_node</span> -->
                        <span class="metismenu-icon material-symbols-rounded">schedule</span>
                        Cadastros Pendentes
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>meus-diretos" class="<?= activeSidebarLink('meus-diretos'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-note2"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">share</span>
                        Meus Diretos
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>minha-equipe" class="<?= activeSidebarLink('minha-equipe'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-users"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">network_node</span>
                        Minha Equipe
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>premios-da-semana" class="<?= activeSidebarLink('premios-da-semana'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-culture"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">event</span>
                        Prêmios da Semana
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>financeiro" class="<?= activeSidebarLink('financeiro'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-wallet"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">account_balance</span>
                        Financeiro
                    </a>
                </li>

                <?php if ($user['plan_id'] != 3) : ?>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>resultados" class="<?= activeSidebarLink('resultados'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-wallet"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">list</span>
                        Resultados
                    </a>
                </li>

                <?php endif; ?>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>plano-de-negocio" class="<?= activeSidebarLink('plano-de-negocio'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-wallet"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">monitoring</span>
                        Plano de Negócio
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>minha-conta" class="<?= activeSidebarLink('minha-conta'); ?>">
                        <!-- <i class="metismenu-icon pe-7s-user"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">account_circle</span>
                        Minha Conta
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>suporte" class="<?= activeSidebarLink('suporte'); ?>">
                        <!-- <i class="metismenu-icon lnr-question-circle"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">support_agent</span>
                        Suporte
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>vouchers" class="<?= activeSidebarLink('vouchers'); ?>">
                        <!-- <i class="metismenu-icon lnr-question-circle"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">stars</span>
                        Vouchers
                    </a>
                </li>

                <?php endif; ?>

                <li class="mt-3">
                    <a href="<?= INCLUDE_PATH_AUTH; ?>sair" class="text-danger <?= activeSidebarLink('sair'); ?>">
                        <!-- <i class="metismenu-icon lnr-exit text-danger"></i> -->
                        <span class="metismenu-icon material-symbols-rounded">logout</span>
                        Sair
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>