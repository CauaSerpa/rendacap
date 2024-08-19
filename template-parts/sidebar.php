<?php
    //Funcao '.active' Sidebar
    function activeSidebarLink($par) {
        $url = explode('/',@$_GET['url'])[0];
        if ($url == $par)
        {
            echo 'class="mm-active"';
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
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>home" <?= activeSidebarLink('home'); ?>>
                        <i class="metismenu-icon pe-7s-home"></i>
                        Início
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>meus-titulos" <?= activeSidebarLink('meus-titulos'); ?>>
                        <i class="metismenu-icon pe-7s-albums"></i>
                        Meus Títulos
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>meu-grupo" <?= activeSidebarLink('meu-grupo'); ?>>
                        <i class="metismenu-icon lnr-users"></i>
                        Meu Grupo (5 Participantes)
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>meus-direitos" <?= activeSidebarLink('meus-direitos'); ?>>
                        <i class="metismenu-icon pe-7s-note2"></i>
                        Meus Direitos
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>minha-equipe" <?= activeSidebarLink('minha-equipe'); ?>>
                        <i class="metismenu-icon pe-7s-users"></i>
                        Minha Equipe
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>minha-conta" <?= activeSidebarLink('minha-conta'); ?>>
                        <i class="metismenu-icon lnr-user"></i>
                        Minha Conta
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>financeiro" <?= activeSidebarLink('financeiro'); ?>>
                        <i class="metismenu-icon pe-7s-cash"></i>
                        Financeiro
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>academia-rendacap" <?= activeSidebarLink('academia-rendacap'); ?>>
                        <i class="metismenu-icon pe-7s-user"></i>
                        Academia RendaCap
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>suporte" <?= activeSidebarLink('suporte'); ?>>
                        <i class="metismenu-icon lnr-question-circle"></i>
                        Suporte
                    </a>
                </li>
                <li>
                    <a href="<?= INCLUDE_PATH_AUTH; ?>sair" <?= activeSidebarLink('sair'); ?>>
                        <i class="metismenu-icon lnr-exit"></i>
                        Sair
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>