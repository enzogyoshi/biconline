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