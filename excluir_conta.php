<?php
require_once "includes/verifica_login.php";
require_once "config/conexao.php";

$mensagem = "";
$id_usuario = $_SESSION["id_usuario"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $senha_atual = $_POST["senha_atual"] ?? "";
    $confirmar_exclusao = $_POST["confirmar_exclusao"] ?? "";

    if (empty($senha_atual)) {
        $mensagem = "Informe sua senha atual para excluir a conta.";
    } elseif ($confirmar_exclusao !== "sim") {
        $mensagem = "Você precisa confirmar que deseja excluir a conta.";
    } else {
        $sql = "SELECT senha FROM usuarios WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            $mensagem = "Usuário não encontrado.";
        } else {
            $usuario = $resultado->fetch_assoc();

            if (!password_verify($senha_atual, $usuario["senha"])) {
                $mensagem = "Senha atual incorreta.";
            } else {
                $sql_delete = "DELETE FROM usuarios WHERE id_usuario = ?";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("i", $id_usuario);

                if ($stmt_delete->execute()) {
                    session_unset();
                    session_destroy();

                    header("Location: index.php?conta=excluida");
                    exit;
                } else {
                    $mensagem = "Erro ao excluir conta: " . $stmt_delete->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Excluir Conta - Avalia</title>
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

<main class="pagina-formulario">
    <section class="caixa-confirmacao">
        <h1>Excluir Conta</h1>

        <div class="aviso-perigo">
            <strong>Atenção:</strong> esta ação não poderá ser desfeita.
            Ao excluir sua conta, suas avaliações também serão removidas.
        </div>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-formulario">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="excluir_conta.php">
            <div class="grupo-form">
                <label>Digite sua senha atual para confirmar</label>
                <input type="password" name="senha_atual" required>
            </div>

            <label class="checkbox-confirmacao">
                <input type="checkbox" name="confirmar_exclusao" value="sim" required>
                <span>Confirmo que desejo excluir minha conta permanentemente.</span>
            </label>

            <div class="botoes-confirmacao">
                <a class="botao-secundario" href="perfil.php">Cancelar</a>

                <button 
                    class="botao-perigo" 
                    type="submit"
                    onclick="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não poderá ser desfeita.');"
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