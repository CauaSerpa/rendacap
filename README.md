# RendaCap

RendaCap é uma solução para aquisição de títulos de capitalização, desenvolvida para facilitar a gestão e a compra de títulos de maneira prática e segura.

## Instalação

Siga os passos abaixo para instalar e configurar o sistema:

1. **Baixar ou clonar o repositório:**

   Faça o download ou clone o repositório do plugin e coloque a pasta no diretório público da sua hospedagem.

2. **Instalar dependências:**

   Navegue até o diretório do projeto e execute o comando abaixo para instalar as bibliotecas necessárias:

   ```sh
   $ composer install
   ```

3. Configurar o banco de dados:

   Crie um banco de dados utilizando o cPanel (ou outra ferramenta de sua preferência) e importe o arquivo SQL localizado no diretório sql via PHPMyAdmin ou qualquer outro gerenciador de banco de dados que você preferir.
  
   Crie um banco de dados pelo cPanel (ou soluções alternativas a ele) e restaure o banco de dados que está no diretório ***sql***, via PHPmyAdmin ou conforme sua preferência.

## Configuração do Sistema

   Antes de iniciar o ambiente, você precisará configurá-lo. Para isso:
  
1. Crie um arquivo `.env ` no servidor com base no arquivo `.env.example`.

   ```sh
   $ cp .env_example .env
   ```

2. Abra o arquivo `.env ` em seu editor de texto favorito e preencha-o com as configurações adequadas para o seu ambiente.

   ```sh
   $ nano .env
   ```

## Usuário Administrador

O banco de dados inicial vem com um usuário administrador pré-configurado, que possui permissões completas para modificar as informações da página principal.

- **Email**: `admin@admin.com`
- **Senha**: `admin`

## Suporte

Caso você tenha alguma dúvida ou problema, sinta-se à vontade para abrir uma issue ou entrar em contato com o suporte.
