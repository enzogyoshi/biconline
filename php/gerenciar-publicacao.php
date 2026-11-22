<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "conn.php";

if (!isset($_SESSION["usuario"]) || empty($_SESSION["usuario"])) {
    http_response_code(401);
    exit("Você precisa estar logado.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método não permitido.");
}

$id_usuario = (int) $_SESSION["usuario"];
$id_publicacao = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$acao = $_POST["acao"] ?? "";
$voltar = ($_POST["voltar"] ?? "") === "feed" ? "feed" : "perfil";

if (!$id_publicacao || !in_array($acao, ["editar", "excluir"], true)) {
    http_response_code(400);
    exit("Solicitação inválida.");
}

if ($acao === "excluir") {
    $stmt = $conn->prepare("DELETE FROM publicacao WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_publicacao, $id_usuario);
} else {
    $texto = trim($_POST["texto"] ?? "");
    $link = trim($_POST["link"] ?? "");

    if ($texto === "") {
        http_response_code(400);
        exit("O texto da publicação não pode ficar vazio.");
    }

    if ($link !== "" && !filter_var($link, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        exit("Informe um link válido.");
    }

    $link_bd = $link !== "" ? $link : null;
    $arquivo = $_FILES["midia"] ?? null;
    $nova_midia = null;

    if ($arquivo && $arquivo["error"] !== UPLOAD_ERR_NO_FILE) {
        if ($arquivo["error"] !== UPLOAD_ERR_OK || $arquivo["size"] > 5 * 1024 * 1024) {
            http_response_code(400);
            exit("A mídia deve ter no máximo 5 MB e ser enviada corretamente.");
        }

        $tipos_permitidos = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/gif" => "gif",
            "image/webp" => "webp",
            "video/mp4" => "mp4",
            "video/webm" => "webm",
            "video/ogg" => "ogv"
        ];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipo = $finfo->file($arquivo["tmp_name"]);

        if (!isset($tipos_permitidos[$tipo])) {
            http_response_code(400);
            exit("Formato de imagem ou vídeo não permitido.");
        }

        $pasta = __DIR__ . "/../img/";
        if (!is_dir($pasta) && !mkdir($pasta, 0777, true)) {
            http_response_code(500);
            exit("Não foi possível salvar a mídia.");
        }

        $nome_arquivo = uniqid("pub_", true) . "." . $tipos_permitidos[$tipo];
        if (!move_uploaded_file($arquivo["tmp_name"], $pasta . $nome_arquivo)) {
            http_response_code(500);
            exit("Não foi possível salvar a mídia.");
        }
        $nova_midia = "/biconline/img/" . $nome_arquivo;
    }

    if ($nova_midia !== null) {
        $stmt = $conn->prepare("UPDATE publicacao SET conteudo = ?, link = ?, url_midia = ? WHERE id = ? AND id_usuario = ?");
        $stmt->bind_param("sssii", $texto, $link_bd, $nova_midia, $id_publicacao, $id_usuario);
    } else {
        $stmt = $conn->prepare("UPDATE publicacao SET conteudo = ?, link = ? WHERE id = ? AND id_usuario = ?");
        $stmt->bind_param("ssii", $texto, $link_bd, $id_publicacao, $id_usuario);
    }
}

if (!$stmt->execute()) {
    http_response_code(500);
    exit("Não foi possível salvar a alteração.");
}

$stmt->close();
$conn->close();
header("Location: " . ($acao === "editar"
    ? "editar-publicacao.php?id=" . $id_publicacao . "&voltar=" . $voltar . "&sucesso=1"
    : ($voltar === "feed" ? "../feed/feed.php" : "perfil.php")));
exit;
?>
