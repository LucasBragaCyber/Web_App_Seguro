CREATE TABLE alugueis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT,
  id_livro INT,
  data_aluguel DATETIME DEFAULT CURRENT_TIMESTAMP,
  data_devolucao DATETIME,
  devolvido BOOLEAN DEFAULT FALSE
);
