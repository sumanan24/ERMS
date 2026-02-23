<?php
require_once __DIR__ . '/../config/database.php';

class ExamResult {
    private $conn;
    private $table = "exam_results";

    public $id;
    public $exam_id;
    public $student_id;
    public $eligibility;
    public $student_offense;
    public $attempt;
    public $assessment_marks;
    public $final_exam_marks;
    public $final_marks;
    public $status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getDbConnection();
    }

    public function create() {
        $query = "INSERT INTO {$this->table} 
                  (exam_id, student_id, eligibility, student_offense, attempt, assessment_marks, final_exam_marks, 
                   final_marks, status) 
                  VALUES (:exam_id, :student_id, :eligibility, :student_offense, :attempt, :assessment_marks, :final_exam_marks,
                          :final_marks, :status)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':exam_id', $this->exam_id);
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->bindParam(':eligibility', $this->eligibility);
        $stmt->bindParam(':student_offense', $this->student_offense);
        $stmt->bindParam(':attempt', $this->attempt);
        $stmt->bindParam(':assessment_marks', $this->assessment_marks);
        $stmt->bindParam(':final_exam_marks', $this->final_exam_marks);
        $stmt->bindParam(':final_marks', $this->final_marks);
        $stmt->bindParam(':status', $this->status);
        return $stmt->execute();
    }

    public function getAll($filters = []) {
        $onePerStudent = !empty($filters['one_per_student']);
        unset($filters['one_per_student']);

        if ($onePerStudent) {
            return $this->getAllOnePerStudent($filters);
        }

        $query = "SELECT er.*, 
                         e.exam_date, e.time_slot, e.location, e.assessment_percentage, e.final_exam_percentage,
                         c.cname as course_name, m.mcode as module_code, m.mname as module_name,
                         s.reg_no, s.fullname as student_name
                  FROM {$this->table} er
                  LEFT JOIN exams e ON er.exam_id = e.id
                  LEFT JOIN courses c ON e.course_id = c.id
                  LEFT JOIN module m ON e.module_id = m.id
                  LEFT JOIN student s ON er.student_id = s.id
                  WHERE 1=1";
        $params = [];
        
        if (!empty($filters['exam_id'])) {
            $query .= " AND er.exam_id = :exam_id";
            $params[':exam_id'] = $filters['exam_id'];
        }
        if (!empty($filters['student_id'])) {
            $query .= " AND er.student_id = :student_id";
            $params[':student_id'] = $filters['student_id'];
        }
        if (!empty($filters['status'])) {
            $query .= " AND er.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $query .= " AND (s.reg_no LIKE :search OR s.fullname LIKE :search)";
            $params[':search'] = '%'.$filters['search'].'%';
        }
        if (!empty($filters['course_id'])) {
            $query .= " AND e.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }
        if (!empty($filters['approved_only'])) {
            $query .= " AND (e.resulted_status = 'approved' OR e.resulted_status IS NULL)";
        }
        $query .= " ORDER BY e.exam_date DESC, s.reg_no ASC";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get results with at most one row per (exam_id, student_id) - latest attempt only.
     */
    private function getAllOnePerStudent($filters = []) {
        $query = "SELECT er.*, 
                         e.exam_date, e.time_slot, e.location, e.assessment_percentage, e.final_exam_percentage,
                         c.cname as course_name, m.mcode as module_code, m.mname as module_name,
                         s.reg_no, s.fullname as student_name
                  FROM {$this->table} er
                  INNER JOIN (
                      SELECT exam_id, student_id, MAX(attempt) AS max_attempt
                      FROM {$this->table}
                      GROUP BY exam_id, student_id
                  ) latest ON er.exam_id = latest.exam_id AND er.student_id = latest.student_id AND er.attempt = latest.max_attempt
                  LEFT JOIN exams e ON er.exam_id = e.id
                  LEFT JOIN courses c ON e.course_id = c.id
                  LEFT JOIN module m ON e.module_id = m.id
                  LEFT JOIN student s ON er.student_id = s.id
                  WHERE 1=1";
        $params = [];
        
        if (!empty($filters['exam_id'])) {
            $query .= " AND er.exam_id = :exam_id";
            $params[':exam_id'] = $filters['exam_id'];
        }
        if (!empty($filters['student_id'])) {
            $query .= " AND er.student_id = :student_id";
            $params[':student_id'] = $filters['student_id'];
        }
        if (!empty($filters['status'])) {
            $query .= " AND er.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $query .= " AND (s.reg_no LIKE :search OR s.fullname LIKE :search)";
            $params[':search'] = '%'.$filters['search'].'%';
        }
        if (!empty($filters['course_id'])) {
            $query .= " AND e.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }
        if (!empty($filters['approved_only'])) {
            $query .= " AND (e.resulted_status = 'approved' OR e.resulted_status IS NULL)";
        }
        $query .= " ORDER BY e.exam_date DESC, s.reg_no ASC";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE {$this->table} 
                  SET eligibility=:eligibility, student_offense=:student_offense, attempt=:attempt, 
                      assessment_marks=:assessment_marks, final_exam_marks=:final_exam_marks,
                      final_marks=:final_marks, status=:status
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':eligibility', $this->eligibility);
        $stmt->bindParam(':student_offense', $this->student_offense);
        $stmt->bindParam(':attempt', $this->attempt);
        $stmt->bindParam(':assessment_marks', $this->assessment_marks);
        $stmt->bindParam(':final_exam_marks', $this->final_exam_marks);
        $stmt->bindParam(':final_marks', $this->final_marks);
        $stmt->bindParam(':status', $this->status);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id=:id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function exists($exam_id, $student_id, $attempt = null) {
        if ($attempt !== null) {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE exam_id = :exam_id AND student_id = :student_id AND attempt = :attempt";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':exam_id', $exam_id);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':attempt', $attempt);
        } else {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE exam_id = :exam_id AND student_id = :student_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':exam_id', $exam_id);
            $stmt->bindParam(':student_id', $student_id);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function getByExam($exam_id) {
        $query = "SELECT er.*, e.assessment_percentage, e.final_exam_percentage, s.reg_no, s.fullname as student_name
                  FROM {$this->table} er
                  LEFT JOIN exams e ON er.exam_id = e.id
                  LEFT JOIN student s ON er.student_id = s.id
                  WHERE er.exam_id = :exam_id
                  ORDER BY s.reg_no ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':exam_id', $exam_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get exams a student is registered for (facing) within a date range only.
     * Used for admission cards - only exams between date_from and date_to.
     * Excludes not_eligible modules (only eligible results are shown on the card).
     */
    public function getExamsForStudentInDateRange($student_id, $date_from, $date_to) {
        $query = "SELECT e.id as exam_id, e.exam_date, e.time_slot, e.location,
                         m.mcode as module_code, m.mname as module_name, m.semester,
                         c.cname as course_name
                  FROM {$this->table} er
                  INNER JOIN exams e ON er.exam_id = e.id
                  LEFT JOIN module m ON e.module_id = m.id
                  LEFT JOIN courses c ON e.course_id = c.id
                  WHERE er.student_id = :student_id
                    AND e.exam_date >= :date_from AND e.exam_date <= :date_to
                    AND (er.eligibility IS NULL OR er.eligibility != 'not_eligible')
                  ORDER BY e.exam_date ASC, e.time_slot ASC, m.mcode ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':date_from', $date_from);
        $stmt->bindParam(':date_to', $date_to);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get distinct student IDs that have at least one exam in the given date range.
     * Only counts eligible results (not_eligible modules excluded, for admission cards).
     */
    public function getStudentIdsWithExamsInDateRange($date_from, $date_to) {
        $query = "SELECT DISTINCT er.student_id
                  FROM {$this->table} er
                  INNER JOIN exams e ON er.exam_id = e.id
                  WHERE e.exam_date >= :date_from AND e.exam_date <= :date_to
                    AND (er.eligibility IS NULL OR er.eligibility != 'not_eligible')
                  ORDER BY er.student_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date_from', $date_from);
        $stmt->bindParam(':date_to', $date_to);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get all result rows for marks summary: students in batch, exams in list.
     * Returns flat list with er.*, e.assessment_percentage, e.final_exam_percentage, s.reg_no, s.fullname.
     * Caller can group by (student_id, exam_id) and take latest or attempt=1.
     */
    public function getResultsForMarksSummary($batch_id, $exam_ids) {
        if (empty($exam_ids)) return [];
        $placeholders = implode(',', array_fill(0, count($exam_ids), '?'));
        $query = "SELECT er.*, e.assessment_percentage, e.final_exam_percentage,
                         s.reg_no, s.fullname as student_name
                  FROM {$this->table} er
                  INNER JOIN exams e ON er.exam_id = e.id
                  INNER JOIN student s ON er.student_id = s.id
                  WHERE s.bid = ? AND er.exam_id IN ($placeholders)
                  ORDER BY s.reg_no ASC, er.exam_id ASC, er.attempt DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $batch_id, PDO::PARAM_INT);
        foreach (array_values($exam_ids) as $i => $eid) {
            $stmt->bindValue($i + 2, $eid, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
