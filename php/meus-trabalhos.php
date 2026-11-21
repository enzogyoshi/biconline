<?php
    require_once "proteger.php";
    require_once "conn.php";
    require_once "contratacao_lib.php";

    $idUsuario = (int)$_SESSION['usuario'];
    $csrf = csrfToken();

    // trabalhos que eu contratei
    $sql1 = "
        SELECT s.id, s.data_serv, s.servico_prestado, s.status_serv, ut.nome AS outro_nome,
               a.id AS id_avaliacao, a.nota AS nota_dada
        FROM servico s
        JOIN trabalhador t ON t.id = s.id_contratado
        JOIN usuario ut ON ut.id = t.id_usuario
        LEFT JOIN avaliacoes a ON a.id_servico = s.id AND a.id_usuario = ut.id
        WHERE s.id_contratante = ?
        ORDER BY s.data_serv DESC, s.id DESC";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $idUsuario);
    $stmt1->execute();
    $contratados = $stmt1->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt1->close();

    // trabalhos que eu estou fazendo
    $sql2 = "
        SELECT s.id, s.data_serv, s.servico_prestado, s.status_serv, uc.nome AS outro_nome
        FROM servico s
        JOIN trabalhador t ON t.id = s.id_contratado
        JOIN usuario uc ON uc.id = s.id_contratante
        WHERE t.id_usuario = ?
        ORDER BY s.data_serv DESC, s.id DESC";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $idUsuario);
    $stmt2->execute();
    $realizando = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();

    function dataBR($d) { return $d ? date('d/m/Y', strtotime($d)) : ''; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus trabalhos</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="meus-trabalhos.css">
</head>
<body>
    <header class="cabecalho">
        <nav class="navegacao">
            <a href="index.html">Home</a>
        </nav>
    </header>
    <main class="pagina">
        <h2>Meus trabalhos</h2>

        <section class="trabalhos-secao">
            <h3>Trabalhos que contratei</h3>
            <?php if (!$contratados): ?>
                <p class="mensagem-vazia">Você ainda não contratou ninguém.</p>
            <?php else: ?>
                <ul class="lista-trabalhos">
                    <?php foreach ($contratados as $s): ?>
                        <li class="trabalho-item status-<?= $s['status_serv'] ? 'ativo' : 'concluido' ?>">
                            <strong><?= h($s['servico_prestado']) ?></strong>
                            <span>com <?= h($s['outro_nome']) ?></span>
                            <span><?= dataBR($s['data_serv']) ?></span>
                            <span class="selo"><?= $s['status_serv'] ? 'Em andamento' : 'Concluído' ?></span>

                            <?php if (!$s['status_serv'] && !$s['id_avaliacao']): ?>
                                <form class="form-avaliar" method="post" action="cria_avaliacao.php">
                                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                                    <input type="hidden" name="id_servico" value="<?= (int)$s['id'] ?>">
                                    <select name="nota" required>
                                        <option value="">Nota</option>
                                        <option value="5">★★★★★ (5)</option>
                                        <option value="4">★★★★ (4)</option>
                                        <option value="3">★★★ (3)</option>
                                        <option value="2">★★ (2)</option>
                                        <option value="1">★ (1)</option>
                                    </select>
                                    <input type="text" name="comentario" maxlength="200" placeholder="Comentário (opcional)">
                                    <button type="submit">Avaliar</button>
                                </form>
                            <?php elseif (!$s['status_serv'] && $s['id_avaliacao']): ?>
                                <span class="ja-avaliado">Você avaliou: <?= str_repeat('★', (int)$s['nota_dada']) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="trabalhos-secao">
            <h3>Trabalhos que estou realizando</h3>
            <?php if (!$realizando): ?>
                <p class="mensagem-vazia">Você ainda não foi contratado por ninguém.</p>
            <?php else: ?>
                <ul class="lista-trabalhos">
                    <?php foreach ($realizando as $s): ?>
                        <li class="trabalho-item status-<?= $s['status_serv'] ? 'ativo' : 'concluido' ?>">
                            <strong><?= h($s['servico_prestado']) ?></strong>
                            <span>para <?= h($s['outro_nome']) ?></span>
                            <span><?= dataBR($s['data_serv']) ?></span>
                            <span class="selo"><?= $s['status_serv'] ? 'Em andamento' : 'Concluído' ?></span>

                            <?php if ($s['status_serv']): ?>
                                <form class="form-concluir" method="post" action="concluir_servico.php">
                                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                                    <input type="hidden" name="id_servico" value="<?= (int)$s['id'] ?>">
                                    <button type="submit">Marcar como concluído</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>

    <script src="meus-trabalhos.js"></script>
</body>
</html>