<?php
    // Caso prefira o .env apenas descomente o codigo e comente o "include('parameters.php');" acima
	// Carrega as variáveis de ambiente do arquivo .env

    // Caminho para o diretório pai
    $parentDir = __DIR__;

	require $parentDir . '/vendor/autoload.php';
	$dotenv = Dotenv\Dotenv::createImmutable($parentDir);
	$dotenv->load();

	// Acessa as variáveis de ambiente
	$dbHost = $_ENV['DB_HOST'];
	$dbUser = $_ENV['DB_USER'];
	$dbPass = $_ENV['DB_PASS'];
	$dbName = $_ENV['DB_NAME'];
	$port = $_ENV['DB_PORT'];

    try{
        //Conexão com a porta
        $conn = new PDO("mysql:host=$dbHost;port=$port;dbname=" . $dbName, $dbUser, $dbPass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //Conexão sem a porta
        //$conn = new PDO("mysql:host=$host;dbname=" . $dbname, $user, $pass);
        //echo "Conexão com banco de dados realizado com sucesso!";
    } catch (PDOException $e) {
        // Tratamento de erros
        //echo 'Erro de conexão com o banco de dados: ' . $e->getMessage();
    }

    // Definir url principal
    // define('INCLUDE_PATH', $_ENV['URL']);
    define('INCLUDE_PATH_DASHBOARD', $_ENV['URL']);
    define('INCLUDE_PATH_AUTH', $_ENV['URL'] . "auth/");



    // Definir o nome do projeto
	$project_name = $_ENV['PROJECT_NAME'];

    // Definir o fuso horário para o Brasil
	$default_timezone = $_ENV['DEFAULT_TIMEZONE'];
    date_default_timezone_set($default_timezone);

    // Tamanho maximo de arquivo
	$max_file_size = $_ENV['MAX_FILE_SIZE'];



    // Asaas
	$config['asaas_api_url'] = $_ENV['ASAAS_API_URL'];
	$config['asaas_api_key'] = $_ENV['ASAAS_API_KEY'];
    $config['project_name'] = $_ENV['PROJECT_NAME'];
	$config['groupname'] = $_ENV['GROUPNAME'] ?? null;



    // Incluir codigo de funcionalidades
    include('back-end/utility-functions/mail.php');



    // Array de tradução dos meses
    $month_names = array(
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril', 5 => 'maio', 6 => 'junho',
        7 => 'julho', 8 => 'agosto', 9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
    );

    function formatToBRL($price) {
        $formattedPrice = number_format($price, 2, ',', '.');
        // Remove os centavos se forem zero
        if (strpos($formattedPrice, ',00') !== false) {
            $formattedPrice = str_replace(',00', '', $formattedPrice);
        }
        return 'R$ ' . $formattedPrice;
    }

    function formatOcultCpf($cpf) {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/\D/', '', $cpf);
    
        // Verifica se o CPF tem o tamanho correto
        if (strlen($cpf) != 11) {
            return 'CPF inválido';
        }

        // Formata o CPF com base no padrão ***.000.000-**
        $formattedCpf = '***.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-**';

        return $formattedCpf;
    }

    function maskEmail($email) {
        // Verifica se o e-mail é válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "E-mail inválido";
        }
    
        // Divide o e-mail em partes
        list($username, $domain) = explode('@', $email);
        
        // Máscara do nome de usuário
        $maskedUsername = str_repeat('*', strlen($username));
    
        // Divide o domínio em partes
        list($domainName, $tld) = explode('.', $domain);
        
        // Máscara do domínio
        $maskedDomainName = str_repeat('*', strlen($domainName));
        $maskedTld = str_repeat('*', strlen($tld));
    
        // Monta o e-mail mascarado
        return $maskedUsername . '@' . $maskedDomainName . '.' . $maskedTld;
    }

    function cardFlagSVG($user) {
        // Caminho para o diretório SVG
        $svg_dir = 'images/svgs/card-flags/';
    
        // Verifique se a bandeira do cartão está presente
        if ($user['payment_method'] == 'PIX') {
            // Se não houver bandeira, exibir o ícone de Pix
            include('images/svgs/pix.svg');
            echo '<h3 class="font-weight-bold mb-0">Pix</h3>';
        } else if ($user['payment_method'] == 'VOUCHER') {
            echo '<h3 class="font-weight-bold mb-0">Voucher</h3>';
        } else if ($user['payment_method'] == 'FREE_PLAN') {
            echo '<h3 class="font-weight-bold mb-0">Plano Grátis</h3>';
        } else {
            // Convertendo a bandeira para minúsculas
            $user['card_brand'] = strtolower($user['card_brand']);
    
            // Nome do arquivo SVG baseado na variável
            $svg_file = $svg_dir . $user['card_brand'] . '.svg';
    
            // Verifique se o arquivo SVG existe
            if (file_exists($svg_file)) {
                include($svg_file);
                echo '<h3 class="font-weight-bold ml-2 mb-0">**** ' . $user['card_last_digits'] . '</h3>';
            } else {
                // Exiba uma mensagem de erro ou uma imagem padrão
                echo 'SVG não encontrado';
            }
        }
    }



    // Lógica de permissões
    function hasPermission($userId, $permissionName, $conn) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM tb_user_roles ur
            INNER JOIN tb_role_permissions rp ON ur.role_id = rp.role_id
            INNER JOIN tb_permissions p ON rp.permission_id = p.id
            WHERE ur.user_id = ? AND p.permission_name = ?
        ");
        $stmt->execute([$userId, $permissionName]);
    
        return $stmt->fetchColumn() > 0;
    }

    // Mapa das permissões
    $permissionsMap = [
        'manage_users' => 'admin',
        'edit_content' => 'editor',
        'use_content' => 'user'
    ];
?>
