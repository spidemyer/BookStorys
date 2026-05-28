CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    idade INT NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS funcionarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    rf VARCHAR(20) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS livros (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    estoque INT NOT NULL,
    url_capa TEXT NOT NULL
);

DROP TABLE IF EXISTS livros;
DROP TABLE IF EXISTS funcionarios;


CREATE TABLE funcionarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    rf VARCHAR(20) NOT NULL UNIQUE
);

CREATE TABLE livros (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    url_capa TEXT NOT NULL,
    funcionario_rf VARCHAR(20) NOT NULL,

    CONSTRAINT fk_funcionario_livro 
        FOREIGN KEY (funcionario_rf) 
        REFERENCES funcionarios(rf) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
);

INSERT INTO funcionarios (nome, rf) 
VALUES ('admin', '123456')
ON CONFLICT (rf) DO NOTHING;

TRUNCATE TABLE livros RESTART IDENTITY;

INSERT INTO livros (titulo, autor, estoque, url_capa, funcionario_rf) VALUES 
(
    'O Senhor dos Anéis', 
    'J.R.R. Tolkien', 
    3, 
    'img/senhor-dos-aneis.jpg', 
    '123456'
),
(
    'Harry Potter e a Pedra Filosofal', 
    'J.K. Rowling', 
    5, 
    'img/harry-potter.jpg', 
    '123456'
),
(
    '1984', 
    'George Orwell', 
    0, 
    'img/1984.jpg', 
    '123456'
),
(
    'Dom Casmurro', 
    'Machado de Assis', 
    2, 
    'img/dom-casmurro.jpg', 
    '123456'
);