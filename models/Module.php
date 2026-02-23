<?php
require_once __DIR__ . '/../config/database.php';

class Module {
    private $conn;
    private $table = "module";

    public $id;
    public $mcode;
    public $mname;
    public $cid;
    public $version_id;
    public $semester;
    public $credit;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getDbConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (mcode, mname, cid, version_id, semester, credit) 
                  VALUES (:mcode, :mname, :cid, :version_id, :semester, :credit)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":mcode", $this->mcode);
        $stmt->bindParam(":mname", $this->mname);
        $stmt->bindParam(":cid", $this->cid);
        $stmt->bindParam(":version_id", $this->version_id);
        $stmt->bindParam(":semester", $this->semester);
        $stmt->bindParam(":credit", $this->credit);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function getAllModules($filters = []) {
        $query = "SELECT m.id, m.mcode, m.mname, m.cid, m.version_id, m.semester, m.credit, m.created_at,
                         c.cname as course_name, v.version_name
                  FROM " . $this->table . " m
                  LEFT JOIN courses c ON m.cid = c.id
                  LEFT JOIN versions v ON m.version_id = v.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['course_id'])) {
            $query .= " AND m.cid = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }
        
        if (!empty($filters['version_id'])) {
            $query .= " AND m.version_id = :version_id";
            $params[':version_id'] = $filters['version_id'];
        }
        
        if (!empty($filters['semester'])) {
            $query .= " AND m.semester = :semester";
            $params[':semester'] = $filters['semester'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (m.mcode LIKE :search OR m.mname LIKE :search OR c.cname LIKE :search OR v.version_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $query .= " ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get distinct semester values for a version (for schedule dropdown).
     */
    public function getSemestersByVersion($version_id) {
        $query = "SELECT DISTINCT m.semester FROM " . $this->table . " m
                  WHERE m.version_id = :version_id AND m.semester IS NOT NULL AND m.semester != ''
                  ORDER BY m.semester";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":version_id", $version_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getModuleById($id) {
        $query = "SELECT id, mcode, mname, cid, version_id, semester, credit, created_at 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->mcode = $row['mcode'];
            $this->mname = $row['mname'];
            $this->cid = $row['cid'];
            $this->version_id = $row['version_id'];
            $this->semester = $row['semester'];
            $this->credit = $row['credit'];
            return true;
        }
        
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET mcode = :mcode, 
                      mname = :mname,
                      cid = :cid,
                      version_id = :version_id,
                      semester = :semester,
                      credit = :credit 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":mcode", $this->mcode);
        $stmt->bindParam(":mname", $this->mname);
        $stmt->bindParam(":cid", $this->cid);
        $stmt->bindParam(":version_id", $this->version_id);
        $stmt->bindParam(":semester", $this->semester);
        $stmt->bindParam(":credit", $this->credit);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function mcodeExists($mcode, $version_id, $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE mcode = :mcode AND version_id = :version_id";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":mcode", $mcode);
        $stmt->bindParam(":version_id", $version_id);
        
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function importFromArray($modules, $cid, $version_id) {
        $success = 0;
        $errors = [];
        
        foreach ($modules as $index => $module) {
            try {
                $this->mcode = trim($module['mcode'] ?? '');
                $this->mname = trim($module['mname'] ?? '');
                $this->cid = $cid;
                $this->version_id = $version_id;
                $this->semester = !empty($module['semester']) ? trim($module['semester']) : null;
                $this->credit = !empty($module['credit']) ? (float)$module['credit'] : 0;
                
                if (empty($this->mcode) || empty($this->mname)) {
                    $errors[] = "Row " . ($index + 2) . ": Module code and name are required";
                    continue;
                }
                
                if ($this->mcodeExists($this->mcode, $this->version_id)) {
                    $errors[] = "Row " . ($index + 2) . ": Module code '{$this->mcode}' already exists for this version";
                    continue;
                }
                
                if ($this->create()) {
                    $success++;
                } else {
                    $errors[] = "Row " . ($index + 2) . ": Failed to import module '{$this->mcode}'";
                }
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
        
        return ['success' => $success, 'errors' => $errors];
    }
}
?>
