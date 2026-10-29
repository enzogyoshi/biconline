<?php
require_once "proteger.php";
require_once("conn.php");

$pesquisa = filter_input(INPUT_GET, "pesquisa", FILTER_DEFAULT);
if (!empty($pesquisa)) {
$sql_busca = "SELECT id FROM usuario WHERE nome LIKE ? LIMIT 1";
$stmt_busca = $conn->prepare($sql_busca);
$termo_param = "%" . trim($pesquisa) . "%";
$stmt_busca->bind_param("s", $termo_param);
$stmt_busca->execute();
$resultado_busca = $stmt_busca->get_result()->fetch_assoc();
$stmt_busca->close();

if ($resultado_busca) {
    header("Location: perfil.php?pesquisa=" . $resultado_busca["id"]);
    exit;
} else {
    $_SESSION["erro"] = "Nenhum usuário encontrado.";
    header("Location: perfil.php");
    exit;
}
}

?>