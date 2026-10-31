<?php
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/config.local.php';
require_once __DIR__ . '/gerar-token.php';

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$idUsuario = (int)$_SESSION['usuario'];
$idChat = (int)($_GET['id'] ?? 0);

if ($idChat <= 0) {
    header('Location: conversas.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT c.id,
           CASE WHEN c.id_usuario = ? THEN ut.nome ELSE uc.nome END AS outro
    FROM chat c
    JOIN usuario uc ON uc.id = c.id_usuario
    JOIN trabalhador t ON t.id = c.id_trabalhador
    JOIN usuario ut ON ut.id = t.id_usuario
    WHERE c.id = ? AND (c.id_usuario = ? OR t.id_usuario = ?)");
$stmt->bind_param("iiii", $idUsuario, $idChat, $idUsuario, $idUsuario);
$stmt->execute();
$conversa = $stmt->get_result()->fetch_assoc();

if (!$conversa) {
    http_response_code(403);
    exit('Você não faz parte dessa conversa.');
}

$stmt = $conn->prepare("SELECT nome, is_moderador FROM usuario WHERE id = ?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();

if (!$u) { // usuário apagado do banco
    session_destroy();
    header('Location: login.php');
    exit;
}

$token = gerarTokenChat([
    'id'     => $idUsuario,
    'nome'   => $u['nome'],
    'papel'  => ((int)$u['is_moderador'] === 1) ? 'moderador' : 'trabalhador',
    'sala'   => 'c' . $idChat,
    'titulo' => $conversa['outro'],
]);
$src = CHAT_URL . '/?token=' . urlencode($token);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Conversa com <?= htmlspecialchars($conversa['outro']) ?></title>
</head>
<body>
  <p><a href="conversas.php">&larr; Minhas conversas</a></p>
  <iframe src="<?= htmlspecialchars($src) ?>"
          style="width:100%; max-width:450px; height:620px; border:none; border-radius:16px;"></iframe>
</body>
</html>