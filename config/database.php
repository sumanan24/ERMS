<?php
/**
 * Database configuration.
 * On the server (e.g. exam.ucj.ac.lk), set environment variables or create
 * config/database.local.php (git-ignored) with $db_host, $db_name, $db_user, $db_pass
 * to override these defaults.
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        if (file_exists(__DIR__ . '/database.local.php')) {
            require __DIR__ . '/database.local.php';
        }
        $this->host     = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST')     ?: ($db_host     ?? 'localhost'));
        $this->db_name  = defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME')     ?: ($db_name     ?? 'exam_management'));
        $this->username = defined('DB_USER') ? DB_USER : (getenv('DB_USER')     ?: ($db_user     ?? 'root'));
        $this->password = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASSWORD') ?: ($db_pass     ?? '1234'));
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
        
        return $this->conn;
    }

    public function getDbConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
        
        return $this->conn;
    }
}
?>

