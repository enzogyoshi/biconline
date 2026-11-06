
<?php
require_once "proteger.php";
require_once("conn.php");

$id_usuario = $_SESSION["usuario"];


function editar_nome($conn, $nome, $id) {
    $stmt = $conn->prepare("UPDATE usuario SET nome = ? WHERE id = ?");
    $stmt->bind_param("si", $nome, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_email($conn, $email, $id) {
    $stmt = $conn->prepare("UPDATE usuario SET email = ? WHERE id = ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_cidade($conn, $cidade, $id) {
    $stmt = $conn->prepare("UPDATE usuario SET cidade = ? WHERE id = ?");
    $stmt->bind_param("si", $cidade, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_uf($conn, $uf, $id) {
    $stmt = $conn->prepare("UPDATE usuario SET uf = ? WHERE id = ?");
    $stmt->bind_param("si", $uf, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_senha($conn, $senha, $id) {
    $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuario SET senha = ? WHERE id = ?");
    $stmt->bind_param("si", $hashSenha, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_bio($conn, $bio, $id) {
    $stmt = $conn->prepare("UPDATE usuario SET bio = ? WHERE id = ?");
    $stmt->bind_param("si", $bio, $id);
    $stmt->execute();
    $stmt->close();
}

function editar_foto($conn, $url_foto, $id) {
    $stmt = $conn->prepare("UPDATE usuario SET url_foto_perfil = ? WHERE id = ?");
    $stmt->bind_param("si", $url_foto, $id);
    $stmt->execute();
    $stmt->close();
}

function fetchCidades($uf) {
    if (empty($uf)) return [];
    $url = "https://servicodados.ibge.gov.br/api/v1/localidades/estados/$uf/municipios";
    $opcoes = ['http' => ['method' => 'GET', 'timeout' => 5]];
    $contexto = stream_context_create($opcoes);
    $respJson = @file_get_contents($url, false, $contexto);

    if ($respJson === false) return false;

    $cidades = json_decode($respJson, true);
    if (empty($cidades)) return false;

    $lista_cidades = [];
    foreach ($cidades as $cidade) {
        $lista_cidades[] = $cidade["nome"];
    }
    return $lista_cidades;
}

function cadInvalido($mensagem) {
    $_SESSION['erro'] = $mensagem;
    header("Location: editar_perfil_view.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. FOTO DE PERFIL
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

    $uf = trim($_POST['uf'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');

    if (!empty($uf)) {
        $lista_cidades = fetchCidades($uf);
        
        if ($lista_cidades === false) {
            cadInvalido("Estamos com problemas no servidor do IBGE, tente novamente mais tarde.");
        }

        if (!empty($cidade)) {
            if (in_array($cidade, $lista_cidades, true)) {
                editar_uf($conn, $uf, $id_usuario);
                editar_cidade($conn, $cidade, $id_usuario);
            } else {
                cadInvalido("Cidade inválida. Por favor, selecione uma cidade da lista.");
            }
        }
    }

    $bio = trim(filter_input(INPUT_POST, 'bio', FILTER_DEFAULT) ?? '');
    if ($bio !== '') {
        editar_bio($conn, $bio, $id_usuario);
    }

    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_DEFAULT) ?? '';
    $email_contato = filter_input(INPUT_POST, 'email_contato', FILTER_VALIDATE_EMAIL);

    if (!empty($telefone) || $email_contato) {
        $stmt = $conn->prepare("INSERT INTO contatos (numero_telefone, email_contato, id_usuario) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $telefone, $email_contato, $id_usuario);
        $stmt->execute();
        $stmt->close();
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
        $habilidade = trim(filter_input(INPUT_POST, 'habilidade', FILTER_DEFAULT) ?? '');

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
        }

        if (!empty($habilidade) && isset($id_trab)) {
            $stmt = $conn->prepare("INSERT INTO habilidades (habilidade, id_trabalhador) VALUES (?, ?)");
            $stmt->bind_param("si", $habilidade, $id_trab);
            $stmt->execute();
            $stmt->close();
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
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$eh_trabalhador = !empty($usuario["id_trabalhador"]);
$checked = $eh_trabalhador ? 'checked' : '';
$display = $eh_trabalhador ? 'block' : 'none';
?>

