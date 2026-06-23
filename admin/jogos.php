<?php
require_once "../includes/verifica_admin.php";
require_once "../config/conexao.php";

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
               OR distribuidora LIKE ?
            ORDER BY titulo ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $busca_sql, $busca_sql, $busca_sql, $busca_sql);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $sql = "SELECT * FROM jogos ORDER BY titulo ASC";
    $resultado = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Jogos - Avalia</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
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
                value="<?php echo htmlspecialchars($busca); ?>"
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

<main class="area-admin">
    <section class="topo-admin">
        <h1>Gerenciar Jogos</h1>

        <a class="botao-admin" href="jogo_cadastrar.php">
            + Adicionar jogo
        </a>
    </section>
    <?php if (!empty($busca)): ?>
        <p class="mensagem-admin">
            Resultados para: <strong><?php echo htmlspecialchars($busca); ?></strong>
            —
            <a class="link-admin" href="jogos.php">limpar busca</a>
        </p>
        <br>
    <?php endif; ?>

    <?php if (!$resultado): ?>

        <p class="mensagem-admin">
            Erro ao buscar jogos: <?php echo htmlspecialchars($conn->error); ?>
        </p>

    <?php elseif ($resultado->num_rows > 0): ?>

        <table class="tabela-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Gênero</th>
                    <th>Lançamento</th>
                    <th>Desenvolvedora</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($jogo = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $jogo["id_jogo"]; ?></td>

                        <td><?php echo htmlspecialchars($jogo["titulo"]); ?></td>

                        <td><?php echo htmlspecialchars($jogo["genero"]); ?></td>

                        <td>
                            <?php echo !empty($jogo["data_lancamento"]) 
                                ? date("d/m/Y", strtotime($jogo["data_lancamento"])) 
                                : "-"; ?>
                        </td>

                        <td>
                            <?php echo !empty($jogo["desenvolvedora"]) 
                                ? htmlspecialchars($jogo["desenvolvedora"]) 
                                : "-"; ?>
                        </td>

                        <td>
                            <div class="acoes-admin">
                                <a 
                                    class="link-admin" 
                                    href="../jogo.php?id=<?php echo $jogo["id_jogo"]; ?>"
                                >
                                    Visualizar
                                </a>

                                <a 
                                    class="link-admin" 
                                    href="jogo_editar.php?id=<?php echo $jogo["id_jogo"]; ?>&origem=gerenciar"
                                >
                                    Editar
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p class="mensagem-admin">
            Nenhum jogo cadastrado.
        </p>

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