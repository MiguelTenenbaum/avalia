<?php
session_start();
require_once "config/conexao.php";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id_jogo = intval($_GET["id"]);

$sql_jogo = "SELECT * FROM jogos WHERE id_jogo = ?";
$stmt_jogo = $conn->prepare($sql_jogo);
$stmt_jogo->bind_param("i", $id_jogo);
$stmt_jogo->execute();
$resultado_jogo = $stmt_jogo->get_result();

if ($resultado_jogo->num_rows === 0) {
    echo "Jogo não encontrado.";
    exit;
}

$jogo = $resultado_jogo->fetch_assoc();

$sql_medias = "SELECT 
                    AVG(nota_geral) AS media_geral,
                    AVG(nota_performance) AS media_performance,
                    COUNT(*) AS total_avaliacoes,
                    SUM(CASE WHEN possui_bug = 'sim' THEN 1 ELSE 0 END) AS total_com_bugs,
                    ROUND(
                        (SUM(CASE WHEN possui_bug = 'sim' THEN 1 ELSE 0 END) / COUNT(*)) * 100,
                        1
                    ) AS percentual_bugs
               FROM avaliacoes
               WHERE id_jogo = ? AND visivel = 1";

$stmt_medias = $conn->prepare($sql_medias);
$stmt_medias->bind_param("i", $id_jogo);
$stmt_medias->execute();
$medias = $stmt_medias->get_result()->fetch_assoc();

$sql_avaliacoes = "SELECT 
                        avaliacoes.*,
                        usuarios.nome
                   FROM avaliacoes
                   INNER JOIN usuarios ON avaliacoes.id_usuario = usuarios.id_usuario
                   WHERE avaliacoes.id_jogo = ? AND avaliacoes.visivel = 1
                   ORDER BY avaliacoes.data_avaliacao DESC";

$stmt_avaliacoes = $conn->prepare($sql_avaliacoes);
$stmt_avaliacoes->bind_param("i", $id_jogo);
$stmt_avaliacoes->execute();
$resultado_avaliacoes = $stmt_avaliacoes->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($jogo["titulo"]); ?> - Avalia</title>
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
                        <a href="admin/jogo_editar.php?id=<?php echo $jogo["id_jogo"]; ?>">Editar este jogo</a>
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

<main class="pagina-jogo">
    <section class="layout-jogo">
        <div class="capa-detalhe">
            <?php if (!empty($jogo["capa_url"])): ?>
                <img 
                    src="<?php echo htmlspecialchars($jogo["capa_url"]); ?>" 
                    alt="Capa de <?php echo htmlspecialchars($jogo["titulo"]); ?>"
                >
            <?php else: ?>
                <span>Capa Jogo</span>
            <?php endif; ?>
        </div>

        <section class="info-detalhe">
            <h1><?php echo htmlspecialchars($jogo["titulo"]); ?></h1>

            <p>
                <strong>Gênero:</strong>
                <?php echo htmlspecialchars($jogo["genero"]); ?>
            </p>

            <p>
                <strong>Data de lançamento:</strong>
                <?php echo !empty($jogo["data_lancamento"]) 
                    ? date("d/m/Y", strtotime($jogo["data_lancamento"])) 
                    : "Não informada"; ?>
            </p>

            <p>
                <strong>Desenvolvedora:</strong>
                <?php echo htmlspecialchars($jogo["desenvolvedora"]); ?>
            </p>

            <p>
                <strong>Distribuidora:</strong>
                <?php echo htmlspecialchars($jogo["distribuidora"]); ?>
            </p>

            <div class="descricao-jogo">
                <h2>Descrição</h2>
                <p>
                    <?php echo nl2br(htmlspecialchars($jogo["descricao"])); ?>
                </p>
            </div>
        </section>

        <aside class="caixa-notas">
            <h2>Avaliações</h2>

            <div class="item-nota">
                <span>Nota geral</span>

                <?php if ($medias["media_geral"]): ?>
                    <div class="valor-nota">
                        <?php echo number_format($medias["media_geral"], 1, ",", "."); ?>
                    </div>
                    <small>/ 5</small>
                <?php else: ?>
                    <p class="texto-sem-nota">Sem avaliações</p>
                <?php endif; ?>
            </div>

            <div class="item-nota">
                <span>Performance</span>

                <?php if ($medias["media_performance"]): ?>
                    <div class="valor-nota">
                        <?php echo number_format($medias["media_performance"], 1, ",", "."); ?>
                    </div>
                    <small>/ 5</small>
                <?php else: ?>
                    <p class="texto-sem-nota">Sem avaliações</p>
                <?php endif; ?>
            </div>

            <div class="item-nota">
                <span>Relatos de bugs</span>

                <?php if ($medias["total_avaliacoes"] > 0): ?>
                    <div class="valor-nota">
                        <?php echo number_format($medias["percentual_bugs"], 1, ",", "."); ?>%
                    </div>

                    <small>
                        <?php echo $medias["total_com_bugs"]; ?> de 
                        <?php echo $medias["total_avaliacoes"]; ?> avaliações
                    </small>
                <?php else: ?>
                    <p class="texto-sem-nota">Sem avaliações</p>
                <?php endif; ?>
            </div>
        </aside>
    </section>

    <section class="secao-avaliacoes">
        <h2>Avaliações dos usuários</h2>

        <?php if ($resultado_avaliacoes->num_rows > 0): ?>

            <?php while ($avaliacao = $resultado_avaliacoes->fetch_assoc()): ?>
                <article class="card-avaliacao">
                    <div class="topo-avaliacao">
                        <span class="usuario-avaliacao">
                            <?php echo htmlspecialchars($avaliacao["nome"]); ?>
                        </span>

                        <span class="data-avaliacao">
                            <?php echo date("d/m/Y H:i", strtotime($avaliacao["data_avaliacao"])); ?>
                        </span>
                    </div>

                    <p>
                        <strong>Nota geral:</strong>
                        <?php echo htmlspecialchars($avaliacao["nota_geral"]); ?> / 5
                    </p>

                    <?php if (!empty($avaliacao["comentario_geral"])): ?>
                        <p>
                            <?php echo nl2br(htmlspecialchars($avaliacao["comentario_geral"])); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($avaliacao["nota_performance"])): ?>
                        <p>
                            <strong>Performance:</strong>
                            <?php echo htmlspecialchars($avaliacao["nota_performance"]); ?> / 5
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($avaliacao["plataforma"])): ?>
                        <p>
                            <strong>Plataforma:</strong>
                            <?php echo htmlspecialchars($avaliacao["plataforma"]); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($avaliacao["possui_bug"] === "sim"): ?>
                        <p>
                            <strong>Relato de bug:</strong><br>
                            <?php echo nl2br(htmlspecialchars($avaliacao["descricao_bug"])); ?>
                        </p>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>

        <?php else: ?>

            <p class="mensagem-vazia">
                Este jogo ainda não possui avaliações.
            </p>

        <?php endif; ?>
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