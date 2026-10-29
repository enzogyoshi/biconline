<?php
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/config.local.php';
require_once __DIR__ . '/gerar-token.php';

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$stmt = $conn->prepare("SELECT id, nome, is_moderador FROM usuario WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario']);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();

if (!$u) { // usuário apagado do banco
    session_destroy();
    header('Location: login.php');
    exit;
}

$token = gerarTokenChat([
    'id'    => $u['id'],
    'nome'  => $u['nome'],
    'papel' => ((int)$u['is_moderador'] === 1) ? 'moderador' : 'trabalhador',
]);
$src = CHAT_URL . '/?token=' . urlencode($token);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Chat</title></head>
<body>
  <iframe src="<?= htmlspecialchars($src) ?>"
          style="width:100%; max-width:450px; height:620px; border:none; border-radius:16px;"></iframe>
</body>
</html>