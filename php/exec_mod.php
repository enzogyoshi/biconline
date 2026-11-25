<?php
require_once 'proteger.php';
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: moderar.php");
    exit;
}

$id_moderador = (int)($_SESSION['usuario'] ?? 0);

$stmtCheck = $conn->prepare("SELECT is_moderador FROM usuario WHERE id = ?");
$stmtCheck->bind_param("i", $id_moderador);
$stmtCheck->execute();
$eh_mod = (int)($stmtCheck->get_result()->fetch_assoc()['is_moderador'] ?? 0);
$stmtCheck->close();

if ($eh_mod !== 1) {
    http_response_code(403);
    exit("Acesso negado.");
}

$id_denuncia     = (int)($_POST['id_denuncia'] ?? 0);
$id_usuario_alvo = (int)($_POST['id_usuario_alvo'] ?? 0);
$id_publicacao   = (int)($_POST['id_publicacao'] ?? 0);
$item_denunciado = trim($_POST['item_denunciado'] ?? '');
$motivo          = trim($_POST['motivo'] ?? '');
$acao            = trim($_POST['acao'] ?? '');

if ($id_denuncia <= 0 || $acao === '') {
    header("Location: moderar.php?status=erro");
    exit;
}

$sql_moderar = "INSERT INTO moderacao (item_denunciado, motivo, acao, id_moderador, id_usuario_alvo, id_denuncia) 
                VALUES (?, ?, ?, ?, ?, ?)";
$stmt_moderar = $conn->prepare($sql_moderar);
$stmt_moderar->bind_param('sssiii', $item_denunciado, $motivo, $acao, $id_moderador, $id_usuario_alvo, $id_denuncia);
$stmt_moderar->execute();
$stmt_moderar->close();

if ($acao === 'Deletar Publicacao' && $id_publicacao > 0) {
    $stmtDelPub = $conn->prepare("DELETE FROM publicacao WHERE id = ?");
    $stmtDelPub->bind_param("i", $id_publicacao);
    $stmtDelPub->execute();
    $stmtDelPub->close();

} elseif ($acao === 'Banir' && $id_usuario_alvo > 0) {

    $conn->begin_transaction();

    try {
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // 1. Atualiza os dados do usuário para estado de banido (sem excluir a conta)
        $nome_banido = "Usuário Banido";
        $bio = "Este usuário foi banido por violar as regras da plataforma.";
        $stmt_update = $conn->prepare("UPDATE usuario SET nome = ?, bio = ?, url_foto_perfil = NULL WHERE id = ?");
        $stmt_update->bind_param("ssi", $nome_banido, $bio, $id_usuario_alvo);
        $stmt_update->execute();
        $stmt_update->close();

        // 2. Apaga propostas vinculadas aos chats do usuário
        $stmt = $conn->prepare("
            DELETE p FROM proposta p
            INNER JOIN chat c ON p.id_chat = c.id
            LEFT JOIN trabalhador t ON c.id_trabalhador = t.id
            WHERE c.id_usuario = ? OR t.id_usuario = ?
        ");
        if ($stmt) {
            $stmt->bind_param("ii", $id_usuario_alvo, $id_usuario_alvo);
            $stmt->execute();
            $stmt->close();
        }

        // 3. Apaga mensagens dos chats do usuário
        $stmt = $conn->prepare("
            DELETE m FROM mensagem m
            INNER JOIN chat c ON m.id_chat = c.id
            LEFT JOIN trabalhador t ON c.id_trabalhador = t.id
            WHERE c.id_usuario = ? OR t.id_usuario = ?
        ");
        if ($stmt) {
            $stmt->bind_param("ii", $id_usuario_alvo, $id_usuario_alvo);
            $stmt->execute();
            $stmt->close();
        }

        // 4. Apaga os chats
        $stmt = $conn->prepare("
            DELETE c FROM chat c
            LEFT JOIN trabalhador t ON c.id_trabalhador = t.id
            WHERE c.id_usuario = ? OR t.id_usuario = ?
        ");
        if ($stmt) {
            $stmt->bind_param("ii", $id_usuario_alvo, $id_usuario_alvo);
            $stmt->execute();
            $stmt->close();
        }

        // 5. Apaga habilidades e perfil de trabalhador
        $stmt = $conn->prepare("
            DELETE h FROM habilidades h
            INNER JOIN trabalhador t ON h.id_trabalhador = t.id
            WHERE t.id_usuario = ?
        ");
        if ($stmt) {
            $stmt->bind_param("i", $id_usuario_alvo);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("DELETE FROM trabalhador WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario_alvo);
        $stmt->execute();
        $stmt->close();

        // 6. Apaga publicações, contatos e denúncias ligadas a ele
        $tabelas = [
            "DELETE FROM publicacao WHERE id_usuario = ?",
            "DELETE FROM contatos WHERE id_usuario = ?",
            "DELETE FROM denuncia WHERE id_usuario_denunciado = ? OR id_usuario_denunciante = ?",
        ];

        foreach ($tabelas as $sql) {
            $stmt = $conn->prepare($sql);
            if (substr_count($sql, '?') === 2) {
                $stmt->bind_param("ii", $id_usuario_alvo, $id_usuario_alvo);
            } else {
                $stmt->bind_param("i", $id_usuario_alvo);
            }
            $stmt->execute();
            $stmt->close();
        }

        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $conn->commit();

    } catch (Throwable $e) {
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $conn->rollback();
        throw $e;
    }
}


$stmtDelDenuncia = $conn->prepare("DELETE FROM denuncia WHERE id = ?");
$stmtDelDenuncia->bind_param("i", $id_denuncia);
$stmtDelDenuncia->execute();
$stmtDelDenuncia->close();

header("Location: moderar.php?status=sucesso");
exit;


