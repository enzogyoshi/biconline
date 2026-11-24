function validarNovaSenha() {
    const senha = document.getElementById("txtSenha");
    const confirm = document.getElementById("senhaConfirm");
    const msgErro = document.getElementById("msgErro");

    if (senha.value.length < 8 || senha.value.length > 72) {
        msgErro.textContent = "A senha deve ter entre 8 e 72 caracteres.";
        senha.focus();
        return false;
    }

    if (senha.value !== confirm.value) {
        msgErro.textContent = "As senhas não coincidem.";
        confirm.focus();
        return false;
    }

    const btn = document.getElementById("btnSubmit");
    btn.disabled = true;
    btn.textContent = "Salvando...";
    return true;
}

document.querySelectorAll(".btn-eye").forEach(btn => {
    btn.addEventListener("click", function () {
        const input = document.getElementById(this.dataset.target);
        const iconEye = this.querySelector(".icon-eye");
        const iconEyeOff = this.querySelector(".icon-eye-off");

        if (input && input.type === "password") {
            input.type = "text";
            if (iconEye) iconEye.style.display = "none";
            if (iconEyeOff) iconEyeOff.style.display = "block";
        } else if (input) {
            input.type = "password";
            if (iconEye) iconEye.style.display = "block";
            if (iconEyeOff) iconEyeOff.style.display = "none";
        }
    });
});