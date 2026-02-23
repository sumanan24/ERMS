<?php
require_once __DIR__ . '/../config/database.php';

class Course {
    private $conn;
    private $table = "courses";

    public $id;
    public $cname;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getDbConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (cname) VALUES (:cname)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cname", $this->cname);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function getAllCourses($filters = []) {
        $query = "SELECT id, cname, created_at 
                  FROM " . $this->table . " 
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $query .= " AND cname LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $query .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseById($id) {
        $query = "SELECT id, cname, created_at 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->cname = $row['cname'];
            return true;
        }
        
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET cname = :cname 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":cname", $this->cname);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function cnameExists($cname, $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE cname = :cname";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cname", $cname);
        
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function hasModules($course_id) {
        $query = "SELECT COUNT(*) FROM module WHERE cid = :course_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function hasVersions($course_id) {
        $query = "SELECT COUNT(*) FROM versions WHERE course_id = :course_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>
