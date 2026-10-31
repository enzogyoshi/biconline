<?php
session_start();
require_once __DIR__ . '/conn.php';

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$idUsuario = (int)$_SESSION['usuario'];
$idTrabalhador = (int)($_GET['trabalhador'] ?? 0);

if ($idTrabalhador <= 0) {
    http_response_code(400);
    exit('Trabalhador inválido.');
}

$stmt = $conn->prepare("SELECT id_usuario FROM trabalhador WHERE id = ?");
$stmt->bind_param("i", $idTrabalhador);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();

if (!$t) {
    http_response_code(404);
    exit('Trabalhador não encontrado.');
}

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
    $stmt->execute();
    $idChat = (int)$conn->insert_id;
} else {
    $idChat = (int)$c['id'];
}

header('Location: chat.php?id=' . $idChat);
exit;