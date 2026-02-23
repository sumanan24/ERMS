<?php
require_once __DIR__ . '/../config/database.php';

class Exam {
    private $conn;
    private $table = "exams";

    public $id;
    public $exam_date;
    public $time_slot;
    public $course_id;
    public $module_id;
    public $location;
    public $assessment_percentage;
    public $final_exam_percentage;
    public $resulted_status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getDbConnection();
    }

    public function create() {
        $status = $this->resulted_status ?? 'pending';
        $query = "INSERT INTO {$this->table} (exam_date, time_slot, course_id, module_id, location, assessment_percentage, final_exam_percentage, resulted_status) 
                  VALUES (:exam_date, :time_slot, :course_id, :module_id, :location, :assessment_percentage, :final_exam_percentage, :resulted_status)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':exam_date', $this->exam_date);
        $stmt->bindParam(':time_slot', $this->time_slot);
        $stmt->bindParam(':course_id', $this->course_id);
        $stmt->bindParam(':module_id', $this->module_id);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':assessment_percentage', $this->assessment_percentage);
        $stmt->bindParam(':final_exam_percentage', $this->final_exam_percentage);
        $stmt->bindParam(':resulted_status', $status);
        return $stmt->execute();
    }

    public function getAll($filters = []) {
        $query = "SELECT e.id, e.exam_date, e.time_slot, e.course_id, e.module_id, e.location,
                         e.resulted_status,
                         c.cname as course_name, m.mcode as module_code, m.mname as module_name
                  FROM {$this->table} e
                  LEFT JOIN courses c ON e.course_id = c.id
                  LEFT JOIN module m ON e.module_id = m.id
                  WHERE 1=1";
        $params = [];
        if (!empty($filters['approved_only'])) {
            $query .= " AND (e.resulted_status = 'approved' OR e.resulted_status IS NULL)";
        }
        if (!empty($filters['date_from'])) {
            $query .= " AND e.exam_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND e.exam_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        if (!empty($filters['course_id'])) {
            $query .= " AND e.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }
        if (!empty($filters['search'])) {
            $query .= " AND (c.cname LIKE :search OR m.mcode LIKE :search OR m.mname LIKE :search OR e.location LIKE :search)";
            $params[':search'] = '%'.$filters['search'].'%';
        }
        $query .= " ORDER BY e.exam_date ASC, e.time_slot ASC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT id, exam_date, time_slot, course_id, module_id, location, assessment_percentage, final_exam_percentage, resulted_status 
                  FROM {$this->table} 
                  WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->exam_date = $row['exam_date'];
            $this->time_slot = $row['time_slot'];
            $this->course_id = $row['course_id'];
            $this->module_id = $row['module_id'];
            $this->location = $row['location'];
            $this->assessment_percentage = $row['assessment_percentage'] ?? 0;
            $this->final_exam_percentage = $row['final_exam_percentage'] ?? 0;
            $this->resulted_status = $row['resulted_status'] ?? 'pending';
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE {$this->table} 
                  SET exam_date=:exam_date, time_slot=:time_slot, course_id=:course_id, 
                      module_id=:module_id, location=:location, assessment_percentage=:assessment_percentage, 
                      final_exam_percentage=:final_exam_percentage, resulted_status=:resulted_status 
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':exam_date', $this->exam_date);
        $stmt->bindParam(':time_slot', $this->time_slot);
        $stmt->bindParam(':course_id', $this->course_id);
        $stmt->bindParam(':module_id', $this->module_id);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':assessment_percentage', $this->assessment_percentage);
        $stmt->bindParam(':final_exam_percentage', $this->final_exam_percentage);
        $status = $this->resulted_status ?? 'pending';
        $stmt->bindParam(':resulted_status', $status);
        return $stmt->execute();
    }

    /** Ensure resulted_status column exists (one-time migration for existing DBs). */
    public function ensureResultedStatusColumn() {
        try {
            $stmt = $this->conn->query("SELECT resulted_status FROM {$this->table} LIMIT 1");
            if ($stmt) $stmt->fetch(PDO::FETCH_ASSOC);
            return true;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'resulted_status') !== false) {
                $this->conn->exec("ALTER TABLE {$this->table} ADD COLUMN resulted_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
            }
            return true;
        }
    }

    /** Update only resulted_status (pending / approved). */
    public function updateResultedStatus($id, $status) {
        $status = in_array($status, ['pending', 'approved']) ? $status : 'pending';
        $query = "UPDATE {$this->table} SET resulted_status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getExamsForSchedule($date_from, $date_to) {
        // Safety: if dates are empty/null, use very wide range
        if (empty($date_from)) {
            $date_from = '0001-01-01';
        }
        if (empty($date_to)) {
            $date_to = '9999-12-31';
        }

        $query = "SELECT e.exam_date, e.time_slot, e.course_id, e.module_id, e.location,
                         c.cname as course_name, m.mcode as module_code, m.mname as module_name
                  FROM {$this->table} e
                  LEFT JOIN courses c ON e.course_id = c.id
                  LEFT JOIN module m ON e.module_id = m.id
                  WHERE e.exam_date >= :date_from AND e.exam_date <= :date_to
                  ORDER BY e.exam_date ASC, e.time_slot ASC, c.cname ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date_from', $date_from);
        $stmt->bindParam(':date_to', $date_to);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get exams for a given course, version and semester (for marks summary by batch).
     */
    public function getExamsByCourseVersionSemester($course_id, $version_id, $semester) {
        $query = "SELECT e.id as exam_id, e.exam_date, e.assessment_percentage, e.final_exam_percentage,
                         m.id as module_id, m.mcode as module_code, m.mname as module_name, m.semester, m.credit
                  FROM {$this->table} e
                  INNER JOIN module m ON e.module_id = m.id
                  WHERE e.course_id = :course_id AND m.version_id = :version_id AND (:semester = '' OR m.semester = :semester2)
                  ORDER BY e.exam_date ASC, m.mcode ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->bindParam(':version_id', $version_id, PDO::PARAM_INT);
        $stmt->bindValue(':semester', $semester);
        $stmt->bindValue(':semester2', $semester);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get existing exams for a given course and version, keyed by module_id.
     */
    public function getByCourseAndVersion($course_id, $version_id) {
        $query = "SELECT e.id, e.exam_date, e.time_slot, e.course_id, e.module_id, e.location, 
                         e.assessment_percentage, e.final_exam_percentage,
                         m.mcode as module_code, m.mname as module_name, m.version_id
                  FROM {$this->table} e
                  INNER JOIN module m ON e.module_id = m.id
                  WHERE e.course_id = :course_id AND m.version_id = :version_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->bindParam(':version_id', $version_id, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $byModule = [];
        foreach ($rows as $row) {
            $byModule[$row['module_id']] = $row;
        }
        return $byModule;
    }

    /**
     * Create or update an exam entry for a specific course + module.
     */
    public function saveForModule($course_id, $module_id, $exam_date, $time_slot, $location, $assessment_percentage = 0, $final_exam_percentage = 0) {
        // First check if an exam already exists for this course + module
        $query = "SELECT id FROM {$this->table} WHERE course_id = :course_id AND module_id = :module_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->bindParam(':module_id', $module_id, PDO::PARAM_INT);
        $stmt->execute();
        $existingId = $stmt->fetchColumn();

        if ($existingId) {
            // Update
            $update = "UPDATE {$this->table}
                       SET exam_date = :exam_date,
                           time_slot = :time_slot,
                           location  = :location,
                           assessment_percentage = :assessment_percentage,
                           final_exam_percentage = :final_exam_percentage
                       WHERE id = :id";
            $stmt = $this->conn->prepare($update);
            $stmt->bindParam(':id', $existingId, PDO::PARAM_INT);
            $stmt->bindParam(':exam_date', $exam_date);
            $stmt->bindParam(':time_slot', $time_slot);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':assessment_percentage', $assessment_percentage);
            $stmt->bindParam(':final_exam_percentage', $final_exam_percentage);
            if ($stmt->execute()) {
                return $existingId;
            }
            return false;
        } else {
            // Insert
            $insert = "INSERT INTO {$this->table} (exam_date, time_slot, course_id, module_id, location, assessment_percentage, final_exam_percentage)
                       VALUES (:exam_date, :time_slot, :course_id, :module_id, :location, :assessment_percentage, :final_exam_percentage)";
            $stmt = $this->conn->prepare($insert);
            $stmt->bindParam(':exam_date', $exam_date);
            $stmt->bindParam(':time_slot', $time_slot);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->bindParam(':module_id', $module_id, PDO::PARAM_INT);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':assessment_percentage', $assessment_percentage);
            $stmt->bindParam(':final_exam_percentage', $final_exam_percentage);
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        }
    }
}
?>
