<?php
require_once "includes/verifica_login.php";
require_once "config/conexao.php";

$mensagem = "";
$id_usuario = $_SESSION["id_usuario"];


if (!isset($_GET["id_jogo"]) || empty($_GET["id_jogo"])) {
    header("Location: index.php");
    exit;
}

$id_jogo = intval($_GET["id_jogo"]);

$sql_jogo = "SELECT id_jogo, titulo, capa_url
             FROM jogos
             WHERE id_jogo = ?";

$stmt_jogo = $conn->prepare($sql_jogo);
$stmt_jogo->bind_param("i", $id_jogo);
$stmt_jogo->execute();

$resultado_jogo = $stmt_jogo->get_result();

if ($resultado_jogo->num_rows === 0) {
    echo "Jogo não encontrado.";
    exit;
}

$jogo = $resultado_jogo->fetch_assoc();

$sql_verifica = "SELECT id_avaliacao
                 FROM avaliacoes
                 WHERE id_usuario = ? AND id_jogo = ?";

$stmt_verifica = $conn->prepare($sql_verifica);
$stmt_verifica->bind_param("ii", $id_usuario, $id_jogo);
$stmt_verifica->execute();

$resultado_verifica = $stmt_verifica->get_result();
$ja_avaliou = $resultado_verifica->num_rows > 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$ja_avaliou) {
    $nota_geral = intval($_POST["nota_geral"] ?? 0);
    $comentario_geral = trim($_POST["comentario_geral"] ?? "");

    $possui_bug = $_POST["possui_bug"] ?? "";
    $descricao_bug = trim($_POST["descricao_bug"] ?? "");

    $plataforma = trim($_POST["plataforma"] ?? "");
    $nota_performance = $_POST["nota_performance"] ?? "";
    $especificacoes = trim($_POST["especificacoes"] ?? "");

    $plataformas_validas = [
        "PC",
        "PlayStation 5",
        "PlayStation 4",
        "Xbox Series X|S",
        "Xbox One",
        "Nintendo Switch",
        "Steam Deck",
        "Outro"
    ];

    if ($nota_geral < 1 || $nota_geral > 5) {
        $mensagem = "Selecione uma nota geral entre 1 e 5.";
    } elseif (!in_array($possui_bug, ["sim", "nao"], true)) {
        $mensagem = "Informe se você encontrou bugs.";
    } elseif (
        !empty($plataforma) &&
        !in_array($plataforma, $plataformas_validas, true)
    ) {
        $mensagem = "A plataforma selecionada é inválida.";
    } elseif (
        $nota_performance !== "" &&
        (intval($nota_performance) < 1 || intval($nota_performance) > 5)
    ) {
        $mensagem = "A nota de performance deve estar entre 1 e 5.";
    } elseif (
        (!empty($plataforma) && $nota_performance === "") ||
        (empty($plataforma) && $nota_performance !== "")
    ) {
        $mensagem = "Para preencher o relatório de performance, informe a plataforma e a nota de performance.";
    } else {

        if ($possui_bug === "nao") {
            $descricao_bug = "";
        }

        $comentario_geral = $comentario_geral !== ""
            ? $comentario_geral
            : null;

        $descricao_bug = $descricao_bug !== ""
            ? $descricao_bug
            : null;

        $plataforma = $plataforma !== ""
            ? $plataforma
            : null;

        $nota_performance = $nota_performance !== ""
            ? intval($nota_performance)
            : null;


        if (!in_array($plataforma, ["PC", "Outro"], true)) {
            $especificacoes = "";
        }

        $especificacoes = $especificacoes !== ""
            ? $especificacoes
            : null;

        $sql_insert = "INSERT INTO avaliacoes
                       (
                           id_usuario,
                           id_jogo,
                           nota_geral,
                           comentario_geral,
                           nota_performance,
                           plataforma,
                           especificacoes,
                           possui_bug,
                           descricao_bug,
                           visivel
                       )
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

        $stmt_insert = $conn->prepare($sql_insert);

        $stmt_insert->bind_param(
            "iiisissss",
            $id_usuario,
            $id_jogo,
            $nota_geral,
            $comentario_geral,
            $nota_performance,
            $plataforma,
            $especificacoes,
            $possui_bug,
            $descricao_bug
        );

        if ($stmt_insert->execute()) {
            header(
                "Location: jogo.php?id=" .
                $id_jogo .
                "&avaliacao=sucesso"
            );
            exit;
        } else {
            $mensagem = "Não foi possível publicar a avaliação.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Avaliar <?php echo htmlspecialchars($jogo["titulo"]); ?> - Avalia</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/assets/css/style.css'); ?>">
</head>
<body>

<header class="topo">
    <div class="logo">
        <a href="index.php">Avalia</a>
    </div>
    
    <div class="area-pesquisa">
        <?php
        $caminho_base = "";
        require "includes/barra_pesquisa.php";
        ?>
    </div>

    <nav class="menu">
        <?php if ($_SESSION["tipo"] === "admin"): ?>
            <a
                class="botao-menu botao-admin-header"
                href="admin/jogos.php"
            >
                Gerenciar jogos
            </a>
        <?php endif; ?>

        <details class="dropdown-usuario">
            <summary class="icone-perfil" title="Menu do usuário">
                👤
            </summary>

            <div class="menu-dropdown">
                <p class="nome-dropdown">
                    <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                </p>

                <a href="usuario.php?id=<?php echo $_SESSION["id_usuario"]; ?>">
                    Acessar perfil
                </a>
                <a href="logout.php" class="sair-dropdown">Sair</a>
            </div>
        </details>
    </nav>
</header>

<main class="pagina-avaliacao">
    <section class="layout-avaliacao">

        <aside class="resumo-jogo-avaliacao">
            <div class="capa-avaliacao">
                <?php if (!empty($jogo["capa_url"])): ?>
                    <img
                        src="<?php echo htmlspecialchars($jogo["capa_url"]); ?>"
                        alt="Capa de <?php echo htmlspecialchars($jogo["titulo"]); ?>"
                    >
                <?php else: ?>
                    <span>Capa do jogo</span>
                <?php endif; ?>
            </div>

            <a
                class="link-voltar-jogo"
                href="jogo.php?id=<?php echo $id_jogo; ?>"
            >
                Ver página do jogo
            </a>
        </aside>

        <section class="form-avaliacao">
            <header class="cabecalho-avaliacao">
                <span>Nova avaliação</span>

                <h1>
                    Avaliar <?php echo htmlspecialchars($jogo["titulo"]); ?>
                </h1>

                <p>
                    Compartilhe sua experiência geral e, opcionalmente,
                    informe detalhes sobre bugs e performance.
                </p>
            </header>

            <?php if ($ja_avaliou): ?>

                <div class="aviso-avaliacao">
                    <h2>Você já avaliou este jogo</h2>

                    <p>
                        Cada usuário pode publicar apenas uma avaliação
                        para o mesmo jogo.
                    </p>

                    <a
                        class="botao-principal"
                        href="jogo.php?id=<?php echo $id_jogo; ?>"
                    >
                        Voltar para o jogo
                    </a>
                </div>

            <?php else: ?>

                <?php if (!empty($mensagem)): ?>
                    <p class="mensagem-formulario">
                        <?php echo htmlspecialchars($mensagem); ?>
                    </p>
                <?php endif; ?>

                <form
                    method="POST"
                    action="avaliar.php?id_jogo=<?php echo $id_jogo; ?>"
                >

                    <section class="secao-form-avaliacao">
                        <div class="titulo-secao-avaliacao">
                            <div>
                                <span class="numero-secao">1</span>
                            </div>

                            <div>
                                <h2>Avaliação geral</h2>

                                <p>
                                    Informe sua nota e descreva como foi
                                    sua experiência com o jogo.
                                </p>
                            </div>
                        </div>

                        <div class="grupo-form">
                            <label for="nota_geral">
                                Nota geral
                            </label>

                            <select
                                name="nota_geral"
                                id="nota_geral"
                                required
                            >
                                <option value="">
                                    Selecione uma nota
                                </option>

                                <option
                                    value="1"
                                    <?php echo ($_POST["nota_geral"] ?? "") === "1"
                                        ? "selected"
                                        : ""; ?>
                                >
                                    1 — Muito ruim
                                </option>

                                <option
                                    value="2"
                                    <?php echo ($_POST["nota_geral"] ?? "") === "2"
                                        ? "selected"
                                        : ""; ?>
                                >
                                    2 — Ruim
                                </option>

                                <option
                                    value="3"
                                    <?php echo ($_POST["nota_geral"] ?? "") === "3"
                                        ? "selected"
                                        : ""; ?>
                                >
                                    3 — Regular
                                </option>

                                <option
                                    value="4"
                                    <?php echo ($_POST["nota_geral"] ?? "") === "4"
                                        ? "selected"
                                        : ""; ?>
                                >
                                    4 — Bom
                                </option>

                                <option
                                    value="5"
                                    <?php echo ($_POST["nota_geral"] ?? "") === "5"
                                        ? "selected"
                                        : ""; ?>
                                >
                                    5 — Excelente
                                </option>
                            </select>
                        </div>

                        <div class="grupo-form">
                            <label for="comentario_geral">
                                Comentário
                                <span class="campo-opcional">(opcional)</span>
                            </label>

                            <textarea
                                name="comentario_geral"
                                id="comentario_geral"
                                placeholder="Conte como foi sua experiência com o jogo"
                            ><?php echo htmlspecialchars(
                                $_POST["comentario_geral"] ?? ""
                            ); ?></textarea>
                        </div>
                    </section>

                    <section class="secao-form-avaliacao">
                        <div class="titulo-secao-avaliacao">
                            <div>
                                <span class="numero-secao">2</span>
                            </div>

                            <div>
                                <h2>Relato de bugs</h2>

                                <p>
                                    Informe se encontrou problemas durante
                                    sua experiência com o jogo.
                                </p>
                            </div>
                        </div>

                        <div class="grupo-form">
                            <label>
                                Você encontrou bugs?
                            </label>

                            <div class="opcoes-radio">
                                <label class="opcao-radio">
                                    <input
                                        type="radio"
                                        name="possui_bug"
                                        value="nao"
                                        required
                                        <?php echo ($_POST["possui_bug"] ?? "") === "nao"
                                            ? "checked"
                                            : ""; ?>
                                    >

                                    <span>Não encontrei bugs</span>
                                </label>

                                <label class="opcao-radio">
                                    <input
                                        type="radio"
                                        name="possui_bug"
                                        value="sim"
                                        required
                                        <?php echo ($_POST["possui_bug"] ?? "") === "sim"
                                            ? "checked"
                                            : ""; ?>
                                    >

                                    <span>Encontrei bugs</span>
                                </label>
                            </div>
                        </div>

                        <?php $encontrou_bug = ($_POST["possui_bug"] ?? "") === "sim";?>

                        <div
                            class="grupo-form grupo-descricao-bug
                            <?php echo !$encontrou_bug ? "campo-desativado" : ""; ?>"
                            id="grupoDescricaoBug"
                        >
                            <label for="descricao_bug">
                                Descrição dos bugs
                                <span class="campo-opcional">(opcional)</span>
                            </label>

                            <textarea
                                name="descricao_bug"
                                id="descricao_bug"
                                placeholder="Descreva os problemas encontrados"
                                <?php echo !$encontrou_bug ? "disabled" : ""; ?>
                            ><?php echo htmlspecialchars(
                                $_POST["descricao_bug"] ?? ""
                            ); ?></textarea>
                        </div>
                    </section>

                    <section class="secao-form-avaliacao">
                        <div class="titulo-secao-avaliacao">
                            <div>
                                <span class="numero-secao">3</span>
                            </div>

                            <div>
                                <h2>Relatório de performance</h2>

                                <p>
                                    Esta seção é opcional. Caso seja preenchida,
                                    informe a plataforma e a nota de performance.
                                </p>
                            </div>
                        </div>

                        <div class="linha-avaliacao">
                            <div class="grupo-form">
                                <label for="plataforma">
                                    Plataforma
                                </label>

                                <select
                                    name="plataforma"
                                    id="plataforma"
                                >
                                    <option value="">
                                        Selecione a plataforma
                                    </option>

                                    <?php
                                    $plataformas_formulario = [
                                        "PC",
                                        "PlayStation 5",
                                        "PlayStation 4",
                                        "Xbox Series X|S",
                                        "Xbox One",
                                        "Nintendo Switch",
                                        "Steam Deck",
                                        "Outro"
                                    ];

                                    $plataforma_selecionada =
                                        $_POST["plataforma"] ?? "";
                                    ?>

                                    <?php foreach ($plataformas_formulario as $item): ?>
                                        <option
                                            value="<?php echo htmlspecialchars($item); ?>"
                                            <?php echo $plataforma_selecionada === $item
                                                ? "selected"
                                                : ""; ?>
                                        >
                                            <?php echo htmlspecialchars($item); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="grupo-form">
                                <label for="nota_performance">
                                    Nota de performance
                                </label>

                                <select
                                    name="nota_performance"
                                    id="nota_performance"
                                >
                                    <option value="">
                                        Selecione uma nota
                                    </option>

                                    <option
                                        value="1"
                                        <?php echo ($_POST["nota_performance"] ?? "") === "1"
                                            ? "selected"
                                            : ""; ?>
                                    >
                                        1 — Muito ruim
                                    </option>

                                    <option
                                        value="2"
                                        <?php echo ($_POST["nota_performance"] ?? "") === "2"
                                            ? "selected"
                                            : ""; ?>
                                    >
                                        2 — Ruim
                                    </option>

                                    <option
                                        value="3"
                                        <?php echo ($_POST["nota_performance"] ?? "") === "3"
                                            ? "selected"
                                            : ""; ?>
                                    >
                                        3 — Regular
                                    </option>

                                    <option
                                        value="4"
                                        <?php echo ($_POST["nota_performance"] ?? "") === "4"
                                            ? "selected"
                                            : ""; ?>
                                    >
                                        4 — Boa
                                    </option>

                                    <option
                                        value="5"
                                        <?php echo ($_POST["nota_performance"] ?? "") === "5"
                                            ? "selected"
                                            : ""; ?>
                                    >
                                        5 — Excelente
                                    </option>
                                </select>
                            </div>
                        </div>

                        <?php
                        $permite_especificacoes = in_array(
                            $plataforma_selecionada,
                            ["PC", "Outro"],
                            true
                        );
                        ?>

                        <div
                            class="grupo-form grupo-especificacoes
                            <?php echo !$permite_especificacoes ? "campo-desativado" : ""; ?>"
                            id="grupoEspecificacoes"
                        >
                            <label for="especificacoes">
                                Especificações do sistema
                                <span class="campo-opcional">(opcional)</span>
                            </label>

                            <textarea
                                name="especificacoes"
                                id="especificacoes"
                                placeholder="Ex.: Windows 11, Ryzen 5 5600, 16 GB RAM e RTX 3060"
                                <?php echo !$permite_especificacoes ? "disabled" : ""; ?>
                            ><?php echo htmlspecialchars(
                                $_POST["especificacoes"] ?? ""
                            ); ?></textarea>
                        </div>
                    </section>

                    <div class="acoes-avaliacao">
                        <a
                            class="botao-secundario"
                            href="jogo.php?id=<?php echo $id_jogo; ?>"
                        >
                            Cancelar
                        </a>

                        <button
                            class="botao-principal"
                            type="submit"
                        >
                            Publicar avaliação
                        </button>
                    </div>
                </form>

            <?php endif; ?>
        </section>
    </section>
</main>

<script>
    document.addEventListener("click", function(event) {
        const dropdown = document.querySelector(".dropdown-usuario");

        if (dropdown && !dropdown.contains(event.target)) {
            dropdown.removeAttribute("open");
        }
    });
</script>

<script>
    const opcoesBug = document.querySelectorAll(
        'input[name="possui_bug"]'
    );

    const campoDescricaoBug = document.getElementById(
        "descricao_bug"
    );

    const grupoDescricaoBug = document.getElementById(
        "grupoDescricaoBug"
    );

    function atualizarCampoDescricaoBug() {
        const opcaoSelecionada = document.querySelector(
            'input[name="possui_bug"]:checked'
        );

        const encontrouBug =
            opcaoSelecionada &&
            opcaoSelecionada.value === "sim";

        campoDescricaoBug.disabled = !encontrouBug;

        grupoDescricaoBug.classList.toggle(
            "campo-desativado",
            !encontrouBug
        );

        if (!encontrouBug) {
            campoDescricaoBug.value = "";
        }
    }

    opcoesBug.forEach(function (opcao) {
        opcao.addEventListener(
            "change",
            atualizarCampoDescricaoBug
        );
    });

    atualizarCampoDescricaoBug();
    const campoPlataforma = document.getElementById(
    "plataforma"
    );

    const campoEspecificacoes = document.getElementById(
        "especificacoes"
    );

    const grupoEspecificacoes = document.getElementById(
        "grupoEspecificacoes"
    );

    function atualizarCampoEspecificacoes() {
        const plataformasPermitidas = [
            "PC",
            "Outro"
        ];

        const permiteEspecificacoes =
            plataformasPermitidas.includes(
                campoPlataforma.value
            );

        campoEspecificacoes.disabled =
            !permiteEspecificacoes;

        grupoEspecificacoes.classList.toggle(
            "campo-desativado",
            !permiteEspecificacoes
        );

        if (!permiteEspecificacoes) {
            campoEspecificacoes.value = "";
        }
    }

    campoPlataforma.addEventListener(
        "change",
        atualizarCampoEspecificacoes
    );

    atualizarCampoEspecificacoes();
</script>
</body>
</html>