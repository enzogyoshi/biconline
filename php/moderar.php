<?php

require_once 'usuario.php';

if (!isset($is_moderador) || $is_moderador != 1) {
    http_response_code(403);
    exit("Acesso negado. Você não tem permissão para acessar esta página.");
}

$sql = "SELECT d.id, d.motivo, d.descricao, u.nome AS denunciante_nome, u2.nome AS denunciado_nome
        FROM denuncia d
        JOIN usuario u ON d.id_usuario_denunciante = u.id
        JOIN usuario u2 ON d.id_usuario_denunciado = u2.id";

$resultado = $conn->query($sql);

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

    <?php
    if(isset($denuncia))
        
    ?>
</body>
</html>


