<?php


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>''
    <h1>Denunciar</h1>
    <form action="denuncia_php.php" method="post">
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
        <input type="hidden" name="id_alvo" value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>">

        <label for="detalhes">Detalhes adicionais (opcional):</label>
        <textarea id="detalhes" name="detalhes" rows="4" cols="50" placeholder="Forneça mais detalhes sobre a denúncia..."></textarea>

        <button type="submit">Enviar Denúncia</button>
    </form>
</body>
</html>