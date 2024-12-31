<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon py-0">
                <i class="ion-android-notifications icon-gradient bg-malibu-beach"></i>
            </div>
            <div>
                Notificações
                <div class="page-title-subheading">Aqui você pode visualizar notificações enviadas.</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>enviar-notificacao" class="btn-shadow mr-3 btn-icon btn btn-dark">
                <i class="pe-7s-upload btn-icon-wrapper mr-1"></i>
                Enviar Notificação
            </a>
        </div>
    </div>
</div>

<?php
    // Busca todas as notificações
    $query = "
        SELECT 
            n.id, 
            n.title, 
            n.content, 
            n.created_at, 
            COUNT(nr.id) AS recipient_count,
            GROUP_CONCAT(u.firstname, ' ', u.lastname ORDER BY u.firstname ASC SEPARATOR ', ') AS recipient_names
        FROM tb_notifications n
        LEFT JOIN tb_notification_recipients nr ON n.id = nr.notification_id
        LEFT JOIN tb_users u ON nr.user_id = u.id
        GROUP BY n.id
        ORDER BY n.created_at DESC;
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Notificações</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered w-100" id="notifications">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Descrição</th>
                                <th>Destinatários</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($notifications)) : ?>
                                <?php foreach ($notifications as $notification) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($notification['title']); ?></td>
                                        <td><?= htmlspecialchars($notification['content']); ?></td>
                                        <td>
                                            <?php 
                                            if ($notification['recipient_count'] > 5) {
                                                echo "Todos";
                                            } else {
                                                echo htmlspecialchars($notification['recipient_names'] ?: 'Nenhum destinatário');
                                            }
                                            ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($notification['created_at'])); ?></td>
                                        <td>
                                            <a href="editar-notificacao?id=<?= $notification['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                            <a href="deletar-notificacao?id=<?= $notification['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja deletar a notificação?');">Excluir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nenhuma notificação encontrada.</td>
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
        $("#notifications").DataTable({
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