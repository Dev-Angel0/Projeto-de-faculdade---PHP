-- Recria o banco de dados do projeto.
DROP DATABASE IF EXISTS plataforma_livros;
CREATE DATABASE plataforma_livros CHARACTER SET utf8mb4;
USE plataforma_livros;

-- Tabelas do modelo lógico solicitado no enunciado.
-- Armazena os dados das pessoas autoras.
CREATE TABLE autores (
    id_autor INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL
);

-- Define os tipos disponíveis para uma publicação.
CREATE TABLE tipo_publicacao (
    id_tipo_publicacao INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
);

-- Armazena convidados dos eventos de divulgação.
CREATE TABLE convidados (
    id_convidado INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL
);

-- Registra data, local e convidado de uma divulgação.
CREATE TABLE divulgacao (
    id_divulgacao INT AUTO_INCREMENT PRIMARY KEY,
    data_evento DATE NOT NULL,
    local_evento VARCHAR(100) NOT NULL,
    id_convidado INT NOT NULL,
    FOREIGN KEY (id_convidado) REFERENCES convidados(id_convidado)
);

-- Uma publicação pode ter no máximo um evento de divulgação.
CREATE TABLE publicacao (
    id_publicacao INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    resumo TEXT NOT NULL,
    data_publicacao DATE NOT NULL,
    id_divulgacao INT NULL UNIQUE,
    id_tipo_publicacao INT NOT NULL,
    FOREIGN KEY (id_divulgacao) REFERENCES divulgacao(id_divulgacao),
    FOREIGN KEY (id_tipo_publicacao) REFERENCES tipo_publicacao(id_tipo_publicacao)
);

-- A função do autor é específica para cada publicação.
CREATE TABLE publicacao_autor (
    id_publicacao INT NOT NULL,
    id_autor INT NOT NULL,
    funcao VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_publicacao, id_autor),
    FOREIGN KEY (id_publicacao) REFERENCES publicacao(id_publicacao) ON DELETE CASCADE,
    FOREIGN KEY (id_autor) REFERENCES autores(id_autor)
);

-- Dados iniciais para os campos de seleção.
INSERT INTO tipo_publicacao (nome) VALUES ('Livro acadêmico'), ('Livro científico'), ('E-book');
