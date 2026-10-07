<?php
     if (session_id() == "") {
        session_start();
     }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BicoNline</title>
</head>
<body>
    <section>
        <h2>cadastro</h2>
        <?php
        if (isset($_SESSION["erro"])) {
            echo "<p style='color: red;'>" . $_SESSION["erro"] . "</p>";
            unset($_SESSION["erro"]);
        }
        ?>
       <form action="cadastrar_php.php" method="post" onsubmit="return validarForm()" id="formCadastro">
            <p>usuário:</p>
            <input type="text" name="txtUser" id="txtUser" placeholder="username"><br>
            <p>senha:</p>
            <input type="password" name="txtSenha" id="txtSenha" placeholder="senha"><br>
            <p>confirmar senha:</p>
            <input type="password" id="confirmSenha" placeholder="confirmar senha"><br>
            <input type="submit" value="Cadastrar">
       </form> 
    </section>
    <script src="cadastro.js"></script>
</body>
</html>