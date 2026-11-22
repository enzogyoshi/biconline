<?php
// PARTE 2 — O contratante envia uma proposta de contratação dentro de uma conversa.
// POST: csrf, id_chat, descricao, valor (opcional), data_servico (opcional, AAAA-MM-DD)
session_start();
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/contratacao_lib.php';

$idUsuario = exigirLoginJson();
exigirPost();
exigirCsrf();

$idChat = (int)($_POST['id_chat'] ?? 0);
$descricao = trim((string)($_POST['descricao'] ?? ''));
$valor = lerValor((string)($_POST['valor'] ?? ''));
$data = lerData((string)($_POST['data_servico'] ?? ''));

if ($idChat <= 0) {
    respostaJson(400, ['ok' => false, 'erro' => 'Conversa inválida.']);
}
if (mb_strlen($descricao) < 3 || mb_strlen($descricao) > 150) {
    respostaJson(400, ['ok' => false, 'erro' => 'Descreva o serviço em 3 a 150 caracteres.']);
}
if ($valor === false) {
    respostaJson(400, ['ok' => false, 'erro' => 'Valor inválido. Exemplo: 150,00']);
}
if ($data === false) {
    respostaJson(400, ['ok' => false, 'erro' => 'Data inválida (use hoje ou uma data futura).']);
}

// a pessoa faz parte da conversa e é quem contrata?
$conversa = conversaDoUsuario($conn, $idChat, $idUsuario);
if (!$conversa) {
    respostaJson(403, ['ok' => false, 'erro' => 'Você não faz parte dessa conversa.']);
}
 if (!ehConversaValida($conversa)) {
        respostaJson(403, ['ok' => false, 'erro' => 'Conversa inválida.']);
}
if ($conversa['papel'] !== 'contratante') {
    respostaJson(403, ['ok' => false, 'erro' => 'Só quem iniciou a conversa pode enviar uma proposta de contratação.']);
}

try {
    $conn->begin_transaction();

    // trava a conversa: duas propostas ao mesmo tempo não passam pela checagem juntas
    $stmt = $conn->prepare("SELECT id FROM chat WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $idChat);
    $stmt->execute();
    $stmt->get_result();

    $stmt = $conn->prepare("SELECT id FROM proposta WHERE id_chat = ? AND status = 'pendente' LIMIT 1");
    $stmt->bind_param("i", $idChat);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $conn->rollback();
        respostaJson(409, ['ok' => false, 'erro' => 'Já existe uma proposta aguardando resposta nesta conversa.']);
    }

    $stmt = $conn->prepare("INSERT INTO proposta (id_chat, descricao, valor, data_servico) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $idChat, $descricao, $valor, $data);
    if (!$stmt->execute()) {
        throw new RuntimeException('Falha ao gravar a proposta.');
    }
    $idProposta = (int)$stmt->insert_id;
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    error_log('proposta_criar: ' . $e->getMessage());
    respostaJson(500, ['ok' => false, 'erro' => 'Não foi possível criar a proposta.']);
}

respostaJson(201, [
    'ok' => true,
    'proposta' => [
        'id' => $idProposta,
        'id_chat' => $idChat,
        'descricao' => $descricao,
        'valor' => $valor,
        'data_servico' => $data,
        'status' => 'pendente',
    ],
]);