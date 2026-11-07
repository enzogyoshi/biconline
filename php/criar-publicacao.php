<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "conn.php";

if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    http_response_code(401);
    die("Você precisa estar logado para publicar.");
}

$id_usuario = $_SESSION['usuario'];

$texto_publicacao = isset($_POST["texto"]) ? trim($_POST["texto"]) : "";
$arquivo_midia = isset($_FILES["midia"]) ? $_FILES["midia"] : null;
$link = isset($_POST["link"]) ? trim($_POST["link"]) : "";

$url_midia = null;
$link_bd = !empty($link) ? $link : null;

if ($texto_publicacao === "" && empty($arquivo_midia["name"]) && $link === "") {
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
        $url_midia = "/biconline/img/" . $nome_arquivo;
    }else {
        die("Erro ao salvar a mídia.");
    }
}
$sql = "INSERT INTO publicacao (conteudo, url_midia, link, data_pub, id_usuario)
        VALUES (?, ?, ?, NOW(), ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssi",
    $texto_publicacao,
    $url_midia,
    $link_bd,
    $id_usuario
);

if ($stmt->execute()) {
    header("Location: perfil.php?sucesso=1");
    exit;
} else {
    http_response_code(500);
    echo "Erro ao salvar publicação no banco de dados.";
}

$stmt->close();
$conn->close();
?>