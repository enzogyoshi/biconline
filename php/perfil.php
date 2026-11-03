<?php
require_once("usuario.php");
require_once('pesquisa.php');

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