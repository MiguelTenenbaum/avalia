<?php
require_once "includes/verifica_login.php";
require_once "config/conexao.php";

$mensagem = "";
$id_usuario = $_SESSION["id_usuario"];

$sql = "SELECT id_usuario, nome, email, tipo FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Usuário não encontrado.";
    exit;
}

$usuario = $resultado->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha_atual = $_POST["senha_atual"];
    $nova_senha = $_POST["nova_senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if (empty($nome) || empty($email)) {
        $mensagem = "Nome e e-mail são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "E-mail inválido.";
    } elseif (empty($senha_atual)) {
        $mensagem = "Informe sua senha atual para salvar as alterações.";
    } else {
        // Verifica se o e-mail já está sendo usado por outro usuário
        $sql_verifica = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
        $stmt_verifica = $conn->prepare($sql_verifica);
        $stmt_verifica->bind_param("si", $email, $id_usuario);
        $stmt_verifica->execute();
        $resultado_verifica = $stmt_verifica->get_result();

        if ($resultado_verifica->num_rows > 0) {
            $mensagem = "Este e-mail já está sendo usado por outro usuário.";
        } else {
            // Busca a senha atual salva no banco
            $sql_senha = "SELECT senha FROM usuarios WHERE id_usuario = ?";
            $stmt_senha = $conn->prepare($sql_senha);
            $stmt_senha->bind_param("i", $id_usuario);
            $stmt_senha->execute();
            $dados_senha = $stmt_senha->get_result()->fetch_assoc();

            if (!password_verify($senha_atual, $dados_senha["senha"])) {
                $mensagem = "Senha atual incorreta.";
            } else {
                // Se o usuário preencheu nova senha ou confirmação, então quer trocar a senha
                if (!empty($nova_senha) || !empty($confirmar_senha)) {
                    if ($nova_senha !== $confirmar_senha) {
                        $mensagem = "A nova senha e a confirmação não coincidem.";
                    } elseif (strlen($nova_senha) < 6) {
                        $mensagem = "A nova senha deve ter pelo menos 6 caracteres.";
                    } else {
                        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                        $sql_update = "UPDATE usuarios 
                                       SET nome = ?, email = ?, senha = ?
                                       WHERE id_usuario = ?";

                        $stmt_update = $conn->prepare($sql_update);
                        $stmt_update->bind_param("sssi", $nome, $email, $senha_hash, $id_usuario);

                        if ($stmt_update->execute()) {
                            $_SESSION["nome"] = $nome;
                            $_SESSION["email"] = $email;

                            $mensagem = "Perfil e senha atualizados com sucesso.";

                            $usuario["nome"] = $nome;
                            $usuario["email"] = $email;
                        } else {
                            $mensagem = "Erro ao atualizar perfil.";
                        }
                    }
                } else {
                    // Se não preencheu nova senha, altera apenas nome/e-mail
                    $sql_update = "UPDATE usuarios 
                                   SET nome = ?, email = ?
                                   WHERE id_usuario = ?";

                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("ssi", $nome, $email, $id_usuario);

                    if ($stmt_update->execute()) {
                        $_SESSION["nome"] = $nome;
                        $_SESSION["email"] = $email;

                        $mensagem = "Perfil atualizado com sucesso.";

                        $usuario["nome"] = $nome;
                        $usuario["email"] = $email;
                    } else {
                        $mensagem = "Erro ao atualizar perfil.";
                    }
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
    <title>Meu Perfil - Avalia</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="topo">
    <div class="logo">
        <a href="index.php">Avalia</a>
    </div>

    <div class="area-pesquisa"></div>

    <nav class="menu">
        <a class="botao-menu" href="index.php">Catálogo</a>

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
    </nav>
</header>

<main class="pagina-formulario">
    <section class="caixa-perfil">
        <h1>Editar Perfil</h1>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-formulario">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="perfil.php">
            <div class="grupo-form">
                <label>Nome de usuário</label>
                <input 
                    type="text" 
                    name="nome" 
                    value="<?php echo htmlspecialchars($usuario["nome"]); ?>" 
                    required
                >
            </div>

            <div class="grupo-form">
                <label>E-mail</label>
                <input 
                    type="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($usuario["email"]); ?>" 
                    required
                >
            </div>

            <section class="secao-senha">
                <h2>Alterar senha</h2>

                <p class="texto-ajuda">
                    Para salvar qualquer alteração, informe sua senha atual.
                    Preencha a nova senha somente se quiser trocar a senha.
                </p>

                <div class="grupo-form">
                    <label>Senha atual</label>
                    <input type="password" name="senha_atual" required>
                </div>

                <div class="grupo-form">
                    <label>Nova senha</label>
                    <input type="password" name="nova_senha">
                </div>

                <div class="grupo-form">
                    <label>Confirmar nova senha</label>
                    <input type="password" name="confirmar_senha">
                </div>
            </section>

            <button class="botao-principal" type="submit">
                Salvar Alterações
            </button>
        </form>

        <section class="area-excluir-conta">
            <h2>Excluir minha conta</h2>

            <p class="texto-perigo">
                Ao excluir sua conta, suas avaliações também serão removidas.
            </p>

            <a class="botao-perigo" href="excluir_conta.php">
                Excluir minha conta
            </a>
        </section>
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