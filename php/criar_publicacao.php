<?php
include "conn.php";

$texto_publicacao = isset($_POST["texto"]) ? $_POST["texto"] : "";
$arquivo_midia    = isset($_FILES["midia"]) ? $_FILES["midia"] : "";
$link_publicacao  = isset($_POST["link"]) ? trim($_POST["link"]) : "";

// ID temporário para testes
$id_usuario = 1;

$limite_tamanho_foto = 5242880; 
$caminho_midia = '';

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

// Trata os textos contra caracteres especiais para evitar erros na query SQL
$texto_seguro = $conn->real_escape_string($texto_publicacao);
$link_seguro  = $conn->real_escape_string($link_publicacao);

// Instrução SQL corrigida (aspas e parêntesis fechados corretamente)
$sql = "INSERT INTO publicacao (conteudo, url_midia, link, data_pub, id_usuario)
        VALUES ('$texto_seguro', '$caminho_midia', '$link_seguro', NOW(), '$id_usuario')";

if ($conn->query($sql) === TRUE) {
    echo "Publicação realizada com sucesso!";
} else {
    echo "Erro ao publicar: " . $conn->error;
}

$conn->close();
?>