<?php
session_start();
require_once "config/conexao.php";

$busca = "";
$filtro_genero = "";
$filtro_nota = "";
$filtro_plataforma = "";

$origem = "index.php";

if (isset($_GET["busca"])) {
    $busca = trim($_GET["busca"]);
}

if (isset($_GET["genero"])) {
    $filtro_genero = trim($_GET["genero"]);
}

if (isset($_GET["nota"])) {
    $filtro_nota = trim($_GET["nota"]);
}

if (isset($_GET["plataforma"])) {
    $filtro_plataforma = trim($_GET["plataforma"]);
}

if (!in_array($filtro_nota, ["", "1", "2", "3", "4", "5"], true)) {
    $filtro_nota = "";
}

if (isset($_GET["origem"]) && !empty($_GET["origem"])) {
    $origem_recebida = $_GET["origem"];

    if (
        substr($origem_recebida, 0, 1) === "/" &&
        substr($origem_recebida, 0, 2) !== "//"
    ) {
        $origem = $origem_recebida;
    }
}

$filtros_avancados_ativos =
    !empty($filtro_genero) ||
    !empty($filtro_nota) ||
    !empty($filtro_plataforma);

$filtros_ativos =
    !empty($busca) ||
    $filtros_avancados_ativos;

$sql = "SELECT 
            jogos.*,
            medias.media_geral
        FROM jogos
        LEFT JOIN (
            SELECT 
                id_jogo,
                AVG(nota_geral) AS media_geral
            FROM avaliacoes
            WHERE visivel = 1
            GROUP BY id_jogo
        ) AS medias ON jogos.id_jogo = medias.id_jogo";

$condicoes = [];
$parametros = [];
$tipos = "";

if (!empty($busca)) {
    $busca_sql = "%" . $busca . "%";

    $condicoes[] = "(
        jogos.titulo LIKE ?
        OR jogos.genero LIKE ?
        OR jogos.desenvolvedora LIKE ?
    )";

    $parametros[] = $busca_sql;
    $parametros[] = $busca_sql;
    $parametros[] = $busca_sql;
    $tipos .= "sss";
}

if (!empty($filtro_genero)) {
    $condicoes[] = "jogos.genero = ?";
    $parametros[] = $filtro_genero;
    $tipos .= "s";
}

if (!empty($filtro_plataforma)) {
    $condicoes[] = "EXISTS (
        SELECT 1
        FROM avaliacoes a2
        WHERE a2.id_jogo = jogos.id_jogo
          AND a2.visivel = 1
          AND a2.plataforma = ?
    )";

    $parametros[] = $filtro_plataforma;
    $tipos .= "s";
}

if (!empty($filtro_nota)) {
    $condicoes[] = "medias.media_geral >= ?";
    $parametros[] = floatval($filtro_nota);
    $tipos .= "d";
}

if (!empty($condicoes)) {
    $sql .= " WHERE " . implode(" AND ", $condicoes);
}

$sql .= " ORDER BY jogos.titulo ASC";

if (!empty($parametros)) {
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param($tipos, ...$parametros);
        $stmt->execute();
        $resultado = $stmt->get_result();
    } else {
        $resultado = false;
    }
} else {
    $resultado = $conn->query($sql);
}

$titulo_pagina = $filtros_ativos ? "Resultados" : "Página Inicial";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Avalia</title>

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
        <?php if (isset($_SESSION["id_usuario"])): ?>

            <?php if ($_SESSION["tipo"] === "admin"): ?>
                <a class="botao-menu botao-admin-header" href="admin/jogos.php">
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

                    <a href="logout.php" class="sair-dropdown">
                        Sair
                    </a>
                </div>
            </details>

        <?php else: ?>

            <a class="botao-menu" href="login.php">
                Entrar
            </a>

            <a class="botao-menu botao-destaque" href="cadastro.php">
                Criar conta
            </a>

        <?php endif; ?>
    </nav>
</header>

<main class="container">
    <h1 class="titulo-pagina">
        <?php echo $titulo_pagina; ?>
    </h1>

    <?php if ($filtros_ativos): ?>
        <p class="mensagem-busca">
            Resultados encontrados

            <?php if (!empty($busca)): ?>
                para <strong><?php echo htmlspecialchars($busca); ?></strong>
            <?php endif; ?>

            <?php if (!empty($filtro_genero)): ?>
                · gênero:
                <strong><?php echo htmlspecialchars($filtro_genero); ?></strong>
            <?php endif; ?>

            <?php if (!empty($filtro_nota)): ?>
                · nota mínima:
                <strong><?php echo htmlspecialchars($filtro_nota); ?></strong>
            <?php endif; ?>

            <?php if (!empty($filtro_plataforma)): ?>
                · plataforma:
                <strong><?php echo htmlspecialchars($filtro_plataforma); ?></strong>
            <?php endif; ?>

            —
            <a href="<?php echo htmlspecialchars($origem); ?>">
                limpar busca
            </a>
        </p>
    <?php endif; ?>

    <h2 class="subtitulo">Jogos</h2>

    <?php if (!$resultado): ?>

        <p class="mensagem-vazia">
            Erro na consulta: <?php echo htmlspecialchars($conn->error); ?>
        </p>

    <?php elseif ($resultado->num_rows > 0): ?>

        <section class="grade-jogos">
            <?php while ($jogo = $resultado->fetch_assoc()): ?>
                <article class="card-jogo">
                    <a href="jogo.php?id=<?php echo $jogo["id_jogo"]; ?>">
                        <div class="capa-jogo">
                            <?php if (!empty($jogo["capa_url"])): ?>
                                <img 
                                    src="<?php echo htmlspecialchars($jogo["capa_url"]); ?>" 
                                    alt="Capa de <?php echo htmlspecialchars($jogo["titulo"]); ?>"
                                >
                            <?php else: ?>
                                <span>Capa Jogo</span>
                            <?php endif; ?>
                        </div>

                        <p class="nome-jogo">
                            <?php echo htmlspecialchars($jogo["titulo"]); ?>
                        </p>

                        <p class="info-jogo-card">
                            <?php echo htmlspecialchars($jogo["genero"]); ?>
                        </p>

                        <?php if ($jogo["media_geral"] !== null): ?>
                            <p class="info-jogo-card">
                                Nota média:
                                <?php echo number_format($jogo["media_geral"], 1, ",", "."); ?>
                                / 5
                            </p>
                        <?php endif; ?>
                    </a>
                </article>
            <?php endwhile; ?>
        </section>

    <?php else: ?>

        <p class="mensagem-vazia">
            Nenhum jogo encontrado.
        </p>

    <?php endif; ?>
</main>

<script>
    document.addEventListener("click", function(event) {
        const dropdownUsuario = document.querySelector(".dropdown-usuario");

        if (
            dropdownUsuario &&
            !dropdownUsuario.contains(event.target)
        ) {
            dropdownUsuario.removeAttribute("open");
        }

        const dropdownFiltros = document.querySelector(
            ".dropdown-filtros-pesquisa"
        );

        if (
            dropdownFiltros &&
            !dropdownFiltros.contains(event.target)
        ) {
            dropdownFiltros.removeAttribute("open");
        }
    });
</script>

</body>
</html>