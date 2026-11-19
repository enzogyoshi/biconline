<?php
// PARTE 2 — Responder a uma proposta.
// POST: csrf, id_proposta, acao = aceitar | recusar | cancelar
//   aceitar/recusar: só o trabalhador da conversa
//   cancelar:        só o contratante, e só enquanto estiver pendente
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/contratacao_lib.php';

$idUsuario = exigirLoginJson();
exigirPost();
exigirCsrf();

$idProposta = (int)($_POST['id_proposta'] ?? 0);
$acao = (string)($_POST['acao'] ?? '');
$novoStatus = ['aceitar' => 'aceita', 'recusar' => 'recusada', 'cancelar' => 'cancelada'][$acao] ?? null;

if ($idProposta <= 0 || $novoStatus === null) {
    respostaJson(400, ['ok' => false, 'erro' => 'Pedido inválido.']);
}

try {
    $conn->begin_transaction();

    // trava a proposta para ninguém responder duas vezes ao mesmo tempo
    $stmt = $conn->prepare("
        SELECT p.id, p.id_chat, p.status,
               c.id_usuario AS id_contratante,
               t.id_usuario AS id_usuario_trabalhador
        FROM proposta p
        JOIN chat c ON c.id = p.id_chat
        JOIN trabalhador t ON t.id = c.id_trabalhador
        WHERE p.id = ? FOR UPDATE");
    $stmt->bind_param("i", $idProposta);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();

    if (!$p) {
        $conn->rollback();
        respostaJson(404, ['ok' => false, 'erro' => 'Proposta não encontrada.']);
    }

    $ehContratante = ((int)$p['id_contratante'] === $idUsuario);
    $ehTrabalhador = ((int)$p['id_usuario_trabalhador'] === $idUsuario);

    // quem pode fazer o quê
    $permitido = ($acao === 'cancelar') ? $ehContratante : $ehTrabalhador;
    if (!$permitido) {
        $conn->rollback();
        respostaJson(403, ['ok' => false, 'erro' => 'Você não pode fazer isso nesta proposta.']);
    }

    if ($p['status'] !== 'pendente') {
        $conn->rollback();
        respostaJson(409, ['ok' => false, 'erro' => 'Esta proposta já foi respondida.']);
    }

    $stmt = $conn->prepare("UPDATE proposta SET status = ?, respondida_em = NOW() WHERE id = ? AND status = 'pendente'");
    $stmt->bind_param("si", $novoStatus, $idProposta);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) {
        $conn->rollback();
        respostaJson(409, ['ok' => false, 'erro' => 'Esta proposta já foi respondida.']);
    }

    // PARTE 4: quando $acao === 'aceitar', é aqui que será criado o registro em "servico"
    // e gravado proposta.id_servico, ainda dentro desta transação.

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    error_log('proposta_responder: ' . $e->getMessage());
    respostaJson(500, ['ok' => false, 'erro' => 'Não foi possível responder à proposta.']);
}

respostaJson(200, ['ok' => true, 'id_proposta' => $idProposta, 'status' => $novoStatus]);