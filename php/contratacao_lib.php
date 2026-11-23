<?php
if (!function_exists('h')) {
    function h($texto): string {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}
function h($texto): string {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function respostaJson(int $codigo, array $dados): void {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function exigirLoginJson(): int {
    if (empty($_SESSION['usuario'])) {
        respostaJson(401, ['ok' => false, 'erro' => 'Você precisa entrar para continuar.']);
    }
    return (int)$_SESSION['usuario'];
}

function exigirPost(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        respostaJson(405, ['ok' => false, 'erro' => 'Método não permitido.']);
    }
}

//proteção contra envio forjado
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function exigirCsrf(): void {
    $enviado = (string)($_POST['csrf'] ?? '');
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $enviado)) {
        respostaJson(403, ['ok' => false, 'erro' => 'Sessão expirada. Recarregue a página.']);
    }
}

function conversaDoUsuario(mysqli $conn, int $idChat, int $idUsuario): ?array {
    $stmt = $conn->prepare("
        SELECT c.id,
               c.id_usuario      AS id_contratante,
               t.id              AS id_trabalhador,
               t.id_usuario      AS id_usuario_trabalhador,
               uc.nome           AS nome_contratante,
               ut.nome           AS nome_trabalhador
        FROM chat c
        JOIN trabalhador t ON t.id = c.id_trabalhador
        JOIN usuario uc ON uc.id = c.id_usuario
        JOIN usuario ut ON ut.id = t.id_usuario
        WHERE c.id = ? AND (c.id_usuario = ? OR t.id_usuario = ?)");
    $stmt->bind_param("iii", $idChat, $idUsuario, $idUsuario);
    $stmt->execute();
    $c = $stmt->get_result()->fetch_assoc();
    if (!$c) {
        return null;
    }
    $c['papel'] = ((int)$c['id_contratante'] === $idUsuario) ? 'contratante' : 'trabalhador';
    return $c;
}
    function ehConversaValida(array $conversa): bool {
        return (int)$conversa['id_contratante'] !== (int)$conversa['id_usuario_trabalhador'];
    }
// a proposta mais recente da conversa 
function propostaMaisRecente(mysqli $conn, int $idChat): ?array {
    $stmt = $conn->prepare("
        SELECT id, id_chat, descricao, valor, data_servico, status, criada_em, respondida_em, id_servico
        FROM proposta WHERE id_chat = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $idChat);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    return $p ?: null;
}

function lerValor(string $txt) {
    $txt = trim(str_replace(['R$', ' '], '', $txt));
    if ($txt === '') {
        return null;
    }
    if (strpos($txt, ',') !== false) {
        $txt = str_replace('.', '', $txt);
        $txt = str_replace(',', '.', $txt);
    }
    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $txt) || (float)$txt <= 0) {
        return false;
    }
    return number_format((float)$txt, 2, '.', '');
}

function lerData(string $txt) {
    $txt = trim($txt);
    if ($txt === '') {
        return null;
    }
    $d = DateTime::createFromFormat('!Y-m-d', $txt);
    if (!$d || $d->format('Y-m-d') !== $txt || $d < new DateTime('today')) {
        return false;
    }
    return $txt;
}
//envia mensagem automática para o chat
function enviarMensagemSistema(string $nomeSala, string $texto): void {
    if (!defined('CHAT_URL') || !defined('CHAT_SEGREDO')) {
        return; // sem config.local.php carregado, não quebra nada, só não envia
    }
    $payload = json_encode(['segredo' => CHAT_SEGREDO, 'sala' => $nomeSala, 'texto' => $texto]);
 
    $ch = curl_init(rtrim(CHAT_URL, '/') . '/sistema/mensagem');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 2,       // não trava a página esperando o chat
        CURLOPT_CONNECTTIMEOUT => 1,
    ]);
    curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('enviarMensagemSistema: ' . curl_error($ch)); // só registra, nunca quebra a página
    }
    curl_close($ch);
}