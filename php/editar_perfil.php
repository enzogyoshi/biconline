<?php
require_once "proteger.php";
require_once("conn.php");

$id_usuario = $_SESSION["usuario"];


function editar_nome($conn, $nome, $id) {
    $sql = "UPDATE usuario SET nome = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nome, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_email($conn, $email, $id) {
    $sql = "UPDATE usuario SET email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_cidade($conn, $cidade, $id) {
    $sql = "UPDATE usuario SET cidade = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $cidade, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_uf($conn, $uf, $id) {
    $sql = "UPDATE usuario SET uf = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $uf, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_senha($conn, $senha, $id) {
    $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
    $sql = "UPDATE usuario SET senha = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashSenha, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_bio($conn, $bio, $id) {
    $sql = "UPDATE usuario SET bio = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $bio, $id);
    $stmt->execute();
    $stmt->close();
}
function editar_foto($conn, $url_foto, $id) {
    $sql = "UPDATE usuario SET url_foto_perfil = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $url_foto, $id);
    $stmt->execute();
    $stmt->close();
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

    if ($respJson === false) {
        return false;
    }

    $cidades = json_decode($respJson, true);
    if (empty($cidades)) {
        return false;
    }

    foreach ($cidades as $cidade) {
        $lista_cidades[] = $cidade["nome"];
    }

    return $lista_cidades;
}



if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    $arquivo = $_FILES['foto_perfil'];
    
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    if (in_array($extensao, $extensoes_permitidas)) {
        $novo_nome_arquivo = "usuario_" . $id_usuario . "_" . time() . "." . $extensao;
        
        $pasta_destino = "uploads/";
        
        if (!is_dir($pasta_destino)) {
            mkdir($pasta_destino, 0755, true);
        }

        $caminho_completo = $pasta_destino . $novo_nome_arquivo;

        if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
            editar_foto($conn, $caminho_completo, $id_usuario);
        }
    }
}

    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_DEFAULT) ?? '');
    if (!empty($nome)) {
        editar_nome($conn, $nome, $id_usuario);
    }
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if ($email) {
        editar_email($conn, $email, $id_usuario);
    }

    $senha = filter_input(INPUT_POST, 'senha', FILTER_DEFAULT) ?? '';
    $senhaConfirm = filter_input(INPUT_POST, 'senhaConfirm', FILTER_DEFAULT) ?? '';
    if (!empty($senha)) {
        if ($senha === $senhaConfirm && strlen($senha) >= 8 && strlen($senha) <= 72) {
            editar_senha($conn, $senha, $id_usuario);
        }
    }


    $uf = filter_input(INPUT_POST, 'uf', FILTER_DEFAULT);
    $cidade = trim(filter_input(INPUT_POST, 'cidade', FILTER_DEFAULT) ?? '');
    $ufs_validos = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

    if (!empty($uf) && in_array($uf, $ufs_validos)) {
        if (!empty($cidade)) {
            $lista_cidades_ibge = fetchCidades($uf);
            if ($lista_cidades_ibge !== false && in_array($cidade, $lista_cidades_ibge, true)) {
                editar_uf($conn, $uf, $id_usuario);
                editar_cidade($conn, $cidade, $id_usuario);
            }
        } else {
            editar_uf($conn, $uf, $id_usuario);
        }
    }

    $bio = trim(filter_input(INPUT_POST, 'bio', FILTER_DEFAULT) ?? '');
    if ($bio !== '') {
        editar_bio($conn, $bio, $id_usuario);
    }

    $e_trabalhador = isset($_POST['tipo']);
    
    $stmt = $conn->prepare("SELECT id FROM trabalhador WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $res_trab = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($e_trabalhador) {
        $experiencia = filter_input(INPUT_POST, 'experiencia', FILTER_VALIDATE_INT) ?? 0;
        $disponibilidade = isset($_POST['disponibilidade']) ? 1 : 0;
        $habilidade = trim(filter_input(INPUT_POST, 'habildade', FILTER_DEFAULT) ?? '');

        if ($res_trab) {
            $id_trab = $res_trab['id'];
            $stmt = $conn->prepare("UPDATE trabalhador SET experiencia = ?, disponibilidade = ? WHERE id = ?");
            $stmt->bind_param("iii", $experiencia, $disponibilidade, $id_trab);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO trabalhador (experiencia, disponibilidade, id_usuario) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $experiencia, $disponibilidade, $id_usuario);
            $stmt->execute();
            $id_trab = $conn->insert_id;
            $stmt->close();

            if (!empty($habilidade)) {
                $stmt = $conn->prepare("INSERT INTO habilidades (habilidade, id_trabalhador) VALUES (?, ?)");
                $stmt->bind_param("si", $habilidade, $id_trab);
                $stmt->execute();
                $stmt->close();
            }
        }
    } else {
        if ($res_trab) {
            $id_trab = $res_trab['id'];
            
            $stmt = $conn->prepare("DELETE FROM habilidades WHERE id_trabalhador = ?");
            $stmt->bind_param("i", $id_trab);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM trabalhador WHERE id = ?");
            $stmt->bind_param("i", $id_trab);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    header("Location: perfil.php");
    exit;
}



$sql = "SELECT u.nome, u.email, u.cidade, u.uf, u.bio, u.url_foto_perfil, 
               t.id AS id_trabalhador, t.experiencia, t.disponibilidade 
        FROM usuario u 
        LEFT JOIN trabalhador t ON u.id = t.id_usuario 
        WHERE u.id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

$eh_trabalhador = !empty($usuario["id_trabalhador"]);
$checked = $eh_trabalhador ? 'checked' : '';
$display = $eh_trabalhador ? 'block' : 'none';
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
        <form action="editar_perfil.php" method="post" enctype="multipart/form-data" onsubmit="return validar_edicao()"    >
            
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

            <label for="txtSenha">Senha:</label>
            <input type="password" id="txtSenha" name="senha" class="txtbox">
            <br><br>

            <label for="senhaConfirm">Confirmar senha:</label>
            <input type="password" id="senhaConfirm" name="senhaConfirm" class="txtbox">
            <br><br>

            
            <label for="ufSelect">Estado (UF):</label>
            <select name="uf" id="ufSelect">
                <option value="">Selecione...</option>
                <?php 
                $lista_ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
                foreach($lista_ufs as $sigla) {
                    $selected = (($usuario["uf"] ?? '') === $sigla) ? 'selected' : '';
                    echo "<option value=\"$sigla\" $selected>$sigla</option>";
                    }
                    ?>
            </select>
            <br><br>
            <label for="txtCidade">Cidade:</label>
            <input type="text" id="txtCidade" name="cidade" maxlength="50" class="txtbox"
                placeholder="<?php echo htmlspecialchars($usuario["cidade"] ?? ''); ?>">
            <br><br>

            <label for="bio">Biografia: </label>
            <input type="text" maxlength="500" name="bio" id="bio" placeholder="<?php echo htmlspecialchars($usuario["bio"] ?? ''); ?>">
            <br><br>

            <label for="tipo">Você deseja mudar sua conta para perfil de trabalhador </label>
            <input type="checkbox" name="tipo" id="tipo" <?php echo $checked; ?>>
            <br><br>

            <div class="trabalhador" id="blocoTrabalhador" style="display: <?php echo $display; ?>;">
                <label for="habilidade">Habilidade: </label>
                <input type="text" id="habilidade" name="habildade">
                <br><br>
                <label for="experiencia">Anos de experiencia:</label>
                <input type="number" id="experiencia" name="experiencia" value="<?php echo htmlspecialchars($usuario["experiencia"] ?? ''); ?>">
                <br><br>
                <label for="disponibilidade">Disponibilidade:</label>
                <input type="checkbox" name="disponibilidade" id="disponibilidade" <?php echo (!empty($usuario["disponibilidade"])) ? 'checked' : ''; ?>>
                <br><br>
            </div>

            <label for="telefone">Telefone de contato:</label>
            <input type="tel" id="telefone" name="telefone">
            <br><br>
            <label for="email_contato">E-mail de contato:</label>
            <input type="email" id="email_contato" name="email_contato">
            
            <br><br>

            <input type="submit" value="Editar" id="btnsubmit">
        </form>

        <form action="deletar.php" method="post"  onsubmit="return confirm('Tem certeza que deseja deletar o perfil?')">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($_SESSION["usuario"]); ?>">
            <input type="submit" value="Deletar perfil">
        </form>
    </div>

<script src="../java.js/editar.js" defer></script>
</body>
</html>