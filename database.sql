CREATE DATABASE IF NOT EXISTS escaperoom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE escaperoom;

CREATE TABLE jogadores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE partidas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jogador_id INT UNSIGNED NOT NULL,
    dificuldade ENUM('facil', 'medio', 'dificil') NOT NULL,
    sala VARCHAR(20) NOT NULL,
    limite_segundos INT UNSIGNED NOT NULL,
    inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fim DATETIME NULL,
    tempo_segundos INT UNSIGNED NULL,
    erros INT UNSIGNED NOT NULL DEFAULT 0,
    dicas_usadas INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('em_andamento', 'concluida', 'tempo_esgotado', 'abandonada')
        NOT NULL DEFAULT 'em_andamento',

    CONSTRAINT fk_partida_jogador
        FOREIGN KEY (jogador_id) REFERENCES jogadores(id)
        ON DELETE CASCADE,

    INDEX idx_ranking (sala, status, tempo_segundos)
) ENGINE=InnoDB;