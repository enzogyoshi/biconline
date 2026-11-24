<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro'] = 'Nenhum formulário enviado.';
    header('Location: esqueci_senha.php');
    exit;
}

require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/mailer.php';

function campo($chave) {
    return (isset($_POST[$chave]) && is_string($_POST[$chave])) ? $_POST[$chave] : "";
}

$email = strtolower(trim(campo("email")));

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro'] = 'Informe um e-mail válido.';
    header('Location: esqueci_senha.php');
    exit;
}

$stmt = $conn->prepare("SELECT id FROM usuario WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($usuario) {
    $idUsuario = (int)$usuario['id'];

    $stmtInv = $conn->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE id_usuario = ? AND usado = 0");
    $stmtInv->bind_param("i", $idUsuario);
    $stmtInv->execute();
    $stmtInv->close();

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $stmtIns = $conn->prepare("
        INSERT INTO recuperacao_senha (id_usuario, token_hash, expira_em)
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
    ");
    $stmtIns->bind_param("is", $idUsuario, $tokenHash);
    $stmtIns->execute();
    $stmtIns->close();

    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $dirAtual = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $linkRecuperacao = "{$protocolo}://{$_SERVER['HTTP_HOST']}{$dirAtual}/redefinir_senha.php?token={$token}";

    enviarEmailRecuperacao($email, $linkRecuperacao);
}

$_SESSION['sucesso'] = 'Se o e-mail estiver cadastrado, enviaremos um link para redefinir sua senha.';
header('Location: esqueci_senha.php');
exit;