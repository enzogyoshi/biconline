<?php
include "conn.php";

$texto_publicacao = isset($_POST["texto"]) ? $_POST["texto"] : "";
$arquivo_midia    = isset($_FILES["midia"]) ? $_FILES["midia"] : "";
$link_publicacao  = isset($_POST["link"]) ? trim($_POST["link"]) : "";

$limite_tamanho_foto = 5242880; 
$caminho_midia = '';

// exemplo teste
$id_usuario = 1;

// Área de arquivos/fotos
if (isset($arquivo_midia["error"]) && $arquivo_midia["error"] == 0) {
    if (
        $arquivo_midia["type"] == "image/png" ||
        $arquivo_midia["type"] == "image/jpeg" ||
        $arquivo_midia["type"] == "image/gif" ||
        $arquivo_midia["type"] == "image/webp"
    ) {
        if ($arquivo_midia["size"] <= $limite_tamanho_foto) {
            
            $nome_arquivo = time() . "_" . $arquivo_midia["name"];
            $destino = "../img/" . $nome_arquivo;

            if (move_uploaded_file($arquivo_midia["tmp_name"], $destino)) {
                $caminho_midia = "img/" . $nome_arquivo;
            }

        } else {
            echo "Tamanho de arquivo maior que 5MB.<br>";
        }
    } else {
        echo "Tipo de arquivo não permitido.<br>";
    }
}


$sql = "INSERT INTO publicacao (conteudo, url_midia, link, data_pub, id_usuario)
        VALUES ('$texto_publicacao', '$caminho_midia', '$link_publicacao', NOW(), '$id_usuario')";

if ($conn->query($sql) === TRUE) {
    echo "Publicação realizada com sucesso!";
} else {
    echo "Erro ao publicar: " . $conn->error;
}

$conn->close();
?>