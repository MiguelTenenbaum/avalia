# Deploy do sistema Avalia com Docker

Este documento apresenta as instruções necessárias para executar o sistema **Avalia** utilizando Docker. A aplicação foi desenvolvida em PHP e utiliza um banco de dados MariaDB. Para facilitar a implantação, os serviços foram organizados com Docker Compose.

## Arquivos principais

Para executar o projeto com Docker, os seguintes arquivos devem estar na pasta principal do sistema:

```text
Dockerfile
docker-compose.yml
avaliaja.sql
DEPLOY.md
```

Além deles, a pasta principal também deve conter os arquivos e diretórios da aplicação, como `index.php`, `config/`, `includes/`, `admin/` e `assets/`.

## Serviços utilizados

O projeto utiliza três serviços no Docker Compose:

* **app**: container da aplicação PHP com Apache;
* **db**: container do banco de dados MariaDB;
* **phpmyadmin**: interface web para visualizar e administrar o banco de dados.

A aplicação fica disponível na porta `8080`, enquanto o phpMyAdmin fica disponível na porta `8081`.

## Configuração do banco de dados

O banco de dados utilizado pela aplicação se chama `avaliaja`. A criação das tabelas e a inserção dos dados iniciais são feitas a partir do arquivo `avaliaja.sql`, exportado pelo phpMyAdmin.

No ambiente Docker, a aplicação se conecta ao banco por meio das variáveis definidas no arquivo `docker-compose.yml`:

```yaml
DB_HOST: db
DB_PORT: 3306
DB_NAME: avaliaja
DB_USER: avalia_user
DB_PASSWORD: avalia_pass
```

O valor `db` é utilizado como host porque esse é o nome do serviço do banco de dados dentro do Docker Compose.

## Como executar o projeto

Primeiro, é necessário ter o Docker instalado e em execução. No Windows, foi utilizado o Docker Desktop.

Com o Docker aberto, acesse a pasta principal do projeto pelo terminal. Em seguida, execute o comando:

```bash
docker compose up -d --build
```

Esse comando constrói a imagem da aplicação e inicia os containers necessários para o funcionamento do sistema.

Após a execução, o sistema pode ser acessado pelo navegador em:

```text
http://localhost:8080
```

O phpMyAdmin pode ser acessado em:

```text
http://localhost:8081
```

## Acesso ao phpMyAdmin

Para acessar o banco pelo phpMyAdmin, podem ser utilizadas as seguintes credenciais:

```text
Servidor: db
Usuário: root
Senha: root_pass
```

Também é possível acessar com o usuário usado pela aplicação:

```text
Servidor: db
Usuário: avalia_user
Senha: avalia_pass
```

Ao entrar no phpMyAdmin, o banco `avaliaja` deve aparecer com as tabelas utilizadas pelo sistema, como `usuarios`, `jogos` e `avaliacoes`.

## Parar a execução

Para parar os containers, utilize:

```bash
docker compose down
```

Esse comando encerra os serviços, mas mantém os dados armazenados no volume do banco.

## Reiniciar o banco do zero

Caso seja necessário apagar os dados do banco e importar novamente o arquivo `avaliaja.sql`, utilize:

```bash
docker compose down -v
docker compose up -d --build
```

A opção `-v` remove o volume do banco de dados. Por isso, qualquer dado cadastrado depois da primeira execução será apagado.

## Observações

O arquivo `avaliaja.sql` é importado automaticamente apenas na primeira criação do banco. Se o volume do banco já existir, alterações feitas posteriormente no arquivo SQL não serão aplicadas automaticamente.

Durante o desenvolvimento, os arquivos do projeto são montados dentro do container da aplicação. Dessa forma, alterações feitas no código local podem ser testadas sem a necessidade de reconstruir a imagem a cada mudança.


## Usuário admin

No banco de dados, já existe um administrador cadastrado que pode ser usado para testes.
E-mail: admin@admin.com
Senha: admin123