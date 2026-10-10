<?php
    session_start();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['erro'] = 'nenhum formulario enviado';
        header('Location: cadastrar.php');
        exit;
    }
    $nome = $_POST['nome'] ?? "";
    $cpf = $_POST['cpf'] ?? "";
    $cnpj = $_POST['cnpj'] ?? "";
    $senha = $_POST['senha'] ?? "";
    $senhaConfirm = $_POST['senhaConfirm'] ?? "";
    $data_nasc = $_POST['data_nasc'] ?? "";
    $email = $_POST['email'] ?? "";
    $uf = $_POST['uf'] ?? "";
    $cidade = $_POST['cidade'] ?? "";

    function cadInvalido($erro) {
        $_SESSION["erro"] = $erro;
        header("Location: cadastrar.php");
        exit;
    }

    if ($senha !== $senhaConfirm) {
        cadInvalido("senha não confirmada, tente novamente");
    }

    if (trim($nome) === "" || empty($data_nasc) || trim($email) === "" || trim($uf) === "" || trim($cidade) === "") {
        cadInvalido("cadastro incompleto tente novamente");
    }

    if (trim($cpf) !== "" && trim($cnpj) !== "") {
        cadInvalido("preencha apenas o CPF ou apenas o CNPJ");
    }

    if (trim($cpf) === "" && trim($cnpj) === "") {
        cadInvalido("preencha o CPF ou o CNPJ");
    }

    function validarCNPJ($cnpj) {
        $cnpj = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $cnpj));

        if (strlen($cnpj) !== 14)
            return false;

        if (preg_match('/^([a-zA-Z0-9])\1{13}$/', $cnpj))
            return false;

        $soma = 0;
        $peso = 5;
        for ($i = 0; $i < 12; $i++) {
            $soma += (ord($cnpj[$i]) - 48) * $peso;
            $peso = $peso === 2 ? 9 : $peso - 1;
        }
        $resto = $soma % 11;
        $digito1 = $resto < 2 ? 0 : 11 - $resto;

        if ((int) $cnpj[12] !== $digito1)
            return false;

        $soma = 0;
        $peso = 6;
        for ($i = 0; $i < 13; $i++) {
            $soma += (ord($cnpj[$i]) - 48) * $peso;
            $peso = $peso === 2 ? 9 : $peso - 1;
        }
        $resto = $soma % 11;
        $digito2 = $resto < 2 ? 0 : 11 - $resto;

        if ((int) $cnpj[13] !== $digito2)
            return false;

        return true;
    }

    function validarCPF($cpf) {
        $cpf = preg_replace('/[^\d]/', '', $cpf);

        if (strlen($cpf) !== 11)
            return false;

        if (preg_match('/^(\d)\1{10}$/', $cpf))
            return false;

        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int) $cpf[$i] * (10 - $i);
        }
        $resto = ($soma * 10) % 11;
        if ($resto === 10 || $resto === 11)
            $resto = 0;
        if ($resto !== (int) $cpf[9])
            return false;

        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += (int) $cpf[$i] * (11 - $i);
        }
        $resto = ($soma * 10) % 11;
        if ($resto === 10 || $resto === 11)
            $resto = 0;
        if ($resto !== (int) $cpf[10])
            return false;

        return true;
    }

    function verificarMaioridade($dataNascimentoString){
        $hoje = new DateTime();
        $hoje->setTime(0, 0, 0);

        $dataLimite = clone $hoje;
        $dataLimite->modify('-18 years');

        $dataNascimento = new DateTime($dataNascimentoString);
        $dataNascimento->setTime(0, 0, 0);

        return $dataNascimento <= $dataLimite;
    }

    function validarEmail($email){
        $regexEmail = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        return preg_match($regexEmail, $email) === 1;
    }

    if (!empty($cpf)) {
        if (!validarCPF($cpf)) {
            cadInvalido("cpf invalido");
        }
    }

    if (!empty($cnpj)) {
        if (!validarCNPJ($cnpj)) {
            cadInvalido("cpf invalido");
        }
    }
?>