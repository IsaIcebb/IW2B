CREATE TABLE pets (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    nome_dono VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    rg VARCHAR(20) NOT NULL,
    nome_pet VARCHAR(100) NOT NULL,
    idade_pet INT(3) NOT NULL,
    raca_pet VARCHAR(100) NOT NULL
);
