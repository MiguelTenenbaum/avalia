<?php
require_once "../includes/verifica_admin.php";
require_once "../config/conexao.php";

$mensagem = "";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: jogos.php");
    exit;
}

$id_jogo = intval($_GET["id"]);

$sql_busca = "SELECT * FROM jogos WHERE id_jogo = ?";
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
    $titulo = trim($_POST["titulo"]);
    $genero = trim($_POST["genero"]);
    $data_lancamento = $_POST["data_lancamento"];
    $desenvolvedora = trim($_POST["desenvolvedora"]);
    $distribuidora = trim($_POST["distribuidora"]);
    $descricao = trim($_POST["descricao"]);
    $capa_url = trim($_POST["capa_url"]);

    if (empty($titulo) || empty($genero)) {
        $mensagem = "Título e gênero são obrigatórios.";
    } else {
        if (empty($data_lancamento)) {
            $data_lancamento = null;
        }

        $sql = "UPDATE jogos 
                SET titulo = ?, 
                    genero = ?, 
                    data_lancamento = ?, 
                    desenvolvedora = ?, 
                    distribuidora = ?, 
                    descricao = ?, 
                    capa_url = ?
                WHERE id_jogo = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssi",
            $titulo,
            $genero,
            $data_lancamento,
            $desenvolvedora,
            $distribuidora,
            $descricao,
            $capa_url,
            $id_jogo
        );

        if ($stmt->execute()) {
            header("Location: jogos.php");
            exit;
        } else {
            $mensagem = "Erro ao atualizar jogo: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Jogo - Avalia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="topo">
    <div class="logo">
        <a href="../index.php">Avalia</a>
    </div>

    <div class="area-pesquisa">
        <form class="form-pesquisa" method="GET" action="jogos.php">
            <input 
                type="text" 
                name="busca" 
                placeholder="Pesquisar na tabela"
            >
            <button type="submit">🔍</button>
        </form>
    </div>

    <nav class="menu">
        <details class="dropdown-usuario">
            <summary class="icone-perfil" title="Menu do usuário">
                👤
            </summary>

            <div class="menu-dropdown">
                <p class="nome-dropdown">
                    <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                </p>

                <a href="../perfil.php">Editar perfil</a>
                <a href="jogos.php">Gerenciar jogos</a>
                <a href="../logout.php" class="sair-dropdown">Sair</a>
            </div>
        </details>
    </nav>
</header>

<main class="layout-form-jogo">
    <aside class="preview-capa" id="previewCapa">
        <?php if (!empty($jogo["capa_url"])): ?>
            <img 
                src="<?php echo htmlspecialchars($jogo["capa_url"]); ?>" 
                alt="Capa de <?php echo htmlspecialchars($jogo["titulo"]); ?>"
            >
        <?php else: ?>
            Capa do jogo
        <?php endif; ?>
    </aside>

    <section class="form-jogo">
        <h1>Editar Jogo</h1>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-formulario">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="jogo_editar.php?id=<?php echo $id_jogo; ?>">
            <div class="grupo-form">
                <label>Título</label>
                <input 
                    type="text" 
                    name="titulo" 
                    value="<?php echo htmlspecialchars($jogo["titulo"]); ?>" 
                    required
                >
            </div>

            <div class="linha-form">
                <div class="grupo-form">
                    <label>Gênero</label>
                    <input 
                        type="text" 
                        name="genero" 
                        value="<?php echo htmlspecialchars($jogo["genero"]); ?>" 
                        required
                    >
                </div>

                <div class="grupo-form">
                    <label>Data de lançamento</label>
                    <input 
                        type="date" 
                        name="data_lancamento" 
                        value="<?php echo htmlspecialchars($jogo["data_lancamento"]); ?>"
                    >
                </div>
            </div>

            <div class="linha-form">
                <div class="grupo-form">
                    <label>Desenvolvedora</label>
                    <input 
                        type="text" 
                        name="desenvolvedora" 
                        value="<?php echo htmlspecialchars($jogo["desenvolvedora"]); ?>"
                    >
                </div>

                <div class="grupo-form">
                    <label>Distribuidora</label>
                    <input 
                        type="text" 
                        name="distribuidora" 
                        value="<?php echo htmlspecialchars($jogo["distribuidora"]); ?>"
                    >
                </div>
            </div>

            <div class="grupo-form">
                <label>Descrição</label>
                <textarea name="descricao"><?php echo htmlspecialchars($jogo["descricao"]); ?></textarea>
            </div>

            <div class="grupo-form">
                <label>URL da imagem/capa</label>
                <input 
                    type="text" 
                    name="capa_url" 
                    id="campoCapa"
                    value="<?php echo htmlspecialchars($jogo["capa_url"]); ?>"
                    placeholder="Preferencialmente imagem vertical"
                >
            </div>

            <div class="acoes-form-jogo">
                <a class="botao-secundario" href="jogos.php">Cancelar</a>

                <button class="botao-principal" type="submit">
                    Salvar Alterações
                </button>
            </div>
        </form>

        <section class="area-excluir-jogo">
            <h2>Excluir Jogo</h2>

            <p>
                Ao excluir este jogo, suas avaliações também serão removidas.
            </p>

            <a 
                class="botao-perigo"
                href="jogo_excluir.php?id=<?php echo $jogo["id_jogo"]; ?>"
            >
                Excluir jogo
            </a>
        </section>
    </section>
</main>

<script>
    const campoCapa = document.getElementById("campoCapa");
    const previewCapa = document.getElementById("previewCapa");

    campoCapa.addEventListener("input", function() {
        const url = campoCapa.value.trim();

        if (url.length > 0) {
            previewCapa.innerHTML = `<img src="${url}" alt="Preview da capa">`;
        } else {
            previewCapa.innerHTML = "Capa do jogo";
        }
    });

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