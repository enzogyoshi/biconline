<?php //endpoint trabalhador cria avaliação do serviço
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/contratacao_lib.php';

$idUsuario = exigirLoginJson();
exigirPost();
exigirCsrf();

$idServico = (int)($_POST['id_servico'] ?? 0);
$nota = (int)($_POST['nota'] ?? 0);
$comentario = trim((string)($_POST['comentario'] ?? ''));

if ($idServico <= 0 || $nota < 1 || $nota > 5) {
    respostaJson(400, ['ok' => false, 'erro' => 'Dê uma nota de 1 a 5 estrelas.']);
}
if (mb_strlen($comentario) > 200) {
    respostaJson(400, ['ok' => false, 'erro' => 'Comentário muito longo (máximo 200 caracteres).']);
}
$comentario = $comentario === '' ? null : $comentario;

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("
        SELECT s.id, s.status_serv, s.id_contratante, t.id_usuario AS id_usuario_trabalhador
        FROM servico s
        JOIN trabalhador t ON t.id = s.id_contratado
        WHERE s.id = ? FOR UPDATE");
    $stmt->bind_param("i", $idServico);
    $stmt->execute();
    $s = $stmt->get_result()->fetch_assoc();

    if (!$s) {
        $conn->rollback();
        respostaJson(404, ['ok' => false, 'erro' => 'Serviço não encontrado.']);
    } // Só quem contrata pode avaliar
    if ((int)$s['id_contratante'] !== $idUsuario) {
        $conn->rollback();
        respostaJson(403, ['ok' => false, 'erro' => 'Só quem contratou pode avaliar este serviço.']);
    } // Só dá pra avaliar depois de concluído , status_serv = 0
    if ((int)$s['status_serv'] !== 0) {
        $conn->rollback();
        respostaJson(409, ['ok' => false, 'erro' => 'Você só pode avaliar um serviço já concluído.']);
    }//  nunca avaliar um serviço de auto-contratação.
    if ((int)$s['id_contratante'] === (int)$s['id_usuario_trabalhador']) {
        $conn->rollback();
        respostaJson(403, ['ok' => false, 'erro' => 'Serviço inválido.']);
    }

    $idUsuarioTrabalhador = (int)$s['id_usuario_trabalhador'];

    // uma avaliação por serviço 
    $stmt = $conn->prepare("SELECT id FROM avaliacoes WHERE id_servico = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $idServico, $idUsuarioTrabalhador);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $conn->rollback();
        respostaJson(409, ['ok' => false, 'erro' => 'Você já avaliou este serviço.']);
    }

    $stmt = $conn->prepare("INSERT INTO avaliacoes (comentario, nota, id_servico, id_usuario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siii", $comentario, $nota, $idServico, $idUsuarioTrabalhador);
    if (!$stmt->execute()) {
        throw new RuntimeException('Falha ao gravar a avaliação.');
    }

    // recalcula a média e grava no perfil do trabalhador
    $stmt = $conn->prepare("SELECT AVG(nota) AS media FROM avaliacoes WHERE id_usuario = ?");
    $stmt->bind_param("i", $idUsuarioTrabalhador);
    $stmt->execute();
    $media = (float)($stmt->get_result()->fetch_assoc()['media'] ?? 0);
    $media = round($media, 2);

    $stmt = $conn->prepare("UPDATE usuario SET score_avaliacao = ? WHERE id = ?");
    $stmt->bind_param("di", $media, $idUsuarioTrabalhador);
    $stmt->execute();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    error_log('avaliacao_criar: ' . $e->getMessage());
    respostaJson(500, ['ok' => false, 'erro' => 'Não foi possível gravar a avaliação.']);
}

respostaJson(201, ['ok' => true, 'media_atualizada' => $media]);