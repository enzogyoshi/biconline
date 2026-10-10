let lista_cidades = []
let txtCidade = document.getElementById("txtCidade")
txtCidade.disabled = true
let ufSelect = document.getElementById("ufSelect")

function validarCadastro() {
    document.getElementById("msgErro").textContent = ""

    const nome = document.getElementById("txtNome")
    const cpf = document.getElementById("txtCpf")
    const cnpj = document.getElementById("txtCnpj")
    const senha = document.getElementById("txtSenha")
    const data_nasc = document.getElementById("data_nasc")
    const email = document.getElementById("txtEmail")
    const cidade = document.getElementById("txtCidade")
    const uf = document.getElementById("ufSelect")

    if (nome.value.trim().length < 3 || nome.value.trim().length > 100) {
        return cadInvalido(nome, "o nome deve ter entre 3 e 100 caracteres")
    }

    if ((!(cpf.value === "") && !(cnpj.value === "")) || ((cpf.value === "") && (cnpj.value === ""))) {
        cnpj.focus()
        return cadInvalido(cpf, "apenas um entre o cpf e cnpj deve ser preenchido")
    }

    if (!(cpf.value === "")) {
        if (!validarCPF(cpf.value)) {
            return cadInvalido(cpf, "cpf invalido")
        }
    }
    if (!(cnpj.value === "")) {
        if (!validarCNPJ(cnpj.value)) {
            return cadInvalido(cnpj, "cnpj invalido")
        }
    }

    if (senha.value.length < 8 || senha.value.length > 100) {
        return cadInvalido(senha, "a senha deve ter no minimo 8 caracteres e no maximo 100")
    }

    if (data_nasc.value === "") {
        return cadInvalido(data_nasc, "preencha a data de nascimento")
    }

    if (!verificarMaioridade(data_nasc.value)) {
        return cadInvalido(data_nasc, "o usuario deve ter 18 anos ou mais")
    }
    
    if (email.value === "" || !validarEmail(email.value)) {
        return cadInvalido(email, "digite um email valido")
    }

    if (uf.value === "") {
        return cadInvalido(uf, "escolha uma unidade federativa")
    }

    if (cidade.value.trim() === "" || !validarCidade(cidade.value)) {
        return cadInvalido(cidade, "escolha uma cidade da lista")
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

function verificarMaioridade(dataNascimentoString) {
    const hoje = new Date()
    hoje.setHours(0, 0, 0, 0)

    const dataLimite = new Date(hoje)
    dataLimite.setFullYear(hoje.getFullYear() - 18)

    const [ano, mes, dia] = dataNascimentoString.split('-')

    const dataNascimento = new Date(ano, mes - 1, dia)
    dataNascimento.setHours(0, 0, 0, 0)

    return dataNascimento <= dataLimite
}

function validarEmail(email) {
    const regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
    return regexEmail.test(email)
}

function validarCidade(cidade) {
    if (lista_cidades.length === 0) {
        return false
    }
    return lista_cidades.includes(cidade.trim())
}

function cadInvalido(item, erro) {
    document.getElementById("msgErro").textContent = erro
    item.focus()
    return false
}

ufSelect.addEventListener("change", function() {
    let datalist = document.getElementById("cidades")
    datalist.replaceChildren()
    lista_cidades = []
    txtCidade.value = ""
    txtCidade.disabled = true
    document.getElementById("msgErro").textContent = ""

    if (ufSelect.value === "") {
        return
    }
    
    const url = `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${ufSelect.value}/municipios`

    fetch(url)
     .then(r => { 
        if (!r.ok) {
            throw new Error(`ERRO HTTP! Status: ${r.status}`)
        }
        return r.json()
     })

     .then(dados => {
        dados.forEach((cidade) => {
            lista_cidades.push(cidade.nome)
            const op = document.createElement("option")
            op.value = cidade.nome
            datalist.appendChild(op)
        })
        txtCidade.disabled = false
    })
    
    .catch(error => {
        document.getElementById("msgErro").textContent = "falha na requisição, tente novamente"
        console.error("Erro capturado: " + error.message)
    })
})
