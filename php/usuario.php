<?php

require_once 'proteger.php';
require_once 'conn.php';
require_once 'ofuscar_id.php';

$id_logado = (int)$_SESSION['usuario'];

$stmt_ban = $conn->prepare("SELECT acao FROM moderacao WHERE id_usuario_alvo = ? AND acao = 'Banir' LIMIT 1");
$stmt_ban->bind_param("i", $id_logado);
$stmt_ban->execute();
$foi_banido = $stmt_ban->get_result()->num_rows > 0;
$stmt_ban->close();

if ($foi_banido) {
    $permissoes = false;
    session_unset();
    session_destroy();
    header("Location: login.php?erro=conta_banida");
    exit;
} 


// is_Moderador
$stmt_mod = $conn->prepare("SELECT is_moderador FROM usuario WHERE id = ?");
$stmt_mod->bind_param("i", $id_logado);
$stmt_mod->execute();
$res_mod = $stmt_mod->get_result()->fetch_assoc();
$stmt_mod->close();

$moderador = (int)($res_mod['is_moderador'] ?? 0);
$_SESSION['is_moderador'] = $moderador;



$permissoes = false;

if (isset($_GET['pesquisa']) && trim($_GET['pesquisa']) !== '') {
    $id_decodificado = revelarId(trim($_GET['pesquisa']));

    if ($id_decodificado === null || $id_decodificado <= 0) {
        http_response_code(404);
        exit("Perfil inválido.");
    }

    $id_usuario = $id_decodificado;

    if ($id_usuario == $_SESSION['usuario']) {
        $permissoes = true;
    }
} else {
    $id_usuario = (int)$_SESSION['usuario'];
    $permissoes = true;
}

// Usuario
$sql = "SELECT nome, cidade, uf, bio, score_avaliacao, url_foto_perfil FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

if (!$usuario) {
    http_response_code(404);
    exit("Usuário não encontrado.");
}

// Contato
$sql4 = 'SELECT numero_telefone, email_contato FROM contatos WHERE id_usuario = ?';
$stmt4 = $conn->prepare($sql4);
$stmt4->bind_param('i', $id_usuario);
$stmt4->execute();
$resultado4 = $stmt4->get_result();
$contatos = $resultado4->fetch_assoc();
$stmt4->close();

$telefone = $contatos["numero_telefone"] ?? "Não informado";
$email_contato = $contatos["email_contato"] ?? "Não informado";

// Trabalhador
$sql3 = 'SELECT id, disponibilidade, experiencia FROM trabalhador WHERE id_usuario = ?';
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param('i', $id_usuario);
$stmt3->execute();
$resultado3 = $stmt3->get_result();
$trabalhador = $resultado3->fetch_assoc();
$stmt3->close();

$eh_trabalhador = !empty($trabalhador);
$experiencia = $trabalhador["experiencia"] ?? 0;
$disponibilidade = $trabalhador["disponibilidade"] ?? 0;
$id_trab = $trabalhador["id"] ?? null;

// Habilidades e Serviços
$habilidades_lista = [];
$servicos = [];

$sql_servicos = "SELECT 
                        s.id AS id_servico,
                        s.servico_prestado,
                        s.data_serv,
                        s.status_serv,
                        s.id_contratado,
                        u.nome AS nome_contratado
                    FROM servico s
                    INNER JOIN trabalhador t ON s.id_contratado = t.id
                    INNER JOIN usuario u ON t.id_usuario = u.id
                    WHERE s.id_contratante = ?
                    ORDER BY s.data_serv DESC";

if ($stmt_serv = $conn->prepare($sql_servicos)) {
    $stmt_serv->bind_param("i", $id_usuario);
    $stmt_serv->execute();
    $res_serv = $stmt_serv->get_result();
    while ($row = $res_serv->fetch_assoc()) {
        $servicos[] = $row;
    }
    $stmt_serv->close();

    if ($eh_trabalhador && $id_trab) {
        $sql2 = 'SELECT habilidade FROM habilidades WHERE id_trabalhador = ?';
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param('i', $id_trab);
        $stmt2->execute();
        $resultado2 = $stmt2->get_result();
        while ($row_hab = $resultado2->fetch_assoc()) {
            $habilidades_lista[] = $row_hab["habilidade"];
        }
        $stmt2->close();

        $sql_servicos = "SELECT 
                        s.id AS id_servico,
                        s.servico_prestado,
                        s.data_serv,
                        s.status_serv,
                        s.id_contratante,
                        u.nome AS nome_contratante
                    FROM servico s
                    INNER JOIN usuario u ON s.id_contratante = u.id
                    WHERE s.id_contratado = ?
                    ORDER BY s.data_serv DESC";

        if ($stmt_serv = $conn->prepare($sql_servicos)) {
            $stmt_serv->bind_param("i", $id_trab);
            $stmt_serv->execute();
            $res_serv = $stmt_serv->get_result();
            while ($row = $res_serv->fetch_assoc()) {
                $servicos[] = $row;
            }
            $stmt_serv->close();
        }
    }
}

// Publicações 
$publicacoes = [];
if ($id_usuario) {
    $sql_publicacoes = "SELECT
                            p.id,
                            p.conteudo,
                            p.url_midia,
                            p.link,
                            p.data_pub,
                            u.nome,
                            u.url_foto_perfil
                        FROM publicacao p
                        LEFT JOIN usuario u ON p.id_usuario = u.id
                        WHERE p.id_usuario = ?
                        ORDER BY p.data_pub DESC, p.id DESC";

    if ($stmt_publica = $conn->prepare($sql_publicacoes)) {
        $stmt_publica->bind_param("i", $id_usuario);
        if ($stmt_publica->execute()) {
            $resultado_pub = $stmt_publica->get_result();
            while ($row_pub = $resultado_pub->fetch_assoc()) {
                $publicacoes[] = $row_pub;
            }
        }
        $stmt_publica->close();
    }
}

$nome = $usuario["nome"] ?? "Sem nome";
$cidade = $usuario["cidade"] ?? "Sem cidade";
$uf = $usuario["uf"] ?? "";
$bio = $usuario["bio"] ?? "Sem biografia";
$score = $usuario["score_avaliacao"] ?? 0;
$url_foto = $usuario["url_foto_perfil"] ?? "";

?>