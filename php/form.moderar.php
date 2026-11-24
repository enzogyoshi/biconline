<?php
require_once 'proteger.php';
require_once 'conn.php';

$id_moderador = (int)($_SESSION['usuario'] ?? 0);

$stmtCheck = $conn->prepare("SELECT is_moderador FROM usuario WHERE id = ?");
$stmtCheck->bind_param("i", $id_moderador);
$stmtCheck->execute();
$eh_mod = (int)($stmtCheck->get_result()->fetch_assoc()['is_moderador'] ?? 0);
$stmtCheck->close();

if ($eh_mod !== 1) {
    http_response_code(403);
    exit("Acesso negado.");
}

$id_denuncia = (int)($_GET['id'] ?? 0);

$sql = "SELECT d.*, u.nome AS nome_alvo 
        FROM denuncia d 
        JOIN usuario u ON d.id_usuario_denunciado = u.id 
        WHERE d.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_denuncia);
$stmt->execute();
$denuncia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$denuncia) {
    exit("Denúncia não encontrada.");
}

$id_publicacao = (int)($denuncia['id_publicacao'] ?? 0);
$item_denunciado = ($id_publicacao > 0) ? 'publicacao' : 'Perfil do usuario';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderar <?= htmlspecialchars($item_denunciado) ?></title>
    <link rel="stylesheet" href="../css/mod.css?v=3">
</head>
<body>
    <main class="analise-container">
        <div class="analise-card">
            <h2>Analisando Denúncia #<?= $denuncia['id'] ?></h2>

            <div class="info-denuncia">
                <p><strong>Usuário Alvo:</strong> <?= htmlspecialchars($denuncia['nome_alvo']) ?></p>
                <p><strong>Tipo:</strong> <?= htmlspecialchars($item_denunciado) ?></p>
                <p><strong>Motivo:</strong> <?= htmlspecialchars($denuncia['motivo']) ?></p>
                <p><strong>Descrição:</strong> <?= htmlspecialchars($denuncia['descricao'] ?? 'Sem detalhes adicionais.') ?></p>
            </div>

            <form action="exec_mod.php" method="POST" class="form-moderacao">
                <input type="hidden" name="id_denuncia" value="<?= $denuncia['id'] ?>">
                <input type="hidden" name="id_usuario_alvo" value="<?= $denuncia['id_usuario_denunciado'] ?>">
                <input type="hidden" name="id_publicacao" value="<?= $id_publicacao ?>">
                <input type="hidden" name="item_denunciado" value="<?= htmlspecialchars($item_denunciado) ?>">
                <input type="hidden" name="motivo" value="<?= htmlspecialchars($denuncia['motivo']) ?>">

                <label for="acao">Ação:</label>
                <select name="acao" id="acao" required>
                    <option value="">Selecione uma ação:</option>
                    <option value="Banir">Banir Usuário</option>
                    <?php if ($id_publicacao > 0): ?>
                        <option value="Deletar Publicacao">Deletar Publicação</option>
                    <?php endif; ?>
                    <option value="Rejeitar">Rejeitar</option>
                </select>

                <div class="acoes-form">
                    <a href="moderar.php" class="btn-voltar">Voltar</a>
                    <button type="submit">Moderar</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>