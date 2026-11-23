CREATE DATABASE bicoonline;
USE bicoonline;

CREATE TABLE usuario(
	id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf CHAR(11) UNIQUE,
    cnpj CHAR(14) UNIQUE,
    senha VARCHAR(60) NOT NULL,
    data_nascimento DATE NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    cidade VARCHAR(50) NOT NULL,
    uf CHAR(2) NOT NULL,
    url_foto_perfil VARCHAR(200),
    bio VARCHAR(500),
    score_avaliacao INT,
    is_moderador BOOLEAN NOT NULL,
    CONSTRAINT chk_senha CHECK (LENGTH(senha) BETWEEN 7 AND 60),
    CONSTRAINT chk_documento
    CHECK ((cpf IS NOT NULL OR cnpj IS NOT NULL)
	AND (NOT(cpf IS NOT NULL AND cnpj IS NOT NULL)))
    );

CREATE TABLE trabalhador(
	id INT AUTO_INCREMENT PRIMARY KEY,
	disponibilidade BOOLEAN NOT NULL,
    experiencia INT NOT NULL,
    id_usuario INT,
    CONSTRAINT fk_perfil_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
    );
    
CREATE TABLE habilidades(
	habilidade VARCHAR(50) NOT NULL,
    id_trabalhador INT,
    CONSTRAINT fk_habilidades_usuario
    FOREIGN KEY (id_trabalhador) REFERENCES trabalhador(id)
	);

CREATE TABLE moderacao(
	id INT AUTO_INCREMENT PRIMARY KEY,
	item_denunciado VARCHAR(200) NOT NULL,
    motivo VARCHAR(200) NOT NULL,
    acao VARCHAR(150) NOT NULL,
	id_moderador INT,
    id_usuario_alvo INT,
    CONSTRAINT fk_moderador 
    FOREIGN KEY (id_moderador) REFERENCES usuario(id),
    CONSTRAINT fk_usuario_alvo
    FOREIGN KEY (id_usuario_alvo) REFERENCES usuario(id)
    );
    
CREATE TABLE publicacao(
    id INT AUTO_INCREMENT PRIMARY KEY,
    conteudo VARCHAR(500) NOT NULL,
    url_midia VARCHAR(255) NULL,
    link VARCHAR(255) NULL,
    data_pub DATE NOT NULL,
    id_usuario INT,
    CONSTRAINT fk_usuario_pub
    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
);
    
CREATE TABLE servico(
	id INT AUTO_INCREMENT PRIMARY KEY,
    data_serv DATE NOT NULL,
    servico_prestado VARCHAR(150) NOT NULL,
    status_serv BOOLEAN NOT NULL,
    id_contratante INT,
    id_contratado INT,
    CONSTRAINT fk_contratante
    FOREIGN KEY (id_contratante) REFERENCES usuario(id),
    CONSTRAINT fk_contratado
    FOREIGN KEY (id_contratado) REFERENCES trabalhador(id)
	);
    
CREATE TABLE avaliacoes(
	id INT AUTO_INCREMENT PRIMARY KEY,
	comentario VARCHAR(200),
    nota INT NOT NULL,
    id_servico INT,
    id_usuario INT,
    CONSTRAINT fk_avaliacao_servico
    FOREIGN KEY (id_servico) REFERENCES servico(id),
    CONSTRAINT fk_avaliacao_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuario(id),
    CONSTRAINT UNIQUE(id_servico, id_usuario),
    CONSTRAINT chk_nota_aval
    CHECK (nota BETWEEN 1 AND 5)
    );
    
CREATE TABLE contatos(
	numero_telefone VARCHAR(15) NOT NULL,
    email_contato VARCHAR(50) NOT NULL,
    id_usuario INT,
    CONSTRAINT fk_contatos_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
	);
    
CREATE TABLE chat(
	id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_trabalhador INT,
    CONSTRAINT fk_chat_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuario(id),
    CONSTRAINT fk_chat_trabalhador
    FOREIGN KEY (id_trabalhador) REFERENCES trabalhador(id),
    CONSTRAINT UNIQUE (id_usuario, id_trabalhador)
    );
    
CREATE TABLE mensagem(
	id INT AUTO_INCREMENT PRIMARY KEY,
    conteudo VARCHAR(500) NOT NULL,
    data_msg DATE NOT NULL,
    id_chat INT,
    id_usuario INT,
    CONSTRAINT fk_msg_chat
    FOREIGN KEY (id_chat) REFERENCES chat(id),
    CONSTRAINT fk_msg_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
    );
    
    INSERT INTO usuario (
    nome,
    cpf,
    cnpj,
    senha,
    data_nascimento,
    email,
    cidade,
    uf,
    url_foto_perfil,
    bio,
    score_avaliacao,
    is_moderador
)
VALUES (
    'João da Silva',
    '89332861013',
    NULL,
    'senhasegura1',
    '2000-05-21',
    'joao@gmail.com',
    'Araucária',
    'PR',
    NULL,
    'Usuario de teste do BicoOnline',
    5,
    FALSE
);

INSERT INTO publicacao
    (conteudo, url_midia, link, data_pub, id_usuario)
VALUES
    ('Publicacao de teste 1', NULL, NULL, '2026-10-20', 1),
    ('Publicacao de teste 2', NULL, NULL, '2026-10-21', 1),
    ('Publicacao de teste 3', NULL, NULL, '2026-10-22', 1),
    ('Publicacao de teste 4', NULL, NULL, '2026-10-23', 1),
    ('Publicacao de teste 5', NULL, NULL, '2026-10-24', 1),
    ('Publicacao de teste 6', NULL, NULL, '2026-10-25', 1);
    
SELECT id, conteudo, data_pub, id_usuario
FROM publicacao
WHERE id_usuario = 1;

UPDATE publicacao
SET conteudo = 'Publicacao de teste 1 - atualizada'
WHERE id = 1;

SELECT id, conteudo, data_pub, id_usuario
FROM publicacao
WHERE id = 1;

SELECT id, conteudo, data_pub, id_usuario
FROM publicacao
WHERE id = 6;

DELETE FROM publicacao
WHERE id = 6;

SELECT id, conteudo, data_pub, id_usuario
FROM publicacao
WHERE id = 6;

INSERT INTO publicacao
    (conteudo, url_midia, link, data_pub, id_usuario)
VALUES
    ('Publicacao de teste 7', NULL, NULL, '2026-10-26', 1);
    
SELECT id, conteudo, data_pub, id_usuario
FROM publicacao
WHERE id_usuario = 1;

SELECT
    publicacao.id AS id_publicacao,
    publicacao.conteudo,
    publicacao.data_pub,
    usuario.id AS id_usuario,
    usuario.nome AS nome_usuario
FROM publicacao
INNER JOIN usuario
    ON publicacao.id_usuario = usuario.id
WHERE usuario.id = 1;