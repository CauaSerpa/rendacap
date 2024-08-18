<?php
    // TEMPORARIO, CRIAR MAIN PARA PAGINAS, EX.: MAIN PARA PAGINAS DE LOGIN, MAIN PARA PAGINAS DO PAINEL
    // Durante o processo de login
    $stmt = $conn->prepare("SELECT * FROM tb_users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    if ($stmt->rowCount()) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
?>
<h1>Olá <?= $user['firstname'] ?>,</h1>
<h5>Seu código de convite é:</h5>
<div class="form-row">
    <div class="input-group col-md-4">
        <input type="text" class="form-control"
            id="clipboard-token" value="<?= INCLUDE_PATH_AUTH . 'registrar/' . $user['token']; ?>">
        <div class="input-group-append">
            <button type="button" data-clipboard-target="#clipboard-token" class="btn btn-primary clipboard-trigger">
                <i class="fa fa-copy"></i>
            </button>
        </div>
    </div>
</div>

<h5 class="mt-3">
    <a href="<?= INCLUDE_PATH_AUTH; ?>sair" class="text-primary">Sair</a>
</h5>