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
       <form action="cadastrar_php.php" method="post" onsubmit="return validarCadastro()">
        
        <label for="nome">Nome Completo:</label>
        <input type="text" id="txtNome" name="nome" class="txtbox">
        <br><br>

        <label for="cpf">CPF (Somente números):</label>
        <input type="text" if="txtCpf" name="cpf" maxlength="11" pattern="\d{11}" class="txtbox">
        <br><br>

        <label for="cnpj">CNPJ (Somente números):</label>
        <input type="text" id="txtCnpj" name="cnpj" maxlength="14" pattern="\d{14}" class="txtbox">
        <br><br>

        <label for="senha">Senha:</label>
        <input type="password" id="txtSenha" name="senha" class="txtbox">
        <br><br>

        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nasc" name="data_nascimento" class="data_nascbox">
        <br><br>

        <label for="email">E-mail:</label>
        <input type="email" id="txtEmail" name="email" maxlength="50" class="txtbox">
        <br><br>

        <label for="cidade">Cidade:</label>
        <input type="text" id="txtCidade" name="cidade" maxlength="50" class="txtbox">
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

        <button type="submit" class="btnSubmit" id="btnSubmit">Cadastrar</button>

    <script src="java.js/cadastro.js"></script>
    </form>
</body>
</html>