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
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <nav>
            <img src="#" alt="Logo">
            <input type="search" name="Pesquisar" placeholder="Pesquisar" class="logo">

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
    <section class="cadastro-container">
        <div class="cadastro-card">
        <h2>login</h2>
        <?php
        if (isset($_SESSION["erro"])) {
            echo "<p style='color: red;'>" . $_SESSION["erro"] . "</p>";
            unset($_SESSION["erro"]);
        }
        ?>
        <?php
        if (isset($_SESSION["sucesso"])) {
            echo "<p style='color: green;'>" . $_SESSION["sucesso"] . "</p>";
            unset($_SESSION["sucesso"]);
        }
        ?>
       <form action="login_php.php" method="post" onsubmit="return validarLogin()">
        
           <p id="msgErro" style="color: red;"></p>

            <label for="txtEmail">E-mail:</label>
            <input type="email" id="txtEmail" name="email" maxlength="50" class="txtbox">
            <br><br>
            
            <label for="txtSenha">Senha:</label>
            <input type="password" id="txtSenha" name="senha" class="txtbox">
            <br><br>

            <button type="submit" class="btnSubmit" id="btnSubmit">Login</button>

            <p class="login-redirect">
                Não tem uma conta? <a href="cadastrar.php">cadastre-se</a>
            </p>

        </form>
        </div>
    <script src="../java.js/login.js"></script>
</body>
</html>