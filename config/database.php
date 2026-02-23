<?php
/**
 * Database configuration - edit this file to change connection settings.
 */
class Database {
    private $host     = 'localhost';
    private $db_name  = 'exam_management';
    private $username = 'root';
    private $password = '1234';
    private $conn;

    /** Last connection error message (for optional ?debug=1 display). */
    public static $lastError;

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
            self::$lastError = $e->getMessage();
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
        return $this->conn;
    }

    public function getDbConnection() {
        $this->conn = null;
        self::$lastError = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            self::$lastError = $e->getMessage();
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
        return $this->conn;
    }
}
