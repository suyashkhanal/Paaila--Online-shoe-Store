CREATE DATABASE paaila;
USE paaila;


CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
email VARCHAR(100) UNIQUE,
password VARCHAR(255)
);


CREATE TABLE products (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
price DECIMAL(10,2),
image VARCHAR(255)
);


CREATE TABLE cart (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT,
product_id INT,
qty INT DEFAULT 1
);


INSERT INTO products (name, price, image) VALUES
('Running Shoes', 2500, 'shoes1.jpg'),
('Casual Sneakers', 3000, 'shoes2.jpg'),
('Formal Leather Shoes', 4500, 'shoes3.jpg');