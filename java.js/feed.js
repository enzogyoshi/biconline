const feed = document.getElementById("feed-publicacoes");
async function carregarPublicacoes() {
    if (!feed) {
        console.error('Elemento "feed-publicacoes" não encontrado.');
        return;
    }
    try {
        const resposta = await fetch("../php/buscar-publicacao.php");
        if (!resposta.ok) {
            throw new Error("Erro HTTP " + resposta.status);
        }
        const publicacoes = await resposta.json();
        if (!Array.isArray(publicacoes)) {
            throw new Error("Resposta inválida do PHP.");
        }
        feed.innerHTML = "";
        if (publicacoes.length === 0) {
            feed.textContent = "Nenhuma publicação ainda.";
            return;
        }
        publicacoes.forEach(publicacao => {
            const cartao = document.createElement("article");
            cartao.className = "publicacao";
            const cabecalho = document.createElement("div");
            cabecalho.className = "publicacao-cabecalho";
            const foto = document.createElement("img");
            foto.className = "publicacao-foto";
            foto.src = publicacao.url_foto_perfil
                ? "../biconline/" + publicacao.url_foto_perfil.replace(/^\/+/, "")
                : "../biconline/img/perfil-padrao.png";
            foto.alt = "Foto de perfil";
            const usuario = document.createElement("div");
            usuario.className = "publicacao-usuario";
            usuario.textContent = publicacao.nome || "Usuário";
            const horario = document.createElement("div");
            horario.className = "publicacao-horario";
            if (publicacao.data_pub) {
                const data = new Date(
                    publicacao.data_pub.replace(" ", "T")
                );
                horario.textContent = isNaN(data.getTime())
                    ? publicacao.data_pub
                    : data.toLocaleString("pt-BR");
            }
            cabecalho.append(foto, usuario, horario);
            const texto = document.createElement("p");
            texto.className = "publicacao-texto";
            texto.textContent = publicacao.conteudo || "";
            cartao.append(cabecalho, texto);
            if (publicacao.url_midia) {
                const imagem = document.createElement("img");
                imagem.src = "../biconline/" +
                    publicacao.url_midia.replace(/^\/+/, "");
                imagem.alt = "Imagem da publicação";
                imagem.style.maxWidth = "100%";
                cartao.appendChild(imagem);
            }
            if (publicacao.link) {
                const link = document.createElement("a");
                link.href = publicacao.link;
                link.textContent = "Acessar link";
                link.target = "_blank";
                link.rel = "noopener noreferrer";
                cartao.appendChild(link);
            }
            feed.appendChild(cartao);
        });
    } catch (erro) {
        console.error("Erro ao carregar publicações:", erro);
        feed.textContent = "Erro ao carregar publicações.";
    }
}
document.querySelectorAll("button, a").forEach(elemento => {
    if (elemento.textContent.trim() === "+") {
        elemento.addEventListener("click", function (evento) {
            evento.preventDefault();
            window.location.href = "../biconline/publicacao.html";
        });
    }
});
carregarPublicacoes();