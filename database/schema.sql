-- Vending Machine Database Schema
-- Run: mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS vending_machine CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vending_machine;

CREATE TABLE IF NOT EXISTS users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role     ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(100) NOT NULL,
    price              DECIMAL(10,3) NOT NULL,
    quantity_available INT NOT NULL DEFAULT 0,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    product_id  INT NOT NULL,
    user_id     INT NOT NULL,
    total_price DECIMAL(10,3) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tx_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_tx_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE RESTRICT
) ENGINE=InnoDB;
