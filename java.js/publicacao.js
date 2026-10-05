const texto = document.getElementById("post-texto");
const link_da_midia = document.getElementById("post-link");
const arquivo_midia = document.getElementsByName("midia");
const form = document.querySelector(".formulario-post");

form.addEventListener("submit", function (event) {
    event.preventDefault();

    const dados = new FormData(form);

    fetch("php/criar-publicacao.php", {
        method: "POST",
        body: dados
    })
        .then(function (resposta) {
            return resposta.text();
        })
        .then(function (texto) {
            console.log(texto);
        });
});