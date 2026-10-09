const form = document.getElementById("form");
const texto = document.getElementById("texto");
const mensagens = document.getElementById("mensagens");
const status = document.getElementById("status");

// nome salvo no navegador
let nome = localStorage.getItem("nome");
if (!nome) {
  nome = prompt("Qual é o seu nome?") || "Anônimo";
  localStorage.setItem("nome", nome);
}

// identificador único deste navegador
let meuId = localStorage.getItem("meuId");
if (!meuId) {
  meuId = Math.random().toString(36).slice(2);
  localStorage.setItem("meuId", meuId);
}

// conecta ao servidor (ws:// ou wss:// conforme a página)
const protocolo = location.protocol === "https:" ? "wss://" : "ws://";
const socket = new WebSocket(protocolo + location.host);

socket.onopen = () => (status.textContent = "online");
socket.onclose = () => (status.textContent = "desconectado");

socket.onmessage = (evento) => {
  const msg = JSON.parse(evento.data);
  const minha = msg.id === meuId;

  const div = document.createElement("div");
  div.className = "msg " + (minha ? "enviada" : "recebida");

  const n = document.createElement("div");
  n.className = "nome";
  n.textContent = msg.nome;

  const t = document.createElement("div");
  t.textContent = msg.texto;

  const h = document.createElement("div");
  h.className = "hora";
  h.textContent = msg.hora;

  div.append(n, t, h);
  mensagens.appendChild(div);
  mensagens.scrollTop = mensagens.scrollHeight;
};

form.addEventListener("submit", (e) => {
  e.preventDefault();
  const conteudo = texto.value.trim();
  if (!conteudo || socket.readyState !== 1) return;

  socket.send(JSON.stringify({ id: meuId, nome: nome, texto: conteudo }));
  texto.value = "";
});