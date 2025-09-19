<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->createTables();
        } catch(PDOException $exception) {
            try {
                $temp_conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
                $temp_conn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name);
                $temp_conn = null;
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->exec("set names utf8");
                $this->createTables();
            } catch(PDOException $e) {
                die("Erreur lors de la création de la base de données: " . $e->getMessage());
            }
        }
        return $this->conn;
    }
    
    private function createTables() {
        // Users table with est_actif
        $query = "CREATE TABLE IF NOT EXISTS users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            photo VARCHAR(255),
            qr_data VARCHAR(255),
            role ENUM('admin', 'user') DEFAULT 'user',
            est_actif TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($query);
        
        // Presences table with entry/exit times and status
        $query = "CREATE TABLE IF NOT EXISTS presences (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            date DATE NOT NULL,
            entry_time TIME,
            exit_time TIME,
            status ENUM('present', 'late', 'absent') DEFAULT 'absent',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $this->conn->exec($query);
        
        // Absences table
        $query = "CREATE TABLE IF NOT EXISTS absences (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            date DATE NOT NULL,
            reason VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $this->conn->exec($query);
        
        $this->createDefaultAdmin();
    }
    
    private function createDefaultAdmin() {
        $checkAdmin = $this->conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $result = $checkAdmin->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] == 0) {
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $query = "INSERT INTO users (nom, prenom, email, password, role, est_actif) VALUES 
                     ('Admin', 'System', 'admin@example.com', '$password', 'admin', 1)";
            $this->conn->exec($query);
        }
    }
}
?>