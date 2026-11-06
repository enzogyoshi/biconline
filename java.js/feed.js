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
            
            const fotoUrl = publicacao.url_foto_perfil 
                ? "../" + publicacao.url_foto_perfil.replace(/^\/+/, "")
                : "../img/perfil-padrao.png";
            foto.src = fotoUrl;
            foto.alt = "Foto de perfil";

            const usuario = document.createElement("div");
            usuario.className = "publicacao-usuario";
            usuario.textContent = publicacao.nome || "Usuário";

            const horario = document.createElement("div");
            horario.className = "publicacao-horario";
            if (publicacao.data_pub) {
                const data = new Date(publicacao.data_pub.replace(" ", "T"));
                horario.textContent = isNaN(data.getTime())
                    ? publicacao.data_pub
                    : data.toLocaleString("pt-BR", {
                        day: "2-digit",
                        month: "2-digit",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit"
                      });
            }

            cabecalho.append(foto, usuario, horario);

            const texto = document.createElement("p");
            texto.className = "publicacao-texto";
            texto.textContent = publicacao.conteudo || "";

            cartao.append(cabecalho, texto);

            if (publicacao.url_midia) {
                const caminhoMidia = "../" + publicacao.url_midia.replace(/^\/+/, "");
                const extensao = caminhoMidia.split('.').pop().toLowerCase();

                if (['mp4', 'webm'].includes(extensao)) {
                    const video = document.createElement("video");
                    video.src = caminhoMidia;
                    video.controls = true;
                    video.className = "publicacao-midia";
                    video.style.maxWidth = "100%";
                    cartao.appendChild(video);
                } else {
                    // Se for imagem
                    const imagem = document.createElement("img");
                    imagem.src = caminhoMidia;
                    imagem.alt = "Mídia da publicação";
                    imagem.className = "publicacao-midia";
                    imagem.style.maxWidth = "100%";
                    cartao.appendChild(imagem);
                }
            }

            if (publicacao.link) {
                const link = document.createElement("a");
                link.href = publicacao.link;
                link.textContent = "Acessar link";
                link.className = "publicacao-link";
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

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("button, a").forEach(elemento => {
        if (elemento.textContent.trim() === "+") {
            elemento.addEventListener("click", function (evento) {
                evento.preventDefault();
                window.location.href = "../publicacao.html";
            });
        }
    });

    carregarPublicacoes();
});