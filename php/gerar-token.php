<?php
function gerarTokenChat(array $u): string {
    $dados = json_encode([
        'id'     => (string)$u['id'],
        'nome'   => $u['nome'],
        'papel'  => ($u['papel'] ?? '') === 'moderador' ? 'moderador' : 'trabalhador',
        'sala'   => $u['sala'] ?? 'geral',
        'titulo' => $u['titulo'] ?? '',
        'exp'    => time() + 600, 
    ], JSON_UNESCAPED_UNICODE);

    $b64 = rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');
    return $b64 . '.' . hash_hmac('sha256', $b64, CHAT_SEGREDO);
}