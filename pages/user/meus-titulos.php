<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-albums icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Meus Títulos
                <div class="page-title-subheading">Aqui você pode acompanhar seus títulos semanais.</div>
            </div>
        </div>
    </div>
</div>

<?php
    // Supondo que você tenha a variável $current_user_id com o ID do usuário atual
    $current_user_id = $_SESSION['user_id']; // ou outra forma de obter o ID do usuário

    $query = "
        SELECT 
            dtp.id, 
            dtp.title_id, 
            g.group_code, 
            dp.name AS product, 
            dtp.series, 
            dtp.title, 
            dtp.operation_code, 
            dtp.dv, 
            dtp.lucky_number, 
            dtp.draw_date
        FROM tb_group_users gu
        JOIN tb_groups g ON gu.group_id = g.id
        JOIN tb_draw_titles dt ON g.title_id = dt.id
        LEFT JOIN tb_draw_title_products dtp ON dt.id = dtp.draw_title_id
        JOIN tb_draw_products dp ON dtp.product_id = dp.id
        WHERE gu.user_id = ?
        ORDER BY dtp.draw_date DESC, dp.id ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([$current_user_id]);
    $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    tr[data-draw="telesena"] {
        background-color: rgba(63, 106, 216, .3) !important;
    }
    tr[data-draw="viva-sorte"] {
        background-color: rgba(58, 196, 125, .3) !important;
    }
    tr[data-draw="hiper-cap-brasil"] {
        background-color: rgba(247, 185, 36, .3) !important;
    }

    .dataTables_scrollBody {
        margin-bottom: 1.25rem !important;
    }
</style>

<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Meus Títulos</h5>
        <div class="table-responsive">
            <table id="my-titles-table" class="table table-hover table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Código do Título</th>
                        <th>Produto</th>
                        <th>Série</th>
                        <th>Título</th>
                        <th>Código de Operação</th>
                        <th>DV</th>
                        <th>Número da Sorte</th>
                        <th>Sorteio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($titles)) : ?>
                        <?php foreach ($titles as $title) : ?>
                            <tr data-draw="<?= str_replace(' ', '-', strtolower($title['product'])); ?>">
                                <td><?= $title['title_id'] ?? "-- Indefinido --"; ?></td>
                                <td><?= htmlspecialchars($title['product']); ?></td>
                                <td><?= htmlspecialchars($title['series']); ?></td>
                                <td><?= htmlspecialchars($title['title']); ?></td>
                                <td><?= $title['operation_code'] ?? "-- Indefinido --"; ?></td>
                                <td><?= htmlspecialchars($title['dv']); ?></td>
                                <td><?= htmlspecialchars($title['lucky_number']); ?></td>
                                <td><?= htmlspecialchars(date('d/M', strtotime($title['draw_date']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9">Você não possui nenhum título.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Datatables

    $(document).ready(() => {
        $("#my-titles-table").DataTable({
            ordering: false,
            pageLength: 25, // Exibir no máximo 6 registros por página
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