<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'proteger.php';
require_once 'conn.php';
require_once 'ofuscar_id.php';

$idLogado = (int)($_SESSION['usuario'] ?? 0);
$codigoUsuario = trim($_GET['usuario'] ?? '');
$idFiltroUsuario = null;

if ($codigoUsuario !== '') {
    $idFiltroUsuario = revelarId($codigoUsuario);
    if ($idFiltroUsuario === null || $idFiltroUsuario <= 0) {
        echo json_encode([]);
        exit;
    }
}

$sql = "SELECT 
            p.id,
            p.id_usuario,
            p.conteudo,
            p.url_midia,
            p.link,
            p.data_pub,
            u.nome,
            u.url_foto_perfil
        FROM publicacao p
        INNER JOIN usuario u ON p.id_usuario = u.id";

if ($idFiltroUsuario !== null) {
    $sql .= " WHERE p.id_usuario = ? ORDER BY p.data_pub DESC, p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idFiltroUsuario);
} else {
    $sql .= " ORDER BY p.data_pub DESC, p.id DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$resultado = $stmt->get_result();

$publicacoes = [];
while ($row = $resultado->fetch_assoc()) {
    $ehDono = ((int)$row['id_usuario'] === $idLogado);
    $publicacoes[] = [
        'id'              => (int)$row['id'],
        'id_usuario'      => esconderId((int)$row['id_usuario']),
        'nome'            => $row['nome'],
        'url_foto_perfil' => $row['url_foto_perfil'],
        'conteudo'        => $row['conteudo'],
        'url_midia'       => $row['url_midia'],
        'link'            => $row['link'],
        'data_pub'        => $row['data_pub'],
        'eh_dono'         => $ehDono
    ];
}

$stmt->close();
echo json_encode($publicacoes);