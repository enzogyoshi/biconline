function validar_edicao() {
    document.getElementById("msgErro").textContent = ""
    const nome = document.getElementById("txtNome")
    const senha = document.getElementById("txtSenha")
    const confirmSenha = document.getElementById("senhaConfirm")
    const email = document.getElementById("txtEmail")
    const cidade = document.getElementById("txtCidade")
    const uf = document.getElementById("ufSelect")

    if (nome.value.trim().length < 3 || nome.value.trim().length > 100) {
        return cadInvalido(nome, "o nome deve ter entre 3 e 100 caracteres")
    }

    if (senha.value !== "" || confirmSenha.value !== "") {
        if (senha.value.length < 8 || senha.value.length > 100) {
            return cadInvalido(senha, "a senha deve ter no minimo 8 caracteres e no maximo 100")
        }
        if (senha.value !== confirmSenha.value) {
            return cadInvalido(senha, "senha não confirmada, tente novamente")
        }
    }
    
    if (email.value === "" || !validarEmail(email.value)) {
        return cadInvalido(email, "digite um email valido")
    }

    if (uf.value === "") {
        return cadInvalido(uf, "escolha uma unidade federativa")
    }

    if (lista_cidades.length === 0) {
        return cadInvalido(uf, "lista de cidades não carregou, troque o uf para Selecione.. e tente de novo")
    }

    if (cidade.value.trim() === "" || !validarCidade(cidade.value)) {
        return cadInvalido(cidade, "escolha uma cidade da lista")
    }

    const btn = document.getElementById("btnSubmit")
    if (btn) {
        btn.disabled = true
        btn.textContent = "Salvando..."
    }

    return true
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


const checkboxTipo = document.getElementById("tipo")
const blocoTrabalhador = document.getElementById("blocoTrabalhador")

if (checkboxTipo && blocoTrabalhador) {
    function alternarBlocoTrabalhador() {
        if (checkboxTipo.checked) {
            blocoTrabalhador.style.display = "block"
        } else {
            blocoTrabalhador.style.display = "none"
        }
    }

    alternarBlocoTrabalhador()

    checkboxTipo.addEventListener("change", alternarBlocoTrabalhador)
}



