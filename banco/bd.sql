CREATE DATABASE IF NOT EXISTS mypocket;
USE mypocket;

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS carteira (
    id_carteira INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome_carteira VARCHAR(150) NOT NULL,
    tipo_carteira VARCHAR(50) NULL,
    saldo_atual DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
);

CREATE TABLE IF NOT EXISTS categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(100) NOT NULL,
    tipo_categoria ENUM('entrada', 'saida') NOT NULL
);

CREATE TABLE IF NOT EXISTS transacao (
    id_transacao INT AUTO_INCREMENT PRIMARY KEY,
    id_carteira INT NOT NULL,
    id_categoria INT NOT NULL,
    data_transacao DATE NOT NULL,
    descricao VARCHAR(255) NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo_transacao ENUM('receita', 'despesa') NOT NULL,
    FOREIGN KEY (id_carteira) REFERENCES carteira (id_carteira),
    FOREIGN KEY (id_categoria) REFERENCES categoria (id_categoria),
    CHECK (valor > 0)
);

CREATE TABLE IF NOT EXISTS receita (
    id_transacao INT PRIMARY KEY,
    origem VARCHAR(150) NULL,
    FOREIGN KEY (id_transacao) REFERENCES transacao (id_transacao)
);

CREATE TABLE IF NOT EXISTS despesa (
    id_transacao INT PRIMARY KEY,
    forma_pagamento VARCHAR(100) NULL,
    FOREIGN KEY (id_transacao) REFERENCES transacao (id_transacao)
);

CREATE TABLE IF NOT EXISTS fechamentomensal (
    id_fechamento INT AUTO_INCREMENT PRIMARY KEY,
    id_carteira INT NOT NULL,
    mes_ano DATE NOT NULL,
    total_entradas DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_saidas DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_diario DECIMAL(10,2) NOT NULL DEFAULT 0,
    performance DECIMAL(10,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uk_carteira_mes (id_carteira, mes_ano),
    FOREIGN KEY (id_carteira) REFERENCES carteira (id_carteira)
);
