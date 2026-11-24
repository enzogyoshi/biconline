const feed = document.getElementById("feed-publicacoes");

function corrigirCaminho(caminho) {
    if (!caminho) return "";

    if (caminho.startsWith("http://") || caminho.startsWith("https://")) {
        return caminho;
    }

    if (caminho.startsWith("/biconline/")) {
        return caminho;
    }

    let caminhoLimpo = caminho
        .replace(/^(\.\.\/|\.\/)+/, "")
        .replace(/^\/+/, "");

    if (caminhoLimpo.startsWith("uploads/")) {
        caminhoLimpo = "php/" + caminhoLimpo;
    }

    return "/biconline/" + caminhoLimpo;
}

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

            if (publicacao.url_foto_perfil) {
                const foto = document.createElement("img");
                foto.className = "publicacao-foto";
                foto.src = corrigirCaminho(publicacao.url_foto_perfil);
                foto.alt = "Foto de " + (publicacao.nome || "Usuário");
                foto.onerror = () => {
                    foto.remove();
                };
                cabecalho.appendChild(foto);
            }

            let usuario;
            if (publicacao.id_usuario) {
                usuario = document.createElement("a");
                usuario.href = "../php/perfil.php?id=" + encodeURIComponent(publicacao.id_usuario);
            } else {
                usuario = document.createElement("div");
            }
            usuario.className = "publicacao-usuario";
            usuario.textContent = publicacao.nome || "Usuário";

            cabecalho.appendChild(usuario);

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

            cabecalho.appendChild(horario);

            const temMenu = Boolean(publicacao.id_usuario || publicacao.eh_dono);

            if (temMenu) {
                const menuContainer = document.createElement("div");
                menuContainer.className = "menu-publicacao";

                const botaoMenu = document.createElement("button");
                botaoMenu.className = "botao-menu-publicacao";
                botaoMenu.textContent = "⋮";
                botaoMenu.type = "button";
                botaoMenu.setAttribute("aria-label", "Opções da publicação");

                const listaMenu = document.createElement("div");
                listaMenu.className = "lista-menu-publicacao";

                if (publicacao.eh_dono) {
                    const editar = document.createElement("a");
                    editar.href = "../php/editar-publicacao.php?id=" + encodeURIComponent(publicacao.id) + "&voltar=feed";
                    editar.target = "_blank";
                    editar.rel = "noopener noreferrer";
                    editar.className = "opcao-editar";
                    editar.textContent = "Editar";
                    listaMenu.appendChild(editar);
                }

                if (publicacao.id_usuario) {
                    const denunciar = document.createElement("a");
                    denunciar.href = "../php/denuncia.php?id=" + encodeURIComponent(publicacao.id_usuario);
                    denunciar.textContent = "Denunciar";
                    denunciar.className = "opcao-denunciar";
                    listaMenu.appendChild(denunciar);
                }

                botaoMenu.addEventListener("click", (e) => {
                    e.stopPropagation();
                    listaMenu.classList.toggle("menu-aberto");
                });

                menuContainer.append(botaoMenu, listaMenu);
                cabecalho.appendChild(menuContainer);
            }

            const texto = document.createElement("p");
            texto.className = "publicacao-texto";
            texto.textContent = publicacao.conteudo || "";

            cartao.append(cabecalho, texto);

            if (publicacao.url_midia) {
                const caminhoMidia = corrigirCaminho(publicacao.url_midia);
                const extensao = caminhoMidia.split(".").pop().toLowerCase();

                if (["mp4", "webm"].includes(extensao)) {
                    const video = document.createElement("video");
                    video.src = caminhoMidia;
                    video.controls = true;
                    video.className = "publicacao-midia";
                    video.style.maxWidth = "100%";
                    cartao.appendChild(video);
                } else {
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

document.addEventListener("click", () => {
    document.querySelectorAll(".lista-menu-publicacao.menu-aberto").forEach(menu => {
        menu.classList.remove("menu-aberto");
    });
});

document.addEventListener("DOMContentLoaded", () => {
    carregarPublicacoes();
});