CREATE DATABASE IF NOT EXISTS industria_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE industria_db;

-- =========================================
-- TABELA DE USUÁRIOS
-- =========================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(100) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1
);

-- =========================================
-- TABELA DE PRODUTOS / INSUMOS
-- =========================================

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    fabricante VARCHAR(150),
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estoque_atual INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 5,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    data_validade DATE NULL,
    peso DECIMAL(10,2) DEFAULT 0.00,

    -- Campos mantidos para compatibilidade
    especie INT NULL,
    porte_animal INT NULL,
    faixa_etaria INT NULL,
    sabor VARCHAR(100) NULL,

    categoria VARCHAR(100) NULL,
    tamanho VARCHAR(100) NULL,
    principios_ativos VARCHAR(255) NULL,
    material_fabricacao VARCHAR(255) NULL,
    indicacao_terapeutica VARCHAR(255) NULL,
    instrucoes_uso TEXT NULL
);

-- =========================================
-- TABELA DE MOVIMENTAÇÕES
-- =========================================

CREATE TABLE IF NOT EXISTS movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- 1 = Entrada | 2 = Saída
    tipo TINYINT NOT NULL,

    data DATE NOT NULL,
    quantidade INT NOT NULL,
    saldo_anterior INT NOT NULL,

    usuarios_id INT NOT NULL,
    produtos_id INT NOT NULL,

    CONSTRAINT fk_mov_usuario
        FOREIGN KEY (usuarios_id)
        REFERENCES usuarios(id),

    CONSTRAINT fk_mov_produto
        FOREIGN KEY (produtos_id)
        REFERENCES produtos(id)
);

-- =========================================
-- USUÁRIOS DE TESTE
-- =========================================

INSERT INTO usuarios
(nome, email, senha, ativo)
VALUES
('Administrador', 'admin@industria.com', '123456', 1),
('Almoxarife', 'almoxarife@industria.com', '123456', 1),
('Gerente de Produção', 'gerente@industria.com', '123456', 1);

-- =========================================
-- PRODUTOS / INSUMOS DE TESTE
-- =========================================

INSERT INTO produtos
(codigo, nome, fabricante, preco, estoque_atual, estoque_minimo, ativo, categoria, tamanho, material_fabricacao)
VALUES
('CHA001', 'Chapa de Aço Carbono', 'Gerdau', 350.00, 25, 10, 1, 'Chapas de Aço', '2m x 1m', 'Aço Carbono'),

('PAR001', 'Parafuso Sextavado M10', 'Ciser', 2.50, 120, 30, 1, 'Parafusos', 'M10', 'Aço'),

('TIN001', 'Tinta Industrial Azul', 'Coral', 185.90, 8, 10, 1, 'Tintas', '18L', 'Tinta Industrial');

-- =========================================
-- MOVIMENTAÇÕES DE TESTE
-- =========================================

INSERT INTO movimentacoes
(tipo, data, quantidade, saldo_anterior, usuarios_id, produtos_id)
VALUES
(1, '2026-09-28', 25, 0, 1, 1),

(1, '2026-09-29', 120, 0, 2, 2),

(1, '2026-09-30', 8, 0, 3, 3);

-- =========================================
-- CONFERÊNCIA
-- =========================================

SELECT * FROM usuarios;

SELECT * FROM produtos;

SELECT * FROM movimentacoes;