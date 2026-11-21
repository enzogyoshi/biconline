document.querySelectorAll(".form-concluir, .form-avaliar").forEach((form) => {
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const botao = form.querySelector("button");
    if (botao.disabled) return; // evita clique duplo / envio duplicado
    botao.disabled = true;
    const fd = new FormData(form);
    try {
      const r = await fetch(form.action, { method: "POST", body: fd });
      const j = await r.json();
      if (!j.ok) {
        alert(j.erro || "Não foi possível completar a ação.");
        botao.disabled = false;
        return;
      }
      location.reload();
    } catch (err) {
      alert("Erro de conexão. Tente novamente.");
      botao.disabled = false;
    }
  });
});

// filtro de pesquisa 
const campoBusca = document.getElementById("busca-trabalhos");
if (campoBusca) {
  campoBusca.addEventListener("input", () => {
    const termo = campoBusca.value.trim().toLowerCase();
    document.querySelectorAll(".trabalho-item").forEach((li) => {
      const bate = !termo || li.dataset.busca.includes(termo);
      li.style.display = bate ? "" : "none";
    });
    // some com a seção inteira se nenhum item dela bater com a busca
    document.querySelectorAll(".trabalhos-secao").forEach((secao) => {
      const algumVisivel = [...secao.querySelectorAll(".trabalho-item")].some((li) => li.style.display !== "none");
      const lista = secao.querySelector(".lista-trabalhos");
      if (lista) secao.style.display = algumVisivel ? "" : "none";
    });
  });
}