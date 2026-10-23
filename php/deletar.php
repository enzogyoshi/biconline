<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("conn.php");

if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Usuário não logado.";
    header("Location: login.php");
    exit;
}

$id = $_SESSION["usuario"];

$stmt_trab = $conn->prepare("SELECT id FROM usuario WHERE id = ?");
$stmt_trab->bind_param("i", $id);
$stmt_trab->execute();
$resultado_usuario = $stmt_trab->get_result()->fetch_assoc();
$stmt_trab->close();

if (!$resultado_usuario) {
    $_SESSION['erro'] = "Usuário não encontrado.";
    header("Location: login.php");
    exit;
}

$conn->begin_transaction();

try {
    $stmt_get_trab = $conn->prepare("SELECT id FROM trabalhador WHERE id_usuario = ?");
    $stmt_get_trab->bind_param("i", $id);
    $stmt_get_trab->execute();
    $res_trab = $stmt_get_trab->get_result()->fetch_assoc();
    $stmt_get_trab->close();
    
    $id_trabalhador = $res_trab['id'] ?? null;

    if ($id_trabalhador) {
        $stmt = $conn->prepare("DELETE FROM habilidades WHERE id_trabalhador = ?");
        $stmt->bind_param("i", $id_trabalhador);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM mensagem WHERE id_chat IN (SELECT id FROM chat WHERE id_trabalhador = ?)");
        $stmt->bind_param("i", $id_trabalhador);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM chat WHERE id_trabalhador = ?");
        $stmt->bind_param("i", $id_trabalhador);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM avaliacoes WHERE id_servico IN (SELECT id FROM servico WHERE id_contratado = ?)");
        $stmt->bind_param("i", $id_trabalhador);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM servico WHERE id_contratado = ?");
        $stmt->bind_param("i", $id_trabalhador);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("DELETE FROM avaliacoes WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM avaliacoes WHERE id_servico IN (SELECT id FROM servico WHERE id_contratante = ?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM servico WHERE id_contratante = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM mensagem WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM chat WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM publicacao WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM contatos WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    if ($id_trabalhador) {
        $stmt = $conn->prepare("DELETE FROM trabalhador WHERE id = ?");
        $stmt->bind_param("i", $id_trabalhador);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("DELETE FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    session_destroy();
    session_start();
    $_SESSION['sucesso'] = "Sua conta e todos os seus dados foram removidos com sucesso.";
    header("Location: login.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['erro'] = "Erro ao deletar conta. Tente novamente mais tarde.";
    header("Location: perfil.php");
    exit;
}
?>