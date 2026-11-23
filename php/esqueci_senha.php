<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($texto): string {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - BicoNline</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <section class="cadastro-container">
        <div class="cadastro-card">
            <h2>Recuperar senha</h2>

            <?php if (isset($_SESSION["erro"])): ?>
                <p style="color: red;"><?= h($_SESSION["erro"]) ?></p>
                <?php unset($_SESSION["erro"]); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION["sucesso"])): ?>
                <p style="color: green;"><?= h($_SESSION["sucesso"]) ?></p>
                <?php unset($_SESSION["sucesso"]); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION["link_teste"])): ?>
                <p style="word-break: break-all; margin: 10px 0;">
                    <strong>[Modo Local XAMPP]</strong> Link gerado:<br>
                    <a href="<?= h($_SESSION["link_teste"]) ?>"><?= h($_SESSION["link_teste"]) ?></a>
                </p>
                <?php unset($_SESSION["link_teste"]); ?>
            <?php endif; ?>

            <form action="esqueci_senha_php.php" method="post">
                <label for="txtEmail">Digite seu e-mail cadastrado:</label>
                <input type="email" id="txtEmail" name="email" maxlength="50" class="txtbox" required>
                <br><br>

                <button type="submit" class="btnSubmit">Enviar link de recuperação</button>

                <p class="login-redirect">
                    Lembrou a senha? <a href="login.php">Voltar ao login</a>
                </p>
            </form>
        </div>
    </section>
</body>
</html>