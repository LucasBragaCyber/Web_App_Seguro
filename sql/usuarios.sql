CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    nome VARCHAR(100),
    senha VARCHAR(255),
    token VARCHAR(255),
    ativado TINYINT(1) DEFAULT 0
);
