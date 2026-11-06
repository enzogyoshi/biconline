<?php
include("editar_perfil.php");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar perfil</title>
    <link rel="stylesheet" href="../style.css">
    <script src="../java.js/editar.js" defer></script>
</head>

<body>
    <section class="cadastro-container">
        <div class="cadastro-card">
            <h2>Editar Perfil</h2>

            <?php
            if (isset($_SESSION["erro"])) {
                echo "<p style='color: red;'>" . htmlspecialchars($_SESSION["erro"]) . "</p>";
                unset($_SESSION["erro"]);
            }
            ?>

            <form action="editar_perfil.php" method="post" enctype="multipart/form-data" onsubmit="return validar_edicao()">
                <p id="msgErro" style="color: red;"></p>

                <label for="txtNome">Nome Completo:</label>
                <input type="text" id="txtNome" name="nome" class="txtbox"
                    value="<?php echo htmlspecialchars($usuario["nome"] ?? ''); ?>">
                <br><br>

                <label for="foto_perfil">Foto de perfil:</label>
                <input type="file" name="foto_perfil" id="foto_perfil">
                <br><br>

                <label for="txtEmail">E-mail:</label>
                <input type="email" id="txtEmail" name="email" maxlength="50" class="txtbox"
                    value="<?php echo htmlspecialchars($usuario["email"] ?? ''); ?>">
                <br><br>

                <label for="txtSenha">Nova Senha (deixe em branco para manter a atual):</label>
                <div class="campo-senha">
                    <input type="password" id="txtSenha" name="senha" class="txtbox">
                    <button type="button" class="btn-eye" data-target="txtSenha" aria-label="Mostrar/ocultar senha">
                        <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="icon-eye-off" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                            <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                    </button>
                </div>
                <br><br>

                <label for="senhaConfirm">Confirmar senha:</label>
                <div class="campo-senha">
                    <input type="password" id="senhaConfirm" name="senhaConfirm" class="txtbox">
                    <button type="button" class="btn-eye" data-target="senhaConfirm" aria-label="Mostrar/ocultar senha">
                        <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="icon-eye-off" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                            <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                    </button>
                </div>
                <br><br>

                <label for="ufSelect">Estado (UF):</label>
                <select name="uf" id="ufSelect">
                    <option value="">Selecione...</option>
                    <?php
                    $ufs = ["AC", "AL", "AP", "AM", "BA", "CE", "DF", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RS", "RO", "RR", "SC", "SP", "SE", "TO"];
                    $ufAtual = $usuario["uf"] ?? '';
                    foreach ($ufs as $uf) {
                        $selected = ($ufAtual === $uf) ? 'selected' : '';
                        echo "<option value='$uf' $selected>$uf</option>";
                    }
                    ?>
                </select>
                <br><br>

                <label for="txtCidade">Cidade:</label>
                <input type="text" list="cidades" id="txtCidade" name="cidade" maxlength="50" class="txtbox"
                    value="<?php echo htmlspecialchars($usuario["cidade"] ?? ''); ?>">
                <datalist id="cidades"></datalist>
                <br><br>

                <label for="bio">Biografia:</label>
                <textarea name="bio" id="bio" maxlength="500" class="txtbox" rows="4"><?php echo htmlspecialchars($usuario["bio"] ?? ''); ?></textarea>
                <br><br>

                <label for="tipo">Deseja mudar sua conta para perfil de trabalhador?</label>
                <input type="checkbox" name="tipo" id="tipo" <?php echo $checked ?? ''; ?>>
                <br><br>


                <div class="trabalhador" id="blocoTrabalhador" style="display: <?php echo $display ?? 'none'; ?>;">
                    <label for="habilidade">Habilidade:</label>
                    <input type="text" id="habilidade" name="habilidade" class="txtbox">
                    <br><br>

                    <label for="experiencia">Anos de experiência:</label>
                    <input type="number" id="experiencia" name="experiencia"
                        value="<?php echo htmlspecialchars($usuario["experiencia"] ?? ''); ?>" class="txtbox">
                    <br><br>

                    <label for="disponibilidade">Disponibilidade:</label>
                    <input type="checkbox" name="disponibilidade" id="disponibilidade" <?php echo (!empty($usuario["disponibilidade"])) ? 'checked' : ''; ?>>
                    <br><br>
                </div>

                <label for="telefone">Telefone de contato:</label>
                <input type="tel" id="telefone" name="telefone" class="txtbox"
                    value="<?php echo htmlspecialchars($usuario["telefone"] ?? ''); ?>">
                <br><br>

                <label for="email_contato">E-mail de contato:</label>
                <input type="email" id="email_contato" name="email_contato" class="txtbox"
                    value="<?php echo htmlspecialchars($usuario["email_contato"] ?? ''); ?>">
                <br><br>

                <input type="submit" value="Salvar Alterações" id="btnsubmit">
            </form>

            <form action="deletar.php" method="post" onsubmit="return confirm('Tem certeza que deseja deletar o perfil?')">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($_SESSION["usuario"] ?? ''); ?>">
                <input type="submit" value="Deletar perfil">
            </form>
        </div>
    </section>
</body>

</html>