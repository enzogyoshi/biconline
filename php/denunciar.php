<?php
require_once "conn.php";
require_once 'proteger.php';

$id_usuario_denunciado = (int)($_GET['usuario'] ?? 0);
$id_usuario = (int)($_SESSION['usuario'] ?? 0);
$motivo = $_POST['motivo'] ?? '';
$detalhes = $_POST['detalhes'] ?? '';



$sql = "INSERT INTO denuncia (motivo, descricao, id_usuario_denunciante, id_usuario_denunciado) VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $motivo, $detalhes, $id_usuario, $id_usuario_denunciado);
$stmt->execute();



?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denunciar</title>
</head>
<body>
    <h1>Denunciar</h1>
    <form action="denunciar.php" method="post">
        <label for="motivo">Motivo da denúncia:</label>
        <select id="motivo" name="motivo" required>
            <option value="">Selecione um motivo</option>
            <option value="conteudo_inapropriado">Conteúdo inapropriado</option>
            <option value="Publicação ofenciva">Publicação ofensiva</option>
            <option value="Não compriu com o serviço">Não compriu com o serviço</option>
            <option value="spam">Spam</option>
            <option value="assedio">Assédio</option>
            <option value="outro">Outro</option>
        </select>
        <br><br>
        <label for="detalhes">Detalhes adicionais (opcional):</label>
        <br>
        <textarea id="detalhes" name="detalhes" rows="4" cols="50" placeholder="Forneça mais detalhes sobre a denúncia..."></textarea>
        <br><br>
        <button type="submit">Enviar Denúncia</button>
    </form>
</body>
</html>