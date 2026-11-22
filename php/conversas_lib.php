<?php

function h($texto): string {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function iniciais(string $nome): string {
    $partes = preg_split('/\s+/', trim($nome));
    $letras = '';
    foreach (array_slice($partes, 0, 2) as $p) {
        $letras .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $letras !== '' ? $letras : '?';
}

function corAvatar(int $id): string {
    return 'hsl(' . (($id * 47) % 360) . ', 60%, 52%)';
}

// todas as conversas em que o usuário participa 
function listarConversas(mysqli $conn, int $idUsuario): array {
    $stmt = $conn->prepare("
        SELECT c.id,
               CASE WHEN c.id_usuario = ? THEN ut.nome ELSE uc.nome END AS outro,
               CASE WHEN c.id_usuario = ? THEN ut.id   ELSE uc.id   END AS id_outro
        FROM chat c
        JOIN usuario uc ON uc.id = c.id_usuario
        JOIN trabalhador t ON t.id = c.id_trabalhador
        JOIN usuario ut ON ut.id = t.id_usuario
        WHERE c.id_usuario = ? OR t.id_usuario = ?
        ORDER BY c.id DESC");
    $stmt->bind_param("iiii", $idUsuario, $idUsuario, $idUsuario, $idUsuario);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function renderLateral(array $conversas, int $ativo = 0): void { ?>
  <aside class="lateral">
    <a class="voltar-site" href="../feed/feed.html">&larr; Voltar ao feed</a>
    <h1>Conversas</h1>
    <div class="lista">
      <?php if (!$conversas): ?>
        <p class="aviso-vazio">Nenhuma conversa ainda.<br>Abra o perfil de um trabalhador e clique em “Conversar”.</p>
      <?php endif; ?>
      <?php foreach ($conversas as $c): ?>
        <a class="item<?= (int)$c['id'] === $ativo ? ' ativo' : '' ?>" href="chat.php?id=<?= (int)$c['id'] ?>">
          <span class="avatar" style="background: <?= corAvatar((int)$c['id_outro']) ?>"><?= h(iniciais($c['outro'])) ?></span>
          <span class="nome"><?= h($c['outro']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </aside>
<?php }