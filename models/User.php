<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $role;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getDbConnection();
    }

    public function login($username, $password) {
        $query = "SELECT id, username, email, password, full_name, role 
                  FROM " . $this->table . " 
                  WHERE username = :username OR email = :username 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->full_name = $row['full_name'];
                $this->role = $row['role'];
                return true;
            }
        }
        
        return false;
    }

    public function getUserById($id) {
        $query = "SELECT id, username, email, full_name, role 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            return true;
        }
        
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (username, email, password, full_name, role) 
                  VALUES (:username, :email, :password, :full_name, :role)";

        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function getAllUsers() {
        $query = "SELECT id, username, email, full_name, role, created_at 
                  FROM " . $this->table . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET username = :username, 
                      email = :email, 
                      full_name = :full_name, 
                      role = :role";

        if (!empty($this->password)) {
            $query .= ", password = :password";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);

        if (!empty($this->password)) {
            $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $hashed_password);
        }

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function usernameExists($username, $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE username = :username";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function emailExists($email, $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE email = :email";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verify current password for a user (for profile password change).
     */
    public function verifyPassword($user_id, $password) {
        $query = "SELECT password FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return password_verify($password, $row['password']);
        }
        return false;
    }

    /**
     * Update only the password for a user (for profile update password).
     */
    public function updatePasswordOnly($user_id, $new_password) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $hash);
        $stmt->bindParam(":id", $user_id);
        return $stmt->execute();
    }
}
?>

