<?php
require_once("conn.php");


if (!isset($_SESSION["id_usuario"])) {
    exit("Você precisa estar logado.");
    }

if ($_SERVER["REQUEST_METHOD"] === "POST") {}
        

        

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar perfil</title>
</head>

<body>
    <div>

        <form action="perfil.php" method="post">
            <label for="nome">Nome Completo:</label>
            <input type="text" id="txtNome" name="nome" class="txtbox" placeholder="<?php echo ($_SESSION("name")) ?>">
            <br><br>

            <label for="foto_perfil">Foto de perfil:</label>
            <input type="file" name="foto_perfil" id="foto_perfil">


            <label for="email">E-mail:</label>
            <input type="email" id="txtEmail" name="email" maxlength="50" class="txtbox"
                placeholder="<?php echo ($_SESSION("email")) ?>">
            <br><br>

            <label for="cidade">Cidade:</label>
            <input type="text" id="txtCidade" name="cidade" maxlength="50" class="txtbox"
                placeholder="<?php echo ($_SESSION("cidade")) ?>">
            <br><br>

            <label for="uf">Estado (UF):</label>
            <select name="uf" id="ufSelect">
                <option value="">Selecione...</option>
                <option value="AC">AC</option>
                <option value="AL">AL</option>
                <option value="AP">AP</option>
                <option value="AM">AM</option>
                <option value="BA">BA</option>
                <option value="CE">CE</option>
                <option value="DF">DF</option>
                <option value="ES">ES</option>
                <option value="GO">GO</option>
                <option value="MA">MA</option>
                <option value="MT">MT</option>
                <option value="MS">MS</option>
                <option value="MG">MG</option>
                <option value="PA">PA</option>
                <option value="PB">PB</option>
                <option value="PR">PR</option>
                <option value="PE">PE</option>
                <option value="PI">PI</option>
                <option value="RJ">RJ</option>
                <option value="RN">RN</option>
                <option value="RS">RS</option>
                <option value="RO">RO</option>
                <option value="RR">RR</option>
                <option value="SC">SC</option>
                <option value="SP">SP</option>
                <option value="SE">SE</option>
                <option value="TO">TO</option>
            </select>
            <br><br>


            <label for="bio">Biografia: </label>
            <input type="text" maxlength="500" name="bio" id="bio" placeholder="<?php echo ($bio) ?>">

            <br><br>

            <label for="tipo">Você deseja mudar sua conta para perfil de trabalhador </label>

            <input type="checkbox" name="tipo" id="tipo">

            <br><br>


            <div class="trabalhador">
                <Label for="habildade">Habilidade: </Label>
                <input type="text" id="habilidade" name="habildade">
                <br><br>
                <label for="experiencia">Anos de experiencia: </label>
                <input type="number" id="experiencia" name="experiencia">
                <br><br>
                <label for="disponibilidade">Disponibilidade</label>
                <input type="checkbox" name="disponibilidade" id="disponibilidade">


            </div>
            <label for="telefone">Telefone de contato: </label>
            <input type="tel" id="telefone" name="telefone">
            <label for="email">E-mail de contato: </label>
            <input type="email" id="email" name="email">

            <input type="submit" value="Editar">
        </form>
    </div>

</body>

</html>