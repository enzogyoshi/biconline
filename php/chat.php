<?php
// Mostra uma conversa privada. Uso: chat.php?id=ID_DA_CONVERSA
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/config.local.php';
require_once __DIR__ . '/gerar-token.php';
require_once __DIR__ . '/conversas_lib.php';

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

// a conversa existe E o usuário logado faz parte dela? (sem isso, ninguém entra)
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

//url chat = auto
$base = (CHAT_URL === 'auto')
    ? 'http://' . preg_replace('/[^A-Za-z0-9.\-]/', '', $_SERVER['SERVER_NAME'] ?? 'localhost') . ':3000'
    : CHAT_URL;
$src = $base . '/?token=' . urlencode($token);

$conversas = listarConversas($conn, $idUsuario);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Conversa com <?= h($conversa['outro']) ?></title>
  <link rel="stylesheet" href="chat-paginas.css">
</head>
<body>
  <div class="app com-conversa">
    <?php renderLateral($conversas, $idChat); ?>
    <section class="principal">
      <a class="voltar" href="conversas.php">&larr; Conversas</a>
      <iframe src="<?= h($src) ?>" title="Conversa com <?= h($conversa['outro']) ?>"></iframe>
    </section>
  </div>
</body>
</html>