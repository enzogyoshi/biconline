const ufsValidas = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
let lista_cidades = [];

document.addEventListener("DOMContentLoaded", function () {
    const ufSelect = document.getElementById("ufSelect");
    const checkboxTipo = document.getElementById("tipo");
    const blocoTrabalhador = document.getElementById("blocoTrabalhador");

    if (ufSelect) {
        if (ufSelect.value !== "") {
            fetchCidades(false);
        }
        ufSelect.addEventListener("change", function () {
            fetchCidades(true);
        });
    }

    if (checkboxTipo && blocoTrabalhador) {
        function alternarBlocoTrabalhador() {
            blocoTrabalhador.style.display = checkboxTipo.checked ? "block" : "none";
        }
        alternarBlocoTrabalhador();
        checkboxTipo.addEventListener("change", alternarBlocoTrabalhador);
    }

    document.querySelectorAll(".btn-eye").forEach(btn => {
        btn.addEventListener("click", function () {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const iconEye = this.querySelector(".icon-eye");
            const iconEyeOff = this.querySelector(".icon-eye-off");

            if (input) {
                if (input.type === "password") {
                    input.type = "text";
                    if (iconEye) iconEye.style.display = "none";
                    if (iconEyeOff) iconEyeOff.style.display = "block";
                } else {
                    input.type = "password";
                    if (iconEye) iconEye.style.display = "block";
                    if (iconEyeOff) iconEyeOff.style.display = "none";
                }
            }
        });
    });
});

function fetchCidades(trocouUF = false) {
    const ufSelect = document.getElementById("ufSelect");
    const txtCidade = document.getElementById("txtCidade");
    const datalist = document.getElementById("cidades");
    const msgErro = document.getElementById("msgErro");

    if (!ufSelect || !txtCidade || !datalist) return;

    datalist.replaceChildren();
    lista_cidades = [];

    if (trocouUF) {
        txtCidade.value = "";
        if (msgErro) msgErro.textContent = "";
    }

    if (ufSelect.value === "") {
        txtCidade.disabled = true;
        return;
    }

    txtCidade.disabled = true;
    const url = `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${ufSelect.value}/municipios`;

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(`Status HTTP: ${response.status}`);
            return response.json();
        })
        .then(dados => {
            dados.forEach(cidade => {
                lista_cidades.push(cidade.nome);
                const op = document.createElement("option");
                op.value = cidade.nome;
                datalist.appendChild(op);
            });
            txtCidade.disabled = false;
        })
        .catch(error => {
            if (msgErro) {
                msgErro.textContent = "Erro ao carregar cidades. Troque a UF para tentar novamente.";
            }
            console.error("Erro na requisição da API IBGE:", error);
        });
}

function validar_edicao() {
    const msgErro = document.getElementById("msgErro");
    if (msgErro) msgErro.textContent = "";

    const nome = document.getElementById("txtNome");
    const senha = document.getElementById("txtSenha");
    const confirmSenha = document.getElementById("senhaConfirm");
    const email = document.getElementById("txtEmail");
    const cidade = document.getElementById("txtCidade");
    const uf = document.getElementById("ufSelect");

    if (nome && nome.value.trim() !== "") {
        const tamNome = nome.value.trim().length;
        if (tamNome < 3 || tamNome > 100) {
            return cadInvalido(nome, "O nome deve ter entre 3 e 100 caracteres.");
        }
    }

    if (email && email.value.trim() !== "") {
        if (!validarEmail(email.value)) {
            return cadInvalido(email, "Digite um e-mail válido.");
        }
    }

    if (senha && senha.value.trim() !== "") {
        if (senha.value.length < 8 || senha.value.length > 100) {
            return cadInvalido(senha, "A senha deve ter no mínimo 8 caracteres e no máximo 100.");
        }
        if (confirmSenha && senha.value !== confirmSenha.value) {
            return cadInvalido(confirmSenha, "As senhas não coincidem.");
        }
    }

    if (!uf || !uf.value || !validarUF(uf.value)) {
        return cadInvalido(uf, "Selecione um Estado (UF) válido.");
    }

    if (lista_cidades.length === 0) {
        return cadInvalido(uf, "A lista de cidades não carregou. Troque a UF e tente novamente.");
    }
    if (!cidade || !cidade.value.trim() || !validarCidade(cidade.value)) {
        return cadInvalido(cidade, "Insira uma cidade válida pertencente ao Estado selecionado.");
    }

    const btn = document.getElementById("btnSubmit");
    if (btn) {
        btn.disabled = true;
        btn.textContent = "salvando";
    }

    return true;
}

function validarUF(uf) {
    return ufsValidas.includes(uf.trim().toUpperCase());
}

function validarEmail(email) {
    const regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return regexEmail.test(email);
}

function validarCidade(cidade) {
    if (lista_cidades.length === 0) return false;
    return lista_cidades.includes(cidade.trim());
}

function cadInvalido(item, erro) {
    const msgErro = document.getElementById("msgErro");
    if (msgErro) msgErro.textContent = erro;
    if (item && typeof item.focus === "function") {
        item.focus();
    }
    return false;
}