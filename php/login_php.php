<?php 
    session_start();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['erro'] = 'nenhum formulario enviado';
        header('Location: login.php');
        exit;
    }

    require_once "conn.php";
    function campo($chave) {
        return (isset($_POST[$chave]) && is_string($_POST[$chave])) ? $_POST[$chave] :"";
    }

    function logInvalido($erro) {
        $_SESSION["erro"] = $erro;
        header("Location: login.php");
        exit;
    }

    $email = campo("email");
    $senha = campo("senha");

    if (empty($email) || empty($senha)) {
        logInvalido("Preencha os dados para realizar o login");
    }

    $stmt = $conn->prepare("SELECT id, senha FROM usuario WHERE email = ?");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($senha, $row["senha"])) {
        session_regenerate_id(true);
        $_SESSION["usuario"] = $row["id"];
        header("Location: chat.php"); // temporário, depois vira a página inicial
        exit;
    }
}

logInvalido("email ou senha incorretos");

?>