const express = require("express");
const http = require("http");
const path = require("path");
const crypto = require("crypto");
const { WebSocketServer } = require("ws");

// Configuração (variáveis de ambiente)
const PORTA = process.env.PORT || 3000;
const SEGREDO = process.env.CHAT_SEGREDO || "";
const MODO_DEV = process.env.MODO_DEV === "true";
const lista = (v) => (v || "").split(",").map((s) => s.trim()).filter(Boolean);
const ORIGENS = lista(process.env.ORIGENS_PERMITIDAS);
const DEV_MODS = lista(process.env.DEV_MODERADORES).map((s) => s.toLowerCase());

if (!SEGREDO && !MODO_DEV) {
  console.error("Defina CHAT_SEGREDO (ou MODO_DEV=true apenas para testes).");
  process.exit(1);
}

//Token assinado (gerado pelo PHP)
function assinar(dados) {
  const b64 = Buffer.from(JSON.stringify(dados)).toString("base64url");
  const sig = crypto.createHmac("sha256", SEGREDO).update(b64).digest("hex");
  return b64 + "." + sig;
}

function verificar(token) {
  if (!SEGREDO) return null;
  const [b64, sig] = String(token || "").split(".");
  if (!b64 || !sig) return null;
  const esperado = crypto.createHmac("sha256", SEGREDO).update(b64).digest("hex");
  const a = Buffer.from(sig);
  const b = Buffer.from(esperado);
  if (a.length !== b.length || !crypto.timingSafeEqual(a, b)) return null;
  let d;
  try { d = JSON.parse(Buffer.from(b64, "base64url").toString()); } catch { return null; }
  if (!d || !d.exp || d.exp < Date.now() / 1000) return null;
  if (!d.id || !d.nome) return null;
  return d;
}

//Servidor http
const app = express();
app.disable("x-powered-by");

app.use((req, res, next) => {
  res.setHeader("X-Content-Type-Options", "nosniff");
  // só o próprio chat e os sites listados podem embutir via iframe
  if (!MODO_DEV) {
    res.setHeader("Content-Security-Policy", "frame-ancestors " + ["'self'", ...ORIGENS].join(" "));
  }
  next();
});

app.get("/saude", (req, res) => res.send("ok"));
app.use(express.static(path.join(__dirname, "public")));

const server = http.createServer(app);

//WebSocket
const wss = new WebSocketServer({
  server,
  maxPayload: 4096,
  verifyClient: ({ origin, req }) => {
    if (!origin) return MODO_DEV; // ferramentas sem origem só em testes
    try {
      if (new URL(origin).host === req.headers.host) return true; // chat
    } catch { return false; }
    return ORIGENS.includes(origin) || MODO_DEV;
  },
});

const historico = [];          // últimas 50 mensagens (em memória)
const silenciados = new Map(); // id do usuário -> timestamp do fim do silêncio
let proximoId = 1;

const horaBR = () =>
  new Date().toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit", timeZone: "America/Sao_Paulo" });

function enviar(socket, obj) {
  if (socket.readyState === 1) socket.send(JSON.stringify(obj));
}

function transmitir(obj) {
  const s = JSON.stringify(obj);
  wss.clients.forEach((c) => { if (c.readyState === 1 && c.usuario) c.send(s); });
}

function transmitirOnline() {
  const ids = new Set([...wss.clients].filter((c) => c.usuario).map((c) => c.usuario.id));
  transmitir({ tipo: "online", total: ids.size });
}

function autenticar(socket, m) {
  if (m.tipo !== "auth") return;
  let u = verificar(m.token);

  if (!u && MODO_DEV && m.dev) {
    const nome = String(m.nome || "Anônimo").slice(0, 30);
    u = { id: "dev-" + nome.toLowerCase(), nome, papel: DEV_MODS.includes(nome.toLowerCase()) ? "moderador" : "trabalhador" };
  }

  if (!u) {
    enviar(socket, { tipo: "erro", mensagem: "Sessão inválida ou expirada. Recarregue a página." });
    return socket.close(4003, "token");
  }

  socket.usuario = {
    id: String(u.id).slice(0, 40),
    nome: String(u.nome).slice(0, 30),
    papel: u.papel === "moderador" ? "moderador" : "trabalhador",
  };

  // token de renovação (2h) para o navegador reconectar sem recarregar a página
  const renovacao = SEGREDO ? assinar({ ...socket.usuario, exp: Math.floor(Date.now() / 1000) + 7200 }) : null;
  enviar(socket, { tipo: "ok", usuario: socket.usuario, renovacao });
  enviar(socket, { tipo: "historico", mensagens: historico });
  transmitirOnline();
}

wss.on("connection", (socket) => {
  socket.vivo = true;
  socket.usuario = null;
  socket.envios = [];

  const limite = setTimeout(() => { if (!socket.usuario) socket.close(4001, "auth"); }, 5000);

  socket.on("pong", () => (socket.vivo = true));
  socket.on("close", () => { clearTimeout(limite); transmitirOnline(); });

  socket.on("message", (dados) => {
    let m;
    try { m = JSON.parse(dados); } catch { return; }
    if (!m || typeof m !== "object" || m.tipo === "ping") return;

    if (!socket.usuario) return autenticar(socket, m);
    const u = socket.usuario;

    if (m.tipo === "msg") {
      const texto = String(m.texto || "").trim().slice(0, 500);
      if (!texto) return;

      const fim = silenciados.get(u.id);
      if (fim && fim > Date.now()) {
        const min = Math.ceil((fim - Date.now()) / 60000);
        return enviar(socket, { tipo: "aviso", texto: "Você está silenciado por mais " + min + " min." });
      }

      const agora = Date.now();
      socket.envios = socket.envios.filter((t) => agora - t < 10000);
      if (socket.envios.length >= 5) {
        return enviar(socket, { tipo: "aviso", texto: "Calma! Você está enviando mensagens rápido demais." });
      }
      socket.envios.push(agora);

      const msg = { tipo: "msg", mid: proximoId++, uid: u.id, nome: u.nome, papel: u.papel, texto, hora: horaBR() };
      historico.push(msg);
      if (historico.length > 50) historico.shift();
      transmitir(msg);
      return;
    }

    //ações exclusivas de moderador
    
    if (u.papel !== "moderador") return;

    if (m.tipo === "apagar") {
      const i = historico.findIndex((x) => x.mid === m.mid);
      if (i >= 0) historico.splice(i, 1);
      transmitir({ tipo: "apagada", mid: m.mid });
    }

    if (m.tipo === "silenciar") {
      const alvo = [...wss.clients].find((c) => c.usuario && c.usuario.id === String(m.uid));
      if (alvo && alvo.usuario.papel === "moderador") return;
      const min = Math.min(Math.max(parseInt(m.minutos, 10) || 5, 1), 1440);
      silenciados.set(String(m.uid), Date.now() + min * 60000);
      const nome = alvo ? alvo.usuario.nome : "um usuário";
      transmitir({ tipo: "aviso", texto: "Um moderador silenciou " + nome + " por " + min + " min." });
    }
  });
});

// a cada 30s derruba conexões mortas e mantém as vivas ativas
setInterval(() => {
  wss.clients.forEach((c) => {
    if (!c.vivo) return c.terminate();
    c.vivo = false;
    c.ping();
  });
}, 30000);

server.listen(PORTA, () => {
  console.log("Chat rodando na porta " + PORTA + (MODO_DEV ? " (MODO_DEV ligado)" : ""));
});
