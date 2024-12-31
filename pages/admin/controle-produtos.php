<?php
// Supondo que você já tenha uma conexão com o banco de dados estabelecida
$stmt = $conn->prepare("
    SELECT 
        u.username AS user_login, 
        p.name AS package_name, 
        CASE 
            WHEN p.name = 'Diamante' THEN 48
            WHEN p.name = 'Ouro' THEN 24
            WHEN p.name = 'Prata' THEN 12
            WHEN p.name = 'Bronze' THEN 0
            ELSE 0 
        END AS total_titles, 
        -- Contar a quantidade de títulos de cada produto o usuário está participando
        SUM(CASE WHEN WEEK(dtp.draw_date) = WEEK(CURRENT_DATE()) THEN 1 ELSE 0 END) AS titles_this_week,
        
        -- Calcula o número de títulos restantes para o usuário
        CASE 
            WHEN p.name = 'Diamante' THEN 12
            WHEN p.name = 'Ouro' THEN 6
            WHEN p.name = 'Prata' THEN 3
            WHEN p.name = 'Bronze' THEN 0
            ELSE 0 
        END - SUM(CASE WHEN WEEK(dtp.draw_date) = WEEK(CURRENT_DATE()) THEN 1 ELSE 0 END) AS titles_missing,

        -- Contabiliza o número de grupos que o usuário faz parte neste mês
        COUNT(DISTINCT g.id) AS groups_this_month

    FROM 
        tb_users u
    LEFT JOIN 
        tb_title_users tu ON tu.user_id = u.id
    LEFT JOIN 
        tb_user_roles ur ON ur.user_id = u.id
    LEFT JOIN 
        tb_draw_titles dt ON dt.id = tu.title_id
    LEFT JOIN 
        tb_draw_title_products dtp ON dtp.draw_title_id = dt.id
    LEFT JOIN 
        tb_group_users gu ON gu.user_id = u.id -- Filtra os grupos do mês atual
    LEFT JOIN 
        tb_groups g ON g.id = gu.group_id AND MONTH(g.created_at) = MONTH(CURRENT_DATE()) -- Filtra os grupos do mês atual
    JOIN 
        tb_plans p ON p.id = (SELECT plan_id FROM tb_subscriptions WHERE user_id = u.id AND status = 'ACTIVE' LIMIT 1)
    WHERE 
        u.status = 1 AND 
        ur.role_id = 3 
    GROUP BY 
        u.id
    ORDER BY 
        u.username ASC
");
$stmt->execute();
$usersProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-users icon-gradient bg-tempting-azure"></i>
            </div>
            <div>
                Controle de Produtos
                <div class="page-title-subheading">Aqui estão os detalhes de cada usuário e os produtos que possuem.</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Controle de Produtos</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered w-100" id="product-control">
                        <thead>
                            <tr>
                                <th>Login do Usuário</th>
                                <th>Pacote</th>
                                <th>Quantidade de Títulos</th>
                                <th>Títulos Participando Esta Semana</th>
                                <th>Títulos Faltando Para Participar</th>
                                <th>Grupos no Mês</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usersProducts)) : ?>
                                <?php foreach ($usersProducts as $userProduct) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($userProduct['user_login']); ?></td>
                                        <td><?= htmlspecialchars($userProduct['package_name'] ?? 'Indefinido'); ?></td>
                                        <td><?= htmlspecialchars($userProduct['total_titles']); ?></td>
                                        <td><?= htmlspecialchars($userProduct['titles_this_week']); ?></td>
                                        <td><?= htmlspecialchars($userProduct['titles_missing']); ?></td>
                                        <td><?= htmlspecialchars($userProduct['groups_this_month']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum usuário encontrado.</td>
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
    // Datatables
    $(document).ready(() => {
        $("#product-control").DataTable({
            responsive: true,
            scrollCollapse: true,
            ordering: false,
            language: {
                "sProcessing": "Processando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
                "sInfoPostFix": "",
                "sSearch": "Pesquisar:",
                "sUrl": "",
                "sInfoThousands": ".",
                "sLoadingRecords": "Carregando...",
                "oPaginate": {
                    "sFirst": "Primeiro",
                    "sPrevious": "Anterior",
                    "sNext": "Próximo",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": ativar para classificar a coluna em ordem crescente",
                    "sSortDescending": ": ativar para classificar a coluna em ordem decrescente"
                }
            }
        });
    });

    $(document).ready(function(){
        $("input[type='search']").wrap("<form>");
        $("input[type='search']").closest("form").attr("autocomplete","off");
        $("input[type='search']").attr("autocomplete", "off");
    });
</script>