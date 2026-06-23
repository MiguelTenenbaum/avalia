<?php
require_once "../includes/verifica_admin.php";
require_once "../config/conexao.php";

$mensagem = "";

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

        $sql = "INSERT INTO jogos 
                (titulo, genero, data_lancamento, desenvolvedora, distribuidora, descricao, capa_url)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssss",
            $titulo,
            $genero,
            $data_lancamento,
            $desenvolvedora,
            $distribuidora,
            $descricao,
            $capa_url
        );

        if ($stmt->execute()) {
            header("Location: jogos.php");
            exit;
        } else {
            $mensagem = "Erro ao cadastrar jogo: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Jogo - Avalia</title>
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

<main class="layout-form-jogo">
    <aside class="preview-capa" id="previewCapa">
        Capa do jogo
    </aside>

    <section class="form-jogo">
        <h1>Cadastrar Jogo</h1>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-formulario">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="jogo_cadastrar.php">
            <div class="grupo-form">
                <label>Título</label>
                <input type="text" name="titulo" required>
            </div>

            <div class="linha-form">
                <div class="grupo-form">
                    <label>Gênero</label>
                    <input type="text" name="genero" required>
                </div>

                <div class="grupo-form">
                    <label>Data de lançamento</label>
                    <input type="date" name="data_lancamento">
                </div>
            </div>

            <div class="linha-form">
                <div class="grupo-form">
                    <label>Desenvolvedora</label>
                    <input type="text" name="desenvolvedora">
                </div>

                <div class="grupo-form">
                    <label>Distribuidora</label>
                    <input type="text" name="distribuidora">
                </div>
            </div>

            <div class="grupo-form">
                <label>Descrição</label>
                <textarea name="descricao"></textarea>
            </div>

            <div class="grupo-form">
                <label>URL da imagem/capa</label>
                <input 
                    type="text" 
                    name="capa_url" 
                    id="campoCapa"
                    placeholder="Preferencialmente imagem vertical"
                >
            </div>

            <div class="acoes-form-jogo">
                <a class="botao-secundario" href="jogos.php">Cancelar</a>

                <button class="botao-principal" type="submit">
                    Adicionar Jogo
                </button>
            </div>
        </form>
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