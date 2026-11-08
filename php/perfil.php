<?php
require_once "proteger.php";
require_once("conn.php");
include("pesquisa.php");

$permissoes = false;

$id_url = filter_input(INPUT_GET, 'pesquisa', FILTER_VALIDATE_INT);

if ($id_url && $id_url > 0) {
    $id_usuario = $id_url;
    if ($id_usuario == $_SESSION['usuario']) {
        $permissoes = true;
    }
} else {
    $id_usuario = $_SESSION['usuario'];
    $permissoes = true;
}


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

$sql4 = 'SELECT numero_telefone, email_contato FROM contatos WHERE id_usuario = ?';
$stmt4 = $conn->prepare($sql4);
$stmt4->bind_param('i', $id_usuario);
$stmt4->execute();
$resultado4 = $stmt4->get_result();
$contatos = $resultado4->fetch_assoc();
$stmt4->close();

$telefone = $contatos["numero_telefone"] ?? "Não informado";
$email_contato = $contatos["email_contato"] ?? "Não informado";

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

$habilidades_lista = [];
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
}

$nome = $usuario["nome"] ?? "Sem nome";
$cidade = $usuario["cidade"] ?? "Sem cidade";
$uf = $usuario["uf"] ?? "";
$bio = $usuario["bio"] ?? "Sem biografia";
$score = $usuario["score_avaliacao"] ?? 0;
$url_foto = $usuario["url_foto_perfil"] ?? "";
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($nome ?? 'Usuário') ?></title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <header>
        <nav>
            <img src="#" alt="Logo">
            <form action="perfil.php" method="get">
                <input type="search" name="pesquisa" placeholder="Pesquisar" class="logo">
            </form>

            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-btn">☰</label>
            <ul class="nav-links">
                <li>
                    <a href="../feed/feed.html">
                        <span class="icone-casa">⌂</span> HOME
                    </a>
                </li>
                <li>
                    <a href="#">✉ VAGAS</a>
                </li>
                <li>
                    <a href="perfil.php">
                        <img src="#" alt="Perfil">
                    </a>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="perfil">
            <div class="foto-perfil">
                <?php if (!empty($url_foto)): ?>
                    <img src="<?= htmlspecialchars($url_foto) ?>" alt="Foto de perfil">
                <?php else: ?>
                    <img src="#" alt="Foto de perfil">
                <?php endif; ?>
            </div>

            <div class="dados-perfil">
                <h1><?= htmlspecialchars($nome ?? '') ?></h1>
                <p>
                    <?= htmlspecialchars($cidade ?? '') ?><?= (!empty($cidade) && !empty($uf)) ? ' - ' : '' ?><?= htmlspecialchars($uf ?? '') ?>
                </p>

                <?php if (!empty($eh_trabalhador)): ?>
                    <p><?= htmlspecialchars($experiencia ?? 0) ?> anos de experiência</p>
                    <p>
                        <?php if (!empty($disponibilidade)): ?>
                            Disponível para trabalhos
                        <?php else: ?>
                            Indisponível para trabalhos
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="sobre">
            <h2>Sobre mim</h2>
            <p><?= htmlspecialchars($bio ?? '') ?></p>

            <?php if (!empty($permissoes)): ?>
                <a href="editar.php" class="btn-editar">Editar perfil</a>
            <?php elseif (!empty($disponibilidade)): ?>
                <a href="iniciar_chat.php?usuario=<?= (int)$id_usuario ?>" class="btn-editar">Conversar</a>
                <button type="button" onclick="entrarContato()">Entrar em Contato</button>
            <?php endif; ?>
        </div>

        <div class="servicos">
            <h2>Serviços oferecidos</h2>
            <ul>
                <?php if (!empty($habilidades_lista)): ?>
                    <?php foreach ($habilidades_lista as $hab): ?>
                        <li><?= htmlspecialchars($hab) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Nenhuma habilidade cadastrada.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="informacoes">
            <h2>Informações</h2>
            <p>Telefone: <?= htmlspecialchars($telefone ?? 'Não informado') ?></p>
            <p>E-mail: <?= htmlspecialchars($email_contato ?? 'Não informado') ?></p>
        </div>

<div class="Publicacoes">
    <h2>Publicações</h2>
    <div id="publicacoes-container">
        <?php if (!empty($publicacoes)): ?>
            <?php foreach ($publicacoes as $pub): ?>
                <div class="publicacao">
                    <p><?= htmlspecialchars($pub["conteudo"]) ?></p>
                    
                    <?php if (!empty($pub["url_midia"])): ?>
                        <img src="<?= htmlspecialchars($pub["url_midia"]) ?>" alt="Mídia da publicação" class="midia-publicacao">
                    <?php endif; ?>

                    <?php if (!empty($pub["link"])): ?>
                        <p><a href="<?= htmlspecialchars($pub["link"]) ?>" target="_blank" rel="noopener noreferrer">Acessar link</a></p>
                    <?php endif; ?>

                    <small>Publicado em: <?= date('d/m/Y H:i', strtotime($pub["data_pub"])) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Não há publicações para exibir.</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($permissoes)): ?>
        <a href="../publicacao.html" class="btn-criar-pub">Criar publicações</a>
    <?php endif; ?>
</div>

        </div>

        <div class="avaliacoes">
            <h2>Avaliações</h2>
            <p><?= htmlspecialchars($score ?? '0') ?> estrelas</p>

            <?php if (!empty($comentarios) && is_array($comentarios)): ?>
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="comentario">
                        <p><strong><?= htmlspecialchars($comentario['nome'] ?? 'Usuário anônimo') ?>:</strong></p>
                        <p><?= htmlspecialchars($comentario['nota'] ?? $nota) ?></p>
                        <p><?= htmlspecialchars($comentario['texto'] ?? $comentario) ?></p>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhuma avaliação cadastrada.</p>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>