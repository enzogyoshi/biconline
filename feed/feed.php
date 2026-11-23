<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../php/conn.php";

$fotoPerfilUrl = "";

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

            <input type="search" name="Pesquisar" placeholder="Pesquisar" class="pesquisa-header">

            <input type="checkbox" id="menu-toggle">

            <label for="menu-toggle" class="menu-btn">☰</label>

            <ul class="nav-links">
                <li>
                    <a href="feed.php">
                        <span class="icone-casa">⌂</span> FEED
                    </a>
                </li>
              
                <li>
                    <a href="../php/perfil.php" class="link-perfil-header" title="Perfil">
                        <?php if ($fotoPerfilUrl !== ""): ?>
                            <img src="<?= htmlspecialchars($fotoPerfilUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Perfil" id="foto-perfil-header">
                        <?php else: ?>
                            <span class="avatar-header-vazio" aria-hidden="true">👤</span>
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

            <section class="cartao">
                <h2>Notificações</h2>
                <div class="wrapper">
                    <a href="../php/notificacoes.php" class="btn-animated">
                        <span>Notificações</span>
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

</body>

</html>