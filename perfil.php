
<?php

require_once("conn.php");

$id_usuario = $_GET["id_usuario"] ?? "1";

if (isset($_SESSION["id_usuario"]) &&
    $_SESSION["id_usuario"] == $id_usuario) {
    $permissoes = true;
} else {
    $permissoes = false;
}

$sql = "select name,cidade,uf,bio,score_avaliacao where id_usuario = ?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $id_usuario,);
$stmt->execute();

$resultado = $stmt -> get_result();
$usuario = $resultado->fetch_assoc();

$name = $usuario["name"] ?? "Sem nome";
$cidade = $usuario["cidade"] ?? "Sem cidade";
$uf = $usuario["uf"] ??"uf não encontrada";
$bio = $usuario["bio"] ?? "Sem biografia";
$score = $usuario["score_avaliacao"] ?? "0";
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Perfil - BicoOnline</title>
</head>

<body>
<header>
        <nav>
            <img src="#" alt="Logo">
            <form action="perfil.php" method="get" >
                <input type="search" name="Pesquisar" placeholder="Pesquisar" class="logo">
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
                <img src="<??>" alt="Foto de perfil">
            </div>

            <div class="dados-perfil">
                <h1><?echo($name)?></h1>
                <p id="profissão">Jardineiro</p>
                <p id="cidade">Barroso-MG </p>
                <p id="experiência">6 anos de experiência</p>
                <p id="disponbilidade">Disponível para trabalhos</p>
            </div>

        </div>

        <div class="sobre">

            <h2>Sobre mim</h2>
<button></button>
            <p><? ?> </p>
            <? if($permissoes): ?>
                <div class= add_bio>
                    <form action="editar_perfil.php" method="get">
                       <input type="button" value="editar perfil">
                    </form>
                </div>
            </div>
            <?php endif;?>


        <div class="servicos">

            <h2>Serviços oferecidos</h2>

            <ul>
                
            </ul>

        </div>

        <div class="informacoes">

            <h2>Informações</h2>

            <p><strong>Valor da diária:</strong> R$ 150,00</p>
            <p><strong>Disponibilidade:</strong> Segunda a segunda</p>
            <p><strong>Telefone:</strong> 41 96767-6767</p>

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

            


        </div>

    </main>

</body>

</html>