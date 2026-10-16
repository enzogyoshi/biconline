<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "conn.php";
$sql = "SELECT
            p.id,
            p.conteudo,
            p.url_midia,
            p.link,
            p.data_pub,
            u.nome,
            u.url_foto_perfil
        FROM publicacao p
        LEFT JOIN usuario u ON p.id_usuario = u.id
        ORDER BY p.data_pub DESC, p.id DESC";
$resultado = $conn->query($sql);
if (!$resultado) {
    http_response_code(500);
    echo json_encode([
        "erro" => "Não foi possível buscar as publicações."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$publicacoes = [];
while ($linha = $resultado->fetch_assoc()) {
    $publicacoes[] = $linha;
}
echo json_encode($publicacoes, JSON_UNESCAPED_UNICODE);
$conn->close();
?>