<?php
require_once "../includes/verifica_admin.php";
require_once "../config/conexao.php";

if (
    !isset($_GET["id"]) ||
    empty($_GET["id"]) ||
    !isset($_GET["acao"]) ||
    empty($_GET["acao"])
) {
    header("Location: ../index.php");
    exit;
}

$id_avaliacao = intval($_GET["id"]);
$acao = $_GET["acao"];

if (!in_array($acao, ["ocultar", "restaurar"], true)) {
    header("Location: ../index.php");
    exit;
}

$sql_busca = "SELECT id_avaliacao, id_jogo
              FROM avaliacoes
              WHERE id_avaliacao = ?";

$stmt_busca = $conn->prepare($sql_busca);
$stmt_busca->bind_param("i", $id_avaliacao);
$stmt_busca->execute();

$resultado = $stmt_busca->get_result();

if ($resultado->num_rows === 0) {
    echo "Avaliação não encontrada.";
    exit;
}

$avaliacao = $resultado->fetch_assoc();

$novo_status = $acao === "ocultar" ? 0 : 1;

$sql_update = "UPDATE avaliacoes
               SET visivel = ?
               WHERE id_avaliacao = ?";

$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("ii", $novo_status, $id_avaliacao);

if ($stmt_update->execute()) {
    header(
        "Location: ../jogo.php?id=" .
        $avaliacao["id_jogo"] .
        "#avaliacao-" .
        $id_avaliacao
    );
    exit;
}

echo "Erro ao atualizar visibilidade da avaliação.";