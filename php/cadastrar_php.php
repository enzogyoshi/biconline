<?php
    session_start();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['erro'] = 'nenhum formulario enviado';
        header('Location: cadastrar.php');
        exit;
    }

    require_once "conn.php";
    function campo($chave) {
        return (isset($_POST[$chave]) && is_string($_POST[$chave])) ? $_POST[$chave] :"";
    }

    $nome = campo("nome");
    $cpf = campo("cpf");
    $cnpj = campo("cnpj");
    $senha = campo("senha");
    $senhaConfirm = campo("senhaConfirm");
    $data_nasc = campo("data_nasc");
    $email = campo("email");
    $uf = campo("uf");
    $cidade = campo("cidade");

    $ufs = [
        'AC', // Acre
        'AL', // Alagoas
        'AP', // Amapá
        'AM', // Amazonas
        'BA', // Bahia
        'CE', // Ceará
        'DF', // Distrito Federal
        'ES', // Espírito Santo
        'GO', // Goiás
        'MA', // Maranhão
        'MT', // Mato Grosso
        'MS', // Mato Grosso do Sul
        'MG', // Minas Gerais
        'PA', // Pará
        'PB', // Paraíba
        'PR', // Paraná
        'PE', // Pernambuco
        'PI', // Piauí
        'RJ', // Rio de Janeiro
        'RN', // Rio Grande do Norte
        'RS', // Rio Grande do Sul
        'RO', // Rondônia
        'RR', // Roraima
        'SC', // Santa Catarina
        'SP', // São Paulo
        'SE', // Sergipe
        'TO'  // Tocantins
    ];

    function cadInvalido($erro) {
        $_SESSION["erro"] = $erro;
        header("Location: cadastrar.php");
        exit;
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

        $dataNascimento = DateTime::createFromFormat('Y-m-d', $dataNascimentoString);
        if (!$dataNascimento) {
            return false;
        }

        $dataNascimento->setTime(0, 0, 0);

        return $dataNascimento <= $dataLimite;
    }

    function validarEmail($email){
        $regexEmail = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        return preg_match($regexEmail, $email) === 1;
    }

    function fetchCidades($uf) {
        $lista_cidades = [];
        $url = "https://servicodados.ibge.gov.br/api/v1/localidades/estados/$uf/municipios";

        $opcoes = [
            'http' => [
                'method' => 'GET',
                'timeout' => 5
            ]
        ];

        $contexto = stream_context_create($opcoes);

        $respJson = @file_get_contents($url, false, $contexto);
        if ($respJson !== false) {
            $cidades = json_decode($respJson, true);
        } else {
            return false;
        }

        if ($cidades === null) {
            return false;
        }

        foreach ($cidades as $cidade) {
            $lista_cidades[] = $cidade["nome"];
        }

        return $lista_cidades;
    }

    if (trim($nome) === "") {
        cadInvalido("preencha o nome e tente novamente");
    }

    if (strlen(trim($nome)) < 3 || strlen(trim($nome)) > 100) {
        cadInvalido("o nome deve ter entre 3 e 100 caracteres");
    }

    if (trim($cpf) !== "" && trim($cnpj) !== "") {
        cadInvalido("preencha apenas o CPF ou apenas o CNPJ");
    }

    if (trim($cpf) === "" && trim($cnpj) === "") {
        cadInvalido("preencha o CPF ou o CNPJ");
    }

    $cpfLimpo = null;
    $cnpjLimpo = null;

    if (!(trim($cpf) === "")) {
        if (!validarCPF(trim($cpf))) {
            cadInvalido("cpf invalido");
        }
        $cpfLimpo = preg_replace('/[^\d]/', '', $cpf);
    }

    if (!(trim($cnpj) === "")) {
        if (!validarCNPJ(trim($cnpj))) {
            cadInvalido("cnpj invalido");
        }
        $cnpjLimpo = strtoupper(preg_replace('/[^\dA-Z]/', '', strtoupper(trim($cnpj))));
    }

    if ($senha !== $senhaConfirm) {
        cadInvalido("senha não confirmada, tente novamente");
    }

    if (strlen($senha) > 72 || strlen($senha) < 8) {
        cadInvalido("a senha deve ter entre 8 e 72 bytes");
    }

    if (trim($data_nasc) === "") {
        cadInvalido("preencha a data de nascimento e tente novamente");
    }

    if (!verificarMaioridade($data_nasc)) {
        cadInvalido("o usuário deve ter 18 anos ou mais");
    }

    if (trim($email) === "") {
        cadInvalido("preencha o email e tente novamente");
    }

    if (!validarEmail(trim($email))) {
        cadInvalido("email inválido tente novamente");
    }

    if (trim($uf) === "") {
        cadInvalido("preencha a UF e tente novamente");
    }

    if (!in_array(trim($uf), $ufs)) {
        cadInvalido("UF selecionado invalido, tente novamente");
    }

    if (trim($cidade) === "") {
        cadInvalido("preencha a cidade e tente novamente");
    }

    $nome = trim($nome);
    $data_nasc = trim($data_nasc);
    $email = strtolower(trim($email));
    $uf = trim($uf);
    $cidade = trim($cidade);

    $stmt = $conn->prepare("SELECT cpf, cnpj, email FROM usuario WHERE cpf = ? OR cnpj = ? OR email = ?");
    $stmt->bind_param("sss", $cpfLimpo, $cnpjLimpo, $email);

    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $stmt->close();
        cadInvalido("Já existe uma conta cadastrada com este documento ou email registrado");
    }

    $stmt->close();

    $lista = fetchCidades($uf);

    if ($lista) {
        if (!in_array(trim($cidade), $lista)) {
            cadInvalido("cidade inválida, insira uma cidade da lista");
        } else {
            cadInvalido("estamos com problemas no servidor, tente novamente mais tarde");
        }
    }


?>