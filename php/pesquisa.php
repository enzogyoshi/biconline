<?php
require_once "proteger.php";
require_once("conn.php");

$pesquisa = filter_input(INPUT_GET, "pesquisa", FILTER_DEFAULT);

if (!empty($pesquisa)) {
    $sql = "SELECT u.id, u.nome, u.cidade, u.uf, u.url_foto_perfil,h.habilidade FROM usuario as u inner join habilidades as h on u.id = h.id_usuario WHERE u.nome LIKE ? OR u.cidade LIKE ? OR u.uf LIKE ? OR h.habilidade LIKE ?";
    $stmt = $conn->prepare($sql);
    $likePesquisa = "%" . $pesquisa . "%";
    $stmt->bind_param("ssss", $likePesquisa, $likePesquisa, $likePesquisa, $likePesquisa);
    $stmt->execute();
    $resultado_busca = $stmt->get_result();
    $usuarios = $resultado_busca->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

}

if (!empty($usuarios)) {
  header("Location: perfil.php?pesquisa=" . $resultado_busca["id"]);
    exit;
} 
//else {
    //$_SESSION["erro"] = "Nenhum usuário encontrado.";
    //header("Location: perfil.php");
    //exit;}