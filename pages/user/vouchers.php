

<?php

function calculateCashbackSummary($conn, $allUsers) {
    $totalCashbackLevel1 = 0;
    $totalCashbackLevel2To10 = 0;
    $allCashback = 0;
    $totalDirectUsers = 0;

    // Separar os cashbacks por nível e contar usuários diretos
    foreach ($allUsers as $user) {
        if ($user['type'] === 'Direto') {
            $totalCashbackLevel1 += $user['cashback'];
            $totalDirectUsers++; // Incrementa para cada usuário direto
        } else {
            $totalCashbackLevel2To10 += $user['cashback'];
        }
    }

    // Total geral
    $totalCashback = $totalCashbackLevel1 + $totalCashbackLevel2To10;

    // Consulta para somar os saques do usuário na tabela tb_withdrawals
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT SUM(amount) as totalWithdrawn
        FROM tb_withdrawals
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $withdrawalData = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalWithdrawn = $withdrawalData['totalWithdrawn'] ?? 0;

    // Valor disponível para saque
    $availableCashback = $totalCashback - $totalWithdrawn;

    // Criação do array final
    $result = [
        'totalCashbackLevel1' => $totalCashbackLevel1,
        'totalDirectUsers' => $totalDirectUsers,
        'totalCashbackLevel2To10' => $totalCashbackLevel2To10,
        'totalCashback' => $totalCashback,
        'allCashback' => $allCashback
    ];

    return $result;
}

?>


<?php

function calculateAccumulatedCashback($conn, $allUsers) {
    $totalCashback = 0;

    // Soma todos os cashbacks dos usuários
    foreach ($allUsers as $user) {
        $totalCashback += $user['cashback'];
    }

    // Consulta para somar os saques do usuário na tabela tb_withdrawals
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT SUM(amount) as totalWithdrawn
        FROM tb_withdrawals
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $withdrawalData = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalWithdrawn = $withdrawalData['totalWithdrawn'] ?? 0;

    // Valor disponível para saque
    $availableCashback = $totalCashback - $totalWithdrawn;

    // Cria o array contendo o total acumulado
    $result = [
        'totalCashbackAvailable' => $availableCashback
    ];

    return $result;
}

?>


<?php
function listAssociatedUsersWithCashback($conn, $networkIds, $tipo = 'Direto', $drawDate = null, $level = 0, &$uniqueIds = []) {
    $allUsers = [];
    $maxLevel = 10;

    $query = "
        SELECT u.id, u.username, u.email, s.status AS subscription_status, s.due_date, p.price AS plan_price, p.id AS plan_id, p.name AS plan_name, ? AS type
        FROM tb_users u
        INNER JOIN tb_user_networks un ON u.id = un.user_id
        LEFT JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
        LEFT JOIN tb_plans p ON s.plan_id = p.id
        WHERE u.status = 1 AND un.network_id IN (" . implode(',', array_fill(0, count($networkIds), '?')) . ")
    ";

    if ($drawDate) {
        $dateParts = explode('-', $drawDate);
        $startDate = DateTime::createFromFormat('d/m/Y', trim($dateParts[0]))->format('Y-m-d');
        $endDate = DateTime::createFromFormat('d/m/Y', trim($dateParts[1]))->format('Y-m-d');
        $query .= " AND s.payment_status = 'paid' AND s.creation_date BETWEEN ? AND ?";
    }

    $query .= " ORDER BY u.id DESC";

    $stmt = $conn->prepare($query);
    $params = array_merge([$tipo], $networkIds);
    if ($drawDate) {
        $stmt->execute(array_merge($params, [$startDate, $endDate]));
    } else {
        $stmt->execute($params);
    }

    $currentLevelUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nextNetworkIds = [];

    foreach ($currentLevelUsers as $user) {
        $stmt = $conn->prepare("
            SELECT id
            FROM tb_networks 
            WHERE referrer_id = ? OR (inviter_id = ? AND (referrer_id != ? OR referrer_id IS NULL))
        ");
        $stmt->execute([$user['id'], $user['id'], $_SESSION['user_id']]);
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Adicione os resultados à lista de próximos IDs de rede
        $nextNetworkIds = array_merge($nextNetworkIds, $result);
    }

    foreach ($currentLevelUsers as &$user) {
        if (in_array($user['id'], $uniqueIds)) {
            continue; // Ignora usuários já adicionados
        }
        $uniqueIds[] = $user['id'];

        $cashback = 0;
        $today = new DateTime();
        $dueDate = $user['due_date'] ? new DateTime($user['due_date']) : null;

        if ($user['type'] === 'Direto' && in_array($user['plan_id'], [1, 2, 4]) && $dueDate && $dueDate > $today) {
            $cashback = $user['plan_price'] * 0.20;
        } else if ($user['type'] === 'Indireto' && in_array($user['plan_id'], [1, 2, 4]) && $dueDate && $dueDate > $today) {
            $cashback = $user['plan_price'] * 0.05;
        } else if (in_array($user['plan_id'], [1, 2, 4])) {
            if ($level === 0) {
                $cashback = $user['plan_price'] * 0.10;
            } elseif ($level === 1) {
                $cashback = $user['plan_price'] * 0.05;
            } elseif ($level === 2) {
                $cashback = $user['plan_price'] * 0.02;
            } elseif ($level === 3) {
                $cashback = $user['plan_price'] * 0.015;
            } elseif ($level > 3 && $level <= 7) {
                $cashback = $user['plan_price'] * 0.01;
            } elseif ($level > 7 && $level <= $maxLevel) {
                $cashback = $user['plan_price'] * 0.005;
            }
        }

        $user['cashback'] = $cashback;
        $allUsers[] = $user;
    }

    if (!empty($nextNetworkIds)) {
        $indirectUsers = listAssociatedUsersWithCashback($conn, $nextNetworkIds, 'Indireto', $drawDate, $level + 1, $uniqueIds);
        $allUsers = array_merge($allUsers, $indirectUsers);
    }

    return $allUsers;
}

// ID do usuário atual (da sessão)
$userId = $_SESSION['user_id'];

// Obtém a data do GET
$drawDate = isset($_GET['draw_date']) ? $_GET['draw_date'] : null;

// Consulta para obter todos os network_id associados ao usuário
$stmt = $conn->prepare("
    SELECT id
    FROM tb_networks 
    WHERE referrer_id = ? OR (inviter_id = ? AND referrer_id IS NULL)
");
$stmt->execute([$userId, $userId]);
$networkIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($networkIds)) {
    $users = listAssociatedUsersWithCashback($conn, $networkIds, 'Direto', $drawDate);
    $cashbackSummary = calculateCashbackSummary($conn, $users);
    $accumulatedCashback = calculateAccumulatedCashback($conn, $users);
} else {
    $users = []; // Sem usuários associados
    $cashbackSummary = calculateCashbackSummary($conn, $users);
    $accumulatedCashback = calculateAccumulatedCashback($conn, $users);
}

// Combine os resultados
$networkCashbackData = array_merge($cashbackSummary, $accumulatedCashback);
?>

<div class="app-page-title py-2">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-star icon-gradient bg-malibu-beach"></i>
            </div>
            <div>
                Vouchers
                <div class="page-title-subheading">Aqui você pode comprar e utilizar Vouchers. Compre Vouchers e aumente ainda mais sua rede!</div>
            </div>
        </div>
        <div class="page-title-actions">

            <div class="card widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success card">
                <div class="icon-wrapper rounded-circle">
                    <div class="icon-wrapper-bg bg-success"></div>
                    <i class="pe-7s-wallet text-success"></i>
                </div>
                <div class="widget-chart-content">
                    <div class="widget-subheading">Saldo disponível para compra de vouchers</div>
                    <div class="widget-numbers">
                        <small class="opacity-5 pr-1">R$</small>
                        <span id="cashbackValue" class="count-up-real" data-value="<?= $networkCashbackData['totalCashbackAvailable']; ?>">****</span> <!-- Valor oculto por padrão -->
                        <i id="toggleCashback" class="pe-7s-look text-success cursor-pointer" onclick="toggleVisibility()"></i>
                    </div>
                    <div class="widget-subheading">Saldo disponível</div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4 d-grid mb-3">
        <div class="border card card-body">
            <h5 class="card-title">Voucher Diamante</h5>
            <div class="d-flex flex-column align-items-center">
                <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/avatars/diamante.png" alt="Diamond Icon" style="max-width: 150px; margin: 0 2rem 1rem 2rem;">
                <div class="d-flex align-items-baseline">
                    <small class="opacity-5 pr-1">R$</small>
                    <h3 class="count-up font-weight-semibold" data-value="200.00">200,00</h3>
                </div>
            </div>

            <div class="divider mt-3"></div>
            <p class="text-center">Compre um Voucher Diamante</p>
            <button type="button" class="select-voucher btn-pill btn-hover-shine btn btn-primary btn-lg" data-plan="diamond" disabled>Comprar Voucher</button>
            <button class="loader-button btn-pill btn-hover-shine btn btn-primary btn-lg d-none">
                <div class="loader">
                    <div class="ball-pulse">
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                    </div>
                </div>
            </button>
        </div>
    </div>
    <div class="col-md-4 d-grid mb-3">
        <div class="border card card-body">
            <h5 class="card-title">Voucher Ouro</h5>
            <div class="d-flex flex-column align-items-center">
                <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/avatars/ouro.png" alt="Gold Icon" style="width: 150px; margin: 0 2rem 1rem 2rem;">
                <div class="d-flex align-items-baseline">
                    <small class="opacity-5 pr-1">R$</small>
                    <h3 class="count-up font-weight-semibold" data-value="100.00">100,00</h3>
                </div>
            </div>

            <div class="divider mt-3"></div>
            <p class="text-center">Compre um Voucher Ouro</p>
            <button type="button" class="select-voucher btn-pill btn-hover-shine btn btn-primary btn-lg" data-plan="gold" disabled>Comprar Voucher</button>
            <button class="loader-button btn-pill btn-hover-shine btn btn-primary btn-lg d-none">
                <div class="loader">
                    <div class="ball-pulse">
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                    </div>
                </div>
            </button>
        </div>
    </div>
    <div class="col-md-4 d-grid mb-3">
        <div class="border card card-body">
            <h5 class="card-title">Voucher Prata</h5>
            <div class="d-flex flex-column align-items-center">
                <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/avatars/prata.png" alt="Silver Icon" style="width: 150px; margin: 0 2rem 1rem 2rem;">
                <div class="d-flex align-items-baseline">
                    <small class="opacity-5 pr-1">R$</small>
                    <h3 class="count-up font-weight-semibold" data-value="50.00">50,00</h3>
                </div>
            </div>

            <div class="divider mt-3"></div>
            <p class="text-center">Compre um Voucher Prata</p>
            <button type="button" class="select-voucher btn-pill btn-hover-shine btn btn-primary btn-lg" data-plan="silver" disabled>Comprar Voucher</button>
            <button class="loader-button btn-pill btn-hover-shine btn btn-primary btn-lg d-none">
                <div class="loader">
                    <div class="ball-pulse">
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                        <div style="background-color: rgb(255, 255, 255); width: 8px; height: 8px;"></div>
                    </div>
                </div>
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">

                <!-- Botões de Filtro -->
                <div class="btn-group mb-4" role="group" aria-label="Filtro de Faturas">
                    <button type="button" class="btn btn-secondary btn-filter active" data-filter="all">Todos (<span class="count-all">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="ativo">Pago (<span class="count-ativo">0</span>)</button>
                    <button type="button" class="btn btn-secondary btn-filter" data-filter="inativo">Pendente (<span class="count-inativo">0</span>)</button>
                </div>

                <!-- Tabela de Faturas -->
                <div class="table-responsive">
                    <?php
                        // Consulta para obter os vouchers
                        $sql = "
                            SELECT 
                                vo.id AS voucher_id,
                                v.name AS plano,
                                CASE 
                                    WHEN vo.used = 0 AND vo.status = 'completed' THEN 'Ativo'
                                    ELSE 'Usado'
                                END AS status,
                                FORMAT(vo.price, 2, 'pt_BR') AS valor,
                                DATE_FORMAT(vo.created_at, '%d/%m/%Y') AS data_compra
                            FROM 
                                tb_voucher_orders vo
                            JOIN 
                                tb_vouchers v ON vo.voucher_id = v.id
                            WHERE 
                                vo.user_id = :user_id
                            ORDER BY 
                                vo.created_at DESC
                        ";

                        // Preparar e executar a consulta
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']); // Substitua pela fonte do user_id
                        $stmt->execute();
                        $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <table id="invoices-table" class="table table-hover table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Plano</th>
                                <th>Status</th>
                                <th>Valor</th>
                                <th>Data da Compra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vouchers)): ?>
                                <?php foreach ($vouchers as $voucher): ?>
                                    <tr data-status="<?= strtolower($voucher['status']); ?>">
                                        <td><?= htmlspecialchars($voucher['voucher_id']); ?></td>
                                        <td><?= htmlspecialchars($voucher['plano']); ?></td>
                                        <td><?= htmlspecialchars($voucher['status']); ?></td>
                                        <td>R$ <?= htmlspecialchars($voucher['valor']); ?></td>
                                        <td><?= htmlspecialchars($voucher['data_compra']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum voucher encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Função para ocultar e exibir o valor
    function toggleVisibility() {
        const valueElement = document.getElementById('cashbackValue');
        const currentText = valueElement.textContent.trim();
        
        if (currentText === '****') {
            valueElement.textContent = '<?= number_format($networkCashbackData['totalCashbackAvailable'], 2, ',', '.'); ?>'; // Exibe o valor
        } else {
            valueElement.textContent = '****'; // Oculta o valor
        }
    }
</script>

<!-- Count Up -->
<script>
    $(document).ready(function() {
        $('.count-up').each(function() {
            var $this = $(this);
            var value = $this.data('value');
            
            $this.text('0,00'); // Inicia com zero

            $({ count: 0 }).animate({ count: value }, {
                duration: 2000, // Duração da animação em milissegundos
                step: function() {
                    $this.text(Number(this.count).toFixed(2).replace('.', ','));
                },
                complete: function() {
                    $this.text(Number(this.count).toFixed(2).replace('.', ','));
                }
            });
        });
    });
</script>

<script>
    // Datatables

    $(document).ready(() => {
        // Inicializa DataTable
        var table = $("#invoices-table").DataTable({
            ordering: false,
            language: {
                "sProcessing": "Processando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                "sSearch": "Pesquisar:",
                "oPaginate": {
                    "sFirst": "Primeiro",
                    "sPrevious": "Anterior",
                    "sNext": "Próximo",
                    "sLast": "Último"
                }
            }
        });

        // Função para atualizar as contagens
        function updateCounts() {
            var total = table.rows().count();
            var countAtivo = table
                .rows()
                .data()
                .filter(row => row[3] === "Ativo").length;
            var countInativo = table
                .rows()
                .data()
                .filter(row => row[3] === "Inativo").length;

            // Atualiza os textos dos botões
            $('.count-all').text(total);
            $('.count-ativo').text(countAtivo);
            $('.count-inativo').text(countInativo);
        }

        // Atualizar contagens ao carregar a página
        updateCounts();

        // Evento de clique nos botões de filtro
        $('.btn-filter').on('click', function() {
            var filter = $(this).attr('data-filter');

            // Ativa o botão clicado
            $('.btn-filter').removeClass('active');
            $(this).addClass('active');

            // Aplica o filtro no DataTable
            if (filter === 'all') {
                table.search('').draw();
            } else {
                table.search(filter).draw();
            }

            // Atualiza a contagem após aplicar o filtro
            updateCounts();
        });
    });

    $(document).ready(function(){
        $("input[type='search']").wrap("<form>");
        $("input[type='search']").closest("form").attr("autocomplete","off");
        $("input[type='search']").attr("autocomplete", "off");
    });
</script>