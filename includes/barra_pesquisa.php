<?php
$caminho_base = $caminho_base ?? "";

$busca_pesquisa = trim($_GET["busca"] ?? "");
$filtro_genero_pesquisa = trim($_GET["genero"] ?? "");
$filtro_nota_pesquisa = trim($_GET["nota"] ?? "");
$filtro_plataforma_pesquisa = trim($_GET["plataforma"] ?? "");

if (!in_array($filtro_nota_pesquisa, ["", "1", "2", "3", "4", "5"], true)) {
    $filtro_nota_pesquisa = "";
}

$origem_pesquisa = $_GET["origem"] ?? $_SERVER["REQUEST_URI"];

$filtros_avancados_ativos_pesquisa =
    !empty($filtro_genero_pesquisa) ||
    !empty($filtro_nota_pesquisa) ||
    !empty($filtro_plataforma_pesquisa);

$sql_generos_pesquisa = "SELECT DISTINCT genero
                         FROM jogos
                         WHERE genero IS NOT NULL
                           AND genero != ''
                         ORDER BY genero ASC";

$resultado_generos_pesquisa = $conn->query($sql_generos_pesquisa);

$sql_plataformas_pesquisa = "SELECT DISTINCT plataforma
                             FROM avaliacoes
                             WHERE plataforma IS NOT NULL
                               AND plataforma != ''
                               AND visivel = 1
                             ORDER BY plataforma ASC";

$resultado_plataformas_pesquisa = $conn->query($sql_plataformas_pesquisa);
?>

<form
    class="form-pesquisa form-pesquisa-filtros"
    method="GET"
    action="<?php echo $caminho_base; ?>index.php"
>
    <input
        type="hidden"
        name="origem"
        value="<?php echo htmlspecialchars($origem_pesquisa); ?>"
    >

    <input 
        type="text" 
        name="busca" 
        placeholder="Pesquisar por jogo"
        value="<?php echo htmlspecialchars($busca_pesquisa); ?>"
    >

    <details class="dropdown-filtros-pesquisa">
        <summary
            class="botao-icone-filtros <?php echo $filtros_avancados_ativos_pesquisa ? "filtros-ativos" : ""; ?>"
            title="Filtros"
            aria-label="Abrir filtros de pesquisa"
        >
            <span class="icone-filtro-css">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </summary>

        <div class="painel-filtros-pesquisa">
            <div class="grupo-filtro-pesquisa">
                <label for="genero-pesquisa">
                    Gênero
                </label>

                <select name="genero" id="genero-pesquisa">
                    <option value="">Todos</option>

                    <?php if ($resultado_generos_pesquisa): ?>
                        <?php while ($genero_pesquisa = $resultado_generos_pesquisa->fetch_assoc()): ?>
                            <option
                                value="<?php echo htmlspecialchars($genero_pesquisa["genero"]); ?>"
                                <?php echo $filtro_genero_pesquisa === $genero_pesquisa["genero"]
                                    ? "selected"
                                    : ""; ?>
                            >
                                <?php echo htmlspecialchars($genero_pesquisa["genero"]); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="grupo-filtro-pesquisa">
                <label for="nota-pesquisa">
                    Nota mínima
                </label>

                <select name="nota" id="nota-pesquisa">
                    <option value="">Todas</option>

                    <option value="1" <?php echo $filtro_nota_pesquisa === "1" ? "selected" : ""; ?>>
                        1+
                    </option>

                    <option value="2" <?php echo $filtro_nota_pesquisa === "2" ? "selected" : ""; ?>>
                        2+
                    </option>

                    <option value="3" <?php echo $filtro_nota_pesquisa === "3" ? "selected" : ""; ?>>
                        3+
                    </option>

                    <option value="4" <?php echo $filtro_nota_pesquisa === "4" ? "selected" : ""; ?>>
                        4+
                    </option>

                    <option value="5" <?php echo $filtro_nota_pesquisa === "5" ? "selected" : ""; ?>>
                        5
                    </option>
                </select>
            </div>

            <div class="grupo-filtro-pesquisa">
                <label for="plataforma-pesquisa">
                    Plataforma
                </label>

                <select name="plataforma" id="plataforma-pesquisa">
                    <option value="">Todas</option>

                    <?php if ($resultado_plataformas_pesquisa): ?>
                        <?php while ($plataforma_pesquisa = $resultado_plataformas_pesquisa->fetch_assoc()): ?>
                            <option
                                value="<?php echo htmlspecialchars($plataforma_pesquisa["plataforma"]); ?>"
                                <?php echo $filtro_plataforma_pesquisa === $plataforma_pesquisa["plataforma"]
                                    ? "selected"
                                    : ""; ?>
                            >
                                <?php echo htmlspecialchars($plataforma_pesquisa["plataforma"]); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="botoes-filtros-pesquisa">
                <a href="<?php echo htmlspecialchars($origem_pesquisa); ?>">
                    Limpar
                </a>

                <button type="submit">
                    Aplicar
                </button>
            </div>
        </div>
    </details>

    <button type="submit">🔍</button>
</form>