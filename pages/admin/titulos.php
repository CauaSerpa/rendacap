<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-albums icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Títulos
                <div class="page-title-subheading">Aqui você pode gerar os grupos, visualizar e cadastrar os títulos.</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-titulo" class="btn-shadow mr-3 btn-icon btn btn-dark">
                <i class="pe-7s-upload btn-icon-wrapper mr-1"></i>
                Cadastrar Título
            </a>
        </div>
    </div>
</div>

<?php
$stmt = $conn->prepare("
    SELECT 
        dt.*, 
        p.name AS plan_name, 
        g.id AS group_id, 
        g.group_code, 
        WEEK(MIN(dtp.draw_date), 1) AS week_number 
    FROM tb_draw_titles dt 
    JOIN tb_plans p ON p.id = dt.plan_id 
    LEFT JOIN tb_groups g ON g.title_id = dt.id 
    JOIN tb_draw_title_products dtp ON dt.id = dtp.draw_title_id
    GROUP BY dt.id, g.id, p.id -- Agrupa pelos campos únicos relevantes
    ORDER BY MIN(dtp.draw_date) DESC
");
$stmt->execute();
$titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar os títulos por semana
$groupedTitles = [];
foreach ($titles as $title) {
    $weekNumber = $title['week_number'];
    $groupedTitles[$weekNumber][] = $title;
}
?>

<div class="row">
    <div class="col-lg-12">
        <?php foreach ($groupedTitles as $week => $titles) : ?>
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="card-title">Semana <?= $week; ?></h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Identificador</th>
                                    <th>Plano</th>
                                    <th>Responsável</th>
                                    <th>Grupo Vinculado</th>
                                    <th>Criado Em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($titles as $title) : ?>
                                    <tr>
                                        <td><?= $title['identifier']; ?></td>
                                        <td><?= $title['plan_name']; ?></td>
                                        <td><?= $title['responsible']; ?></td>
                                        <td>
                                            <?= (!empty($title['group_code'])) ? 
                                                '<a href="' . INCLUDE_PATH_DASHBOARD . 'grupo?group_id=' . $title['group_id'] . '">' . $title['group_code'] . '</a>' : 
                                                "-- Indefinido --"; 
                                            ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($title['created_at'])); ?></td>
                                        <td>
                                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>editar-titulo?title_id=<?= $title['id']; ?>" class="btn btn-secondary btn-sm">Visualizar</a>
                                            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $title['id']; ?>)">Deletar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    let titleIdToDelete;

    // Função para abrir o modal de exclusão
    function openDeleteModal(titleId) {
        titleIdToDelete = titleId; // Armazena o ID do título que será excluído
        $('#deleteModal').modal('show'); // Exibe o modal
    }

    $(document).ready(function() {
        // Evento para confirmar a exclusão
        $('#confirmDelete').on('click', function() {
            if (titleIdToDelete) {
                // Redireciona para a página de exclusão do título
                window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>deletar-titulo?title_id=" + titleIdToDelete;
            }
        });
    });
</script>

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