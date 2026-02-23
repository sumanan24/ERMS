<?php
require_once __DIR__ . '/../config/database.php';

class Batch {
    private $conn;
    private $table = "batch";

    public $id;
    public $batch_no;
    public $start_date;
    public $end_date;
    public $cid;
    public $version_id;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getDbConnection();
    }

    public function create() {
        $query = "INSERT INTO {$this->table} (batch_no, start_date, end_date, cid, version_id) VALUES (:batch_no, :start_date, :end_date, :cid, :version_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':batch_no', $this->batch_no);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':cid', $this->cid);
        $stmt->bindParam(':version_id', $this->version_id);
        return $stmt->execute();
    }

    public function getAll($filters = []) {
        $query = "SELECT b.id, b.batch_no, b.start_date, b.end_date, b.cid, b.version_id, b.created_at, 
                         c.cname as course_name, v.version_name
                  FROM {$this->table} b
                  LEFT JOIN courses c ON b.cid = c.id
                  LEFT JOIN versions v ON b.version_id = v.id
                  WHERE 1=1";
        $params = [];
        if (!empty($filters['course_id'])) {
            $query .= " AND b.cid = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }
        if (!empty($filters['version_id'])) {
            $query .= " AND b.version_id = :version_id";
            $params[':version_id'] = $filters['version_id'];
        }
        if (!empty($filters['search'])) {
            $query .= " AND (b.batch_no LIKE :search OR c.cname LIKE :search)";
            $params[':search'] = '%'.$filters['search'].'%';
        }
        if (!empty($filters['start_from'])) {
            $query .= " AND b.start_date >= :start_from";
            $params[':start_from'] = $filters['start_from'];
        }
        if (!empty($filters['start_to'])) {
            $query .= " AND b.start_date <= :start_to";
            $params[':start_to'] = $filters['start_to'];
        }
        $query .= " ORDER BY b.start_date DESC";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT id, batch_no, start_date, end_date, cid, version_id FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id',$id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->batch_no = $row['batch_no'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->cid = $row['cid'];
            $this->version_id = $row['version_id'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE {$this->table} SET batch_no=:batch_no, start_date=:start_date, end_date=:end_date, cid=:cid, version_id=:version_id WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id',$this->id);
        $stmt->bindParam(':batch_no',$this->batch_no);
        $stmt->bindParam(':start_date',$this->start_date);
        $stmt->bindParam(':end_date',$this->end_date);
        $stmt->bindParam(':cid',$this->cid);
        $stmt->bindParam(':version_id',$this->version_id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $stmt->bindParam(':id',$id);
        return $stmt->execute();
    }

    public function batchNoExists($batch_no, $cid, $exclude_id=null) {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE batch_no=:batch_no AND cid=:cid";
        if ($exclude_id) $query .= " AND id != :exclude";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':batch_no',$batch_no);
        $stmt->bindParam(':cid',$cid);
        if ($exclude_id) $stmt->bindParam(':exclude',$exclude_id);
        $stmt->execute();
        return $stmt->fetchColumn()>0;
    }
}
?>
