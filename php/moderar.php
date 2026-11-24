<?php
require_once 'usuario.php';

if (!isset($moderador) || (int)$moderador !== 1) {
    http_response_code(403);
    exit("Acesso negado. Você não tem permissão para acessar esta página.");
}

$sql = "SELECT 
            d.id, 
            d.motivo, 
            u.nome AS denunciante_nome, 
            u2.nome AS denunciado_nome
        FROM denuncia d
        JOIN usuario u ON d.id_usuario_denunciante = u.id
        JOIN usuario u2 ON d.id_usuario_denunciado = u2.id
        LEFT JOIN moderacao m ON d.motivo = m.motivo
        WHERE m.id IS NULL
        ORDER BY d.id ASC";

$resultado = $conn->query($sql);




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
    <title>Painel de Moderação</title>
    <link rel="stylesheet" href="/biconline/css/mod.css?v=1">
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

    <h1>Denúncias Pendentes</h1>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Denunciado</th>
                    <th>Motivo</th>
                    <th>Denunciante</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($denuncia = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $denuncia['id'] ?></td>
                        <td><?= htmlspecialchars($denuncia['denunciado_nome']) ?></td>
                        <td><?= htmlspecialchars($denuncia['motivo']) ?></td>
                        <td><?= htmlspecialchars($denuncia['denunciante_nome']) ?></td>
                        <td>
                            <a href="form.moderar.php?id=<?= $denuncia['id'] ?>" class="btn">Analisar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhuma denúncia pendente no momento.</p>
    <?php endif; ?>

        <script src="../java.js/pesquisa.js"></script>
</body>
</html>