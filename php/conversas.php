<?php
// Lista as conversas do usuário
session_start();
require_once __DIR__ . '/conn.php';

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$idUsuario = (int)$_SESSION['usuario'];

$stmt = $conn->prepare("
    SELECT c.id,
           CASE WHEN c.id_usuario = ? THEN ut.nome ELSE uc.nome END AS outro
    FROM chat c
    JOIN usuario uc ON uc.id = c.id_usuario
    JOIN trabalhador t ON t.id = c.id_trabalhador
    JOIN usuario ut ON ut.id = t.id_usuario
    WHERE c.id_usuario = ? OR t.id_usuario = ?
    ORDER BY c.id DESC");
$stmt->bind_param("iii", $idUsuario, $idUsuario, $idUsuario);
$stmt->execute();
$conversas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Minhas conversas</title>
</head>
<body>
  <h1>Minhas conversas</h1>
  <?php if (!$conversas): ?>
    <p>Você ainda não tem conversas. Abra o perfil de um trabalhador e clique em "Conversar".</p>
  <?php else: ?>
    <ul>
      <?php foreach ($conversas as $c): ?>
        <li><a href="chat.php?id=<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['outro']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</body>
</html>