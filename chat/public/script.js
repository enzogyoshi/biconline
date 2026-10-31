const form = document.getElementById("form");
const texto = document.getElementById("texto");
const botao = form.querySelector("button");
const mensagens = document.getElementById("mensagens");
const status = document.getElementById("status");
const titulo = document.getElementById("titulo");
const params = new URLSearchParams(location.search);
const tokenInicial = params.get("token");
if (tokenInicial) history.replaceState(null, "", location.pathname);

const ler = (k) => { try { return localStorage.getItem(k); } catch { return null; } };
const guardar = (k, v) => { try { localStorage.setItem(k, v); } catch {} };

let nomeDev = params.get("nome") || ler("nome");
let socket, usuario = null, renovacao = null, moderacao = false;
let tentativas = 0, encerrado = false, totalOnline = 0;

function el(tag, classe, conteudo) {
  const e = document.createElement(tag);
  if (classe) e.className = classe;
  if (conteudo !== undefined) e.textContent = conteudo;
  return e;
}

function habilitar(sim) { texto.disabled = botao.disabled = !sim; }
function rolar() { mensagens.scrollTop = mensagens.scrollHeight; }
function enviar(obj) { if (socket && socket.readyState === 1) socket.send(JSON.stringify(obj)); }
function aviso(t) { mensagens.append(el("div", "aviso", t)); rolar(); }

function dadosAuth() {
  const token = renovacao || tokenInicial;
  if (token) return { tipo: "auth", token };
  // modo de testes: sem token, o servidor só aceita se estiver em MODO_DEV
  if (!nomeDev) {
    nomeDev = prompt("Qual é o seu nome?") || "Anônimo";
    guardar("nome", nomeDev);
  }
  return { tipo: "auth", dev: true, nome: nomeDev, sala: params.get("sala") || "geral" };
}

function adicionar(m) {
  const minha = usuario && m.uid === usuario.id;
  const div = el("div", "msg " + (minha ? "enviada" : "recebida"));
  div.dataset.mid = m.mid;

  if (!minha) {
    const n = el("div", "nome", m.nome);
    if (m.papel === "moderador") n.append(el("span", "selo", "MOD"));
    div.append(n);
  }
  div.append(el("div", "conteudo", m.texto), el("div", "hora", m.hora));

  const acoes = el("div", "acoes");
  if (minha || moderacao) {
    const apagar = el("button", "", "🗑");
    apagar.type = "button";
    apagar.title = "Apagar mensagem";
    apagar.onclick = () => enviar({ tipo: "apagar", mid: m.mid });
    acoes.append(apagar);
  }
  if (moderacao && !minha && m.papel !== "moderador") {
    const mudo = el("button", "", "🔇");
    mudo.type = "button";
    mudo.title = "Silenciar usuário";
    mudo.onclick = () => {
      const min = prompt("Silenciar por quantos minutos?", "10");
      if (min) enviar({ tipo: "silenciar", uid: m.uid, minutos: min });
    };
    acoes.append(mudo);
  }
  if (acoes.children.length) div.append(acoes);
  mensagens.append(div);
}

function tratar(m) {
  if (m.tipo === "ok") {
    usuario = m.usuario;
    moderacao = !!m.moderacao;
    if (m.titulo) titulo.textContent = "💬 " + m.titulo;
    renovacao = m.renovacao || renovacao;
    tentativas = 0;
    habilitar(true);
    status.textContent = "online";
  } else if (m.tipo === "historico") {
    mensagens.replaceChildren();
    m.mensagens.forEach(adicionar);
    rolar();
  } else if (m.tipo === "msg") {
    adicionar(m);
    rolar();
  } else if (m.tipo === "apagada") {
    const d = mensagens.querySelector('[data-mid="' + Number(m.mid) + '"]');
    if (d) d.remove();
  } else if (m.tipo === "aviso") {
    aviso(m.texto);
  } else if (m.tipo === "online") {
    totalOnline = m.total;
    status.textContent = "online · " + totalOnline;
  } else if (m.tipo === "erro") {
    encerrado = true;
    aviso(m.mensagem);
  }
}

function conectar() {
  const proto = location.protocol === "https:" ? "wss://" : "ws://";
  socket = new WebSocket(window.CHAT_URL || proto + location.host);

  socket.onopen = () => {
    status.textContent = "autenticando...";
    socket.send(JSON.stringify(dadosAuth()));
  };

  socket.onmessage = (e) => {
    let m;
    try { m = JSON.parse(e.data); } catch { return; }
    tratar(m);
  };

  socket.onclose = () => {
    usuario = null;
    habilitar(false);
    if (encerrado) { status.textContent = "desconectado"; return; }
    status.textContent = "reconectando...";
    tentativas++;
    setTimeout(conectar, Math.min(1000 * tentativas, 10000));
  };
}

form.addEventListener("submit", (e) => {
  e.preventDefault();
  const conteudo = texto.value.trim();
  if (!conteudo || !usuario) return;
  enviar({ tipo: "msg", texto: conteudo });
  texto.value = "";
  texto.focus();
});

setInterval(() => enviar({ tipo: "ping" }), 25000);

conectar();