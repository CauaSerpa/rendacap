<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-albums icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Editar Título
                <div class="page-title-subheading">Aqui você pode editar títulos existentes.</div>
            </div>
        </div>
    </div>
</div>

<?php
// Consultar os produtos
$sql = "SELECT id, name, slug FROM tb_draw_products";
$stmt = $conn->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar o título que será editado
$title_id = $_GET['title_id']; // ID do título a ser editado
$title_stmt = $conn->prepare("SELECT * FROM tb_draw_titles WHERE id = ?");
$title_stmt->execute([$title_id]);
$title_data = $title_stmt->fetch(PDO::FETCH_ASSOC);

// Consultar os produtos associados ao título
$product_stmt = $conn->prepare("
    SELECT t.id, p.name AS product_name, p.slug, t.draw_date, t.title_id, t.title, t.operation_code, t.series, t.dv, t.lucky_number 
    FROM tb_draw_title_products t
    JOIN tb_draw_products p ON t.product_id = p.id
    WHERE t.draw_title_id = ?
");
$product_stmt->execute([$title_id]);
$title_products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar produtos por tipo
$grouped_products = [];
foreach ($title_products as $product) {
    $grouped_products[$product['product_name']][] = $product;
}
?>

<div class="main-card mb-3 card">
    <div class="card-body">
        <form id="title-editing-form">
            <div class="form-row mb-3">
                <div class="col-md-4">
                    <div class="position-relative form-group">
                        <label for="identifier">Identificador</label>
                        <input type="text" class="form-control" id="identifier" name="identifier" value="<?= htmlspecialchars($title_data['identifier']); ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="position-relative form-group">
                        <label for="plan">Plano</label>
                        <select class="form-control" id="plan" name="plan" disabled>
                            <option value="">Selecione o Plano</option>
                            <option value="4" <?= $title_data['plan_id'] == 4 ? 'selected' : ''; ?>>Diamante</option>
                            <option value="1" <?= $title_data['plan_id'] == 1 ? 'selected' : ''; ?>>Ouro</option>
                            <option value="2" <?= $title_data['plan_id'] == 2 ? 'selected' : ''; ?>>Prata</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="position-relative form-group">
                        <label for="responsible">Responsável</label>
                        <select class="form-control" id="responsible" name="responsible" required disabled>
                            <option value="">Selecione o Responsável</option>
                            <option value="Roberto" <?= $title_data['responsible'] == 'Roberto' ? 'selected' : ''; ?>>Roberto</option>
                            <option value="Sergio" <?= $title_data['responsible'] == 'Sergio' ? 'selected' : ''; ?>>Sergio</option>
                            <option value="Ingrid" <?= $title_data['responsible'] == 'Ingrid' ? 'selected' : ''; ?>>Ingrid</option>
                            <option value="Outros" <?= $title_data['responsible'] == 'Outros' ? 'selected' : ''; ?>>Outros</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Agrupar produtos por tipo -->
            <div id="products-section">
                <?php foreach (['Telesena', 'Viva Sorte', 'Hiper Cap Brasil'] as $product_slug): ?>
                    <h5><?= ucfirst($product_slug); ?></h5>
                    <?php if (isset($grouped_products[$product_slug])): ?>
                        <?php foreach ($grouped_products[$product_slug] as $product): ?>
                            <div class="form-row">
                                <input type="hidden" name="products[<?= $product['slug']; ?>][<?= $product['id']; ?>][id]" value="<?= $product['id']; ?>">
                                <div class="col-md-3">
                                    <div class="position-relative form-group">
                                        <label for="title_id_<?= $product['slug']; ?>">ID do Título (<?= htmlspecialchars($product['product_name']); ?>)</label>
                                        <input type="text" class="form-control" id="title_id_<?= $product['slug']; ?>" name="products[<?= $product['slug']; ?>][<?= $product['id']; ?>][title_id]" value="<?= htmlspecialchars($product['title_id']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="position-relative form-group">
                                        <label for="draw_date_<?= $product['slug']; ?>">Data do Sorteio (<?= htmlspecialchars($product['product_name']); ?>)</label>
                                        <input name="products[<?= $product['slug']; ?>][<?= $product['id']; ?>][draw_date]" id="draw_date_<?= $product['slug']; ?>" value="<?= date('d/m/Y', strtotime($product['draw_date'])); ?>" type="text" class="form-control datepicker" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="position-relative form-group">
                                        <label for="title_<?= $product['slug']; ?>">Título (<?= htmlspecialchars($product['product_name']); ?>)</label>
                                        <input type="text" class="form-control" id="title_<?= $product['slug']; ?>" name="products[<?= $product['slug']; ?>][<?= $product['id']; ?>][title]" value="<?= htmlspecialchars($product['title']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="position-relative form-group">
                                        <label for="operation_code_<?= $product['slug']; ?>">Código de Operação (<?= htmlspecialchars($product['product_name']); ?>)</label>
                                        <input type="text" class="form-control" id="operation_code_<?= $product['slug']; ?>" name="products[<?= $product['slug']; ?>][<?= $product['id']; ?>][operation_code]" value="<?= htmlspecialchars($product['operation_code']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="position-relative form-group">
                                        <label for="series_<?= $product['slug']; ?>">Série (<?= htmlspecialchars($product['product_name']); ?>)</label>
                                        <input type="text" class="form-control" id="series_<?= $product['slug']; ?>" name="products[<?= $product['slug']; ?>][<?= $product['id']; ?>][series]" value="<?= htmlspecialchars($product['series']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative form-group">
                                        <label for="dv_<?= $product['slug']; ?>">DV (<?= htmlspecialchars($product['product_name']); ?>)</label>
                                        <input type="text" class="form-control" id="dv_<?= $product['slug']; ?>" name="products[<?= $product['slug']; ?>][<?= $product['id']; ?>][dv]" value="<?= htmlspecialchars($product['dv']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative form-group">
                                        <label for="lucky_number_<?= $product['slug']; ?>">Número da Sorte (<?= htmlspecialchars($product['product_name']); ?>)</label>
                                        <input type="text" class="form-control" id="lucky_number_<?= $product['slug']; ?>" name="products[<?= $product['slug']; ?>][<?= $product['id']; ?>][lucky_number]" value="<?= htmlspecialchars($product['lucky_number']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Mensagem e botão para adicionar novos campos -->
                        <div id="add-new-fields-section-<?= str_replace(' ', '_', strtolower($product_slug)); ?>" class="mb-3">
                            <p>Nenhum título associado encontrado para <?= ucfirst($product_slug); ?>.</p>
                            <button type="button" class="btn btn-secondary add-fields-btn" data-product="<?= str_replace(' ', '_', strtolower($product_slug)); ?>">Adicionar Títulos</button>
                        </div>
                        <!-- Div para os novos campos -->
                        <div id="new-fields-<?= str_replace(' ', '_', strtolower($product_slug)); ?>" class="new-fields d-none">
                            <!-- Aqui serão adicionados os novos campos dinamicamente -->
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="d-flex align-items-center justify-content-between">
                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>titulos" class="btn btn-light">Voltar</a>
    
                <button type="submit" id="btnSubmit" class="btn btn-primary" style="width: 82px;">Salvar</button>
                <button id="btnLoader" class="btn btn-primary d-none" style="width: 82px;" disabled>
                    <div class="loader">
                        <div class="ball-pulse">
                            <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                            <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                            <div style="background-color: rgb(255, 255, 255); width: 6px; height: 6px;"></div>
                        </div>
                    </div>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.add-fields-btn').on('click', function () {
            const selectedPlan = $('#plan').val();
            const productSlug = $(this).data('product');
            const fieldsContainer = $(`#new-fields-${productSlug}`);
            const addFieldsSection = $(`#add-new-fields-section-${productSlug}`);

            let productName;
            if (productSlug === 'telesena') {
                productName = 'Telesena';
            } else if (productSlug === 'viva_sorte') {
                productName = 'Viva Sorte';
            } else if (productSlug === 'hiper_cap_brasil') {
                productName = 'Hiper Cap Brasil';
            } else {
                console.error('Produto não reconhecido:', productSlug);
                return;
            }

            // Configurar a quantidade de campos por plano
            let telesenaCount = 0,
                vivaSorteCount = 0,
                hiperCapBrasilCount = 0;

            if (selectedPlan === '4') { // Plano Diamante
                telesenaCount = 4;
                vivaSorteCount = 4;
                hiperCapBrasilCount = 4;
            } else if (selectedPlan === '1') { // Plano Ouro
                telesenaCount = 2;
                vivaSorteCount = 2;
                hiperCapBrasilCount = 2;
            } else if (selectedPlan === '2') { // Plano Prata
                telesenaCount = 1;
                vivaSorteCount = 1;
                hiperCapBrasilCount = 1;
            }

            // Determinar o número de campos para o produto atual
            let productCount;
            if (productSlug === 'telesena') {
                productCount = telesenaCount;
            } else if (productSlug === 'viva_sorte') {
                productCount = vivaSorteCount;
            } else if (productSlug === 'hiper_cap_brasil') {
                productCount = hiperCapBrasilCount;
            } else {
                console.error('Produto não reconhecido:', productSlug);
                return;
            }

            // Ocultar o botão e mensagem
            addFieldsSection.addClass('d-none');

            // Gerar campos com base no plano e produto
            let newFields = '';
            for (let i = 0; i < productCount; i++) {
                newFields += `
                    <div class="form-row">
                        <div class="col-md-4">
                            <div class="position-relative form-group">
                                <label for="new_draw_date_${productSlug}_${i}">Data do Sorteio (${productName} ${i + 1})</label>
                                <input name="new_products[${productSlug}][${i}][draw_date]" id="new_draw_date_${productSlug}_${i}" type="text" class="form-control datepicker" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="position-relative form-group">
                                <label for="new_title_${productSlug}_${i}">Título (${productName} ${i + 1})</label>
                                <input name="new_products[${productSlug}][${i}][title]" id="new_title_${productSlug}_${i}" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="position-relative form-group">
                                <label for="new_operation_code_${productSlug}_${i}">Código de Operação (${productName} ${i + 1})</label>
                                <input name="new_products[${productSlug}][${i}][operation_code]" id="new_operation_code_${productSlug}_${i}" type="text" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4">
                            <div class="position-relative form-group">
                                <label for="new_series_${productSlug}_${i}">Série (${productName} ${i + 1})</label>
                                <input name="new_products[${productSlug}][${i}][series]" id="new_series_${productSlug}_${i}" type="text" class="form-control" maxlength="10" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="position-relative form-group">
                                <label for="new_dv_${productSlug}_${i}">DV (${productName} ${i + 1})</label>
                                <input name="new_products[${productSlug}][${i}][dv]" id="new_dv_${productSlug}_${i}" type="text" class="form-control" maxlength="2" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="position-relative form-group">
                                <label for="new_lucky_number_${productSlug}_${i}">Número da Sorte (${productName} ${i + 1})</label>
                                <input name="new_products[${productSlug}][${i}][lucky_number]" id="new_lucky_number_${productSlug}_${i}" type="text" class="form-control" maxlength="10" required>
                            </div>
                        </div>
                    </div>
                    <hr>
                `;
            }

            // Adicionar os novos campos ao container
            fieldsContainer.html(newFields).removeClass('d-none');

            // Re-inicializar o datepicker para os novos campos
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
            }).mask('00/00/0000');
        });
    });

    // A mesma lógica para o datepicker que você já tinha
    $(document).ready(function() {
        // Inicializar o datepicker nos campos de data
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true
        }).mask('00/00/0000');
    });

    $('#title-editing-form').on('submit', function(e) {
        e.preventDefault();

        // Define os botões como variáveis
        btnSubmit = $("#btnSubmit");
        btnLoader = $("#btnLoader");

        // Desabilitar botão submit e habilitar loader
        btnSubmit.addClass("d-none");
        btnLoader.removeClass("d-none");

        let formData = new FormData(this);
        formData.append('action', 'title-editing-form'); // Adicionado 'action' diretamente ao FormData
        formData.append('title_id', <?= $title_id; ?>); // Passando o ID do título

        $.ajax({
            url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/title-edit-form.php', // URL do arquivo PHP para editar o título
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == "success") {
                    // Redireciona o usuário após o toastr desaparecer
                    window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>titulos";
                } else {
                    toastr.error(response.message, 'Erro', {
                        closeButton: true,
                        progressBar: true
                    });
                }
            },
            error: function() {
                toastr.error('Erro ao editar o título. Tente novamente.', 'Erro', {
                    closeButton: true,
                    progressBar: true
                });
            },
            complete: function() {
                btnSubmit.removeClass("d-none");
                btnLoader.addClass("d-none");
            }
        });
    });
</script>