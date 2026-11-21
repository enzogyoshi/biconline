<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "conn.php";

if (empty($_SESSION["usuario"])) {
    http_response_code(401);
    exit("Você precisa estar logado.");
}

$id_publicacao = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id_publicacao) {
    http_response_code(400);
    exit("Publicação inválida.");
}

$id_usuario = (int) $_SESSION["usuario"];
$voltar = ($_GET["voltar"] ?? "") === "feed" ? "feed" : "perfil";
$stmt = $conn->prepare("SELECT conteudo, url_midia, link FROM publicacao WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_publicacao, $id_usuario);
$stmt->execute();
$publicacao = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$publicacao) {
    http_response_code(404);
    exit("Publicação não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar publicação - Biconline</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <main class="editar-publicacao-pagina">
        <a class="btn-voltar-publicacao" href="<?= $voltar === "feed" ? "../feed/feed.html" : "perfil.php" ?>">Voltar</a>
        <h1>Editar publicação</h1>
        <?php if (isset($_GET["sucesso"])): ?>
            <p id="mensagem-sucesso">Publicação atualizada com sucesso. Esta aba será fechada.</p>
        <?php endif; ?>

        <?php if (!empty($publicacao["url_midia"])): ?>
            <?php if (preg_match('/\.(mp4|webm|ogv)$/i', $publicacao["url_midia"])): ?>
                <video class="midia-publicacao" controls src="<?= htmlspecialchars($publicacao["url_midia"]) ?>"></video>
            <?php else: ?>
                <img class="midia-publicacao" src="<?= htmlspecialchars($publicacao["url_midia"]) ?>" alt="Mídia atual da publicação">
            <?php endif; ?>
        <?php endif; ?>

        <form class="form-editar-publicacao" action="gerenciar-publicacao.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?= (int)$id_publicacao ?>">
            <input type="hidden" name="voltar" value="<?= htmlspecialchars($voltar) ?>">
            <label>Texto da publicação
                <textarea name="texto" rows="5" required><?= htmlspecialchars($publicacao["conteudo"]) ?></textarea>
            </label>
            <label>Link (opcional)
                <input type="url" name="link" value="<?= htmlspecialchars($publicacao["link"] ?? "") ?>" placeholder="https://exemplo.com">
            </label>
            <label>Alterar imagem ou vídeo (opcional)
                <input type="file" name="midia" accept="image/*,video/*">
            </label>
            <button type="submit">Salvar alterações</button>
        </form>
    </main>
</body>
<?php if (isset($_GET["sucesso"])): ?>
<script>
    window.addEventListener("load", () => {
        window.close();
        setTimeout(() => {
            const mensagem = document.getElementById("mensagem-sucesso");
            if (mensagem && !window.closed) {
                mensagem.textContent = "Publicação atualizada com sucesso. Feche esta aba para voltar à página anterior.";
            }
        }, 300);
    });
</script>
<?php endif; ?>
</html>
