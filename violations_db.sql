CREATE DATABASE IF NOT EXISTS violations_db;
USE violations_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    login VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

ALTER TABLE users 
    ADD UNIQUE INDEX login_unique (login),
    ADD UNIQUE INDEX phone_unique (phone),
    ADD UNIQUE INDEX email_unique (email);

CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    car_number VARCHAR(20) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('new', 'confirmed', 'rejected') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);