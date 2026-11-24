const containerPerfil = document.getElementById("feed-publicacoes");
const AVATAR_PADRAO = "/biconline/foto_de_perfil.webp"; 

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

async function carregarPublicacoesPerfil() {
    if (!containerPerfil) return;

    const codigoUsuario = containerPerfil.dataset.usuario || "";
    if (!codigoUsuario) {
        containerPerfil.textContent = "Usuário do perfil não identificado.";
        return;
    }

    try {
        const resposta = await fetch(`buscar-publicacao.php?usuario=${encodeURIComponent(codigoUsuario)}`);

        if (!resposta.ok) {
            throw new Error("Erro HTTP " + resposta.status);
        }

        const publicacoes = await resposta.json();

        if (!Array.isArray(publicacoes)) {
            throw new Error("Resposta inválida do PHP.");
        }

        containerPerfil.innerHTML = "";

        if (publicacoes.length === 0) {
            containerPerfil.textContent = "Nenhuma publicação ainda.";
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
                ? corrigirCaminho(publicacao.url_foto_perfil)
                : AVATAR_PADRAO;
            foto.alt = "Foto de " + (publicacao.nome || "Usuário");
            foto.onerror = () => {
                foto.onerror = null;
                foto.src = AVATAR_PADRAO;
            };
            cabecalho.appendChild(foto);

            const usuario = document.createElement("div");
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

            if (publicacao.id_usuario || publicacao.eh_dono) {
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
                    editar.href = `editar-publicacao.php?id=${encodeURIComponent(publicacao.id)}&voltar=perfil`;
                    editar.target = "_blank";
                    editar.rel = "noopener noreferrer";
                    editar.className = "opcao-editar";
                    editar.textContent = "Editar";
                    listaMenu.appendChild(editar);

                    const formExcluir = document.createElement("form");
                    formExcluir.action = "gerenciar-publicacao.php";
                    formExcluir.method = "post";
                    formExcluir.onsubmit = () => confirm("Deseja excluir esta publicação?");

                    const inputAcao = document.createElement("input");
                    inputAcao.type = "hidden";
                    inputAcao.name = "acao";
                    inputAcao.value = "excluir";

                    const inputId = document.createElement("input");
                    inputId.type = "hidden";
                    inputId.name = "id";
                    inputId.value = publicacao.id;

                    const btnExcluir = document.createElement("button");
                    btnExcluir.type = "submit";
                    btnExcluir.className = "opcao-excluir";
                    btnExcluir.textContent = "Excluir";

                    formExcluir.append(inputAcao, inputId, btnExcluir);
                    listaMenu.appendChild(formExcluir);
                } else if (publicacao.id_usuario) {
                    const denunciar = document.createElement("a");
                    denunciar.href = `denunciar.php?id=${encodeURIComponent(publicacao.id_usuario)}&id_publicacao=${encodeURIComponent(publicacao.id)}`;
                    denunciar.textContent = "Denunciar";
                    denunciar.className = "opcao-denunciar";
                    listaMenu.appendChild(denunciar);
                }

                botaoMenu.addEventListener("click", (e) => {
                    e.stopPropagation();
                    document.querySelectorAll(".lista-menu-publicacao.menu-aberto").forEach(m => {
                        if (m !== listaMenu) m.classList.remove("menu-aberto");
                    });
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
                    cartao.appendChild(video);
                } else {
                    const imagem = document.createElement("img");
                    imagem.src = caminhoMidia;
                    imagem.alt = "Mídia da publicação";
                    imagem.className = "publicacao-midia";
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

            containerPerfil.appendChild(cartao);
        });

    } catch (erro) {
        console.error("Erro ao carregar publicações do perfil:", erro);
        containerPerfil.textContent = "Erro ao carregar publicações.";
    }
}

document.addEventListener("click", () => {
    document.querySelectorAll(".lista-menu-publicacao.menu-aberto").forEach(menu => {
        menu.classList.remove("menu-aberto");
    });
});

document.addEventListener("DOMContentLoaded", () => {
    carregarPublicacoesPerfil();
});