<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../php/conn.php";
require_once "../php/ofuscar_id.php";
$id_logado = (int)$_SESSION['usuario'];

$stmt_ban = $conn->prepare("SELECT acao FROM moderacao WHERE id_usuario_alvo = ? AND acao = 'Banir' LIMIT 1");
$stmt_ban->bind_param("i", $id_logado);
$stmt_ban->execute();
$foi_banido = $stmt_ban->get_result()->num_rows > 0;
$stmt_ban->close();

if ($foi_banido) {
    $permissoes = false;
    session_unset();
    session_destroy();
    header("Location: ../php/login.php?erro=conta_banida");
    exit;
} 



$fotoPadrao = "../foto_de_perfil_vazio.webp";
$fotoPerfilUrl = $fotoPadrao;

if (!empty($_SESSION["usuario"])) {
    $idUsuario = (int) $_SESSION["usuario"];
    $stmt = $conn->prepare("SELECT url_foto_perfil FROM usuario WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $raw = trim((string) ($row["url_foto_perfil"] ?? ""));
            if ($raw !== "") {
                if (str_starts_with($raw, "http://") || str_starts_with($raw, "https://") || str_starts_with($raw, "/")) {
                    $fotoPerfilUrl = $raw;
                } elseif (str_starts_with($raw, "uploads/")) {
                    $fotoPerfilUrl = "../php/" . $raw;
                } else {
                    $fotoPerfilUrl = "../php/" . ltrim($raw, "/");
                }
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BicoOnline - Feed</title>
    <link rel="stylesheet" href="feed.css">
</head>

<body>
    <header>
        <nav>
            <img src="../paco_minimalista.jpg" alt="Logo" class="logo">

        <div class="search-container">
            <input type="text" id="input-busca" placeholder="Pesquisar pessoas..." autocomplete="off">
            <div id="resultados-busca" class="search-dropdown"></div>
        </div>

            <input type="checkbox" id="menu-toggle">

            <label for="menu-toggle" class="menu-btn">☰</label>

            <ul class="nav-links">
                <li>
                    <a href="feed.php">
                        <span class="icone-casa">⌂</span> FEED
                    </a>
                </li>
               <?php if (!empty($_SESSION['is_moderador'])): ?>
                    <li>
                        <a href="../php/moderar.php">MODERAÇÃO</a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="../php/perfil.php" class="link-perfil-header" title="Perfil">
                        <?php if ($fotoPerfilUrl !== ""): ?>
                            <img src="<?= htmlspecialchars($fotoPerfilUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Perfil" id="foto-perfil-header">
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main class="conteudo">
        <aside class="lateral-esquerda">
            <section class="cartao">
                <h2>Mensagens</h2>
                <div class="wrapper">
                    <a href="../php/conversas.php" class="btn-animated">
                        <span>Mensagens</span>
                    </a>
                </div>
            </section>

        </aside>

        <section class="feed">
            <h2 class="titulo-feed">Home</h2>

            <a href="../publicacao/publicacao.html"
               class="criar-publicacao-topo"
               aria-label="Criar publicação">
                <span>Criar publicação</span>
                <span class="icone-mais-publicacao" aria-hidden="true">+</span>
            </a>

            <div id="feed-publicacoes"></div>
        </section>

        <aside class="lateral-direita">
            <section class="cartao">
                <h2>Meus trabalhos</h2>
                <a href="../php/meus-trabalhos.php" class="link-trabalhos">
                    Ver meus trabalhos
                </a>
            </section>
        </aside>
    </main>

    <script src="../java.js/feed.js"></script>
    <script src="../java.js/pesquisa.js"></script>
</body>

</html>