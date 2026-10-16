<?php
require_once "conn.php";
$texto_publicacao = isset($_POST["texto"]) ? trim($_POST["texto"]) : "";
$arquivo_midia = isset($_FILES["midia"]) ? $_FILES["midia"] : null;
$link = isset($_POST["link"]) ? trim($_POST["link"]) : "";
$id_usuario = 1;
$url_midia = null;
if (
    $texto_publicacao === "" &&
    empty($arquivo_midia["name"]) &&
    $link === ""
) {
    die("Escreva algo ou envie uma mídia ou link.");
}
if (!empty($arquivo_midia["name"])) {
    if ($arquivo_midia["error"] !== UPLOAD_ERR_OK) {
        die("Erro ao enviar a mídia.");
    }
    if ($arquivo_midia["size"] > 5 * 1024 * 1024) {
        die("A mídia deve ter no máximo 5 MB.");
    }
    $pasta = __DIR__ . "/../img/";
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }
    $nome_arquivo = uniqid() . "_" . basename($arquivo_midia["name"]);
    $destino = $pasta . $nome_arquivo;
    if (move_uploaded_file($arquivo_midia["tmp_name"], $destino)) {
        $url_midia = "img/" . $nome_arquivo;
    } else {
        die("Erro ao salvar a mídia.");
    }
}
$sql = "INSERT INTO publicacao
        (conteudo, url_midia, link, data_pub, id_usuario)
        VALUES (?, ?, ?, NOW(), ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssi",
    $texto_publicacao,
    $url_midia,
    $link,
    $id_usuario
);
if ($stmt->execute()) {
    echo "Publicação salva com sucesso!";
} else {
    http_response_code(500);
    echo "Erro ao salvar publicação.";
}
$stmt->close();
$conn->close();
?>