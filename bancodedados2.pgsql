
CREATE TABLE IF NOT EXISTS alugueis (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_aluguel TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_devolucao_prevista DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pendente',


    CONSTRAINT fk_usuario_aluguel
        FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT,

    CONSTRAINT fk_livro_aluguel
        FOREIGN KEY (livro_id) 
        REFERENCES livros(id)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
);