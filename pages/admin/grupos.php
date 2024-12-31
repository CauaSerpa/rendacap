<?php
// Consulta para listar grupos de títulos e usuários participantes organizados por semana
$stmt = $conn->prepare("
    SELECT dt.*, dt.identifier AS title_name, g.id AS group_id, g.title_id, g.identifier, g.group_code, 
           GROUP_CONCAT(DISTINCT u.username SEPARATOR ', ') AS users, -- Adicionado DISTINCT aqui
           WEEK(dtp.draw_date, 1) AS draw_week -- Obtém o número da semana do ano
    FROM tb_draw_titles dt 
    JOIN tb_draw_title_products dtp ON dt.id = dtp.draw_title_id
    LEFT JOIN tb_groups g ON g.title_id = dt.id
    LEFT JOIN tb_group_users gu ON gu.group_id = g.id
    LEFT JOIN tb_users u ON u.id = gu.user_id
    GROUP BY dt.id
    ORDER BY dtp.draw_date DESC
");
$stmt->execute();
$titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar os títulos por semana
$titles_by_week = [];
foreach ($titles as $title) {
    $week = $title['draw_week'] ?? 'Sem Semana Definida';
    $titles_by_week[$week][] = $title;
}
?>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-gift icon-gradient bg-tempting-azure"></i>
            </div>
            <div>
                Grupos
                <div class="page-title-subheading">Aqui estão os grupos dos títulos organizados por semanas.</div>
            </div>
        </div>
    </div>
</div>

<?php foreach ($titles_by_week as $week => $week_titles) : ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="card-title">Semana <?= htmlspecialchars($week); ?></h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Identificador</th>
                                    <th>Título</th>
                                    <th>Código do Grupo</th>
                                    <th>Usuários Participantes</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($week_titles as $title) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($title['identifier'] ?? '-- Indefinido --'); ?></td>
                                        <td><a href="<?= INCLUDE_PATH_DASHBOARD; ?>editar-titulo?title_id=<?= $title['title_id']; ?>"><?= htmlspecialchars($title['title_name']); ?></a></td>
                                        <td><?= htmlspecialchars($title['group_code'] ?? '-- Indefinido --'); ?></td>
                                        <td><?= htmlspecialchars($title['users'] ?? 'Nenhum usuário'); ?></td>
                                        <td>
                                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>grupo?group_id=<?= $title['group_id']; ?>" class="btn btn-secondary btn-sm">Visualizar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    // Datatables
    $(document).ready(() => {
        $("table").DataTable({
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