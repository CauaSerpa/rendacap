<?php $message = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verificação de Segurança</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    background-color: #ffffff;
                    max-width: 600px;
                    margin: 20px auto;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                    border-radius: 8px 8px 0 0;
                    background-image: linear-gradient(to right, #434343 0%, black 100%) !important;
                }
                svg.logo {
                    max-width: 200px; /* Ajuste o tamanho do logotipo conforme necessário */
                    height: 40px; /* Ajuste o tamanho do logotipo conforme necessário */
                }
                .email-header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .email-body {
                    padding: 20px;
                    line-height: 1.6;
                    color: #333333;
                }
                .email-body h2 {
                    font-size: 20px;
                    margin-bottom: 10px;
                }
                .email-body p {
                    margin: 10px 0;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    font-size: 16px;
                    color: #fff;
                    background-color: #007BFF;
                    text-decoration: none;
                    border-radius: 4px;
                }
                .email-footer {
                    text-align: center;
                    color: #777777;
                    font-size: 12px;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <svg class='logo' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' width='500' height='100' viewBox='0 0 500 100' fill='none'>
                        <rect width='500' height='100' fill='url(#pattern0_18_3)'/>
                        <defs>
                            <pattern id='pattern0_18_3' patternContentUnits='objectBoundingBox' width='1' height='1'>
                            <use xlink:href='#image0_18_3' transform='scale(0.002 0.01)'/>
                            </pattern>
                            <image id='image0_18_3' width='500' height='100' xlink:href='data:image/png;base64,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'/>
                        </defs>
                    </svg>
                </div>
                <div class='email-body'>
                    <h2>Olá " . $content['content']['firstname'] . ",</h2>
                    <p>Bem-vindo(a) à $project_name! Para completar o processo de registro e ativar sua conta, por favor, clique no botão abaixo para confirmar seu e-mail:</p>
                    <a href='" . $content['content']['link'] . "' class='button'>Ativar Conta</a>
                    <p>Obrigado por se juntar a nós!</p>
                    <small>Se você não se registrou em nosso site, por favor, ignore este e-mail.</small>
                </div>
                <div class='email-footer'>
                    <p>Este é um e-mail automático. Por favor, não responda.</p>
                    <p>Direitos autorais &copy; $project_name " . date('Y') . "</p>
                </div>
            </div>
        </body>
        </html>
    ";
?>