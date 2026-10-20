<?php
session_start(); 
require_once("conn.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

function editar_nome($conn, $nome, $id) {
    $sql = "UPDATE usuario SET nome = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nome, $id);
    $stmt->execute();
}

function editar_email($conn, $email, $id) {
    $sql = "UPDATE usuario SET email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
}

function editar_cidade($conn, $cidade, $id) {
    $sql = "UPDATE usuario SET cidade = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $cidade, $id);
    $stmt->execute();
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = $_SESSION["id_usuario"];

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if ($email) {
        editar_email($conn, $email, $id_usuario);
    }

    $nome = filter_input(INPUT_POST, 'nome', FILTER_DEFAULT);
    if (!empty($nome)) {
        editar_nome($conn, $nome, $id_usuario);
    }

    $cidade = filter_input(INPUT_POST, 'cidade', FILTER_DEFAULT);
    if (!empty($cidade)) {
        editar_cidade($conn, $cidade, $id_usuario);
    }
    
    header("Location: perfil.php");
    exit;
}

$sql = "SELECT nome, email, cidade, uf, bio, url_foto_perfil FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["id_usuario"]);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
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
        <form action="perfil.php" method="post" enctype="multipart/form-data" onsubmit="return validar_edicao()">
            
            <label for="txtNome">Nome Completo:</label>
            <input type="text" id="txtNome" name="nome" class="txtbox" placeholder="<?php echo htmlspecialchars($usuario["nome"] ?? ''); ?>">
            <br><br>

            <label for="foto_perfil">Foto de perfil:</label>
            <input type="file" name="foto_perfil" id="foto_perfil">
            <br><br>

            <label for="txtEmail">E-mail:</label>
            <input type="email" id="txtEmail" name="email" maxlength="50" class="txtbox"
                placeholder="<?php echo htmlspecialchars($usuario["email"] ?? ''); ?>">
            <br><br>

            <label for="txtCidade">Cidade:</label>
            <input type="text" id="txtCidade" name="cidade" maxlength="50" class="txtbox"
                placeholder="<?php echo htmlspecialchars($usuario["cidade"] ?? ''); ?>">
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

            <label for="bio">Biografia: </label>
            <input type="text" maxlength="500" name="bio" id="bio" placeholder="<?php echo htmlspecialchars($usuario["bio"] ?? ''); ?>">
            <br><br>

            <input type="submit" value="Editar">
        </form>
        
        <script src="editar.js"></script>
    </div>
</body>

</html>