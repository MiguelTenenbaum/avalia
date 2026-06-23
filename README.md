# Avalia

Sistema web desenvolvido em PHP e MySQL para avaliação de jogos.

## Funcionalidades

- Cadastro de usuários
- Login e logout de usuários
- Perfil do usuário com avaliações e edição de dados
- Exclusão de conta com confirmação
- Exclusão e edição de avaliações
- Moderação de avaliações pelo administrador
- Catálogo de jogos
- Busca de jogos por nome e filtros
- Página individual do jogo com avaliações
- CRUD de jogos para administrador
- Cálculo de média geral, média de performance e percentual de relatos de bugs

## Tecnologias utilizadas

- PHP
- MySQL
- phpMyAdmin
- XAMPP
- HTML
- CSS
- JavaScript

## Como executar o projeto sem Docker

1. Copie a pasta do projeto para:

C:\xampp\htdocs\

2. Inicie o Apache e o MySQL no XAMPP

3. Acesse o phpMyAdmin: http://localhost/phpmyadmin

4. Crie um banco chamado 'avaliaja'

5. Importe o arquivo 'database/avaliaja.sql'

6. Acesse o sistema pelo navegador: http://localhost/avaliaja

7. Para acessar como admin, utilize o email 'admin@admin.com' e a senha 'admin123'

## Como executar o projeto com Docker

Leia o arquivo DEPLOY.md
