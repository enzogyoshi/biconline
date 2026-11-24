// Painel de contratação dentro da conversa 
// Não usa WebSocket — faz polling: pergunta pro servidor a cada 30s

(function () {
  const raiz = document.getElementById("contratacao");
  if (!raiz) return;

  const idChat = Number(raiz.dataset.idChat);
  let csrf = "";
//post simples 
  async function chamar(url, dados) {
    const fd = new FormData();
    Object.entries(dados).forEach(([k, v]) => fd.append(k, v));
    fd.append("csrf", csrf);
    const r = await fetch(url, { method: "POST", body: fd });
    return r.json();
  }
//formatação de valores e datas
  function formatoValor(v) {
    return v ? "R$ " + Number(v).toFixed(2).replace(".", ",") : "sem valor combinado";
  }
  function formatoData(d) {
    if (!d) return "sem data combinada";
    const [a, m, dia] = d.split("-");
    return dia + "/" + m + "/" + a;
  }
// Desenha o painel inteiro de novo, baseado no que o servidor respondeu
  function render(estado) {
    csrf = estado.csrf || csrf;
    const p = estado.proposta;
    let html = "";

    if (estado.pode_propor) { //Não tem proposta ativa, mostra o formulário "Contratar"
      html += ` 
        <h3>Contratar ${escapeHtml(estado.outro_nome)}</h3>
        <div class="linha">
          <input name="descricao" placeholder="O que será feito" maxlength="150" autocomplete="off">
          <input name="valor" placeholder="Valor (opcional)" style="width:110px" autocomplete="off">
          <input name="data_servico" type="date" style="width:150px" autocomplete="off">
          <button type="button" id="btn-propor">Enviar proposta</button>
        </div>
        <div class="msg-erro" id="erro-contratacao"></div>`;
    } else if (p && p.status === "pendente") { // Tem proposta esperando resposta mostra ela + botões de ação
      html += `<h3>Proposta de contratação</h3>
        <div class="proposta">
          <b>${escapeHtml(p.descricao)}</b>
          ${formatoValor(p.valor)} · ${formatoData(p.data_servico)}
          <div class="linha">
            <span class="status-pendente">Aguardando resposta</span>
            ${estado.pode_responder ? `
              <button type="button" id="btn-aceitar">Aceitar</button>
              <button type="button" class="recusar" id="btn-recusar">Recusar</button>` : ""}
            ${estado.pode_cancelar ? `<button type="button" class="cancelar" id="btn-cancelar">Cancelar proposta</button>` : ""}
          </div>
          <div class="msg-erro" id="erro-contratacao"></div>
        </div>`;
    } else if (p) { //mostra status final (aceita, recusada...)
      html += `<h3>Proposta de contratação</h3>
        <div class="proposta">
          <b>${escapeHtml(p.descricao)}</b>
          ${formatoValor(p.valor)} · ${formatoData(p.data_servico)}
          <div class="linha"><span class="status-${p.status}">${legendaStatus(p.status)}</span></div>
        </div>`;
    } else {
      html += `<p style="color:#7b8096">Esta conversa não tem contratação em andamento.</p>`;
    }

    raiz.innerHTML = html;
    ligarBotoes(estado); //innerHtml apagou os antigos cliques
  }

  function legendaStatus(s) {
    return { aceita: "Contratação aceita ✓", recusada: "Proposta recusada", cancelada: "Proposta cancelada" }[s] || s;
  }
//evita que o txt vire html
  function escapeHtml(t) {
    const d = document.createElement("div");
    d.textContent = t || "";
    return d.innerHTML;
  }

  function mostrarErro(msg) {
    const e = document.getElementById("erro-contratacao");
    if (e) { e.textContent = msg; e.style.display = "block"; }
  }
//religa os cliques dos botões depois de cada render()
  function ligarBotoes(estado) {
    const btnPropor = document.getElementById("btn-propor");
    if (btnPropor) {
      btnPropor.onclick = async () => {
        btnPropor.disabled = true;
        const descricao = raiz.querySelector('[name="descricao"]').value.trim();
        const valor = raiz.querySelector('[name="valor"]').value.trim();
        const data_servico = raiz.querySelector('[name="data_servico"]').value.trim();
        if (descricao.length < 3) { mostrarErro("Descreva o serviço (mínimo 3 caracteres)."); btnPropor.disabled = false; return; }
        const r = await chamar("proposta_criar.php", { id_chat: idChat, descricao, valor, data_servico });
        if (!r.ok) { mostrarErro(r.erro || "Não foi possível enviar."); btnPropor.disabled = false; return; }
        atualizar();
      };
    } //ligar os botões de ação
    const acao = (id, nome) => {
      const b = document.getElementById(id);
      if (!b) return;
      b.onclick = async () => {
        document.querySelectorAll(".contratacao button").forEach((x) => (x.disabled = true));
        const r = await chamar("proposta_responder.php", { id_proposta: estado.proposta.id, acao: nome });
        if (!r.ok) { mostrarErro(r.erro || "Não foi possível completar a ação."); }
        atualizar();
      };
    };
    acao("btn-aceitar", "aceitar");
    acao("btn-recusar", "recusar");
    acao("btn-cancelar", "cancelar");
  }

  async function atualizar() {
  const campo = raiz.querySelector('[name="descricao"]');
  if (campo && document.activeElement === campo) return; // só pula se o campo estiver com foco agora
  try {
    const r = await fetch("proposta_estado.php?id_chat=" + idChat);
    const estado = await r.json();
    if (estado.ok) render(estado);
  } catch (e) { /* tenta de novo no próximo ciclo */ }
}

  atualizar();
  setInterval(atualizar, 30000);
})();