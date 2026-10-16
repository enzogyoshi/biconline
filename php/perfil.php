<?php

require_once("conn.php");

$id_usuario = filter_input(INPUT_GET, "id_usuario", FILTER_VALIDATE_INT);

if (!$id_usuario || $id_usuario < 1) {
    $id_usuario = 1;
}

$permissoes = isset($_SESSION["id_usuario"]) &&
    (int) $_SESSION["id_usuario"] === (int) $id_usuario;
$sql = "SELECT nome, cidade, uf, bio, score_avaliacao,url_foto_perfil FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$sql4 = 'SELECT numero_telefone ,email_contato FROM contatos WHERE id_usuario = ?';
    $stmt4 = $conn->prepare($sql4);
    $stmt4->bind_param('i', $id_usuario);
    $stmt4->execute();

if ($tipo = "trabalhador"){
    $sql2 = 'SELECT habilidade FROM habilidades WHERE id_usuario = ?';
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param('i', $id_usuario);
    $stmt2->execute();
    
    $sql3 = 'SELECT disponibilidade, experiencia FROM trabalhador WHERE id_usuario = ?';
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param('i', $id_usuario);
    $stmt3->execute();
    
    $resultado2 = $stmt2->get_result();
    $habilidade = $resultado2->fetch_assoc();
    
    $resultado3 = $stmt3->get_result();
    $trabalhador = $resultado3->fetch_assoc();
    
    
    $experiencia = $trabalhador["experiencia"] ?? "0";
    $disponibilidade = $trabalhador["disponibilidade"] ?? "0";

    $habilidade = $habilidades["habilidade"] ?? "";
}
    



$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    http_response_code(404);
    exit("Usuário não encontrado.");
}

$nome = $usuario["nome"] ?? "Sem nome";
$cidade = $usuario["cidade"] ?? "Sem cidade";
$uf = $usuario["uf"] ?? "UF não encontrada";
$bio = $usuario["bio"] ?? "Sem biografia";
$score = $usuario["score_avaliacao"] ?? 0;
$url_foto = $usuario["url_foto_perfil"] ?? "";
$contatos = $contatos['']?? '';
$email = $contatos['email'] ?? '';

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Perfil - BicoOnline</title>
</head>

<body>
    <header>
        <nav>
            <img src="#" alt="Logo">
            <form action="perfil.php" method="get">
                <input type="search" name="Pesquisar" placeholder="Pesquisar" class="logo" >
            </form>

            <input type="checkbox" id="menu-toggle">

            <label for="menu-toggle" class="menu-btn">☰ </label>
            <ul class="nav-links">
                <li>
                    <a href="index.html">
                        <span class="icone-casa">⌂</span> HOME
                    </a>
                </li>
                <li>
                    <a href="#">✉ VAGAS</a>
                </li>
                <li>
                    <a href="Perfil.html">
                        <img src="" alt="Perfil">
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
                <h1><?= htmlspecialchars($nome) ?></h1>

                <p>
                    <?= htmlspecialchars($cidade) ?> - <?= htmlspecialchars($uf) ?>
                </p>
                <p ><?= htmlspecialchars($experiencia)?>: anos de experiência</p>
                
                <?php if ($tipo == "trabalhador") : ?>
                <p> <?php if ($disponibilidade): ?>
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
            <p><?= htmlspecialchars($bio) ?></p>
            <?php if ($permissoes): ?>
                <div class="add_bio">
                    <form action="editar_perfil.php" method="get">
                        <input type="submit" value="Editar perfil">
                    </form>
                </div>
            <?php endif; ?>


        </div>


        <div class="servicos">

            <h2>Serviços oferecidos</h2>

            <ul>

            </ul>

        </div>

        <div class="informacoes">

            <h2>Informações</h2>
            
            <p><?= htmlspecialchars($contatos) ?></p>

            <p><?= htmlspecialchars($email) ?></p>



            

        </div>

        <div class="fotos">

            <h2>Fotos dos meus trabalhos</h2>

            <div class="fotos-trabalhos">

                <div class="foto-trabalho">
                    <img src="" alt="Foto de trabalho">
                </div>

                <div class="foto-trabalho">
                    <img src="" alt="Foto de trabalho">
                </div>

                <div class="foto-trabalho">
                    <img src="" alt="Foto de trabalho">
                </div>

            </div>

        </div>

        <div class="avaliacoes">

            <h2>Avaliações</h2>

            <p> <?= htmlspecialchars($score) ?> estrelas </p>

        </div>

    </main>

</body>

</html>