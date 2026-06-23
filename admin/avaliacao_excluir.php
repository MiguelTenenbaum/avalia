<?php
require_once "../includes/verifica_admin.php";
require_once "../config/conexao.php";

$mensagem = "";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: ../index.php");
    exit;
}

$id_avaliacao = intval($_GET["id"]);

$sql_busca = "SELECT 
                avaliacoes.id_avaliacao,
                avaliacoes.id_jogo,
                avaliacoes.comentario_geral,
                jogos.titulo,
                usuarios.nome
              FROM avaliacoes
              INNER JOIN jogos ON avaliacoes.id_jogo = jogos.id_jogo
              INNER JOIN usuarios ON avaliacoes.id_usuario = usuarios.id_usuario
              WHERE avaliacoes.id_avaliacao = ?";

$stmt_busca = $conn->prepare($sql_busca);
$stmt_busca->bind_param("i", $id_avaliacao);
$stmt_busca->execute();

$resultado = $stmt_busca->get_result();

if ($resultado->num_rows === 0) {
    echo "Avaliação não encontrada.";
    exit;
}

$avaliacao = $resultado->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $confirmar_exclusao = $_POST["confirmar_exclusao"] ?? "";

    if ($confirmar_exclusao !== "sim") {
        $mensagem = "Você precisa confirmar que deseja excluir esta avaliação.";
    } else {
        $sql_delete = "DELETE FROM avaliacoes
                       WHERE id_avaliacao = ?";

        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id_avaliacao);

        if ($stmt_delete->execute()) {
            header(
                "Location: ../jogo.php?id=" .
                $avaliacao["id_jogo"] .
                "&avaliacao=excluida"
            );
            exit;
        }

        $mensagem = "Erro ao excluir avaliação.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <title>Excluir Avaliação - Avalia</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>"
    >
</head>
<body>

<header class="topo">
    <div class="logo">
        <a href="../index.php">Avalia</a>
    </div>

    <div class="area-pesquisa">
        <?php
        $caminho_base = "../";
        require "../includes/barra_pesquisa.php";
        ?>
    </div>

    <nav class="menu">
        <a class="botao-menu botao-admin-header" href="jogos.php">
            Gerenciar jogos
        </a>

        <details class="dropdown-usuario">
            <summary class="icone-perfil" title="Menu do usuário">
                👤
            </summary>

            <div class="menu-dropdown">
                <p class="nome-dropdown">
                    <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                </p>

                <a href="../usuario.php?id=<?php echo $_SESSION["id_usuario"]; ?>">
                    Acessar perfil
                </a>

                <a href="../logout.php" class="sair-dropdown">
                    Sair
                </a>
            </div>
        </details>
    </nav>
</header>

<main class="pagina-formulario">
    <section class="caixa-confirmacao">
        <h1>Excluir Avaliação</h1>

        <div class="aviso-perigo">
            <strong>Atenção:</strong> esta ação não poderá ser desfeita.

            <br><br>

            Você está prestes a excluir a avaliação de
            <strong><?php echo htmlspecialchars($avaliacao["nome"]); ?></strong>
            sobre o jogo
            <strong><?php echo htmlspecialchars($avaliacao["titulo"]); ?></strong>.
        </div>

        <?php if (!empty($avaliacao["comentario_geral"])): ?>
            <p class="previa-avaliacao-exclusao">
                <?php echo nl2br(
                    htmlspecialchars($avaliacao["comentario_geral"])
                ); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-formulario">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <form
            method="POST"
            action="avaliacao_excluir.php?id=<?php echo $id_avaliacao; ?>"
        >
            <label class="checkbox-confirmacao">
                <input
                    type="checkbox"
                    name="confirmar_exclusao"
                    value="sim"
                    required
                >

                <span>
                    Confirmo que desejo excluir esta avaliação permanentemente.
                </span>
            </label>

            <div class="botoes-confirmacao">
                <a
                    class="botao-secundario"
                    href="../jogo.php?id=<?php echo $avaliacao["id_jogo"]; ?>#avaliacao-<?php echo $id_avaliacao; ?>"
                >
                    Cancelar
                </a>

                <button class="botao-principal" type="submit">
                    Excluir avaliação
                </button>
            </div>
        </form>
    </section>
</main>

<script>
    document.addEventListener("click", function(event) {
        const dropdown = document.querySelector(".dropdown-usuario");

        if (!dropdown) {
            return;
        }

        if (!dropdown.contains(event.target)) {
            dropdown.removeAttribute("open");
        }
    });
</script>

</body>
</html>