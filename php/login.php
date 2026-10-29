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
                <div class="campo-senha">
                    <input type="password" id="txtSenha" name="senha" class="txtbox">
                    <button type="button" class="btn-eye" data-target="txtSenha" aria-label="Mostrar/ocultar senha">
                        <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="icon-eye-off" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20"
                            height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68">
                            </path>
                            <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                    </button>
                </div>
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