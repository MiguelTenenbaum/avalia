<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "avaliaja";

$conn = new mysqli($servidor, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>