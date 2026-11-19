(function () {
  const raiz = document.getElementById("contratacao");
  if (!raiz) return;

  const idChat = Number(raiz.dataset.idChat);
  let csrf = "";

  async function chamar(url, dados) {
    const fd = new FormData();
    Object.entries(dados).forEach(([k, v]) => fd.append(k, v));
    fd.append("csrf", csrf);
    const r = await fetch(url, { method: "POST", body: fd });
    return r.json();
  }

  function formatoValor(v) {
    return v ? "R$ " + Number(v).toFixed(2).replace(".", ",") : "sem valor combinado";
  }
  function formatoData(d) {
    if (!d) return "sem data combinada";
    const [a, m, dia] = d.split("-");
    return dia + "/" + m + "/" + a;
  }

  function render(estado) {
    csrf = estado.csrf || csrf;
    const p = estado.proposta;
    let html = "";

    if (estado.pode_propor) {
      html += `
        <h3>Contratar ${escapeHtml(estado.outro_nome)}</h3>
        <div class="linha">
          <input name="descricao" placeholder="O que será feito" maxlength="150">
          <input name="valor" placeholder="Valor (opcional)" style="width:110px">
          <input name="data_servico" type="date" style="width:150px">
          <button type="button" id="btn-propor">Enviar proposta</button>
        </div>
        <div class="msg-erro" id="erro-contratacao"></div>`;
    } else if (p && p.status === "pendente") {
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
    } else if (p) {
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
    ligarBotoes(estado);
  }

  function legendaStatus(s) {
    return { aceita: "Contratação aceita ✓", recusada: "Proposta recusada", cancelada: "Proposta cancelada" }[s] || s;
  }

  function escapeHtml(t) {
    const d = document.createElement("div");
    d.textContent = t || "";
    return d.innerHTML;
  }

  function mostrarErro(msg) {
    const e = document.getElementById("erro-contratacao");
    if (e) { e.textContent = msg; e.style.display = "block"; }
  }

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
    }
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

  function usuarioEstaDigitando() {
    // enquanto o campo de descrição tiver foco ou texto, não redesenha o painel
    const campo = raiz.querySelector('[name="descricao"]');
    return !!campo && (document.activeElement === campo || campo.value.trim() !== "");
  }

  async function atualizar() {
    if (usuarioEstaDigitando()) return; // evita apagar o que a pessoa está escrevendo
    try {
      const r = await fetch("proposta_estado.php?id_chat=" + idChat);
      const estado = await r.json();
      if (estado.ok && !usuarioEstaDigitando()) render(estado);
    } catch (e) { /* tenta de novo no próximo ciclo */ }
  }

  atualizar();
  setInterval(atualizar, 4000);
})();