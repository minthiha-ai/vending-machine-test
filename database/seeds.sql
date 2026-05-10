-- Seed data for Vending Machine
-- Run after schema.sql: mysql -u root -p vending_machine < database/seeds.sql

USE vending_machine;

-- Default admin (password: admin123)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$12$DWQE.BbdLqH3mUDd0343S.GIUyuhcv9qAKRlzNhfN3HgFqEiDzrqS', 'admin')
ON DUPLICATE KEY UPDATE password='$2y$12$DWQE.BbdLqH3mUDd0343S.GIUyuhcv9qAKRlzNhfN3HgFqEiDzrqS', role='admin';

-- Default user (password: user123)
INSERT INTO users (username, password, role) VALUES
('user', '$2y$12$vhi2yC9YUmo/gHPMpSIajeMeVvOCYDuiDYXJScn/lXja6mj4yOqAW', 'user')
ON DUPLICATE KEY UPDATE password='$2y$12$vhi2yC9YUmo/gHPMpSIajeMeVvOCYDuiDYXJScn/lXja6mj4yOqAW', role='user';

-- Products
INSERT INTO products (name, price, quantity_available) VALUES
('Coke',  3.990, 50),
('Pepsi', 6.885, 30),
('Water', 0.500, 100)
ON DUPLICATE KEY UPDATE price=VALUES(price);
