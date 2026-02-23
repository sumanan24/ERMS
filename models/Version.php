<?php
require_once __DIR__ . '/../config/database.php';

class Version {
    private $conn;
    private $table = "versions";

    public $id;
    public $course_id;
    public $version_name;
    public $description;
    public $status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getDbConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (course_id, version_name, description, status) 
                  VALUES (:course_id, :version_name, :description, :status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":version_name", $this->version_name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function getAllVersions($filters = []) {
        $query = "SELECT v.id, v.course_id, v.version_name, v.description, v.status, v.created_at, c.cname as course_name
                  FROM " . $this->table . " v
                  LEFT JOIN courses c ON v.course_id = c.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['course_id'])) {
            $query .= " AND v.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND v.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (v.version_name LIKE :search OR v.description LIKE :search OR c.cname LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $query .= " ORDER BY v.created_at DESC";

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVersionById($id) {
        $query = "SELECT id, course_id, version_name, description, status, created_at 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->course_id = $row['course_id'];
            $this->version_name = $row['version_name'];
            $this->description = $row['description'];
            $this->status = $row['status'];
            return true;
        }
        
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET course_id = :course_id,
                      version_name = :version_name, 
                      description = :description,
                      status = :status 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":version_name", $this->version_name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function versionNameExists($version_name, $course_id, $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE version_name = :version_name AND course_id = :course_id";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":version_name", $version_name);
        $stmt->bindParam(":course_id", $course_id);
        
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function hasModules($version_id) {
        $query = "SELECT COUNT(*) FROM module WHERE version_id = :version_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":version_id", $version_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>
