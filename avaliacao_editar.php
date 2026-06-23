<?php
require_once "includes/verifica_login.php";
require_once "config/conexao.php";

$mensagem = "";
$id_usuario = $_SESSION["id_usuario"];

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id_avaliacao = intval($_GET["id"]);

$sql_avaliacao = "SELECT 
                    avaliacoes.*,
                    jogos.titulo,
                    jogos.capa_url
                  FROM avaliacoes
                  INNER JOIN jogos ON avaliacoes.id_jogo = jogos.id_jogo
                  WHERE avaliacoes.id_avaliacao = ?
                  LIMIT 1";

$stmt_avaliacao = $conn->prepare($sql_avaliacao);
$stmt_avaliacao->bind_param("i", $id_avaliacao);
$stmt_avaliacao->execute();

$resultado_avaliacao = $stmt_avaliacao->get_result();

if ($resultado_avaliacao->num_rows === 0) {
    echo "Avaliação não encontrada.";
    exit;
}

$avaliacao = $resultado_avaliacao->fetch_assoc();

$id_jogo = intval($avaliacao["id_jogo"]);

if (intval($avaliacao["id_usuario"]) !== intval($id_usuario)) {
    header("Location: jogo.php?id=" . $id_jogo);
    exit;
}

$jogo = [
    "id_jogo" => $id_jogo,
    "titulo" => $avaliacao["titulo"],
    "capa_url" => $avaliacao["capa_url"]
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

        if (!in_array($plataforma, ["PC", "Outro"], true)) {
            $especificacoes = "";
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

        $especificacoes = $especificacoes !== ""
            ? $especificacoes
            : null;

        $sql_update = "UPDATE avaliacoes
                       SET nota_geral = ?,
                           comentario_geral = ?,
                           nota_performance = ?,
                           plataforma = ?,
                           especificacoes = ?,
                           possui_bug = ?,
                           descricao_bug = ?
                       WHERE id_avaliacao = ?
                         AND id_usuario = ?";

        $stmt_update = $conn->prepare($sql_update);

        $stmt_update->bind_param(
            "isissssii",
            $nota_geral,
            $comentario_geral,
            $nota_performance,
            $plataforma,
            $especificacoes,
            $possui_bug,
            $descricao_bug,
            $id_avaliacao,
            $id_usuario
        );

        if ($stmt_update->execute()) {
            header(
                "Location: jogo.php?id=" .
                $id_jogo .
                "&avaliacao=editada#avaliacao-" .
                $id_avaliacao
            );
            exit;
        } else {
            $mensagem = "Não foi possível atualizar a avaliação.";
        }
    }
}

$valor_nota_geral =
    $_POST["nota_geral"] ??
    $avaliacao["nota_geral"];

$valor_comentario_geral =
    $_POST["comentario_geral"] ??
    $avaliacao["comentario_geral"] ??
    "";

$valor_possui_bug =
    $_POST["possui_bug"] ??
    $avaliacao["possui_bug"];

$valor_descricao_bug =
    $_POST["descricao_bug"] ??
    $avaliacao["descricao_bug"] ??
    "";

$valor_plataforma =
    $_POST["plataforma"] ??
    $avaliacao["plataforma"] ??
    "";

$valor_nota_performance =
    $_POST["nota_performance"] ??
    $avaliacao["nota_performance"] ??
    "";

$valor_especificacoes =
    $_POST["especificacoes"] ??
    $avaliacao["especificacoes"] ??
    "";

if ($valor_possui_bug === "nao") {
    $valor_descricao_bug = "";
}

if (!in_array($valor_plataforma, ["PC", "Outro"], true)) {
    $valor_especificacoes = "";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar avaliação de <?php echo htmlspecialchars($jogo["titulo"]); ?> - Avalia</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/assets/css/style.css'); ?>"
    >
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
                href="jogo.php?id=<?php echo $id_jogo; ?>#avaliacao-<?php echo $id_avaliacao; ?>"
            >
                Ver página do jogo
            </a>
        </aside>

        <section class="form-avaliacao">
            <header class="cabecalho-avaliacao">
                <span>Editar avaliação</span>

                <h1>
                    Editar avaliação de <?php echo htmlspecialchars($jogo["titulo"]); ?>
                </h1>

                <p>
                    Atualize sua nota, comentário, relato de bugs e informações
                    de performance.
                </p>
            </header>

            <?php if (!empty($mensagem)): ?>
                <p class="mensagem-formulario">
                    <?php echo htmlspecialchars($mensagem); ?>
                </p>
            <?php endif; ?>

            <form
                method="POST"
                action="avaliacao_editar.php?id=<?php echo $id_avaliacao; ?>"
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
                                <?php echo (string)$valor_nota_geral === "1"
                                    ? "selected"
                                    : ""; ?>
                            >
                                1 — Muito ruim
                            </option>

                            <option
                                value="2"
                                <?php echo (string)$valor_nota_geral === "2"
                                    ? "selected"
                                    : ""; ?>
                            >
                                2 — Ruim
                            </option>

                            <option
                                value="3"
                                <?php echo (string)$valor_nota_geral === "3"
                                    ? "selected"
                                    : ""; ?>
                            >
                                3 — Regular
                            </option>

                            <option
                                value="4"
                                <?php echo (string)$valor_nota_geral === "4"
                                    ? "selected"
                                    : ""; ?>
                            >
                                4 — Bom
                            </option>

                            <option
                                value="5"
                                <?php echo (string)$valor_nota_geral === "5"
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
                        ><?php echo htmlspecialchars($valor_comentario_geral); ?></textarea>
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
                                    <?php echo $valor_possui_bug === "nao"
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
                                    <?php echo $valor_possui_bug === "sim"
                                        ? "checked"
                                        : ""; ?>
                                >

                                <span>Encontrei bugs</span>
                            </label>
                        </div>
                    </div>

                    <?php
                    $encontrou_bug = $valor_possui_bug === "sim";
                    ?>

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
                        ><?php echo htmlspecialchars($valor_descricao_bug); ?></textarea>
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

                                $plataforma_selecionada = $valor_plataforma;
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
                                    <?php echo (string)$valor_nota_performance === "1"
                                        ? "selected"
                                        : ""; ?>
                                >
                                    1 — Muito ruim
                                </option>

                                <option
                                    value="2"
                                    <?php echo (string)$valor_nota_performance === "2"
                                        ? "selected"
                                        : ""; ?>
                                >
                                    2 — Ruim
                                </option>

                                <option
                                    value="3"
                                    <?php echo (string)$valor_nota_performance === "3"
                                        ? "selected"
                                        : ""; ?>
                                >
                                    3 — Regular
                                </option>

                                <option
                                    value="4"
                                    <?php echo (string)$valor_nota_performance === "4"
                                        ? "selected"
                                        : ""; ?>
                                >
                                    4 — Boa
                                </option>

                                <option
                                    value="5"
                                    <?php echo (string)$valor_nota_performance === "5"
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
                        ><?php echo htmlspecialchars($valor_especificacoes); ?></textarea>
                    </div>
                </section>

                <div class="acoes-avaliacao">
                    <a
                        class="botao-secundario"
                        href="jogo.php?id=<?php echo $id_jogo; ?>#avaliacao-<?php echo $id_avaliacao; ?>"
                    >
                        Cancelar
                    </a>

                    <button
                        class="botao-principal"
                        type="submit"
                    >
                        Salvar alterações
                    </button>
                </div>
            </form>
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