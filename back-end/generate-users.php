<?php
session_start();
include('./../config.php');

header('Content-Type: application/json');

function generateToken($length = 50) {
    return bin2hex(random_bytes($length));
}

function generateSequentialToken($sequence) {
    return '3005' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
}

function createUser($conn, $firstname, $lastname, $email, $whatsapp, $cpf, $username, $password, $role_id, $token, $inviter_id) {
    // Dados padrões
    $birth_date = '1990-01-01';
    $cep = '01000-000';
    $address = 'Av. Paulista';
    $address_number = '1000';
    $complement = '';
    $neighborhood = 'Bela Vista';
    $city = 'São Paulo';
    $state = 'SP';
    $active_token = generateToken();

    // Inserir usuário na tabela `tb_users`
    $stmt = $conn->prepare("INSERT INTO tb_users (firstname, lastname, email, active_token, whatsapp, birth_date, cpf, username, password, token, inviter_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstname, $lastname, $email, $active_token, $whatsapp, $birth_date, $cpf, $username, password_hash($password, PASSWORD_DEFAULT), $token, $inviter_id]);

    // Obter o ID do último registro inserido
    $user_id = $conn->lastInsertId();

    // Inserir endereço na tabela `tb_address`
    $stmt = $conn->prepare("INSERT INTO tb_address (user_id, cep, address, number, complement, neighborhood, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $cep, $address, $address_number, $complement, $neighborhood, $city, $state]);

    // Inserir o papel do usuário na tabela `tb_user_roles`
    $stmt = $conn->prepare("INSERT INTO tb_user_roles (user_id, role_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $role_id]);

    return $user_id;
}

function createNetwork($conn, $inviter_id, $user_id, $position) {
    // Inserir o registro na tabela `tb_networks`
    $stmt = $conn->prepare("INSERT INTO tb_networks (inviter_id) VALUES (?)");
    $stmt->execute([$inviter_id]);

    // Obter o ID da rede
    $network_id = $conn->lastInsertId();

    // Inserir na tabela `tb_user_networks` com a posição
    $stmt = $conn->prepare("INSERT INTO tb_user_networks (network_id, user_id) VALUES (?, ?)");
    $stmt->execute([$network_id, $user_id]);

    return $network_id;
}

// IDs dos administradores (administradores não participarão da network)
$admin_emails = [
    'rendacapbrasil@gmail.com',
    'roberto.costa.jr@hotmail.com',
    'admin@rendacap.com.br'
];
$role_admin = 1; // Papel de administrador
$role_user = 3; // Papel de usuário

// Token inicial
$token_start = 000000;

// Inserir administradores (não participarão da network)
foreach ($admin_emails as $key => $email) {
    $token = generateSequentialToken($token_start++);
    createUser($conn, 'Admin', 'User', $email, '(11) 11111-1111', '000.000.000-00', 'admin' . $key, 'admin', $role_admin, $token, null);
}

// Inserir usuários na rede (iniciando a partir do usuário 000)
$inviter_base_id = null; // O primeiro usuário não tem um convidador
$position = 1;

// Criar o primeiro usuário (000) sem convidador
$email = 'usuario000@rendacap.com.br';
$username = '000';
$token = generateSequentialToken($token_start++);
$first_user_id = createUser($conn, 'User', '000', $email, '(11) 11111-1111', '000.000.000-00', $username, 'admin', $role_user, $token, null);

// O usuário 000 será o primeiro convidador
$inviter_base_id = $first_user_id;

// Agora criar os usuários, organizando em grupos de 5
for ($i = 1; $i <= 30; $i++) { // 125
    // Definir o convidador do grupo atual
    if ($i % 5 == 1) {
        // Cada grupo de 5 usuários será convidado pelo usuário anterior
        $inviter_base_id = ($i == 1) ? $first_user_id : ($i - 1) / 5 + $first_user_id;
    }

    $email = 'usuario' . str_pad($i, 3, '0', STR_PAD_LEFT) . '@rendacap.com.br';
    $username = str_pad($i, 3, '0', STR_PAD_LEFT);
    $token = generateSequentialToken($token_start++);
    $user_id = createUser($conn, 'User', $i, $email, '(11) 11111-1111', '000.000.000-00', $username, 'admin', $role_user, $token, $inviter_base_id);

    // Cadastrar o usuário na tabela de rede
    $position = ($i % 5 == 0) ? 5 : $i % 5; // Posição dentro do grupo de 5
    createNetwork($conn, $inviter_base_id, $user_id, $position);
}

echo json_encode(['status' => 'success', 'message' => 'Usuários criados com sucesso']);
?>