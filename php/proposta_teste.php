<?php
// PÁGINA DE TESTE DA PARTE 2 — apague este arquivo depois que a Parte 3 estiver pronta.
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/conversas_lib.php';
require_once __DIR__ . '/contratacao_lib.php';

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
$idUsuario = (int)$_SESSION['usuario'];
$conversas = listarConversas($conn, $idUsuario);

$stmt = $conn->prepare("
    SELECT p.id, p.id_chat, p.descricao, p.valor, p.data_servico, p.status
    FROM proposta p
    JOIN chat c ON c.id = p.id_chat
    JOIN trabalhador t ON t.id = c.id_trabalhador
    WHERE c.id_usuario = ? OR t.id_usuario = ?
    ORDER BY p.id DESC");
$stmt->bind_param("ii", $idUsuario, $idUsuario);
$stmt->execute();
$propostas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Teste das propostas</title>
<style>body{font-family:system-ui,Arial,sans-serif;max-width:760px;margin:30px auto;padding:0 16px}
table{border-collapse:collapse;width:100%}td,th{border:1px solid #ccc;padding:6px 8px;text-align:left}
form{margin:0}input,select,button{padding:6px;margin:2px}</style></head>
<body>
<h1>Teste das propostas (Parte 2)</h1>
<p>Logado como usuário <strong><?= $idUsuario ?></strong>. As respostas aparecem como texto JSON; use "Voltar" depois.</p>

<h2>Enviar proposta (só quem iniciou a conversa)</h2>
<form method="post" action="proposta_criar.php">
  <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
  <select name="id_chat">
    <?php foreach ($conversas as $c): ?>
      <option value="<?= (int)$c['id'] ?>">Conversa <?= (int)$c['id'] ?> — <?= h($c['outro']) ?></option>
    <?php endforeach; ?>
  </select><br>
  <input name="descricao" placeholder="Descrição do serviço" size="40" required><br>
  <input name="valor" placeholder="Valor (opcional)">
  <input type="date" name="data_servico">
  <button>Enviar proposta</button>
</form>

<h2>Propostas das suas conversas</h2>
<table>
  <tr><th>#</th><th>Conversa</th><th>Descrição</th><th>Valor</th><th>Data</th><th>Status</th><th>Ação</th></tr>
  <?php foreach ($propostas as $p): ?>
  <tr>
    <td><?= (int)$p['id'] ?></td><td><?= (int)$p['id_chat'] ?></td><td><?= h($p['descricao']) ?></td>
    <td><?= h($p['valor']) ?></td><td><?= h($p['data_servico']) ?></td><td><strong><?= h($p['status']) ?></strong></td>
    <td>
      <?php if ($p['status'] === 'pendente'): ?>
      <form method="post" action="proposta_responder.php">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
        <input type="hidden" name="id_proposta" value="<?= (int)$p['id'] ?>">
        <button name="acao" value="aceitar">Aceitar</button>
        <button name="acao" value="recusar">Recusar</button>
        <button name="acao" value="cancelar">Cancelar</button>
      </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$propostas): ?><tr><td colspan="7">Nenhuma proposta ainda.</td></tr><?php endif; ?>
</table>

<p>Estado de uma conversa: <code>proposta_estado.php?id_chat=NUMERO</code></p>
</body>
</html>