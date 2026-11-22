function validarLogin() {
    const email = document.getElementById("txtEmail")
    const senha = document.getElementById("txtSenha")

    if (email.value.trim().length === 0) {
        return cadInvalido(email, "Preencha o email para prosseguir")
    }

    if (senha.value.length === 0) {
        return cadInvalido(senha, "Preencha a senha para prosseguir")
    }

    const btn = document.getElementById("btnSubmit")
    btn.disabled = true
    btn.textContent = "Verificando..."

    return true
}

function cadInvalido(item, erro) {
    document.getElementById("msgErro").textContent = erro
    item.focus()
    return false
}

document.querySelectorAll(".btn-eye").forEach(btn => {
    btn.addEventListener("click", function() {
        const targetId = this.dataset.target
        const input = document.getElementById(targetId)
        const iconEye = this.querySelector(".icon-eye")
        const iconEyeOff = this.querySelector(".icon-eye-off")

        if (input && input.type === "password") {
            input.type = "text"
            if (iconEye) iconEye.style.display = "none"
            if (iconEyeOff) iconEyeOff.style.display = "block"
        } else if (input) {
            input.type = "password"
            if (iconEye) iconEye.style.display = "block"
            if (iconEyeOff) iconEyeOff.style.display = "none"
        }
    })
})