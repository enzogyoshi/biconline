<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro'] = 'Nenhum formulário enviado.';
    header('Location: login.php');
    exit;
}

require_once "conn.php";

function campo($chave) {
    return (isset($_POST[$chave]) && is_string($_POST[$chave])) ? $_POST[$chave] : "";
}

$token = trim(campo("token"));
$senha = campo("senha");
$senhaConfirm = campo("senhaConfirm");

function voltarComErro($token, $mensagem) {
    $_SESSION['erro'] = $mensagem;
    header("Location: redefinir_senha.php?token=" . urlencode($token));
    exit;
}

if ($token === '' || !ctype_xdigit($token) || strlen($token) !== 64) {
    $_SESSION['erro'] = 'Token inválido.';
    header('Location: login.php');
    exit;
}

if ($senha !== $senhaConfirm) {
    voltarComErro($token, 'As senhas não coincidem.');
}

if (strlen($senha) < 8 || strlen($senha) > 72) {
    voltarComErro($token, 'A senha deve ter entre 8 e 72 caracteres.');
}

$tokenHash = hash('sha256', $token);

$stmt = $conn->prepare("
    SELECT id, id_usuario FROM recuperacao_senha
    WHERE token_hash = ? AND usado = 0 AND expira_em > NOW()
    LIMIT 1
");
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$registro = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$registro) {
    $_SESSION['erro'] = 'Este link expirou ou já foi utilizado.';
    header('Location: esqueci_senha.php');
    exit;
}

$idRecuperacao = (int)$registro['id'];
$idUsuario = (int)$registro['id_usuario'];
$novoHashSenha = password_hash($senha, PASSWORD_DEFAULT);

$conn->begin_transaction();

try {
    $stmtUser = $conn->prepare("UPDATE usuario SET senha = ? WHERE id = ?");
    $stmtUser->bind_param("si", $novoHashSenha, $idUsuario);
    $stmtUser->execute();
    $stmtUser->close();

    $stmtToken = $conn->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE id = ?");
    $stmtToken->bind_param("i", $idRecuperacao);
    $stmtToken->execute();
    $stmtToken->close();

    $conn->commit();

    $_SESSION['sucesso'] = 'Senha redefinida com sucesso! Faça seu login.';
    header('Location: login.php');
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    voltarComErro($token, 'Erro ao atualizar a senha. Tente novamente.');
}