<?php
require_once __DIR__ . '/database.php';

class Install {
    private $db;
    private $dbName;

    /** Last error message for display on install page. */
    public $lastError = '';

    public function __construct() {
        $database = new Database();
        $this->dbName = $database->getDbName();
        $this->db = $database->getConnection();
        if ($this->db === null) {
            throw new Exception("Failed to connect to MySQL server. Check config/database.php (host, username, password) and ensure MySQL is running.");
        }
    }

    public function createDatabase() {
        try {
            $name = $this->dbName;
            $sql = "CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', $name) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $this->db->exec($sql);
            return true;
        } catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Install createDatabase: " . $e->getMessage());
            return false;
        }
    }

    /** Use existing database connection (when CREATE DATABASE not allowed, e.g. shared hosting). */
    public function useExistingDatabase() {
        $database = new Database();
        $this->db = $database->getDbConnection();
        if ($this->db === null) {
            $this->lastError = 'Could not connect to database "' . $this->dbName . '". Create it in cPanel/phpMyAdmin and ensure the user has access.';
            return false;
        }
        return true;
    }

    public function createTables() {
        try {
            $this->db->exec("USE `" . str_replace('`', '``', $this->dbName) . "`");

            // Users table
            $sql_users = "CREATE TABLE IF NOT EXISTS users (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                role ENUM('admin', 'teacher', 'student') DEFAULT 'student',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            // Courses table
            $sql_courses = "CREATE TABLE IF NOT EXISTS courses (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                cname VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            // Versions table
            $sql_versions = "CREATE TABLE IF NOT EXISTS versions (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                course_id INT(11) NOT NULL,
                version_name VARCHAR(100) NOT NULL,
                description TEXT,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
                UNIQUE KEY unique_version_course (version_name, course_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            // Modules table
            $sql_modules = "CREATE TABLE IF NOT EXISTS module (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                mcode VARCHAR(50) NOT NULL,
                mname VARCHAR(200) NOT NULL,
                cid INT(11) NOT NULL,
                version_id INT(11) NOT NULL,
                semester VARCHAR(20) NOT NULL,
                credit DECIMAL(3,1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (cid) REFERENCES courses(id) ON DELETE CASCADE,
                FOREIGN KEY (version_id) REFERENCES versions(id) ON DELETE CASCADE,
                UNIQUE KEY unique_module_code_version (mcode, version_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $this->db->exec($sql_users);
            $this->db->exec($sql_courses);
            $this->db->exec($sql_versions);
            
            // Batches table
            $sql_batches = "CREATE TABLE IF NOT EXISTS batch (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                batch_no VARCHAR(50) NOT NULL,
                start_date DATE,
                end_date DATE,
                cid INT(11) NOT NULL,
                version_id INT(11),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (cid) REFERENCES courses(id) ON DELETE CASCADE,
                FOREIGN KEY (version_id) REFERENCES versions(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $this->db->exec($sql_batches);

            // Student enrollments table
            $sql_student = "CREATE TABLE IF NOT EXISTS student (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                reg_no VARCHAR(50) NOT NULL,
                fullname VARCHAR(150) NOT NULL,
                nic VARCHAR(20),
                cid INT(11) NOT NULL,
                bid INT(11) NOT NULL,
                version_id INT(11),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_regno (reg_no),
                FOREIGN KEY (cid) REFERENCES courses(id) ON DELETE CASCADE,
                FOREIGN KEY (bid) REFERENCES batch(id) ON DELETE CASCADE,
                FOREIGN KEY (version_id) REFERENCES versions(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $this->db->exec($sql_student);
            $this->db->exec($sql_modules);

            // Exams table
            $sql_exams = "CREATE TABLE IF NOT EXISTS exams (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                exam_date DATE NOT NULL,
                time_slot VARCHAR(50) NOT NULL,
                course_id INT(11) NOT NULL,
                module_id INT(11) NOT NULL,
                location VARCHAR(100) NOT NULL,
                assessment_percentage DECIMAL(5,2) DEFAULT 0,
                final_exam_percentage DECIMAL(5,2) DEFAULT 0,
                resulted_status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
                FOREIGN KEY (module_id) REFERENCES module(id) ON DELETE CASCADE,
                INDEX idx_exam_date (exam_date),
                INDEX idx_course (course_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $this->db->exec($sql_exams);

            // Exam Results table
            $sql_exam_results = "CREATE TABLE IF NOT EXISTS exam_results (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                exam_id INT(11) NOT NULL,
                student_id INT(11) NOT NULL,
                eligibility ENUM('eligible', 'not_eligible') DEFAULT 'eligible',
                student_offense VARCHAR(255) DEFAULT NULL,
                attempt INT(1) DEFAULT 1,
                assessment_marks DECIMAL(5,2) DEFAULT 0,
                final_exam_marks DECIMAL(5,2) DEFAULT 0,
                final_marks DECIMAL(5,2) DEFAULT 0,
                status ENUM('pass', 'fail', 'absent') DEFAULT 'absent',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
                FOREIGN KEY (student_id) REFERENCES student(id) ON DELETE CASCADE,
                UNIQUE KEY unique_exam_student_attempt (exam_id, student_id, attempt),
                INDEX idx_exam (exam_id),
                INDEX idx_student (student_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $this->db->exec($sql_exam_results);

            return true;
        } catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error creating tables: " . $e->getMessage());
            return false;
        }
    }

    public function createDefaultAdmin() {
        try {
            $this->db->exec("USE `" . str_replace('`', '``', $this->dbName) . "`");

            // Check if admin already exists
            $check = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return true; // Admin already exists
            }

            // Create default admin
            $username = "admin";
            $email = "admin@exam.com";
            $password = password_hash("admin123", PASSWORD_DEFAULT);
            $full_name = "System Administrator";

            $stmt = $this->db->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
            $stmt->execute([$username, $email, $password, $full_name]);

            return true;
        } catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error creating default admin: " . $e->getMessage());
            return false;
        }
    }

    public function install() {
        if (!$this->createDatabase()) {
            // e.g. shared hosting: user cannot CREATE DATABASE; use existing DB
            if (!$this->useExistingDatabase()) {
                return false;
            }
        }

        if (!$this->createTables()) {
            return false;
        }

        if (!$this->createDefaultAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Import database from exam_management.sql (or any .sql file).
     * Creates database if needed, then runs all statements in the file.
     */
    public function importSqlFile($filePath) {
        $this->lastError = '';
        if (!is_readable($filePath)) {
            $this->lastError = "SQL file not found or not readable: " . basename($filePath);
            return false;
        }
        if (!$this->createDatabase()) {
            if (!$this->useExistingDatabase()) {
                return false;
            }
        }
        try {
            $this->db->exec("USE `" . str_replace('`', '``', $this->dbName) . "`");
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        $sql = file_get_contents($filePath);
        if ($sql === false) {
            $this->lastError = "Could not read SQL file.";
            return false;
        }
        // Remove SQL comments (-- and /* */)
        $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        // Split into statements by semicolon followed by newline
        $statements = array_filter(
            array_map('trim', preg_split('/;\s*[\r\n]+/', $sql)),
            function ($s) {
                $s = trim($s);
                if ($s === '') return false;
                if (preg_match('/^(START\s+TRANSACTION|COMMIT|BEGIN)\s*$/i', $s)) return false;
                return true;
            }
        );
        foreach ($statements as $stmt) {
            if (strlen($stmt) < 3) continue;
            try {
                $this->db->exec($stmt);
            } catch (PDOException $e) {
                $this->lastError = $e->getMessage() . " (in statement: " . substr($stmt, 0, 80) . "...)";
                error_log("Import SQL error: " . $e->getMessage());
                return false;
            }
        }
        return true;
    }
}
?>

