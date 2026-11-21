<?php
session_start();
require_once __DIR__ . '/conn.php';

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$idUsuario = (int)$_SESSION['usuario'];
$porUsuario = isset($_GET['usuario']);
$idParam = (int)($_GET['usuario'] ?? $_GET['trabalhador'] ?? 0);

if ($idParam <= 0) {
    http_response_code(400);
    exit('Pedido inválido.');
}

$sql = $porUsuario
    ? "SELECT id, id_usuario FROM trabalhador WHERE id_usuario = ? LIMIT 1"
    : "SELECT id, id_usuario FROM trabalhador WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idParam);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();

if (!$t) {
    http_response_code(404);
    exit('Essa pessoa não é um trabalhador, então não recebe conversas.');
}

$idTrabalhador = (int)$t['id'];
$idOutro = (int)$t['id_usuario'];

if ($idOutro === $idUsuario) {
    exit('Você não pode abrir uma conversa consigo mesmo.');
}

$stmt = $conn->prepare("
    SELECT c.id
    FROM chat c
    JOIN trabalhador tr ON tr.id = c.id_trabalhador
    WHERE (c.id_usuario = ? AND tr.id_usuario = ?)
       OR (c.id_usuario = ? AND tr.id_usuario = ?)
    LIMIT 1");
$stmt->bind_param("iiii", $idUsuario, $idOutro, $idOutro, $idUsuario);
$stmt->execute();
$c = $stmt->get_result()->fetch_assoc();

if (!$c) {
    $stmt = $conn->prepare("INSERT INTO chat (id_usuario, id_trabalhador) VALUES (?, ?)");
    $stmt->bind_param("ii", $idUsuario, $idTrabalhador);
    if (!$stmt->execute()) {
        http_response_code(500);
        exit('Erro ao criar a conversa: ' . htmlspecialchars($stmt->error));
    }
    $idChat = (int)$stmt->insert_id;
} else {
    $idChat = (int)$c['id'];
}

if ($idChat <= 0) {
    exit('Não foi possível abrir a conversa.');
}

header('Location: chat.php?id=' . $idChat);
exit;