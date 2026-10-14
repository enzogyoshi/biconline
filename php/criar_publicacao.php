<?php
include "conn.php";

$texto_publicacao = isset($_POST["texto"]) ? $_POST["texto"] : "";
$arquivo_midia    = isset($_FILES["midia"]) ? $_FILES["midia"] : "";
$link_publicacao  = isset($_POST["link"]) ? trim($_POST["link"]) : "";

// ID temporário para testes
$id_usuario = 1;

$limite_tamanho_foto = 5242880; // 5MB
$caminho_midia = null; // Usar null caso nenhuma mídia seja enviada

// Área de validação e upload do arquivo
if (isset($arquivo_midia["error"]) && $arquivo_midia["error"] === UPLOAD_ERR_OK) {
    $extensoes_permitidas = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    $extensao = strtolower(pathinfo($arquivo_midia["name"], PATHINFO_EXTENSION));

    if (in_array($extensao, $extensoes_permitidas)) {
        if ($arquivo_midia["size"] <= $limite_tamanho_foto) {
            
            $nome_arquivo = time() . "_" . uniqid() . "." . $extensao;
            $destino = "../img/" . $nome_arquivo;

            if (move_uploaded_file($arquivo_midia["tmp_name"], $destino)) {
                $caminho_midia = "img/" . $nome_arquivo;
            } else {
                echo "Erro ao mover o arquivo enviado.<br>";
            }

        } else {
            echo "Tamanho de arquivo maior que 5MB.<br>";
        }
    } else {
        echo "Tipo de arquivo não permitido.<br>";
    }
}


$data_pub = date("Y-m-d");


$sql = "INSERT INTO publicacao (conteudo, url_midia, link, data_pub, id_usuario) VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    
    $stmt->bind_param("ssssi", $texto_publicacao, $caminho_midia, $link_publicacao, $data_pub, $id_usuario);

    if ($stmt->execute()) {
        echo "Publicação realizada com sucesso!";
    } else {
        echo "Erro ao publicar: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Erro na preparação da consulta: " . $conn->error;
}

$conn->close();
?>