<?php
require_once "config/conexao.php";

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $mensagem = "Preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "E-mail inválido.";
    } elseif ($senha !== $confirmar_senha) {
        $mensagem = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $mensagem = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $sql_verifica = "SELECT id_usuario FROM usuarios WHERE email = ?";
        $stmt_verifica = $conn->prepare($sql_verifica);
        $stmt_verifica->bind_param("s", $email);
        $stmt_verifica->execute();
        $resultado = $stmt_verifica->get_result();

        if ($resultado->num_rows > 0) {
            $mensagem = "Este e-mail já está cadastrado.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'usuario')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nome, $email, $senha_hash);

            if ($stmt->execute()) {
                header("Location: login.php?cadastro=sucesso");
                exit;
            } else {
                $mensagem = "Erro ao cadastrar usuário.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta - Avalia</title>
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
        <a class="botao-menu botao-destaque" href="login.php">Entrar</a>
    </nav>
</header>

<main class="pagina-formulario">
    <section class="caixa-formulario">
        <h1>Cadastrar Usuário</h1>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-formulario">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="cadastro.php">
            <div class="grupo-form">
                <label>Nome de usuário</label>
                <input type="text" name="nome" required>
            </div>

            <div class="grupo-form">
                <label>E-mail</label>
                <input type="email" name="email" required>
            </div>

            <div class="grupo-form">
                <label>Senha</label>
                <input type="password" name="senha" required>
            </div>

            <div class="grupo-form">
                <label>Confirmar senha</label>
                <input type="password" name="confirmar_senha" required>
            </div>

            <button class="botao-principal" type="submit">Criar Conta</button>
        </form>

        <p class="texto-formulario">
            Já tem uma conta?
            <a href="login.php">Faça login</a>
        </p>
    </section>
</main>

</body>
</html>