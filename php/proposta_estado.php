<?php
// PARTE 2 — Estado da contratação em uma conversa (usado pela tela, na Parte 3).
// GET: id_chat
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/contratacao_lib.php';

$idUsuario = exigirLoginJson();
$idChat = (int)($_GET['id_chat'] ?? 0);

$conversa = conversaDoUsuario($conn, $idChat, $idUsuario);
if (!$conversa) {
    respostaJson(403, ['ok' => false, 'erro' => 'Você não faz parte dessa conversa.']);
}

$proposta = propostaMaisRecente($conn, $idChat);
$papel = $conversa['papel'];

respostaJson(200, [
    'ok' => true,
    'papel' => $papel,
    'outro_nome' => ($papel === 'contratante') ? $conversa['nome_trabalhador'] : $conversa['nome_contratante'],
    'pode_propor' => ($papel === 'contratante') && (!$proposta || $proposta['status'] !== 'pendente'),
    'pode_responder' => ($papel === 'trabalhador') && $proposta && $proposta['status'] === 'pendente',
    'pode_cancelar' => ($papel === 'contratante') && $proposta && $proposta['status'] === 'pendente',
    'proposta' => $proposta,
    'csrf' => csrfToken(),
]);