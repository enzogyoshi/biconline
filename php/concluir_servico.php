<?php
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/contratacao_lib.php';

$idUsuario = exigirLoginJson();
exigirPost();
exigirCsrf();

$idServico = (int)($_POST['id_servico'] ?? 0);
if ($idServico <= 0) {
    respostaJson(400, ['ok' => false, 'erro' => 'Serviço inválido.']);
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("
        SELECT s.id, s.status_serv, t.id_usuario AS id_usuario_trabalhador
        FROM servico s
        JOIN trabalhador t ON t.id = s.id_contratado
        WHERE s.id = ? FOR UPDATE");
    $stmt->bind_param("i", $idServico);
    $stmt->execute();
    $s = $stmt->get_result()->fetch_assoc();

    if (!$s) {
        $conn->rollback();
        respostaJson(404, ['ok' => false, 'erro' => 'Serviço não encontrado.']);
    }
    if ((int)$s['id_usuario_trabalhador'] !== $idUsuario) {
        $conn->rollback();
        respostaJson(403, ['ok' => false, 'erro' => 'Só o trabalhador responsável pode concluir este serviço.']);
    }
    if ((int)$s['status_serv'] !== 1) {
        $conn->rollback();
        respostaJson(409, ['ok' => false, 'erro' => 'Este serviço já está concluído.']);
    }

    $stmt = $conn->prepare("UPDATE servico SET status_serv = FALSE WHERE id = ? AND status_serv = TRUE");
    $stmt->bind_param("i", $idServico);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) {
        $conn->rollback();
        respostaJson(409, ['ok' => false, 'erro' => 'Este serviço já está concluído.']);
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    error_log('servico_concluir: ' . $e->getMessage());
    respostaJson(500, ['ok' => false, 'erro' => 'Não foi possível concluir o serviço.']);
}

respostaJson(200, ['ok' => true, 'id_servico' => $idServico]);