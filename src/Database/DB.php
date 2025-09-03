<?php
namespace App\Database;

use PDO;
use PDOException;

class DB {
    private $host = "localhost";
    private $dbname = "flight_db";
    private $username = "root";
    private $password = ""; //YOUR PASSWORD

    public function getConnection() {
        try {
            $pdo = new PDO("mysql:host={$this->host}", $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS {$this->dbname} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE {$this->dbname}");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS flights (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    flight_number VARCHAR(10),
                    airline VARCHAR(100),
                    departure_airport VARCHAR(100),
                    arrival_airport VARCHAR(100),
                    status VARCHAR(50),
                    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");

            return $pdo;
        } catch (PDOException $e) {
            die("DB connection failed: " . $e->getMessage());
        }
    }
}
