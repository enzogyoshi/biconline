<?php

require_once 'usuario.php';

if (!isset($is_moderador) || $is_moderador != 1) {
    http_response_code(403);
    exit("Acesso negado. Você não tem permissão para acessar esta página.");
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Moderar</h1>
    
</body>
</html>


