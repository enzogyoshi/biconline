<?php
// Gera o token assinado que o chat valida. Use só em páginas com usuário logado.
function gerarTokenChat(array $u): string {
    $dados = json_encode([
        'id'    => (string)$u['id'],
        'nome'  => $u['nome'],
        'papel' => ($u['papel'] ?? '') === 'moderador' ? 'moderador' : 'trabalhador',
        'exp'   => time() + 600, // vale 10 minutos para conectar
    ], JSON_UNESCAPED_UNICODE);

    $b64 = rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');
    return $b64 . '.' . hash_hmac('sha256', $b64, CHAT_SEGREDO);
}
