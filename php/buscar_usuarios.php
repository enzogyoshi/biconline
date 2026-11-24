<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'proteger.php';
require_once 'conn.php';
require_once 'ofuscar_id.php';

$termo = trim($_GET['q'] ?? '');

if (strlen($termo) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, nome, url_foto_perfil FROM usuario WHERE nome LIKE ? ORDER BY nome ASC LIMIT 8");
$likeTermo = '%' . $termo . '%';
$stmt->bind_param("s", $likeTermo);
$stmt->execute();
$resultado = $stmt->get_result();

$usuarios = [];
while ($row = $resultado->fetch_assoc()) {
    $usuarios[] = [
        'codigo_url' => esconderId((int)$row['id']),
        'nome' => $row['nome'],
        'foto' => $row['url_foto_perfil']
    ];
}

$stmt->close();
echo json_encode($usuarios);