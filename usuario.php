<?php
session_start();
require_once "config/conexao.php";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    if (isset($_SESSION["id_usuario"])) {
        header("Location: usuario.php?id=" . $_SESSION["id_usuario"]);
        exit;
    }

    header("Location: index.php");
    exit;
}

$id_usuario_perfil = intval($_GET["id"]);

$sql_usuario = "SELECT id_usuario, nome, tipo
                FROM usuarios
                WHERE id_usuario = ?";

$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id_usuario_perfil);
$stmt_usuario->execute();

$resultado_usuario = $stmt_usuario->get_result();

if ($resultado_usuario->num_rows === 0) {
    echo "Usuário não encontrado.";
    exit;
}

$usuario_perfil = $resultado_usuario->fetch_assoc();

$sql_resumo = "SELECT
                    COUNT(*) AS total_avaliacoes,
                    AVG(nota_geral) AS media_geral,
                    SUM(CASE WHEN possui_bug = 'sim' THEN 1 ELSE 0 END) AS total_com_bugs
               FROM avaliacoes
               WHERE id_usuario = ?
                 AND visivel = 1";

$stmt_resumo = $conn->prepare($sql_resumo);
$stmt_resumo->bind_param("i", $id_usuario_perfil);
$stmt_resumo->execute();

$resumo = $stmt_resumo->get_result()->fetch_assoc();

$sql_avaliacoes = "SELECT
                        avaliacoes.*,
                        jogos.id_jogo,
                        jogos.titulo,
                        jogos.genero,
                        jogos.capa_url
                   FROM avaliacoes
                   INNER JOIN jogos ON avaliacoes.id_jogo = jogos.id_jogo
                   WHERE avaliacoes.id_usuario = ?
                     AND avaliacoes.visivel = 1
                   ORDER BY avaliacoes.data_avaliacao DESC";

$stmt_avaliacoes = $conn->prepare($sql_avaliacoes);
$stmt_avaliacoes->bind_param("i", $id_usuario_perfil);
$stmt_avaliacoes->execute();

$resultado_avaliacoes = $stmt_avaliacoes->get_result();

$perfil_do_usuario_logado =
    isset($_SESSION["id_usuario"]) &&
    intval($_SESSION["id_usuario"]) === $id_usuario_perfil;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <title>
        Perfil de <?php echo htmlspecialchars($usuario_perfil["nome"]); ?> - Avalia
    </title>

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

                    <a href="logout.php" class="sair-dropdown">
                        Sair
                    </a>
                </div>
            </details>

        <?php else: ?>

            <a class="botao-menu" href="login.php">Entrar</a>

            <a class="botao-menu botao-destaque" href="cadastro.php">
                Criar conta
            </a>

        <?php endif; ?>
    </nav>
</header>

<main class="pagina-usuario">
    <section class="cabecalho-usuario">
        <div class="avatar-usuario">
            👤
        </div>

        <div class="info-usuario-publico">
            <span class="rotulo-perfil">
                Perfil de usuário
            </span>

            <h1>
                <?php echo htmlspecialchars($usuario_perfil["nome"]); ?>
            </h1>

            <?php if ($usuario_perfil["tipo"] === "admin"): ?>
                <span class="selo-admin">
                    Administrador
                </span>
            <?php endif; ?>

            <?php if ($perfil_do_usuario_logado): ?>
                <a class="link-editar-perfil-publico" href="perfil.php">
                    Editar meu perfil
                </a>
            <?php endif; ?>
        </div>
    </section>

    <section class="resumo-usuario">
        <div class="item-resumo-usuario">
            <span>Avaliações</span>

            <strong>
                <?php echo intval($resumo["total_avaliacoes"]); ?>
            </strong>
        </div>

        <div class="item-resumo-usuario">
            <span>Média das notas</span>

            <strong>
                <?php if ($resumo["media_geral"]): ?>
                    <?php echo number_format($resumo["media_geral"], 1, ",", "."); ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </strong>
        </div>

        <div class="item-resumo-usuario">
            <span>Relatos de bugs</span>

            <strong>
                <?php echo intval($resumo["total_com_bugs"]); ?>
            </strong>
        </div>
    </section>

    <section class="avaliacoes-usuario">
        <h2>
            Avaliações publicadas
        </h2>

        <?php if ($resultado_avaliacoes->num_rows > 0): ?>

            <div class="lista-avaliacoes-usuario">
                <?php while ($avaliacao = $resultado_avaliacoes->fetch_assoc()): ?>
                    <a
                        class="card-avaliacao-usuario"
                        href="jogo.php?id=<?php echo $avaliacao["id_jogo"]; ?>#avaliacao-<?php echo $avaliacao["id_avaliacao"]; ?>"
                    >
                        <div class="capa-avaliacao-usuario">
                            <?php if (!empty($avaliacao["capa_url"])): ?>
                                <img
                                    src="<?php echo htmlspecialchars($avaliacao["capa_url"]); ?>"
                                    alt="Capa de <?php echo htmlspecialchars($avaliacao["titulo"]); ?>"
                                >
                            <?php else: ?>
                                <span>Capa</span>
                            <?php endif; ?>
                        </div>

                        <div class="conteudo-avaliacao-usuario">
                            <div class="topo-card-usuario">
                                <div>
                                    <h3>
                                        <?php echo htmlspecialchars($avaliacao["titulo"]); ?>
                                    </h3>

                                    <span>
                                        <?php echo htmlspecialchars($avaliacao["genero"]); ?>
                                    </span>
                                </div>

                                <div class="nota-card-usuario">
                                    <?php echo htmlspecialchars($avaliacao["nota_geral"]); ?>/5
                                </div>
                            </div>

                            <?php if (!empty($avaliacao["comentario_geral"])): ?>
                                <p>
                                    <?php echo nl2br(
                                        htmlspecialchars($avaliacao["comentario_geral"])
                                    ); ?>
                                </p>
                            <?php else: ?>
                                <p class="texto-nao-informado">
                                    O usuário não escreveu um comentário.
                                </p>
                            <?php endif; ?>

                            <div class="metadados-avaliacao-usuario">
                                <span>
                                    <?php echo date(
                                        "d/m/Y H:i",
                                        strtotime($avaliacao["data_avaliacao"])
                                    ); ?>
                                </span>

                                <?php if (!empty($avaliacao["plataforma"])): ?>
                                    <span>
                                        <?php echo htmlspecialchars($avaliacao["plataforma"]); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($avaliacao["nota_performance"])): ?>
                                    <span>
                                        Performance:
                                        <?php echo htmlspecialchars($avaliacao["nota_performance"]); ?>/5
                                    </span>
                                <?php endif; ?>

                                <span>
                                    Bugs:
                                    <?php echo $avaliacao["possui_bug"] === "sim"
                                        ? "Sim"
                                        : "Não"; ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

        <?php else: ?>

            <p class="mensagem-vazia">
                Este usuário ainda não possui avaliações publicadas.
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

        if (!dropdown.contains(event.target)) {
            dropdown.removeAttribute("open");
        }
    });
</script>

</body>
</html>