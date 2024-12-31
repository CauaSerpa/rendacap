<div class="app-header header-shadow">
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
    <div class="app-header__content">
        <div class="app-header-left">
            <div class="search-wrapper">
                <div class="input-holder">
                    <input type="text" class="search-input" placeholder="Digite para pesquisar" autocomplete="off">
                    <button class="search-icon">
                        <span></span>
                    </button>
                </div>
                <button class="close"></button>
            </div>
        </div>
        <div class="app-header-right">
            <div class="header-dots">
                <div class="dropdown">
                    <?php
                        // Contar o número de notificações não lidas
                        $count_stmt = $conn->prepare("
                            SELECT COUNT(*) AS unread_count
                            FROM tb_notification_recipients nr
                            WHERE nr.user_id = ? AND nr.is_read = 0
                        ");
                        $count_stmt->execute([$_SESSION['user_id']]);
                        $unread_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
                    ?>
                    <button type="button" aria-haspopup="true" aria-expanded="false"
                        data-toggle="dropdown" class="p-0 mr-2 btn btn-link">
                        <span class="icon-wrapper icon-wrapper-alt rounded-circle">
                            <span class="icon-wrapper-bg bg-danger"></span>
                            <i class="icon text-danger ion-android-notifications <?= ($unread_count > 0) ? "icon-anim-pulse" : ""; ?>"></i>
                            <span class="badge badge-dot badge-dot-sm badge-danger <?= ($unread_count == 0) ? "d-none" : ""; ?>">Notificações</span>
                        </span>
                    </button>
                    <div tabindex="-1" role="menu" aria-hidden="true"
                        class="dropdown-menu-xl rm-pointers dropdown-menu dropdown-menu-right">
                        <div class="dropdown-menu-header mb-0">
                            <div class="dropdown-menu-header-inner bg-deep-blue">
                                <div class="menu-header-image opacity-1" style="background-image: url('images/dropdown-header/city3.jpg');"></div>
                                <div class="menu-header-content text-dark">
                                    <h5 class="menu-header-title">Notificações</h5>
                                    <h6 class="menu-header-subtitle">Você tem
                                        <b><?= $unread_count; ?></b> mensagens não lidas
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <ul class="tabs-animated-shadow tabs-animated nav nav-justified tabs-shadow-bordered p-3">
                            <!-- <li class="nav-item">
                                <a role="tab" class="nav-link active" data-toggle="tab" href="#tab-messages-header">
                                    <span>Messages</span>
                                </a>
                            </li> -->
                            <li class="nav-item">
                                <a role="tab" class="nav-link active" data-toggle="tab" href="#tab-events-header">
                                    <span>Notificações</span>
                                </a>
                            </li>
                            <!-- <li class="nav-item">
                                <a role="tab" class="nav-link" data-toggle="tab" href="#tab-errors-header">
                                    <span>System Errors</span>
                                </a>
                            </li> -->
                        </ul>
                        <div class="tab-content">
                            <!-- <div class="tab-pane active" id="tab-messages-header" role="tabpanel">
                                <div class="scroll-area-sm">
                                    <div class="scrollbar-container">
                                        <div class="p-3">
                                            <div class="notifications-box">
                                                <div class="vertical-time-simple vertical-without-time vertical-timeline vertical-timeline--one-column">
                                                    <div class="vertical-timeline-item dot-danger vertical-timeline-element">
                                                        <div>
                                                            <span class="vertical-timeline-element-icon bounce-in"></span>
                                                            <div class="vertical-timeline-element-content bounce-in">
                                                                <h4 class="timeline-title">All Hands Meeting</h4>
                                                                <span class="vertical-timeline-element-date"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="vertical-timeline-item dot-warning vertical-timeline-element">
                                                        <div>
                                                            <span class="vertical-timeline-element-icon bounce-in"></span>
                                                            <div class="vertical-timeline-element-content bounce-in">
                                                                <p>Yet another one, at
                                                                    <span class="text-success">15:00 PM</span>
                                                                </p>
                                                                <span class="vertical-timeline-element-date"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="vertical-timeline-item dot-success vertical-timeline-element">
                                                        <div>
                                                            <span class="vertical-timeline-element-icon bounce-in"></span>
                                                            <div class="vertical-timeline-element-content bounce-in">
                                                                <h4 class="timeline-title">
                                                                    Build the production release
                                                                    <span class="badge badge-danger ml-2">NEW</span>
                                                                </h4>
                                                                <span class="vertical-timeline-element-date"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="vertical-timeline-item dot-primary vertical-timeline-element">
                                                        <div>
                                                            <span class="vertical-timeline-element-icon bounce-in"></span>
                                                            <div class="vertical-timeline-element-content bounce-in">
                                                                <h4 class="timeline-title">
                                                                    Something not important
                                                                    <div class="avatar-wrapper mt-2 avatar-wrapper-overlap">
                                                                        <div class="avatar-icon-wrapper avatar-icon-sm">
                                                                            <div class="avatar-icon">
                                                                                <img src="images/avatars/1.jpg" alt="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="avatar-icon-wrapper avatar-icon-sm">
                                                                            <div class="avatar-icon">
                                                                                <img src="images/avatars/2.jpg" alt="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="avatar-icon-wrapper avatar-icon-sm">
                                                                            <div class="avatar-icon">
                                                                                <img src="images/avatars/3.jpg" alt="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="avatar-icon-wrapper avatar-icon-sm">
                                                                            <div class="avatar-icon">
                                                                                <img src="images/avatars/4.jpg" alt="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="avatar-icon-wrapper avatar-icon-sm">
                                                                            <div class="avatar-icon">
                                                                                <img src="images/avatars/5.jpg" alt="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="avatar-icon-wrapper avatar-icon-sm">
                                                                            <div class="avatar-icon">
                                                                                <img src="images/avatars/9.jpg" alt="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="avatar-icon-wrapper avatar-icon-sm">
                                                                            <div class="avatar-icon">
                                                                                <img src="images/avatars/7.jpg" alt="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="avatar-icon-wrapper avatar-icon-sm">
                                                                            <div class="avatar-icon">
                                                                                <img src="images/avatars/8.jpg" alt="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="avatar-icon-wrapper avatar-icon-sm avatar-icon-add">
                                                                            <div class="avatar-icon">
                                                                                <i>+</i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </h4>
                                                                <span class="vertical-timeline-element-date"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="vertical-timeline-item dot-info vertical-timeline-element">
                                                        <div>
                                                            <span class="vertical-timeline-element-icon bounce-in"></span>
                                                            <div class="vertical-timeline-element-content bounce-in">
                                                                <h4 class="timeline-title">This dot has an info state</h4>
                                                                <span class="vertical-timeline-element-date"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="vertical-timeline-item dot-danger vertical-timeline-element">
                                                        <div>
                                                            <span class="vertical-timeline-element-icon bounce-in"></span>
                                                            <div class="vertical-timeline-element-content bounce-in">
                                                                <h4 class="timeline-title">All Hands Meeting</h4>
                                                                <span class="vertical-timeline-element-date"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="vertical-timeline-item dot-warning vertical-timeline-element">
                                                        <div>
                                                            <span class="vertical-timeline-element-icon bounce-in"></span>
                                                            <div class="vertical-timeline-element-content bounce-in">
                                                                <p>Yet another one, at
                                                                    <span class="text-success">15:00 PM</span>
                                                                </p>
                                                                <span class="vertical-timeline-element-date"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="vertical-timeline-item dot-success vertical-timeline-element">
                                                        <div>
                                                            <span class="vertical-timeline-element-icon bounce-in"></span>
                                                            <div class="vertical-timeline-element-content bounce-in">
                                                                <h4 class="timeline-title">
                                                                    Build the production release
                                                                    <span class="badge badge-danger ml-2">NEW</span>
                                                                </h4>
                                                                <span class="vertical-timeline-element-date"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="vertical-timeline-item dot-dark vertical-timeline-element">
                                                        <div>
                                                            <span class="vertical-timeline-element-icon bounce-in"></span>
                                                            <div class="vertical-timeline-element-content bounce-in">
                                                                <h4 class="timeline-title">This dot has a dark state</h4>
                                                                <span class="vertical-timeline-element-date"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="tab-pane active" id="tab-events-header" role="tabpanel">
                                <div class="scroll-area-sm">
                                    <div class="scrollbar-container">
                                        <div class="p-3">
                                            <div class="vertical-without-time vertical-timeline vertical-timeline--animate vertical-timeline--one-column">
                                                <!-- <div class="vertical-timeline-item vertical-timeline-element">
                                                    <div>
                                                        <span class="vertical-timeline-element-icon bounce-in">
                                                            <i class="badge badge-dot badge-dot-xl badge-success"></i>
                                                        </span>
                                                        <div class="vertical-timeline-element-content bounce-in">
                                                            <h4 class="timeline-title">Complete seu cadastro</h4>
                                                            <p>
                                                                Finalize o registro da sua conta para aumentar a segurança e habilitar a configuração de saques futuros. Acesse a aba 
                                                                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>minha-conta">Minha Conta</a> 
                                                                para concluir o processo.
                                                            </p>
                                                            <span class="vertical-timeline-element-date"></span>
                                                        </div>
                                                    </div>
                                                </div> -->

                                                <?php
                                                    // Selecionar notificações do usuário
                                                    $stmt = $conn->prepare("
                                                        SELECT n.id, n.title, n.content, n.type, n.created_at, nr.is_read
                                                        FROM tb_notifications n
                                                        INNER JOIN tb_notification_recipients nr ON n.id = nr.notification_id
                                                        WHERE nr.user_id = ?
                                                        ORDER BY n.created_at DESC
                                                    ");
                                                    $stmt->execute([$_SESSION['user_id']]);
                                                    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                ?>
                                                
                                                <?php if ($notifications) : ?>
                                                    <?php foreach ($notifications as $notification) : ?>
                                                        <div class="vertical-timeline-item vertical-timeline-element">
                                                            <div>
                                                                <span class="vertical-timeline-element-icon bounce-in">
                                                                    <i class="badge badge-dot badge-dot-xl badge-success"></i>
                                                                </span>
                                                                <div class="vertical-timeline-element-content bounce-in">
                                                                    <h4 class="timeline-title"><?= $notification['title']; ?></h4>
                                                                    <p><?= nl2br($notification['content']); ?></p>
                                                                    <span class="vertical-timeline-element-date"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else : ?>
                                                    <p class="text-center">Você não possui notificações no momento.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($unread_count > 0) : ?>
                            <script>
                                $(document).ready(function() {
                                    // Detectar clique no botão de notificação
                                    $(document).on('click', 'button[data-toggle="dropdown"]', function() {
                                        // Fazer a requisição AJAX para marcar notificações como lidas
                                        $.ajax({
                                            url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/read-notification.php',
                                            method: 'POST',
                                            data: { action: 'read-notification' },
                                            success: function(response) {
                                                console.log('Notificações marcadas como lidas:', response);
                                            },
                                            error: function(xhr, status, error) {
                                                console.error('Erro ao marcar notificações como lidas:', error);
                                            }
                                        });
                                    });
                                });
                            </script>
                            <?php endif; ?>

                            <!-- <div class="tab-pane" id="tab-errors-header" role="tabpanel">
                                <div class="scroll-area-sm">
                                    <div class="scrollbar-container">
                                        <div class="no-results pt-3 pb-0">
                                            <div class="swal2-icon swal2-success swal2-animate-success-icon">
                                                <div class="swal2-success-circular-line-left" style="background-color: rgb(255, 255, 255);"></div>
                                                <span class="swal2-success-line-tip"></span>
                                                <span class="swal2-success-line-long"></span>
                                                <div class="swal2-success-ring"></div>
                                                <div class="swal2-success-fix" style="background-color: rgb(255, 255, 255);"></div>
                                                <div class="swal2-success-circular-line-right" style="background-color: rgb(255, 255, 255);"></div>
                                            </div>
                                            <div class="results-subtitle">All caught up!</div>
                                            <div class="results-title">There are no system errors!</div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        <!-- <ul class="nav flex-column">
                            <li class="nav-item-divider nav-item"></li>
                            <li class="nav-item-btn text-center nav-item">
                                <button class="btn-shadow btn-wide btn-pill btn btn-focus btn-sm">View Latest Changes</button>
                            </li>
                        </ul> -->
                    </div>
                </div>
                <!-- <div class="dropdown">
                    <button type="button" data-toggle="dropdown" class="p-0 mr-2 btn btn-link">
                        <span class="icon-wrapper icon-wrapper-alt rounded-circle">
                            <span class="icon-wrapper-bg bg-focus"></span>
                            <span class="language-icon opacity-8 flag large DE"></span>
                        </span>
                    </button>
                    <div tabindex="-1" role="menu" aria-hidden="true"
                        class="rm-pointers dropdown-menu dropdown-menu-right">
                        <div class="dropdown-menu-header">
                            <div class="dropdown-menu-header-inner pt-4 pb-4 bg-focus">
                                <div class="menu-header-image opacity-05" style="background-image: url('images/dropdown-header/city2.jpg');"></div>
                                <div class="menu-header-content text-center text-white">
                                    <h6 class="menu-header-subtitle mt-0"> Choose Language</h6>
                                </div>
                            </div>
                        </div>
                        <h6 tabindex="-1" class="dropdown-header"> Popular Languages</h6>
                        <button type="button" tabindex="0" class="dropdown-item">
                            <span class="mr-3 opacity-8 flag large US"></span>
                            USA
                        </button>
                        <button type="button" tabindex="0" class="dropdown-item">
                            <span class="mr-3 opacity-8 flag large CH"></span>
                            Switzerland
                        </button>
                        <button type="button" tabindex="0" class="dropdown-item">
                            <span class="mr-3 opacity-8 flag large FR"></span>
                            France
                        </button>
                        <button type="button" tabindex="0" class="dropdown-item">
                            <span class="mr-3 opacity-8 flag large ES"></span>
                            Spain
                        </button>
                        <div tabindex="-1" class="dropdown-divider"></div>
                        <h6 tabindex="-1" class="dropdown-header">Others</h6>
                        <button type="button" tabindex="0" class="dropdown-item active">
                            <span class="mr-3 opacity-8 flag large DE"></span>
                            Germany
                        </button>
                        <button type="button" tabindex="0" class="dropdown-item">
                            <span class="mr-3 opacity-8 flag large IT"></span>
                            Italy
                        </button>
                    </div>
                </div> -->
                <div class="dropdown">
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>suporte" aria-haspopup="true" data-toggle="tooltip" data-placement="bottom"
                        title="Ajuda" aria-expanded="false" class="p-0 btn btn-link dd-chart-btn">
                        <span class="icon-wrapper icon-wrapper-alt rounded-circle">
                            <span class="icon-wrapper-bg bg-info"></span>
                            <i class="icon text-info ion-android-bulb"></i>
                        </span>
                    </a>
                    <div tabindex="-1" role="menu" aria-hidden="true"
                        class="dropdown-menu-xl rm-pointers dropdown-menu dropdown-menu-right">
                        <div class="dropdown-menu-header">
                            <div class="dropdown-menu-header-inner bg-premium-dark">
                                <div class="menu-header-image" style="background-image: url('images/dropdown-header/abstract4.jpg');"></div>
                                <div class="menu-header-content text-white">
                                    <h5 class="menu-header-title">Users Online</h5>
                                    <h6 class="menu-header-subtitle">Recent Account Activity Overview</h6>
                                </div>
                            </div>
                        </div>
                        <div class="widget-chart">
                            <div class="widget-chart-content">
                                <div class="icon-wrapper rounded-circle">
                                    <div class="icon-wrapper-bg opacity-9 bg-focus"></div>
                                    <i class="lnr-users text-white"></i>
                                </div>
                                <div class="widget-numbers">
                                    <span>344k</span>
                                </div>
                                <div class="widget-subheading pt-2">
                                    Profile views since last login
                                </div>
                                <div class="widget-description text-danger">
                                    <span class="pr-1">
                                        <span>176%</span>
                                    </span>
                                    <i class="fa fa-arrow-left"></i>
                                </div>
                            </div>
                            <div class="widget-chart-wrapper">
                                <div id="dashboard-sparkline-carousel-3-pop"></div>
                            </div>
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item-divider mt-0 nav-item"></li>
                            <li class="nav-item-btn text-center nav-item">
                                <button class="btn-shine btn-wide btn-pill btn btn-warning btn-sm">
                                    <i class="fa fa-cog fa-spin mr-2"></i>
                                    View Details
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="header-btn-lg pr-0">
                <div class="widget-content p-0">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="btn-group">
                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                    <img width="42" class="avatar rounded-circle" src="<?= $user['avatar']; ?>" alt="Profile Avatar">
                                    <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                </a>
                                <div tabindex="-1" role="menu" aria-hidden="true"
                                    class="rm-pointers dropdown-menu-lg dropdown-menu dropdown-menu-right">
                                    <div class="dropdown-menu-header">
                                        <div class="dropdown-menu-header-inner bg-info">
                                            <div class="menu-header-image opacity-2" style="background-image: url('images/dropdown-header/city3.jpg');"></div>
                                            <div class="menu-header-content text-left">
                                                <div class="widget-content p-0">
                                                    <div class="widget-content-wrapper">
                                                        <div class="widget-content-left mr-3">
                                                            <img width="42" class="avatar rounded-circle"
                                                                src="<?= $user['avatar']; ?>"  alt="Profile Avatar">
                                                        </div>
                                                        <div class="widget-content-left">
                                                            <div class="widget-heading"><?= $user['shortname']; ?></div>
                                                            <div class="widget-subheading opacity-8"><?= $user['email']; ?></div>
                                                        </div>
                                                        <div class="widget-content-right mr-2">
                                                            <a href="<?= INCLUDE_PATH_AUTH; ?>sair" class="btn-pill btn-shadow btn-shine btn btn-focus">Sair</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="scroll-area-xs" style="height: 100%;">
                                        <div class="scrollbar-container ps">
                                            <ul class="nav flex-column">
                                                <!-- <li class="nav-item-header nav-item">Activity</li>
                                                <li class="nav-item">
                                                    <a href="javascript:void(0);" class="nav-link">
                                                        Chat
                                                        <div class="ml-auto badge badge-pill badge-info">8</div>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="javascript:void(0);" class="nav-link">Recover Password</a>
                                                </li> -->
                                                <li class="nav-item-header nav-item">
                                                    Conta
                                                </li>
                                                <li class="nav-item">
                                                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>financeiro" class="nav-link">Financeiro</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>minha-conta" class="nav-link">
                                                        Configurações
                                                    </a>
                                                </li>
                                                <!-- <li class="nav-item">
                                                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>ajuda" class="nav-link">Ajuda</a>
                                                </li> -->
                                                <li class="nav-item">
                                                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>suporte" class="nav-link">
                                                        Suporte
                                                        <div class="ml-auto badge badge-success">Novo</div>
                                                        <!-- <div class="ml-auto badge badge-warning">512</div> -->
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- <ul class="nav flex-column">
                                        <li class="nav-item-divider mb-0 nav-item"></li>
                                    </ul>
                                    <div class="grid-menu grid-menu-2col">
                                        <div class="no-gutters row">
                                            <div class="col-sm-6">
                                                <button class="btn-icon-vertical btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-warning">
                                                    <i class="pe-7s-chat icon-gradient bg-amy-crisp btn-icon-wrapper mb-2"></i>
                                                    Message Inbox
                                                </button>
                                            </div>
                                            <div class="col-sm-6">
                                                <button class="btn-icon-vertical btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-danger">
                                                    <i class="pe-7s-ticket icon-gradient bg-love-kiss btn-icon-wrapper mb-2"></i>
                                                    <b>Support Tickets</b>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <ul class="nav flex-column">
                                        <li class="nav-item-divider nav-item"></li>
                                        <li class="nav-item-btn text-center nav-item">
                                            <button class="btn-wide btn btn-primary btn-sm"> Open Messages</button>
                                        </li>
                                    </ul> -->
                                </div>
                            </div>
                        </div>
                        <div class="widget-content-left  ml-3 header-user-info">
                            <div class="widget-heading"> <?= $user['shortname']; ?></div>
                            <div class="widget-subheading"> <?= $user['username']; ?></div>
                        </div>
                        <!-- <div class="widget-content-right header-user-info ml-3">
                            <button type="button" class="btn-shadow p-1 btn btn-primary btn-sm show-toastr-example">
                                <i class="fa text-white fa-calendar pr-1 pl-1"></i>
                            </button>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>