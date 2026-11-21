<?php
// Exemplo de página que mostra o chat para o usuário logado.
require_once "proteger.php";
require __DIR__ . '/config.local.php';
require __DIR__ . '/gerar-token.php';

if (empty($_SESSION['id'])) { header('Location: login.php'); exit; }

$token = gerarTokenChat([
    'id'    => $_SESSION['id'],
    'nome'  => $_SESSION['nome'],
    'papel' => $_SESSION['papel'], // 'trabalhador' ou 'moderador', vindo do banco
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
