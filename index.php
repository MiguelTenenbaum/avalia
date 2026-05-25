<?php
session_start();
require_once "config/conexao.php";

$busca = "";

if (isset($_GET["busca"])) {
    $busca = trim($_GET["busca"]);
}

if (!empty($busca)) {
    $busca_sql = "%" . $busca . "%";

    $sql = "SELECT * FROM jogos 
            WHERE titulo LIKE ? 
               OR genero LIKE ? 
               OR desenvolvedora LIKE ?
            ORDER BY titulo ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $busca_sql, $busca_sql, $busca_sql);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $sql = "SELECT * FROM jogos ORDER BY titulo ASC";
    $resultado = $conn->query($sql);
}

$titulo_pagina = !empty($busca) ? "Resultados" : "Página Inicial";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Avalia</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="topo">
    <div class="logo">
        <a href="index.php">Avalia</a>
    </div>

    <div class="area-pesquisa">
        <form class="form-pesquisa" method="GET" action="index.php">
            <input 
                type="text" 
                name="busca" 
                placeholder="Pesquisar por jogo"
                value="<?php echo htmlspecialchars($busca); ?>"
            >
            <button type="submit">🔍</button>
        </form>
    </div>

    <nav class="menu">
        <?php if (isset($_SESSION["id_usuario"])): ?>

            <details class="dropdown-usuario">
                <summary class="icone-perfil" title="Menu do usuário">
                    👤
                </summary>

                <div class="menu-dropdown">
                    <p class="nome-dropdown">
                        <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                    </p>

                    <a href="perfil.php">Editar perfil</a>

                    <?php if ($_SESSION["tipo"] === "admin"): ?>
                        <a href="admin/jogos.php">Gerenciar jogos</a>
                    <?php endif; ?>

                    <a href="logout.php" class="sair-dropdown">Sair</a>
                </div>
            </details>

        <?php else: ?>

            <a class="botao-menu" href="login.php">Entrar</a>
            <a class="botao-menu botao-destaque" href="cadastro.php">Criar conta</a>

        <?php endif; ?>
    </nav>
</header>

<main class="container">
    <h1 class="titulo-pagina"><?php echo $titulo_pagina; ?></h1>

    <?php if (!empty($busca)): ?>
        <p class="mensagem-busca">
            Resultados para: <strong><?php echo htmlspecialchars($busca); ?></strong>
            —
            <a href="index.php">limpar busca</a>
        </p>
    <?php endif; ?>

    <h2 class="subtitulo">Jogos</h2>

    <?php if (!$resultado): ?>

        <p class="mensagem-vazia">
            Erro na consulta: <?php echo $conn->error; ?>
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
                    </a>
                </article>
            <?php endwhile; ?>
        </section>

    <?php else: ?>

        <p class="mensagem-vazia">Nenhum jogo encontrado.</p>

    <?php endif; ?>
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