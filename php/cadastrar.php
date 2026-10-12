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
        <h2>cadastro</h2>
        <?php
        if (isset($_SESSION["erro"])) {
            echo "<p style='color: red;'>" . $_SESSION["erro"] . "</p>";
            unset($_SESSION["erro"]);
        }
        ?>
       <form action="cadastrar_php.php" method="post" onsubmit="return validarCadastro()">
        
            <p id="msgErro" style="color: red;"></p>
            <label for="txtNome">Nome Completo:</label>
            <input type="text" id="txtNome" name="nome" class="txtbox">
            <br><br>

            <label for="txtCpf">CPF (Somente números):</label>
            <input type="text" id="txtCpf" name="cpf" maxlength="11" pattern="\d{11}" class="txtbox">
            <br><br>

            <label for="txtCnpj">CNPJ:</label>
            <input type="text" id="txtCnpj" name="cnpj" class="txtbox">
            <br><br>

            <label for="txtSenha">Senha:</label>
            <input type="password" id="txtSenha" name="senha" class="txtbox">
            <br><br>

            <label for="senhaConfirm">Confirmar senha:</label>
            <input type="password" id="senhaConfirm" name="senhaConfirm" class="txtbox">
            <br><br>

            <label for="data_nasc">Data de Nascimento:</label>
            <input type="date" id="data_nasc" name="data_nasc" class="data_nascbox">
            <br><br>

            <label for="txtEmail">E-mail:</label>
            <input type="email" id="txtEmail" name="email" maxlength="50" class="txtbox">
            <br><br>

            <label for="ufSelect">Estado (UF):</label>
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

            <label for="txtCidade">Cidade:</label>
            <input type="text" list="cidades" id="txtCidade" name="cidade" maxlength="50" class="txtbox">
            <br><br>

            <datalist id="cidades"></datalist>

            <button type="submit" class="btnSubmit" id="btnSubmit">Cadastrar</button>

        </form>
        </div>
    <script src="../java.js/cadastro.js"></script>
</body>
</html>