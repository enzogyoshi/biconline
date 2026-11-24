<?php
require_once 'proteger.php';
require_once 'conn.php';
require_once 'ofuscar_id.php';

$id_denunciante = (int)($_SESSION['usuario'] ?? 0);
$erro = '';

$raw_usuario    = trim($_POST['id'] ?? $_GET['id'] ?? '');
$raw_publicacao = trim($_POST['id_publicacao'] ?? $_GET['id_publicacao'] ?? '');

$id_usuario_denunciado = revelarId($raw_usuario);
if ($id_usuario_denunciado === null && ctype_digit($raw_usuario)) {
    $id_usuario_denunciado = (int)$raw_usuario;
}

$id_publicacao = 0;
if ($raw_publicacao !== '' && $raw_publicacao !== '0') {
    $pub_decodificada = revelarId($raw_publicacao);
    $id_publicacao = ($pub_decodificada !== null) ? $pub_decodificada : (int)$raw_publicacao;
}

if (!$id_usuario_denunciado || $id_usuario_denunciado <= 0 || $id_usuario_denunciado === $id_denunciante) {
    http_response_code(400);
    exit("Usuário inválido para denúncia.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motivo   = trim($_POST['motivo'] ?? '');
    $detalhes = trim($_POST['detalhes'] ?? '');

    if ($motivo === '') {
        $erro = "Selecione um motivo para a denúncia.";
    } else {
        if ($id_publicacao > 0) {
            $sql = "INSERT INTO denuncia (motivo, descricao, id_usuario_denunciante, id_usuario_denunciado, id_publicacao) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiii", $motivo, $detalhes, $id_denunciante, $id_usuario_denunciado, $id_publicacao);
        } else {
            $sql = "INSERT INTO denuncia (motivo, descricao, id_usuario_denunciante, id_usuario_denunciado) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $motivo, $detalhes, $id_denunciante, $id_usuario_denunciado);
        }

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: perfil.php?pesquisa=" . urlencode(esconderId($id_usuario_denunciado)) . "&denuncia=ok");
            exit;
        } else {
            $erro = "Erro ao registrar denúncia: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denunciar - bicoONLINE</title>
    <link rel="stylesheet" href="../css/denunciar.css">
</head>
<body>
    <main class="denuncia-container">
        <div class="denuncia-card">
            <h1>Denunciar <?= $id_publicacao > 0 ? 'publicação' : 'usuário' ?></h1>
            <p class="denuncia-subtitulo">
                Sua denúncia será analisada pela equipe de moderação de forma confidencial.
            </p>

            <?php if ($erro !== ''): ?>
                <div class="msg-erro-denuncia"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form action="denunciar.php" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($raw_usuario) ?>">
                <input type="hidden" name="id_publicacao" value="<?= htmlspecialchars($raw_publicacao) ?>">

                <label for="motivo">Motivo da denúncia</label>
                <select id="motivo" name="motivo" required>
                    <option value="">Selecione um motivo</option>
                    <option value="conteudo_inapropriado">Conteúdo inapropriado</option>
                    <option value="Publicação ofensiva">Publicação ofensiva</option>
                    <option value="Não cumpriu com o serviço">Não cumpriu com o serviço</option>
                    <option value="spam">Spam</option>
                    <option value="assedio">Assédio</option>
                    <option value="outro">Outro</option>
                </select>

                <label for="detalhes">Detalhes adicionais (opcional)</label>
                <textarea id="detalhes" name="detalhes" rows="4" placeholder="Forneça mais detalhes sobre o ocorrido..."></textarea>

                <div class="acoes-denuncia">
                    <a href="perfil.php?pesquisa=<?= urlencode(esconderId($id_usuario_denunciado)) ?>" class="btn-cancelar-denuncia">Cancelar</a>
                    <button type="submit" class="btn-enviar-denuncia">Enviar Denúncia</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>