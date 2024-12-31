<?php
    // // Supondo que você tenha a variável $current_user_id com o ID do usuário atual
    // $current_user_id = $_SESSION['user_id']; // ou outra forma de obter o ID do usuário

    // // Consulta para obter os títulos e dados necessários
    // $stmt = $conn->prepare("
    //     SELECT dt.*, dp.name AS product, tu.group_code
    //     FROM tb_draw_titles dt
    //     JOIN tb_draw_products dp ON dp.id = dt.draw_product_id
    //     JOIN tb_title_users tu ON dt.id = tu.title_id
    //     WHERE tu.user_id = :current_user_id
    //     ORDER BY dt.draw_date ASC
    // ");
    // $stmt->bindParam(':current_user_id', $current_user_id);
    // $stmt->execute();
    // $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // // Encontrar o título com a data de sorteio mais recente
    // $nextTitle = null;
    // if (!empty($titles)) {
    //     $nextTitle = $titles[0]; // O primeiro é o próximo a vencer
    // }

    // // Consulta para pegar todos os participantes do sorteio
    // $stmt = $conn->prepare("
    //     SELECT u.id, u.firstname, u.lastname, u.cpf, a.city, a.state
    //     FROM tb_draw_titles dt
    //     JOIN tb_title_users tu ON dt.id = tu.title_id
    //     JOIN tb_users u ON tu.user_id = u.id
    //     JOIN tb_address a ON u.id = a.user_id
    //     WHERE tu.title_id = :title_id
    //     LIMIT 4
    // ");
    // $stmt->bindParam(':title_id', $titles[0]['id']);
    // $stmt->execute();
    // $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // // Separar o usuário atual dos outros participantes
    // $current_user = null;
    // $other_participants = [];

    // foreach ($participants as $participant) {
    //     if ($participant['id'] == $current_user_id) {
    //         $current_user = $participant;
    //     } else {
    //         $other_participants[] = $participant;
    //     }
    // }

    // // Organizar os participantes, colocando o usuário atual primeiro
    // $sorted_participants = array_merge([$current_user], $other_participants);

    // // Ajusta os nomes e cria as variáveis para exibição
    // $sorted_participants = array_map(function($participant) {
    //     $participant['fullname'] = $participant['firstname'] . " " . $participant['lastname'];
    //     $participant['surname'] = explode(' ', $participant['lastname'])[0];
    //     $participant['shortname'] = $participant['firstname'] . " " . $participant['surname'];
    //     return $participant;
    // }, $sorted_participants);
?>

<?php
    // Supondo que você tenha a variável $current_user_id com o ID do usuário atual
    $current_user_id = $_SESSION['user_id']; // ou outra forma de obter o ID do usuário

    $query = "
        SELECT 
            dtp.id, 
            g.id AS group_id, 
            g.group_code, 
            dp.name AS product, 
            dtp.series, 
            dtp.title, 
            dtp.dv, 
            dtp.lucky_number, 
            dtp.draw_date, 
            dtp.id AS title_id
        FROM tb_group_users gu
        JOIN tb_groups g ON gu.group_id = g.id
        JOIN tb_draw_titles dt ON g.title_id = dt.id
        LEFT JOIN tb_draw_title_products dtp ON dt.id = dtp.draw_title_id
        JOIN tb_draw_products dp ON dtp.product_id = dp.id
        WHERE gu.user_id = ?
        AND WEEKDAY(dtp.draw_date) >= 0  -- Segunda-feira (0)
        AND WEEKDAY(dtp.draw_date) <= 6  -- Domingo (6)
        AND dtp.draw_date BETWEEN DATE_ADD(CURDATE(), INTERVAL -WEEKDAY(CURDATE()) DAY)
                            AND DATE_ADD(CURDATE(), INTERVAL 6 - WEEKDAY(CURDATE()) DAY)
        ORDER BY dtp.draw_date DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([$current_user_id]);
    $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Encontrar o título com a data de sorteio mais recente
    $nextTitle = null;
    if (!empty($titles)) {
        $nextTitle = $titles[0]; // Como ordenamos pela data de sorteio, o primeiro é o próximo a vencer
    }

    if ($nextTitle) {
        // Consulta para listar os usuários do grupo
        $query = "
            SELECT u.id, s.user_id, s.plan_id, p.name AS plan_name, u.firstname, u.lastname, u.email, u.username, u.cpf, u.profile_image, a.city, a.state
            FROM tb_group_users gu
            JOIN tb_users u ON gu.user_id = u.id
            JOIN tb_subscriptions s ON u.id = s.user_id AND s.status = 'ACTIVE'
            JOIN tb_plans p ON s.plan_id = p.id
            JOIN tb_address a ON u.id = a.user_id
            WHERE gu.group_id = ? 
            ORDER BY s.id ASC
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$nextTitle['group_id']]);
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-users icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Meu Grupo (5 Participantes)
                <div class="page-title-subheading">Aqui você pode acompanhar seu grupo de 5 participantes da semana.</div>
            </div>
        </div>
    </div>
</div>

<style>
    .chart-container {
        position: relative;
        width: 300px;
        height: 300px;
    }
    .chart {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: conic-gradient(
            #ff6384 0% 16.66%, /* Red */
            #36a2eb 16.66% 33.33%, /* Blue */
            #cc65fe 33.33% 50%, /* Purple */
            #ffce56 50% 66.66%, /* Yellow */
            #4bc0c0 66.66% 83.33%, /* Teal */
            #f7464a 83.33% 100% /* Light Red */
        );
    }
    .images {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    .images div {
        position: absolute;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 3px solid #fff;
        background-size: cover;
        background-position: center;
    }
    .images .img1 {
        top: 20%;
        left: 33%;
        transform: translate(-50%, -50%);
        background-image: url(https://via.placeholder.com/50?text=1);
    }
    .images .img2 {
        top: 20%;
        right: 33%;
        transform: translate(50%, -50%);
        background-image: url(https://via.placeholder.com/50?text=2);
    }
    .images .img3 {
        top: 50%;
        right: 15%;
        transform: translate(50%, -50%);
        background-image: url(https://via.placeholder.com/50?text=3);
    }
    .images .img4 {
        bottom: 20%;
        right: 33%;
        transform: translate(50%, 50%);
        background-image: url(https://via.placeholder.com/50?text=4);
    }
    .images .img5 {
        bottom: 20%;
        left: 33%;
        transform: translate(-50%, 50%);
        background-image: url(https://via.placeholder.com/50?text=5);
    }
    .images .img6 {
        top: 50%;
        left: 15%;
        transform: translate(-50%, -50%);
        background-image: url(https://via.placeholder.com/50?text=6);
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Meu Grupo</h5>
                <div class="row">
                    <div class="col-lg-4 d-flex align-items-center justify-content-center">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>images/chart/chart.png" alt="Gráfico Pizza" style="width: 300px; height: 300px; pointer-events: none;">
                    </div>
                    <div class="col-lg-8">
                        
                        <div class="mb-3">
                            <h6 class="opacity-5">Grupo:</h6>
                            <h5 class="font-weight-bold">
                                <?= !empty($nextTitle['group_code']) ? $nextTitle['group_code'] : '-- Indefinido --'; ?>
                            </h5>
                        </div>
    
                        <div class="table-responsive">
                            <table id="group" class="mb-0 table table-borderless w-100">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Nome</th>
                                        <th>CPF</th>
                                        <th>Cidade</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($participants)): ?>
                                        <?php foreach ($participants as $index => $participant): ?>
                                            <?php
                                                // Define variáveis ​​com nomes diferentes
                                                $participant['fullname'] = $participant['firstname'] . " " . $participant['lastname'];
                                                $participant['surname'] = explode(' ', $participant['lastname'])[0];
                                                $participant['shortname'] = $participant['firstname'] . " " . $participant['surname'];
                                            ?>
                                            <tr>
                                                <td><?= $index + 1; ?></td>
                                                <td><?= $participant['shortname']; ?> <?= $participant['id'] == $current_user_id ? '(Você)' : ''; ?></td>
                                                <td><?= formatOcultCpf($participant['cpf']); ?></td>
                                                <td><?= $participant['city']; ?></td>
                                                <td><?= $participant['state']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td>5</td>
                                            <td>Renda CAP Brasil</td>
                                            <td><?= formatOcultCpf('00000000000'); ?></td>
                                            <td>São Paulo Centro</td>
                                            <td>SP</td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5">Nenhum participante encontrado.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var lastClickedButton = null; // Variável para armazenar o último botão clicado

        $('.get-users').click(function() {
            $('#my-titles-table .btn-secondary').each(function() {
                $(this).removeClass('btn-secondary')   // Remove a classe btn-secondary
                    .addClass('btn-primary')           // Adiciona a classe btn-primary
                    .text('Ver')                       // Altera o texto para 'Ver'
                    .prop('disabled', false);          // Habilita o botão
            });

            var titleId = $(this).data('title-id');
            var groupCode = $(this).data('group-code');

            if (groupCode === "") {
                var groupCode = "-- Indefinido --";
            }

            // Reabilita o botão anterior, se existir
            if (lastClickedButton && lastClickedButton !== this) {
                $(lastClickedButton).prop('disabled', false);
                $(lastClickedButton).text('Ver'); // Texto padrão
                $(lastClickedButton).removeClass('btn-secondary').addClass('btn-primary'); // Retorna à classe padrão
            }

            // Desabilita o botão que foi clicado
            $(this).prop('disabled', true);
            $(this).text('Exibindo');
            $(this).removeClass('btn-primary').addClass('btn-secondary');

            // Atualiza a referência do último botão clicado
            lastClickedButton = this;

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/get_draw_data.php', // URL do script que retorna os dados do título
                method: 'POST',
                data: { title_id: titleId },
                dataType: 'json',
                success: function(data) {
                    // Atualizar o código do grupo
                    $('.font-weight-bold').text(groupCode);

                    // Limpa a tabela de participantes
                    $('#group tbody').empty();

                    // Preenche a tabela com os participantes
                    if (data.participants.length > 0) {
                        data.participants.forEach(function(participant, index) {
                            var row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${participant.shortname} ${(participant.is_you ? '(Você)' : '')}</td>
                                    <td>${participant.cpf}</td>
                                    <td>${participant.city}</td>
                                    <td>${participant.state}</td>
                                </tr>`;
                            $('#group tbody').append(row);
                        });
                    } else {
                        $('#group tbody').append('<tr><td colspan="5">Nenhum participante encontrado.</td></tr>');
                    }

                    // Adiciona o usuário "RendaCap"
                    var rendaCapRow = `
                        <tr>
                            <td>5</td>
                            <td>Renda CAP Brasil</td>
                            <td>***.000.000-**</td>
                            <td>São Paulo Centro</td>
                            <td>SP</td>
                        </tr>`;
                    $('#group tbody').append(rendaCapRow);
                },
                error: function() {
                    toastr.error('Erro ao carregar dados do sorteio.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });
                }
            });
        });
    });
</script>