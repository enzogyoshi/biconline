<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "conn.php";

function h($texto): string {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

$token = isset($_GET['token']) && is_string($_GET['token']) ? trim($_GET['token']) : '';

if ($token === '' || !ctype_xdigit($token) || strlen($token) !== 64) {
    $_SESSION['erro'] = 'Link de recuperação inválido.';
    header('Location: login.php');
    exit;
}

$tokenHash = hash('sha256', $token);

$stmt = $conn->prepare("
    SELECT id FROM recuperacao_senha
    WHERE token_hash = ? AND usado = 0 AND expira_em > NOW()
    LIMIT 1
");
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$registro = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$registro) {
    $_SESSION['erro'] = 'Este link expirou ou já foi utilizado. Solicite um novo.';
    header('Location: esqueci_senha.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha - BicoNline</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <section class="cadastro-container">
        <div class="cadastro-card">
            <h2>Nova senha</h2>

            <?php if (isset($_SESSION["erro"])): ?>
                <p style="color: red;"><?= h($_SESSION["erro"]) ?></p>
                <?php unset($_SESSION["erro"]); ?>
            <?php endif; ?>

            <form action="redefinir_senha_php.php" method="post" onsubmit="return validarNovaSenha()">
                <p id="msgErro" style="color: red;"></p>
                <input type="hidden" name="token" value="<?= h($token) ?>">

                <label for="txtSenha">Nova senha (mínimo 8 caracteres):</label>
                <div class="campo-senha">
                    <input type="password" id="txtSenha" name="senha" class="txtbox" required>
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
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                            <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                    </button>
                </div>
                <br><br>

                <label for="senhaConfirm">Confirmar nova senha:</label>
                <div class="campo-senha">
                    <input type="password" id="senhaConfirm" name="senhaConfirm" class="txtbox" required>
                    <button type="button" class="btn-eye" data-target="senhaConfirm" aria-label="Mostrar/ocultar senha">
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
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                            <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                    </button>
                </div>
                <br><br>

                <button type="submit" class="btnSubmit" id="btnSubmit">Salvar nova senha</button>
            </form>
        </div>
    </section>
    <script src="../java.js/redefinir_senha.js"></script>
</body>
</html>