const express = require("express");
const http = require("http");
const path = require("path");
const { WebSocketServer } = require("ws");

const app = express();
app.use(express.static(path.join(__dirname, "public")));

const server = http.createServer(app);
const wss = new WebSocketServer({ server });

wss.on("connection", (socket) => {
  socket.vivo = true;
  socket.on("pong", () => (socket.vivo = true));

  socket.on("message", (dados) => {
    let msg;
    try { msg = JSON.parse(dados); } catch { return; }
    if (msg.tipo === "ping") return; // batimento do cliente

    const pacote = JSON.stringify({
      id: String(msg.id).slice(0, 40),
      nome: String(msg.nome).slice(0, 20),
      texto: String(msg.texto).slice(0, 500),
      hora: new Date().toLocaleTimeString("pt-BR", {
        hour: "2-digit", minute: "2-digit", timeZone: "America/Sao_Paulo",
      }),
    });

    wss.clients.forEach((c) => {
      if (c.readyState === 1) c.send(pacote);
    });
  });
});

// a cada 30s, derruba quem não responde e mantém os outros ativos
setInterval(() => {
  wss.clients.forEach((c) => {
    if (!c.vivo) return c.terminate();
    c.vivo = false;
    c.ping();
  });
}, 30000);

const PORTA = process.env.PORT || 3000;
server.listen(PORTA, () => console.log("Chat rodando na porta " + PORTA));