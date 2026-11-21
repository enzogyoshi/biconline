<?php
// Lista as conversas do usuário logado.
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/conversas_lib.php';

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$conversas = listarConversas($conn, (int)$_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Minhas conversas</title>
  <link rel="stylesheet" href="chat-paginas.css">
</head>
<body>
  <div class="app sem-conversa">
    <?php renderLateral($conversas); ?>
    <section class="principal">
      <div class="placeholder">
        <p>Selecione uma conversa<br>para começar</p>
      </div>
    </section>
  </div>
</body>
</html>