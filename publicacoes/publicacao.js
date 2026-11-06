document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector(".formulario-post");

    if (form) {
        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            const dados = new FormData(form);
            const btnPublicar = form.querySelector(".btn-publicar");

            try {
                if (btnPublicar) {
                    btnPublicar.disabled = true;
                    btnPublicar.textContent = "Publicando...";
                }

                const resposta = await fetch("php/criar-publicacao.php", {
                    method: "POST",
                    body: dados
                });

                if (!resposta.ok) {
                    throw new Error(`Erro no servidor: ${resposta.status}`);
                }

                const retorno = await resposta.text();
                console.log("Resposta do servidor:", retorno);

                window.location.href = "php/perfil.php";

            } catch (erro) {
                console.error("Erro ao publicar:", erro);
                alert("Ocorreu um erro ao enviar sua publicação. Tente novamente.");
                
                // Reabilita o botão caso haja erro
                if (btnPublicar) {
                    btnPublicar.disabled = false;
                    btnPublicar.textContent = "Publicar";
                }
            }
        });
    }
});

function voltarPagina() {
    window.history.back();
}