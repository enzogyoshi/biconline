<?php
require_once("usuario.php");

$fotoPadrao = "../foto_de_perfil.jpg";
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
    <title>Perfil de <?= htmlspecialchars($nome ?? 'Usuário') ?></title>
    <link rel="stylesheet" href="../perfil.css">
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
                    <a href="../feed/feed.php">
                        <span class="icone-casa">⌂</span> FEED
                    </a>
                </li>
                <?php if (!empty($_SESSION['is_moderador'])): ?>
                    <li>
                        <a href="../php/moderar.php">MODERAÇÃO</a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="perfil.php" class="link-perfil-header" title="Perfil">
                        <img src="<?= htmlspecialchars($fotoPerfilUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Perfil" id="foto-perfil-header" onerror="this.onerror=null;this.src='<?= htmlspecialchars($fotoPadrao, ENT_QUOTES, 'UTF-8') ?>';">
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="perfil">
            <div class="foto-perfil">
                <img src="<?= htmlspecialchars(!empty($url_foto) ? $url_foto : $fotoPadrao) ?>" alt="Foto de perfil" onerror="this.onerror=null;this.src='<?= htmlspecialchars($fotoPadrao, ENT_QUOTES, 'UTF-8') ?>';">
            </div>

            <div class="dados-perfil">
                <h1><?= htmlspecialchars($nome ?? '') ?></h1>
                <p>
                    <?= htmlspecialchars($cidade ?? '') ?><?= (!empty($cidade) && !empty($uf)) ? ' - ' : '' ?><?= htmlspecialchars($uf ?? '') ?>
                </p>

                <?php if (!empty($eh_trabalhador)): ?>
                    <p><?= htmlspecialchars($experiencia ?? 0) ?> anos de experiência</p>
                    <p>
                        <?php if (!empty($disponibilidade)): ?>
                            Disponível para trabalhos
                        <?php else: ?>
                            Indisponível para trabalhos
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <?php if (!$permissoes): ?>
                    <a href="denunciar.php?id=<?= esconderId($id_usuario) ?>" class="btn-denunciar-perfil">Denunciar usuário</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="sobre">
            <h2>Sobre mim</h2>
            <p><?= htmlspecialchars($bio ?? '') ?></p>
            <?php if (!empty($permissoes)): ?>
                <div class="acoes-sobre-perfil">
                    <a href="editar.php" class="btn-editar">Editar perfil</a>
                    <form action="logout.php" method="post">
                        <button type="submit" class="btn-sair">Sair</button>
                    </form>
                </div>
            <?php elseif (!empty($disponibilidade)): ?>
                <a href="iniciar_chat.php?usuario=<?= esconderId($id_usuario) ?>" class="btn-editar">Conversar</a>
            <?php endif; ?>
        </div>

        <div class="servicos">
            <h2>Serviços oferecidos</h2>
            <ul>
                <?php if (!empty($habilidades_lista)): ?>
                    <?php foreach ($habilidades_lista as $hab): ?>
                        <li><?= htmlspecialchars($hab) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Nenhuma habilidade cadastrada.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="informacoes">
            <h2>Informações</h2>
            <p>Telefone: <?= htmlspecialchars($telefone ?? 'Não informado') ?></p>
            <p>E-mail: <?= htmlspecialchars($email_contato ?? 'Não informado') ?></p>
        </div>

        <section class="secao-publicacoes-perfil">
            <div class="topo-publicacoes-perfil">
                <h2>Publicações</h2>
                <?php if (!empty($permissoes)): ?>
                    <a href="../publicacao/publicacao.html" class="criar-publicacao-topo" aria-label="Criar publicação">
                        <span>Criar publicação</span>
                        <span class="icone-mais-publicacao" aria-hidden="true">+</span>
                    </a>
                <?php endif; ?>
            </div>

            <div id="feed-publicacoes" data-usuario="<?= htmlspecialchars(esconderId($id_usuario)) ?>"></div>
        </section>

        <div class="avaliacoes">
            <h2>Avaliações</h2>
            <p><?= htmlspecialchars($score ?? '0') ?> estrelas</p>

            <?php if (!empty($comentarios) && is_array($comentarios)): ?>
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="comentario">
                        <p><strong><?= htmlspecialchars($comentario['nome'] ?? 'Usuário anônimo') ?>:</strong></p>
                        <p><?= htmlspecialchars($comentario['nota'] ?? $nota) ?></p>
                        <p><?= htmlspecialchars($comentario['texto'] ?? $comentario) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhuma avaliação cadastrada.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="../java.js/pesquisa.js"></script>
    <script src="../java.js/perfil.js"></script>
</body>

</html>