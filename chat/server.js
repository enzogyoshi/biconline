const express = require("express");
const http = require("http");
const { WebSocketServer } = require("ws");

const app = express();
app.use(express.static("public"));

const server = http.createServer(app);
const wss = new WebSocketServer({ server });

wss.on("connection", (socket) => {
  socket.on("message", (dados) => {
    let msg;
    try { msg = JSON.parse(dados); } catch { return; }

    const pacote = JSON.stringify({
      id: String(msg.id).slice(0, 40),
      nome: String(msg.nome).slice(0, 20),
      texto: String(msg.texto).slice(0, 500),
      hora: new Date().toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit" }),
    });

    // envia para todos os conectados
    wss.clients.forEach((c) => {
      if (c.readyState === 1) c.send(pacote);
    });
  });
});

const PORTA = process.env.PORT || 3000;
server.listen(PORTA, () => console.log("Chat rodando em http://localhost:" + PORTA));