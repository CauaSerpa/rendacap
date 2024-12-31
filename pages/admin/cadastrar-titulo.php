<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-albums icon-gradient bg-ripe-malin"></i>
            </div>
            <div>
                Cadastrar Título
                <div class="page-title-subheading">Aqui você pode cadastrar novos títulos.</div>
            </div>
        </div>
    </div>
</div>

<?php
    // Consultar os produtos
    $sql = "SELECT id, name FROM tb_draw_products";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all rows as an associative array
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-card mb-3 card">
    <div class="card-body">
        <form id="title-registration-form">
            <div class="form-row mb-3">
                <div class="col-md-4">
                    <div class="position-relative form-group">
                        <label for="identifier">Identificador</label>
                        <input type="text" class="form-control" id="identifier" name="identifier" required>
                    </div>
                </div>
                <div class="form-row col-md-8">
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <label for="responsible">Responsável</label>
                            <select class="form-control" id="responsible" name="responsible" required>
                                <option value="" selected disabled>Selecione o Responsável</option>
                                <option value="Roberto">Roberto</option>
                                <option value="Sergio">Sergio</option>
                                <option value="Ingrid">Ingrid</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <label for="group_creation_method">Método de criação de grupo</label>
                            <select class="form-control" id="group_creation_method" name="group_creation_method" required>
                                <option value="" selected disabled>Selecione o Método</option>
                                <option value="automatic">Automático</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <label for="plan">Plano</label>
                            <select class="form-control" id="plan" name="plan" required>
                                <option value="" selected disabled>Selecione o Plano</option>
                                <option value="4">Diamante</option>
                                <option value="1">Ouro</option>
                                <option value="2">Prata</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" id="titles-container" style="display: none;">
                    <div class="position-relative form-group">
                        <label for="">Títulos que serão cadastrados</label>
                        <div>
                            <div class="custom-checkbox custom-control">
                                <input type="checkbox" id="telesena" name="selected_titles" class="custom-control-input">
                                <label class="custom-control-label" for="telesena">Telesena</label>
                            </div>
                            <div class="custom-checkbox custom-control">
                                <input type="checkbox" id="viva_sorte" name="selected_titles" class="custom-control-input">
                                <label class="custom-control-label" for="viva_sorte">Viva Sorte</label>
                            </div>
                            <div class="custom-checkbox custom-control">
                                <input type="checkbox" id="hiper_cap_brasil" name="selected_titles" class="custom-control-input">
                                <label class="custom-control-label" for="hiper_cap_brasil">Hiper Cap Brasil</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Campos para cadastrar os titulos -->
            <div id="products-section"></div>

            <div class="d-flex align-items-center justify-content-between">
                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>titulos" class="btn btn-light">Voltar</a>
    
                <button type="submit" id="btnSubmit" class="btn btn-primary" style="width: 82px;">Cadastrar</button>
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
    document.getElementById('plan').addEventListener('change', function () {
        const selectedPlan = this.value;
        const titlesContainer = document.getElementById('titles-container');
        const productsSection = document.getElementById('products-section');

        titlesContainer.style.display = 'block'; // Exibir checkboxes
        productsSection.innerHTML = ''; // Limpar campos de produtos ao trocar plano

        // Desmarcar todos os checkboxes
        const checkboxes = document.querySelectorAll('#titles-container input[type="checkbox"]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = false; // Desmarcar checkbox
        });

        // Configurar a quantidade de campos por plano
        let telesenaCount = 0,
            vivaSorteCount = 0,
            hiperCapBrasilCount = 0;

        if (selectedPlan === '4') {
            telesenaCount = 4;
            vivaSorteCount = 4;
            hiperCapBrasilCount = 4;
        } else if (selectedPlan === '1') {
            telesenaCount = 2;
            vivaSorteCount = 2;
            hiperCapBrasilCount = 2;
        } else if (selectedPlan === '2') {
            telesenaCount = 1;
            vivaSorteCount = 1;
            hiperCapBrasilCount = 1;
        }

        // Adicionar evento aos checkboxes novamente
        checkboxes.forEach((checkbox) => {
            // Remover qualquer listener antigo para evitar múltiplos eventos
            checkbox.removeEventListener('change', checkbox.listener);

            // Adicionar novo listener
            checkbox.listener = function () {
                const productKey = this.id;
                const productName = this.nextElementSibling.textContent;
                const productCount =
                    productKey === 'telesena'
                        ? telesenaCount
                        : productKey === 'viva_sorte'
                        ? vivaSorteCount
                        : hiperCapBrasilCount;

                if (this.checked) {
                    // Gerar campos para o produto selecionado
                    productsSection.innerHTML += generateFieldsForProduct(productName, productKey, productCount);
                    initializeDatepickersForProductTelesena(productKey, productCount); // Inicializar datepickers
                    initializeDatepickersForProductVivaSorte(productKey, productCount); // Inicializar datepickers
                    initializeDatepickersForProductHiperCapBrasil(productKey, productCount); // Inicializar datepickers
                } else {
                    // Remover campos do produto desmarcado
                    const productFields = document.querySelectorAll(`#products-section div[data-product="${productKey}"]`);
                    productFields.forEach((field) => field.remove());
                }
            };

            checkbox.addEventListener('change', checkbox.listener);
        });
    });

    // Função para gerar campos
    function generateFieldsForProduct(productName, productKey, productCount) {
        let html = `<div data-product="${productKey}"><h4>${productName}</h4>`;
        for (let i = 0; i < productCount; i++) {
            html += `
                <div class="form-row">
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <label for="draw_date_${productKey}_${i}">Data do Sorteio (${productName} ${i+1})</label>
                            <div class="input-group">
                                <div id="draw_date_trigger_${productKey}_${i}" class="input-group-prepend datepicker-trigger">
                                    <div class="input-group-text">
                                        <i class="fa fa-calendar-alt"></i>
                                    </div>
                                </div>
                                <input name="products[${productKey}][${i}][draw_date]" id="draw_date_${productKey}_${i}"
                                    placeholder="dd/mm/aaaa" type="text" class="form-control datepicker" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <label for="title_${productKey}_${i}">Título (${productName} ${i+1})</label>
                            <input type="text" class="form-control" id="title_${productKey}_${i}" name="products[${productKey}][${i}][title]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <label for="operation_code_${productKey}_${i}">Código de Operação (${productName} ${i+1})</label>
                            <input type="text" class="form-control" id="operation_code_${productKey}_${i}" name="products[${productKey}][${i}][operation_code]" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <label for="series_${productKey}_${i}">Série (${productName} ${i+1})</label>
                            <input type="text" class="form-control" id="series_${productKey}_${i}" name="products[${productKey}][${i}][series]" maxlength="10" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <label for="dv_${productKey}_${i}">DV (${productName} ${i+1})</label>
                            <input type="text" class="form-control" id="dv_${productKey}_${i}" name="products[${productKey}][${i}][dv]" maxlength="2" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="position-relative form-group">
                            <label for="lucky_number_${productKey}_${i}">Número da Sorte (${productName} ${i+1})</label>
                            <input type="text" class="form-control" id="lucky_number_${productKey}_${i}" name="products[${productKey}][${i}][lucky_number]" maxlength="10" required>
                        </div>
                    </div>
                </div>

                <hr>
            `;
        }
        html += `</div>`;
        return html;
    }

    // Inicializar datepickers
    function initializeDatepickersForProductTelesena(productKey, productCount) {
        for (let i = 0; i < productCount; i++) {
            const datepicker = `#draw_date_telesena_${i}`;
            const trigger = `#draw_date_trigger_telesena_${i}`;

            $(datepicker).datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                trigger: trigger,
            });
            $(datepicker).mask('00/00/0000'); // Máscara
        }
    }

    // Inicializar datepickers
    function initializeDatepickersForProductVivaSorte(productKey, productCount) {
        for (let i = 0; i < productCount; i++) {
            const datepicker = `#draw_date_viva_sorte_${i}`;
            const trigger = `#draw_date_trigger_viva_sorte_${i}`;

            $(datepicker).datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                trigger: trigger,
            });
            $(datepicker).mask('00/00/0000'); // Máscara
        }
    }

    // Inicializar datepickers
    function initializeDatepickersForProductHiperCapBrasil(productKey, productCount) {
        for (let i = 0; i < productCount; i++) {
            const datepicker = `#draw_date_hiper_cap_brasil_${i}`;
            const trigger = `#draw_date_trigger_hiper_cap_brasil_${i}`;

            $(datepicker).datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                trigger: trigger,
            });
            $(datepicker).mask('00/00/0000'); // Máscara
        }
    }
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#title-registration-form').on('submit', function(e) {
            e.preventDefault();

            // Define os botões como variáveis
            btnSubmit = $("#btnSubmit");
            btnLoader = $("#btnLoader");

            // Desabilitar botão submit e habilitar loader
            btnSubmit.addClass("d-none");
            btnLoader.removeClass("d-none");

            let formData = new FormData(this);
            formData.append('action', 'title-registration-form'); // Adicionado 'action' diretamente ao FormData

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/title-registration-form.php', // URL do arquivo PHP para salvar a senha
                type: 'POST',
                data: formData, // Enviando FormData diretamente
                contentType: false,
                processData: false,
                success: function(response) {
                    if(response.status == "success") {

                        if (response.redirect) {
                            // Redireciona o usuário para a URL especificada
                            window.location.href = response.redirect;
                        } else {
                            // Redireciona o usuário para o caminho padrão
                            window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>titulos";
                        }

                        // Reseta o formulario
                        $('#title-registration-form')[0].reset();

                        // Desabilitar loader e habilitar botão submit
                        btnSubmit.removeClass("d-none");
                        btnLoader.addClass("d-none");
                    } else {
                        // Exibe mensagens de erro
                        toastr.error(response.message, 'Erro', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });

                        // Desabilitar loader e habilitar botão submit
                        btnSubmit.removeClass("d-none");
                        btnLoader.addClass("d-none");
                    }
                },
                error: function() {
                    // Caso ocorra algum erro na requisição
                    toastr.error('Ocorreu um erro ao enviar os dados. Tente novamente.', 'Erro', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 3000
                    });

                    // Desabilitar loader e habilitar botão submit
                    btnSubmit.removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        });
    });
</script>