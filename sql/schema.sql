CREATE DATABASE IF NOT EXISTS flight_db;
USE flight_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','user') DEFAULT 'user'
);

CREATE TABLE flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_number VARCHAR(10),
    airline VARCHAR(100),
    departure_airport VARCHAR(100),
    arrival_airport VARCHAR(100),
    status VARCHAR(50),
    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
