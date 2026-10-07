function validarCadastro() {
    const nome = document.getElementById("txtNome")
    const cpf = document.getElementById("txtCpf")
    const cnpj = document.getElementById("txtCnpj")
    const senha = document.getElementById("txtSenha")
    const data_nasc = document.getElementById("data_nasc")
    const email = document.getElementById("txtEmail")
    const cidade = document.getElementById("txtCidade")
    const uf = document.getElementById("ufSelect")

    if (nome.value.trim().length < 3 || nome.value.trim().length > 100) {
        return cadInvalido(nome)
    }

    if (!(cpf.value === "") && !(cnpj.value === "")) {
        cnpj.value = ""
        return cadInvalido(cpf)
    }

    if (!(cpf.value === "")) {
        if (!validarCPF(cpf.value)) {
            return cadInvalido(cpf)
        }
    }
    if (!(cnpj.value === "")) {
        if (!validarCNPJ(cnpj.value)) {
            return cadInvalido(cnpj)
        }
    }

    return true
}

function validarCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^a-zA-Z0-9]/g, '').toUpperCase()

    if (cnpj.length !== 14) return false

    if (/^([a-zA-Z0-9])\1{13}$/.test(cnpj)) return false

    const obterValorCaractere = (caractere) => {
        return caractere.charCodeAt(0) - 48
    }

    let soma = 0
    let peso = 5
    for (let i = 0; i < 12; i++) {
        soma += obterValorCaractere(cnpj.charAt(i)) * peso
        peso = peso === 2 ? 9 : peso - 1
    }
    let resto = soma % 11
    let digito1 = resto < 2 ? 0 : 11 - resto
    if (parseInt(cnpj.charAt(12)) !== digito1) return false

    soma = 0
    peso = 6
    for (let i = 0; i < 13; i++) {
        soma += obterValorCaractere(cnpj.charAt(i)) * peso
        peso = peso === 2 ? 9 : peso - 1
    }
    resto = soma % 11
    let digito2 = resto < 2 ? 0 : 11 - resto
    if (parseInt(cnpj.charAt(13)) !== digito2) return false

    return true
}

function validarCPF(cpf) {
    cpf = cpf.replace(/[^\d]+/g, '')

    if (cpf.length !== 11) return false

    if (/^(\d)\1{10}$/.test(cpf)) return false

    let soma = 0
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i)
    }
    let resto = (soma * 10) % 11
    if (resto === 10 || resto === 11) resto = 0
    if (resto !== parseInt(cpf.charAt(9))) return false

    soma = 0
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i)
    }
    resto = (soma * 10) % 11
    if (resto === 10 || resto === 11) resto = 0
    if (resto !== parseInt(cpf.charAt(10))) return false

    return true
}

function cadInvalido(item) {
    item.focus()
    item.value = ""
    return false
}