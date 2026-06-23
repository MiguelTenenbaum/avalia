<?php

$servidor = getenv("DB_HOST") ?: "localhost";
$usuario = getenv("DB_USER") ?: "root";
$senha = getenv("DB_PASSWORD") ?: "";
$banco = getenv("DB_NAME") ?: "avaliaja";
$porta = getenv("DB_PORT") ?: 3306;

$conn = new mysqli(
    $servidor,
    $usuario,
    $senha,
    $banco,
    intval($porta)
);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>