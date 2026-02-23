<?php
require_once __DIR__ . '/../config/database.php';

class Student {
    private $conn;
    private $table = "student";

    public $id;
    public $reg_no;
    public $fullname;
    public $nic;
    public $cid;
    public $bid;
    public $version_id;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getDbConnection();
    }

    public function create() {
        $query = "INSERT INTO {$this->table} (reg_no, fullname, nic, cid, bid, version_id)
                  VALUES (:reg_no, :fullname, :nic, :cid, :bid, :version_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':reg_no', $this->reg_no);
        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':nic', $this->nic);
        $stmt->bindParam(':cid', $this->cid);
        $stmt->bindParam(':bid', $this->bid);
        $stmt->bindParam(':version_id', $this->version_id);
        return $stmt->execute();
    }

    public function getAll($filters = []) {
        $query = "SELECT s.id, s.reg_no, s.fullname, s.nic, s.cid, s.bid, s.version_id, s.created_at,
                         c.cname as course_name, b.batch_no, v.version_name
                  FROM {$this->table} s
                  LEFT JOIN courses c ON s.cid = c.id
                  LEFT JOIN batch b ON s.bid = b.id
                  LEFT JOIN versions v ON s.version_id = v.id
                  WHERE 1=1";
        $params = [];
        if (!empty($filters['course_id'])) {
            $query .= " AND s.cid = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }
        if (!empty($filters['version_id'])) {
            $query .= " AND s.version_id = :version_id";
            $params[':version_id'] = $filters['version_id'];
        }
        if (!empty($filters['batch_id'])) {
            $query .= " AND s.bid = :batch_id";
            $params[':batch_id'] = $filters['batch_id'];
        }
        if (!empty($filters['search'])) {
            $query .= " AND (s.reg_no LIKE :search OR s.fullname LIKE :search OR s.nic LIKE :search OR c.cname LIKE :search OR b.batch_no LIKE :search)";
            $params[':search'] = '%'.$filters['search'].'%';
        }
        $query .= " ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT id, reg_no, fullname, nic, cid, bid, version_id FROM {$this->table} WHERE id=:id LIMIT 1");
        $stmt->bindParam(':id',$id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->reg_no = $row['reg_no'];
            $this->fullname = $row['fullname'];
            $this->nic = $row['nic'];
            $this->cid = $row['cid'];
            $this->bid = $row['bid'];
            $this->version_id = $row['version_id'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE {$this->table} SET reg_no=:reg_no, fullname=:fullname, nic=:nic, cid=:cid, bid=:bid, version_id=:version_id WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id',$this->id);
        $stmt->bindParam(':reg_no',$this->reg_no);
        $stmt->bindParam(':fullname',$this->fullname);
        $stmt->bindParam(':nic',$this->nic);
        $stmt->bindParam(':cid',$this->cid);
        $stmt->bindParam(':bid',$this->bid);
        $stmt->bindParam(':version_id',$this->version_id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $stmt->bindParam(':id',$id);
        return $stmt->execute();
    }

    public function regNoExists($reg_no, $exclude_id=null) {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE reg_no=:reg_no";
        if ($exclude_id) $query .= " AND id != :exclude";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':reg_no',$reg_no);
        if ($exclude_id) $stmt->bindParam(':exclude',$exclude_id);
        $stmt->execute();
        return $stmt->fetchColumn()>0;
    }

    public function findByNicOrRegNo($search) {
        $query = "SELECT id, reg_no, fullname, nic, cid, bid, version_id 
                  FROM {$this->table} 
                  WHERE reg_no = :search OR nic = :search 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':search', $search);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->reg_no = $row['reg_no'];
            $this->fullname = $row['fullname'];
            $this->nic = $row['nic'];
            $this->cid = $row['cid'];
            $this->bid = $row['bid'];
            $this->version_id = $row['version_id'];
            return true;
        }
        return false;
    }

    public function importFromArray($students, $cid, $version_id, $bid) {
        $success = 0; $errors = [];
        foreach ($students as $idx => $s) {
            $rowNo = $idx + 2; // considering header row
            $reg = trim($s['reg_no'] ?? '');
            $name = trim($s['fullname'] ?? '');
            $nic = trim($s['nic'] ?? '');
            if (empty($reg) || empty($name)) {
                $errors[] = "Row {$rowNo}: reg_no and fullname are required.";
                continue;
            }
            if ($this->regNoExists($reg)) {
                $errors[] = "Row {$rowNo}: reg_no '{$reg}' already exists.";
                continue;
            }
            $this->reg_no = $reg;
            $this->fullname = $name;
            $this->nic = $nic;
            $this->cid = $cid;
            $this->version_id = $version_id;
            $this->bid = $bid;
            if ($this->create()) {
                $success++;
            } else {
                $errors[] = "Row {$rowNo}: failed to import.";
            }
        }
        return ['success'=>$success,'errors'=>$errors];
    }
}
?>

