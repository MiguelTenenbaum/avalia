<?php
session_start();
require_once "config/conexao.php";

$mensagem = "";

if (isset($_GET["cadastro"]) && $_GET["cadastro"] === "sucesso") {
    $mensagem = "Cadastro realizado com sucesso. Faça login.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    if (empty($email) || empty($senha)) {
        $mensagem = "Preencha todos os campos.";
    } else {
        $sql = "SELECT id_usuario, nome, email, senha, tipo FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            if (password_verify($senha, $usuario["senha"])) {
                $_SESSION["id_usuario"] = $usuario["id_usuario"];
                $_SESSION["nome"] = $usuario["nome"];
                $_SESSION["email"] = $usuario["email"];
                $_SESSION["tipo"] = $usuario["tipo"];

                header("Location: index.php");
                exit;
            } else {
                $mensagem = "E-mail ou senha incorretos.";
            }
        } else {
            $mensagem = "E-mail ou senha incorretos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Entrar - Avalia</title>
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
        <!--<a class="botao-menu" href="index.php">Catálogo</a>-->
        <a class="botao-menu botao-destaque" href="cadastro.php">Criar conta</a>
    </nav>
</header>

<main class="pagina-formulario">
    <section class="caixa-formulario">
        <h1>Login</h1>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-formulario">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="grupo-form">
                <label>E-mail</label>
                <input type="email" name="email" required>
            </div>

            <div class="grupo-form">
                <label>Senha</label>
                <input type="password" name="senha" required>
            </div>

            <button class="botao-principal" type="submit">Entrar</button>
        </form>

        <p class="texto-formulario">
            Ainda não possui uma conta?
            <a href="cadastro.php">Cadastre-se</a>
        </p>
    </section>
</main>

</body>
</html>