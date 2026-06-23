<?php
require_once "../includes/verifica_admin.php";
require_once "../config/conexao.php";

$mensagem = "";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: jogos.php");
    exit;
}

$id_jogo = intval($_GET["id"]);

$sql_busca = "SELECT id_jogo, titulo, capa_url FROM jogos WHERE id_jogo = ?";
$stmt_busca = $conn->prepare($sql_busca);
$stmt_busca->bind_param("i", $id_jogo);
$stmt_busca->execute();
$resultado = $stmt_busca->get_result();

if ($resultado->num_rows === 0) {
    echo "Jogo não encontrado.";
    exit;
}

$jogo = $resultado->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $confirmar_exclusao = $_POST["confirmar_exclusao"] ?? "";

    if ($confirmar_exclusao !== "sim") {
        $mensagem = "Você precisa confirmar que deseja excluir este jogo.";
    } else {
        $sql_delete = "DELETE FROM jogos WHERE id_jogo = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id_jogo);

        if ($stmt_delete->execute()) {
            header("Location: jogos.php");
            exit;
        } else {
            $mensagem = "Erro ao excluir jogo: " . $stmt_delete->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Excluir Jogo - Avalia</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
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
        <a class="botao-menu botao-admin-header" href="jogos.php">Gerenciar jogos</a>

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
        <h1>Excluir Jogo</h1>

        <div class="preview-exclusao-jogo">
            <?php if (!empty($jogo["capa_url"])): ?>
                <img 
                    src="<?php echo htmlspecialchars($jogo["capa_url"]); ?>" 
                    alt="Capa de <?php echo htmlspecialchars($jogo["titulo"]); ?>"
                >
            <?php endif; ?>

            <h2><?php echo htmlspecialchars($jogo["titulo"]); ?></h2>
        </div>

        <div class="aviso-perigo">
            <strong>Atenção:</strong> esta ação não poderá ser desfeita.
            Ao excluir este jogo, todas as avaliações relacionadas a ele também serão removidas.
        </div>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-formulario">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="jogo_excluir.php?id=<?php echo $id_jogo; ?>">
            <label class="checkbox-confirmacao">
                <input type="checkbox" name="confirmar_exclusao" value="sim" required>
                <span>Confirmo que desejo excluir este jogo permanentemente.</span>
            </label>

            <div class="botoes-confirmacao">
                <a class="botao-secundario" href="jogo_editar.php?id=<?php echo $id_jogo; ?>">
                    Cancelar
                </a>

                <button 
                    class="botao-perigo" 
                    type="submit"
                    onclick="return confirm('Tem certeza que deseja excluir este jogo? Esta ação não poderá ser desfeita.');"
                >
                    Excluir
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

        const clicouDentro = dropdown.contains(event.target);

        if (!clicouDentro) {
            dropdown.removeAttribute("open");
        }
    });
</script>

</body>
</html>