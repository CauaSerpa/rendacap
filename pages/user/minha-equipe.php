<?php
// Verificar se a sessão do usuário está ativa
if (isset($_SESSION['user_id'])) {
    // SQL para contar os vouchers ativos por plano
    $sql = "
        SELECT 
            v.slug AS plan,
            COUNT(vo.id) AS count_vouchers
        FROM 
            tb_voucher_orders vo
        JOIN 
            tb_vouchers v ON vo.voucher_id = v.id
        WHERE 
            vo.user_id = :user_id 
            AND vo.status = 'completed' 
            AND vo.used = 0
        GROUP BY 
            v.slug
    ";

    // Preparar a consulta
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);

    // Executar a consulta
    $stmt->execute();

    // Obter os resultados
    $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Preparar a resposta com os resultados formatados
    $response = [
        'status' => 'success',
        'vouchers' => []
    ];

    // Mapear os resultados em um formato chave-valor
    foreach ($vouchers as $voucher) {
        $response['vouchers'][$voucher['plan']] = $voucher['count_vouchers'];
    }

    // Garantir que planos sem vouchers retornem 0
    $allPlans = ['diamond', 'gold', 'silver']; // Lista completa de planos
    foreach ($allPlans as $plan) {
        if (!isset($response['vouchers'][$plan])) {
            $response['vouchers'][$plan] = 0;
        }
    }
} else {
    // Caso os parâmetros não sejam passados corretamente
    $response = [
        'status' => 'error',
        'message' => 'Usuário não encontrado.'
    ];
}
?>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users icon-gradient bg-malibu-beach"></i>
            </div>
            <div>
                Minha Equipe
                <div class="page-title-subheading">Aqui você pode visualizar e expandir sua rede de convidados. Convide novas pessoas e acompanhe o crescimento da sua rede!</div>
            </div>
        </div>
        <div class="page-title-actions text-center">
            <div class="d-flex">
                <div class="voucher-container">
                    <div class="voucher-image">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/avatars/diamante.png" alt="Diamond Icon" class="voucher-img img-fluid">
                    </div>
                    <div class="voucher-info text-left">
                        <p class="mb-0 fsize-1 font-weight-semibold">Diamante</p>
                        <p class="count mb-0"><?= $response['vouchers']['diamond']; ?></p>
                    </div>
                </div>
                <div class="voucher-container">
                    <div class="voucher-image">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/avatars/ouro.png" alt="Gold Icon" class="voucher-img img-fluid">
                    </div>
                    <div class="voucher-info text-left">
                        <p class="mb-0 fsize-1 font-weight-semibold">Ouro</p>
                        <p class="count mb-0"><?= $response['vouchers']['gold']; ?></p>
                    </div>
                </div>
                <div class="voucher-container">
                    <div class="voucher-image">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/avatars/prata.png" alt="Silver Icon" class="voucher-img img-fluid">
                    </div>
                    <div class="voucher-info text-left">
                        <p class="mb-0 fsize-1 font-weight-semibold">Prata</p>
                        <p class="count mb-0"><?= $response['vouchers']['silver']; ?></p>
                    </div>
                </div>
            </div>
            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>vouchers" class="btn btn-link">Adquirir Voucher</a>
        </div>
    </div>
</div>

<?php

// $no_invited_users = []; // Array global para armazenar IDs de usuários sem convidados

// function list_network1($conn, $networkIds) {
//     global $no_invited_users; // Garantir que o array seja global

//     foreach ($networkIds as $networkId) {
//         // Consulta para obter todos os user_id associados à network
//         $stmt = $conn->prepare("
//             SELECT user_id
//             FROM tb_user_networks
//             WHERE network_id = ?
//         ");
//         $stmt->execute([$networkId]);
//         $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

//         foreach ($user_ids as $subuser_id) {
//             // Verifica quantos usuários estão associados à network do subusuário
//             $stmt = $conn->prepare("
//                 SELECT COUNT(*) AS user_count
//                 FROM tb_user_networks
//                 INNER JOIN tb_networks ON tb_user_networks.network_id = tb_networks.id
//                 WHERE tb_networks.inviter_id = ?
//             ");
//             $stmt->execute([$subuser_id]);
//             $userCountResult = $stmt->fetch(PDO::FETCH_ASSOC);
//             $userCount = $userCountResult['user_count'];

//             // Se o subusuário tiver menos de 5 usuários na rede, adiciona ao array
//             if ($userCount < 5) {
//                 $no_invited_users[] = $subuser_id;
//             }

//             // Verifica se o subusuário tem redes
//             $stmt = $conn->prepare("
//                 SELECT id
//                 FROM tb_networks
//                 WHERE inviter_id = ?
//             ");
//             $stmt->execute([$subuser_id]);
//             $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

//             if (empty($networkIds)) {
//                 // Se não há redes, adiciona ao array de usuários sem convidados
//                 $no_invited_users[] = $subuser_id;
//             } else {
//                 // Chama a função recursiva para continuar listando os convidados
//                 list_network1($conn, $networkIds);
//             }
//         }
//     }
// }

// // Verifica quantas vezes o inviter_id aparece na tabela tb_networks
// $stmt = $conn->prepare("
//     SELECT COUNT(*) AS count
//     FROM tb_networks
//     WHERE inviter_id = ?
// ");
// $stmt->execute([$_SESSION['user_id']]);
// $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
// $inviterCount = $countResult['count'];

// // Consulta para obter todos os network_ids do usuário
// $stmt = $conn->prepare("
//     SELECT id
//     FROM tb_networks
//     WHERE inviter_id = ?
// ");
// $stmt->execute([$_SESSION['user_id']]);
// $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// // Se não houver networks, também queremos incluir usuários sem redes
// if (empty($networkIds)) {
//     // Consulta para obter todos os usuários que não têm nenhuma rede cadastrada
//     $stmt = $conn->prepare("
//         SELECT id
//         FROM tb_users
//         WHERE id NOT IN (SELECT user_id FROM tb_user_networks)
//     ");
//     $stmt->execute();
//     $noNetworkUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
//     // Adiciona usuários sem rede ao array
//     $no_invited_users = array_merge($no_invited_users, $noNetworkUsers);
// } else {
//     // Listar os usuários na rede
//     list_network1($conn, $networkIds);
// }

// // Remove duplicatas do array, se houver
// $no_invited_users = array_unique($no_invited_users);

// // Ordena o array em ordem crescente
// sort($no_invited_users);

// // Exibe o array ordenado
// // echo "<pre>";
// // print_r($no_invited_users);
// // echo "</pre>";






















// $users_with_less_than_5_invited = []; // Array global para armazenar IDs de usuários sem convidados

// function list_network($conn, $networkIds) {
//     global $users_with_less_than_5_invited; // Garantir que o array seja global

//     foreach ($networkIds as $networkId) {
//         // Consulta para obter todos os user_id associados à network
//         $stmt = $conn->prepare("
//             SELECT user_id
//             FROM tb_user_networks
//             WHERE network_id = ?
//         ");
//         $stmt->execute([$networkId]);
//         $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

//         foreach ($user_ids as $subuser_id) {
//             // Verifica quantos usuários estão associados à network do subusuário
//             $stmt = $conn->prepare("
//                 SELECT COUNT(*) AS user_count
//                 FROM tb_user_networks
//                 INNER JOIN tb_networks ON tb_user_networks.network_id = tb_networks.id
//                 WHERE tb_networks.inviter_id = ?
//             ");
//             $stmt->execute([$subuser_id]);
//             $userCountResult = $stmt->fetch(PDO::FETCH_ASSOC);
//             $userCount = $userCountResult['user_count'];

//             // Se o subusuário tiver menos de 5 usuários na rede, adiciona ao array
//             if ($userCount < 5) {
//                 $users_with_less_than_5_invited[] = $subuser_id;
//             }

//             // Verifica se o subusuário tem redes
//             $stmt = $conn->prepare("
//                 SELECT id
//                 FROM tb_networks
//                 WHERE inviter_id = ?
//             ");
//             $stmt->execute([$subuser_id]);
//             $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

//             if (empty($networkIds)) {
//                 // Se não há redes, adiciona ao array de usuários sem convidados
//                 $users_with_less_than_5_invited[] = $subuser_id;
//             } else {
//                 // Chama a função recursiva para continuar listando os convidados
//                 list_network($conn, $networkIds);
//             }
//         }
//     }
// }

// // Verifica quantas vezes o inviter_id aparece na tabela tb_networks
// $stmt = $conn->prepare("
//     SELECT COUNT(*) AS count
//     FROM tb_networks
//     WHERE inviter_id = ?
// ");
// $stmt->execute([$_SESSION['user_id']]);
// $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
// $inviterCount = $countResult['count'];

// // Consulta para obter todos os network_ids do usuário
// $stmt = $conn->prepare("
//     SELECT id
//     FROM tb_networks
//     WHERE inviter_id = ?
// ");
// $stmt->execute([$_SESSION['user_id']]);
// $networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// // Se não houver networks, também queremos incluir usuários sem redes
// if (empty($networkIds)) {
//     // Consulta para obter todos os usuários que não têm nenhuma rede cadastrada
//     $stmt = $conn->prepare("
//         SELECT id
//         FROM tb_users
//         WHERE id NOT IN (SELECT user_id FROM tb_user_networks)
//     ");
//     $stmt->execute();
//     $noNetworkUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
//     // Adiciona usuários sem rede ao array
//     $users_with_less_than_5_invited = array_merge($users_with_less_than_5_invited, $noNetworkUsers);
// } else {
//     // Listar os usuários na rede
//     list_network($conn, $networkIds);
// }

// // Remove duplicatas do array, se houver
// $users_with_less_than_5_invited = array_unique($users_with_less_than_5_invited);

// // Ordena o array em ordem crescente
// sort($users_with_less_than_5_invited);

// // Exibe o array ordenado
// echo "<pre>";
// print_r($users_with_less_than_5_invited);
// echo "</pre>";
?>

<style>
    .col-auto {
        margin: 1rem;
        padding: 0;
    }
    .network {
        position: relative;
        text-align: center;
        margin: 50px 0;
        display: flex;
        align-items: center;
    }
    .inviter {
        display: flex;
        justify-content: center;
        padding: 1rem;
    }
    .inviter .inviter-img {
        border-radius: 50%;
        width: 100px;
        position: relative;
    }
    .inviter-container {
        position: relative;
    }
    .inviter .referrer-actions .info-icon {
        position: absolute;
        left: 5px;
        top: 25px;
        font-size: 1rem;
        cursor: pointer;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        color: #fff;
        background: blue;
        align-items: center;
        justify-content: center;
        transition: .3s;
    }
    .your-container {
        z-index: 9;
    }
    .your-img {
        border-radius: 50%;
        width: 150px;
        height: 150px;
        object-fit: cover;
        margin: 0 auto;
    }
    .connected-img {
        border-radius: 50%;
        width: 100px;
        height: 100px;
        object-fit: cover;
        position: relative;
    }
    .more-images {
        display: none;
        position: relative;
        margin-top: 1.5rem;
        width: 100%;
    }
    .more-images .row {
        flex-wrap: inherit !important;
    }
    .more-images .connected-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
    }
    .info-icon {
        position: absolute;
        left: 5px;
        top: 5px;
        font-size: 1rem;
        cursor: pointer;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        color: #fff;
        background: blue;
        align-items: center;
        justify-content: center;
        transition: .3s;
    }
    /* .plus-icon {
        position: absolute;
        right: 5px;
        top: 5px;
        font-size: 1rem;
        cursor: pointer;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
        display: flex;
        color: #fff;
        background: blue;
        transition: .3s;
    } */
    .user-container {
        position: relative;
        width: max-content;
        z-index: 9;
    }
    /* .user-image {
        width: 100px;
    } */

    /* Gap */
    .g-3 {
        gap: 5rem;
    }
</style>

<?php
function count_total_users_in_network($conn, $userId) {
    // Obter IDs das redes criadas pelo usuário
    $networkIds = get_network_ids($conn, $userId);
    $totalUsers = 0;

    foreach ($networkIds as $networkId) {
        // Obter IDs dos usuários na rede
        $userIds = get_user_ids_in_network($conn, $networkId);
        $totalUsers += count($userIds);

        // Para cada usuário, contar também os usuários em suas redes
        foreach ($userIds as $subUserId) {
            $totalUsers += count_total_users_in_network($conn, $subUserId);
        }
    }

    return $totalUsers;
}

function get_user_data($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT u.*, p.name AS plan_name, s.status
        FROM tb_users u
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE u.id = ?
        ORDER BY s.status = 'ACTIVE' DESC, s.id DESC
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $plan = ($user['status'] == 'INACTIVE')
                ? 'inativo' 
                : (!empty($user['plan_name']) ? strtolower($user['plan_name']) : 'inativo');
        $no_image_src = INCLUDE_PATH_DASHBOARD . 'images/avatars/' . $plan . ".png";
        $avatar_src = INCLUDE_PATH_DASHBOARD . 'files/profile/avatar/' . $user['id'] . '/' . $user['profile_image'];
        $user['avatar'] = ($user['profile_image'] !== 'no-image.svg') ? $avatar_src : $no_image_src;

        // Contar o número total de usuários na rede
        $user['count_users'] = count_total_users_in_network($conn, $userId);
    }
    return $user;
}

function get_network_ids($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT id
        FROM tb_networks
        WHERE inviter_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_user_ids_in_network($conn, $networkId) {
    $stmt = $conn->prepare("
        SELECT user_id
        FROM tb_user_networks
        WHERE network_id = ?
    ");
    $stmt->execute([$networkId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function list_network($conn, $networkIds, $user, $elementoPai) {
    foreach ($networkIds as $networkId) {
        $userIds = get_user_ids_in_network($conn, $networkId);

        foreach ($userIds as $subuserId) {
            $subuser = get_user_data($conn, $subuserId);
            if (!$subuser) continue;

            $subuserNetworkIds = get_network_ids($conn, $subuserId);
            $showButton = empty($subuserNetworkIds) ? "d-none" : "";

            ?>
            <div class="col-auto d-flex align-items-center g-3">
                <div class="user-container" id="elemento-<?= $subuser['id']; ?>" data-pai="<?= $elementoPai; ?>">
                    <div class="user-image">
                        <img src="<?= $subuser['avatar']; ?>" alt="<?= htmlspecialchars($subuser['username']); ?>" class="user-img connected-img img-fluid">
                    </div>
                    <div class="user-info text-left">
                        <p class="name mb-0"><?= htmlspecialchars($subuser['username']); ?></p>
                        <p class="count mb-0">
                            <span class="material-symbols-rounded mr-1">group</span>
                            <?= $subuser['count_users']; ?>
                        </p>
                    </div>
                    <div class="user-actions">
                        <span class="info-icon" data-inviter-id="<?= $subuser['id']; ?>">i</span>
                        <span class="plus-icon <?= $showButton; ?>">+</span>
                    </div>
                </div>
                <div class="more-images">
                    <div class="row d-flex flex-column justify-content-center">
                        <?php list_network($conn, $subuserNetworkIds, $user, 'elemento-'.$subuser['id']); ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}
?>

<style>
    .voucher-container {
        min-width: 150px !important;
        margin: 0 .5rem 0 0 !important;
        background-color: #343a40 !important;
    }
    .voucher-info .count {
        font-weight: 600;
    }
    .voucher-container,
    .inviter-container,
    .your-container,
    .user-container {
        display: flex;
        min-width: 180px;
        background-color: #007bff;
        color: #fff;
        border-radius: 8px;
        margin: 20px;
        padding: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        position: relative;
        z-index: 9;
    }
    .voucher-img,
    .inviter-img,
    .your-img,
    .user-img {
        width: 50px !important;
        height: 50px !important;
        margin-right: .5rem !important;
    }
    .inviter-info .name,
    .your-info .name,
    .user-info .name {
        font-weight: 600;
    }
    .inviter-info .count,
    .your-info .count,
    .user-info .count {
        display: flex;
        align-items: center;
    }

    .plus-icon {
        position: absolute;
        right: -10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1rem;
        cursor: pointer;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
        display: flex;
        color: #fff;
        background: blue;
        transition: .3s;
    }

    canvas {
        position: absolute;
        top: 0;
        left: 0;
        pointer-events: none; /* Não interfere nos cliques */
        z-index: 1;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body w-100 overflow-auto">
                <h5 class="card-title">Sua Equipe</h5>
                <div class="mb-3" style="max-width: 400px;">
                    <form formnovalidate>
                        <input type="text" id="searchInput" placeholder="Pesquisar usuário..." class="form-control" onkeyup="searchUsers()" autocomplete="off">
                    </form>
                </div>
                <div class="network g-3 ml-5 position-relative" id="networkContainer">
                    <?php
                    $referrer = $user['referrer_id'] ? get_user_data($conn, $user['referrer_id']) : null;
                    $inviter = $user['inviter_id'] ? get_user_data($conn, $user['inviter_id']) : null;

                    if ($referrer): ?>
                        <div class="inviter pb-0">
                            <div class="inviter-container" id="elemento-<?= $referrer['id']; ?>" data-pai="">
                                <div class="inviter-image">
                                    <img src="<?= $referrer['avatar']; ?>" alt="Referrer image" class="inviter-img connected-img img-fluid">
                                </div>
                                <div class="inviter-info text-left">
                                    <p class="mb-0 fsize-1 font-weight-semibold">Patrocinador</p>
                                    <span class="name"><?= htmlspecialchars($referrer['username']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif;

                    if ($inviter): ?>
                        <div class="inviter">
                            <div class="inviter-container" id="elemento-<?= $inviter['id']; ?>" data-pai="<?= !empty($referrer['id']) ? "elemento-".$referrer['id'] : ""; ?>">
                                <div class="inviter-image">
                                    <img src="<?= $inviter['avatar']; ?>" alt="Inviter image" class="inviter-img connected-img img-fluid">
                                </div>
                                <div class="inviter-info text-left">
                                    <p class="mb-0 fsize-1 font-weight-semibold <?= ($referrer) ? "d-none" : ""; ?>">Patrocinador</p>
                                    <span class="name"><?= htmlspecialchars($inviter['username']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="your-container" id="elemento-<?= $user['id']; ?>" data-pai="elemento-<?= $inviter['id']; ?>">
                        <div class="your-image">
                            <img src="<?= $user['avatar']; ?>" alt="Central Image" class="your-img img-fluid">
                        </div>
                        <div class="your-info text-left">
                            <p class="name mb-0"><?= htmlspecialchars($user['username']); ?> (Você)</p>
                            <p class="count mb-0">
                                <span class="material-symbols-rounded mr-1">group</span>
                                <?= count_total_users_in_network($conn, $user['id']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="row d-flex flex-column">
                        <?php
                        $networkIds = get_network_ids($conn, $_SESSION['user_id']);
                        list_network($conn, $networkIds, $user, 'elemento-'.$user['id']);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<canvas id="canvas"></canvas>









<script>
    function drawCurvedLine(element1, element2, ctx) {
        // Obter as posições dos elementos
        const rect1 = element1.getBoundingClientRect();
        const rect2 = element2.getBoundingClientRect();

        // Calcular as posições absolutas dos elementos em relação ao documento
        const x1 = rect1.x + rect1.width / 2 + window.scrollX;
        const y1 = rect1.y + rect1.height / 2 + window.scrollY;
        const x2 = rect2.x + rect2.width / 2 + window.scrollX;
        const y2 = rect2.y + rect2.height / 2 + window.scrollY;

        // Calcular o ponto intermediário
        const midX = (x1 + x2) / 2;
        const midY = (y1 + y2) / 2;

        // Calcular os pontos de curva (a meio caminho entre os dois elementos)
        const curvePointX = midX;
        const curvePointY = (y1 > y2) ? midY - 50 : midY + 50;

        // Desenhar a linha curva
        ctx.beginPath();
        ctx.moveTo(x1, y1); // Início
        ctx.lineTo(midX, y1); // Linha reta até o meio
        ctx.lineTo(curvePointX, curvePointY); // Curva de 90 graus
        ctx.lineTo(midX, y2); // Linha reta até o outro ponto
        ctx.lineTo(x2, y2); // Conecta ao segundo ponto
        ctx.strokeStyle = '#c4c4c4';
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    // Função principal para configurar as linhas
    function setupLines() {
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');

        // Encontrar todos os elementos com IDs começando com "elemento-"
        const elementos = document.querySelectorAll('[id^="elemento-"]');

        // Inicializar valores máximos de largura e altura
        let maxWidth = 0;
        let maxHeight = 0;

        // Calcular os limites máximos necessários para o canvas
        elementos.forEach(elemento => {
            const rect = elemento.getBoundingClientRect();
            maxWidth = Math.max(maxWidth, rect.x + rect.width + window.scrollX);
            maxHeight = Math.max(maxHeight, rect.y + rect.height + window.scrollY);
        });

        // Ajustar o tamanho do canvas para cobrir toda a área
        canvas.width = maxWidth;
        canvas.height = maxHeight;

        // Para cada elemento, procurar o "data-pai" e desenhar a linha com o elemento pai
        elementos.forEach(elemento => {
            const paiId = elemento.getAttribute('data-pai');
            if (paiId) {
                const elementoPai = document.getElementById(paiId);

                // Verifica se a classe "more-images" do elemento pai está visível
                const moreImagesContainer = $(elemento).closest('.more-images');
                if (moreImagesContainer.length && moreImagesContainer.css('display') === 'none') {
                    return; // Ignorar este elemento se o contêiner ".more-images" estiver oculto
                }

                if (elementoPai) {
                    drawCurvedLine(elementoPai, elemento, ctx);
                }
            }
        });
    }

    document.querySelectorAll('.plus-icon').forEach(function (plusIcon) {
        plusIcon.addEventListener('click', function () {
            const elementoId = this.closest('.user-container').id; // ID do elemento do usuário
            const elementoPaiId = this.closest('.user-container').getAttribute('data-pai'); // ID do pai

            // Espera 500ms antes de atualizar a linha
            setTimeout(function () {
                // Atualiza a linha de rede
                updateNetworkLine(elementoId, elementoPaiId);
            }, 1); // 500 milissegundos de atraso
        });
    });

    function updateNetworkLine(elementoId, elementoPaiId) {
        // Aqui você pode colocar a lógica para atualizar ou redesenhar a linha entre os usuários
        console.log(`Atualizando linha entre ${elementoPaiId} e ${elementoId}`);
        
        // Função para atualizar ou redesenhar as linhas
        setupLines(); // Chama a função para redesenhar as linhas
    }

    // Redesenhar as linhas ao redimensionar a janela
    window.onload = setupLines;
    window.onresize = setupLines;
</script>


<script>
    // function drawCurvedLine(element1, element2, ctx) {
    //     // Obter as posições dos elementos
    //     const rect1 = element1.getBoundingClientRect();
    //     const rect2 = element2.getBoundingClientRect();

    //     // Calcular as posições absolutas dos elementos em relação ao documento
    //     const x1 = rect1.x + rect1.width / 2 + window.scrollX;
    //     const y1 = rect1.y + rect1.height / 2 + window.scrollY;
    //     const x2 = rect2.x + rect2.width / 2 + window.scrollX;
    //     const y2 = rect2.y + rect2.height / 2 + window.scrollY;

    //     // Calcular o ponto intermediário
    //     const midX = (x1 + x2) / 2;
    //     const midY = (y1 + y2) / 2;

    //     // Calcular os pontos de curva (a meio caminho entre os dois elementos)
    //     const curvePointX = midX;
    //     const curvePointY = (y1 > y2) ? midY - 50 : midY + 50;

    //     // Desenhar a linha curva
    //     ctx.beginPath();
    //     ctx.moveTo(x1, y1); // Início
    //     ctx.lineTo(midX, y1); // Linha reta até o meio
    //     ctx.lineTo(curvePointX, curvePointY); // Curva de 90 graus
    //     ctx.lineTo(midX, y2); // Linha reta até o outro ponto
    //     ctx.lineTo(x2, y2); // Conecta ao segundo ponto
    //     ctx.strokeStyle = '#c4c4c4';
    //     ctx.lineWidth = 2;
    //     ctx.stroke();
    // }

    // // Função principal para configurar as linhas
    // function setupLines() {
    //     const canvas = document.getElementById('canvas');
    //     const ctx = canvas.getContext('2d');

    //     // Encontrar todos os elementos com IDs começando com "elemento-"
    //     const elementos = document.querySelectorAll('[id^="elemento-"]');

    //     // Inicializar valores máximos de largura e altura
    //     let maxWidth = 0;
    //     let maxHeight = 0;

    //     // Calcular os limites máximos necessários para o canvas
    //     elementos.forEach(elemento => {
    //         const rect = elemento.getBoundingClientRect();
    //         maxWidth = Math.max(maxWidth, rect.x + rect.width + window.scrollX);
    //         maxHeight = Math.max(maxHeight, rect.y + rect.height + window.scrollY);
    //     });

    //     // Ajustar o tamanho do canvas para cobrir toda a área
    //     canvas.width = maxWidth;
    //     canvas.height = maxHeight;

    //     // Para cada elemento, procurar o "data-pai" e desenhar a linha com o elemento pai
    //     elementos.forEach(elemento => {
    //         const paiId = elemento.getAttribute('data-pai');
    //         if (paiId) {
    //             const elementoPai = document.getElementById(paiId);
    //             if (elementoPai) {
    //                 drawCurvedLine(elementoPai, elemento, ctx);
    //             }
    //         }
    //     });
    // }

    // document.querySelectorAll('.plus-icon').forEach(function (plusIcon) {
    //     plusIcon.addEventListener('click', function () {
    //         const elementoId = this.closest('.user-container').id; // ID do elemento do usuário
    //         const elementoPaiId = this.closest('.user-container').getAttribute('data-pai'); // ID do pai

    //         // Espera 500ms antes de atualizar a linha
    //         setTimeout(function () {
    //             // Atualiza a linha de rede
    //             updateNetworkLine(elementoId, elementoPaiId);
    //         }, 1); // 500 milissegundos de atraso
    //     });
    // });

    // function updateNetworkLine(elementoId, elementoPaiId) {
    //     console.log(`Atualizando linha entre ${elementoPaiId} e ${elementoId}`);
    //     setupLines(); // Chama a função para redesenhar as linhas
    // }

    // // Redesenhar as linhas ao redimensionar a janela ou carregar a página
    // window.onload = setupLines;
    // window.onresize = setupLines;
</script>

























<!-- Copia link dos usuarios -->
<script type="text/javascript">
    $(document).ready(function() {
        $('.copy-icon').click(function() {
            var link = $(this).data('link');
            $('#modal-link-input').val(link);
            $('#copyModal').modal('show');
        });
    });
</script>

<!-- Abrir arvore de usuarios -->
<script type="text/javascript">
    $(document).ready(function() {
        $('.plus-icon').click(function() {
            var $currentMoreImages = $(this).closest('.user-container').next('.more-images');

            // Fechar todas as redes no mesmo nível, mas não nos níveis superiores ou inferiores
            $(this).closest('.row').find('.more-images').not($currentMoreImages).hide();
            $(this).closest('.row').find('.plus-icon').not(this).removeClass('active').text('+');

            // Alternar a rede atual
            $currentMoreImages.toggle();
            $(this).toggleClass('active');
            if ($(this).hasClass('active')) {
                $(this).text('-');
            } else {
                $(this).text('+');
            }
        });
    });
</script>

<!-- Pesquisar usuario na rede -->
<script type="text/javascript">
    function searchUsers() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const users = document.querySelectorAll('#networkContainer .user-container');

        // Se o campo de pesquisa estiver vazio, oculta todos os usuários
        if (filter === "") {
            users.forEach(user => {
                user.style.display = ""; // Exibe o usuário
                const moreImages = user.nextElementSibling; // Obtém o próximo elemento
                if (moreImages && moreImages.classList.contains('more-images')) {
                    moreImages.style.display = "none"; // Oculta as imagens adicionais
                }
                const plusIcon = user.querySelector('.plus-icon');
                if (plusIcon) {
                    plusIcon.classList.remove('active');
                    plusIcon.textContent = '+'; // Reseta o texto do ícone
                }
            });
            return; // Sai da função se o campo estiver vazio
        }

        // Primeiro, oculta todos os usuários e suas sub-redes
        users.forEach(user => {
            user.style.display = "none"; // Oculta o usuário
            const moreImages = user.nextElementSibling; // Obtém o próximo elemento
            if (moreImages && moreImages.classList.contains('more-images')) {
                moreImages.style.display = "none"; // Oculta as imagens adicionais
            }
        });

        // Verifica se algum usuário corresponde à pesquisa
        users.forEach(user => {
            const userName = user.querySelector('.name').textContent.toLowerCase();
            if (userName.includes(filter)) {
                user.style.display = ""; // Exibe o usuário

                // Abre a árvore de subusuários até o topo
                let current = user;

                // Abre todos os more-images necessários
                while (current) {
                    const moreImages = current.nextElementSibling; // Obtém o próximo elemento
                    if (moreImages && moreImages.classList.contains('more-images')) {
                        moreImages.style.display = "block"; // Exibe as imagens adicionais
                        const plusIcon = current.querySelector('.plus-icon');
                        if (plusIcon) {
                            plusIcon.classList.add('active');
                            plusIcon.textContent = '-'; // Muda o texto para indicar que está aberto
                        }
                    }

                    // Move para o elemento pai
                    const parent = current.closest('.more-images') ? current.closest('.more-images').previousElementSibling : null;
                    if (parent && parent.classList.contains('user-container')) {
                        parent.style.display = ""; // Exibe o usuário pai
                    }
                    current = parent; // Avança para o pai
                }
            }
        });
    }
</script>

<style>
    /* .blur {
        filter: blur(4px);
        transition: filter 0.3s;
        pointer-events: none;
    } */
</style>

<!-- Mostrar Modal com Informacoes do convidador -->
<script type="text/javascript">
    $(document).ready(function() {
        // Declare planId fora do escopo da função para que ele seja acessível em outras funções
        let planId;
        let inviterId;

        $('.info-icon').click(function() {
            // Obtém o ID do convidador a partir do botão
            inviterId = $(this).data('inviter-id');
            let type = $(this).data('type');

            // Envia uma requisição AJAX para buscar os dados do convidador
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/inviter.php', // Substitua pelo caminho correto
                type: 'POST',
                data: {
                    inviter_id: inviterId
                },
                success: function(response) {
                    // Aqui você pode processar a resposta e preencher o modal ou outra interface
                    if (response.status == 'success') {
                        console.log(response.data);

                        // Exemplo: Preenche o modal com os dados do convidador
                        $('#inviter-fullname').text(response.data.fullname);
                        $('#inviter-username').text(response.data.username);
                        $('#inviter-email').text(response.data.email);
                        $('#inviter-phone').text(response.data.phone);
                        $('#inviter-plan').text(response.data.plan);
                        $('#inviter-status').text(response.data.status);
                        $('#inviter-cpf').text(response.data.cpf);

                        if (response.data.type == 'indirect') {
                            $('#inviter-email').addClass('blur');
                            $('#inviter-phone').addClass('blur');
                        }

                        if (type == 'referrer' || type == 'inviter') {
                            $('#inviterInfoModal .inviter-cpf').removeClass("d-flex").addClass("d-none");
                            $('#inviterInfoModal .inviter-link').removeClass("d-flex").addClass("d-none");
                        } else {
                            $('#inviterInfoModal .inviter-cpf').addClass("d-flex").removeClass("d-none");
                            $('#inviterInfoModal .inviter-link').addClass("d-flex").removeClass("d-none");
                        }

                        var link = '<?= INCLUDE_PATH_AUTH; ?>registrar/<?= $user['token']; ?>*' + response.data.token;

                        $('#inviter-link').text(link);
                        // $('#inviter-plan').text(response.data.plan); // Adicionar campo antes de usar
                        // $('#inviter-status').text(response.data.status); // Adicionar campo antes de usar

                        if (response.data.status === 'Inativo') {
                            $('#voucher-content').removeClass('d-none');

                            // Conversão do plano para o identificador correto
                            let planName = response.data.plan;
                            if (planName === 'Diamante') {
                                planId = 4;
                                var plan = 'diamond';
                            } else if (planName === 'Ouro') {
                                planId = 1;
                                var plan = 'gold';
                            } else if (planName === 'Prata') {
                                planId = 2;
                                var plan = 'silver';
                            }

                            console.log("TESTE " + planId);

                            $.ajax({
                                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/plan/functions/get-voucher-count.php',
                                method: 'POST',
                                data: { plan: plan },
                                success: function(response) {
                                    if (response.status === "success") {
                                        $('#subuserVouchersCount').text(response.vouchersCount);

                                        if (response.vouchersCount === 0) {
                                            $('#purchaseVoucherButton').removeClass('d-none');
                                            $('#btnActiveUser')
                                                .addClass('disabled')
                                                .attr('disabled', true)
                                                .attr('data-original-title', 'Você não possui vouchers para ativar este usuário.')
                                                .tooltip('enable');
                                        } else {
                                            $('#purchaseVoucherButton').addClass('d-none');
                                            $('#btnActiveUser').tooltip('disable');
                                        }
                                    }
                                },
                            });
                        }

                        // Abre o modal
                        $('#inviterInfoModal').modal('show');
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                    }
                },
                error: function() {
                    toastr.error('Ocorreu um erro ao buscar os dados do convidador.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                }
            });
        });

        // Ativar botão apenas se o usuário concordar com os termos
        $('#creditVoucherAgreement').change(function () {
            if ($(this).is(':checked')) {
                $('#btnActiveUser').removeClass('disabled').attr('disabled', false);
            } else {
                $('#btnActiveUser').addClass('disabled').attr('disabled', true);
            }
        });

        // Confirmar pagamento com envio via AJAX
        $('#btnActiveUser').on("click", function () {
            if (!$('#creditVoucherAgreement').is(':checked')) {
                // Se o checkbox não estiver marcado, a função é interrompida
                toastr.warning('Você precisa concordar com os termos antes de prosseguir.', 'Atenção', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 3000
                });
                return; // Interrompe a execução
            }

            // Define os botões como variáveis
            let btnSubmit = $("#btnActiveUser");
            let btnLoader = $("#btnLoaderActiveUser");

            // Desabilitar botão submit e habilitar loader
            btnSubmit.addClass("d-none");
            btnLoader.removeClass("d-none");

            let ajaxData = { userId: inviterId, action: 'active-user-voucher', method: 'voucher', plan: planId };

            // Requisição AJAX (pré-pronta para adicionar lógica)
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/plan/functions/active-user-voucher.php', // Ajuste conforme sua rota
                method: 'POST',
                data: ajaxData,
                success: function(response) {
                    if(response.status == "success") {
                        // Exibe mensagens de sucesso
                        toastr.success(response.message, 'Sucesso', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        var newAjaxData = {
                            action: 'search-draw-group',
                            plan_id: planId,
                        };

                        // Faz a requisição AJAX
                        $.ajax({
                            type: "POST",
                            url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/search-draw-group.php",
                            data: newAjaxData,
                            dataType: "json", // Espera uma resposta JSON do servidor
                            success: function(response) {
                                console.log(response.message);

                                // Esconde todas as seções e exibe o conteúdo de sucesso
                                $('.step, .step-plan, .payment-section').addClass("d-none");
                                $('.step-success').removeClass("d-none");
                                $('.btn-back').hide();
                                $('#confirmChangePlanPayment').hide();

                                // Atraso de 3 segundos antes de recarregar a página
                                setTimeout(function() {
                                    // Exibe mensagens de alerta
                                    toastr.info('A página será recarregada automaticamente', 'Alerta', {
                                        closeButton: true,
                                        progressBar: true,
                                        timeOut: 3000, // Tempo antes de recarregar
                                        onHidden: function () {
                                            // Recarrega a página após a exibição do toastr
                                            location.reload();
                                        }
                                    });
                                }, 1500); // 1500 milissegundos = 1.5 segundos

                            },
                            error: function() {
                                console.log('Ocorreu um erro ao enviar os dados. Tente novamente.');
                            }
                        });
                    }
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                }
            });
        });
    });
</script>