<?php
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Version.php';
require_once __DIR__ . '/../models/Batch.php';

class StudentController {
    private $student;
    private $course;
    private $version;
    private $batch;

    public function __construct() {
        $this->student = new Student();
        $this->course = new Course();
        $this->version = new Version();
        $this->batch = new Batch();
    }

    private function requireAdminOrTeacher() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','teacher'])) {
            $_SESSION['error'] = "Access denied. Admin or Teacher privileges required.";
            header("Location: index.php?action=dashboard");
            exit();
        }
    }

    private function requireAdmin() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "Access denied. Admin privileges required.";
            header("Location: index.php?action=students");
            exit();
        }
    }

    public function index() {
        $this->requireAdminOrTeacher();
        $filters = [
            'course_id' => $_GET['filter_course'] ?? '',
            'version_id' => $_GET['filter_version'] ?? '',
            'batch_id' => $_GET['filter_batch'] ?? '',
            'search' => $_GET['filter_search'] ?? ''
        ];
        $filters = array_filter($filters, fn($v)=>$v!=='' && $v!==null);
        $students = $this->student->getAll($filters);
        $courses = $this->course->getAllCourses();
        $versions = $this->version->getAllVersions();
        $batches = (new Batch())->getAll();
        require_once __DIR__ . '/../views/students/index.php';
    }

    public function create() {
        $this->requireAdminOrTeacher();
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $this->student->reg_no = trim($_POST['reg_no'] ?? '');
            $this->student->fullname = trim($_POST['fullname'] ?? '');
            $this->student->nic = trim($_POST['nic'] ?? '');
            $this->student->cid = $_POST['cid'] ?? 0;
            $this->student->version_id = $_POST['version_id'] ?? null;
            $this->student->bid = $_POST['bid'] ?? 0;

            if (empty($this->student->reg_no) || empty($this->student->fullname) || empty($this->student->cid) || empty($this->student->bid)) {
                $_SESSION['error'] = "Reg No, Fullname, Course and Batch are required.";
                header("Location: index.php?action=students&sub=create");
                exit();
            }
            if ($this->student->regNoExists($this->student->reg_no)) {
                $_SESSION['error'] = "Reg No already exists.";
                header("Location: index.php?action=students&sub=create");
                exit();
            }
            if ($this->student->create()) {
                $_SESSION['success'] = "Student created successfully.";
                header("Location: index.php?action=students");
                exit();
            }
            $_SESSION['error'] = "Failed to create student.";
            header("Location: index.php?action=students&sub=create");
            exit();
        } else {
            $courses = $this->course->getAllCourses();
            $versions = $this->version->getAllVersions();
            $batches = (new Batch())->getAll();
            require_once __DIR__ . '/../views/students/create.php';
        }
    }

    public function edit() {
        $this->requireAdminOrTeacher();
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $this->student->id = $id;
            $this->student->reg_no = trim($_POST['reg_no'] ?? '');
            $this->student->fullname = trim($_POST['fullname'] ?? '');
            $this->student->nic = trim($_POST['nic'] ?? '');
            $this->student->cid = $_POST['cid'] ?? 0;
            $this->student->version_id = $_POST['version_id'] ?? null;
            $this->student->bid = $_POST['bid'] ?? 0;

            if (empty($this->student->reg_no) || empty($this->student->fullname) || empty($this->student->cid) || empty($this->student->bid)) {
                $_SESSION['error'] = "Reg No, Fullname, Course and Batch are required.";
                header("Location: index.php?action=students&sub=edit&id=".$id);
                exit();
            }
            if ($this->student->regNoExists($this->student->reg_no, $id)) {
                $_SESSION['error'] = "Reg No already exists.";
                header("Location: index.php?action=students&sub=edit&id=".$id);
                exit();
            }
            if ($this->student->update()) {
                $_SESSION['success'] = "Student updated successfully.";
                header("Location: index.php?action=students");
                exit();
            }
            $_SESSION['error'] = "Failed to update student.";
            header("Location: index.php?action=students&sub=edit&id=".$id);
            exit();
        } else {
            if ($this->student->getById($id)) {
                $student = $this->student;
                $courses = $this->course->getAllCourses();
                // Load versions and batches only for the student's course
                $versions = $this->version->getAllVersions(['course_id' => $student->cid]);
                $batches = (new Batch())->getAll(['course_id' => $student->cid]);
                require_once __DIR__ . '/../views/students/edit.php';
            } else {
                $_SESSION['error'] = "Student not found.";
                header("Location: index.php?action=students");
                exit();
            }
        }
    }

    public function delete() {
        $this->requireAdmin();
        $id = $_GET['id'] ?? 0;
        if ($this->student->delete($id)) {
            $_SESSION['success'] = "Student deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete student.";
        }
        header("Location: index.php?action=students");
        exit();
    }

    public function import() {
        $this->requireAdminOrTeacher();
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $cid = $_POST['course_id'] ?? 0;
            $version_id = $_POST['version_id'] ?? null;
            $bid = $_POST['batch_id'] ?? 0;
            if (empty($cid) || empty($bid)) {
                $_SESSION['error'] = "Course and Batch are required.";
                header("Location: index.php?action=students&sub=import");
                exit();
            }
            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error']!=UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Please upload a CSV file.";
                header("Location: index.php?action=students&sub=import");
                exit();
            }
            $file = $_FILES['excel_file']['tmp_name'];
            $students = $this->parseCsv($file);
            if (empty($students)) {
                header("Location: index.php?action=students&sub=import");
                exit();
            }
            $result = $this->student->importFromArray($students, $cid, $version_id, $bid);
            if ($result['success']>0) {
                $_SESSION['success'] = "Imported {$result['success']} student(s).";
                if (!empty($result['errors'])) $_SESSION['import_errors']=$result['errors'];
            } else {
                $_SESSION['error'] = "No students imported. " . implode(', ',$result['errors']);
            }
            header("Location: index.php?action=students");
            exit();
        } else {
            $courses = $this->course->getAllCourses();
            $versions = $this->version->getAllVersions();
            $batches = (new Batch())->getAll();
            require_once __DIR__ . '/../views/students/import.php';
        }
    }

    private function parseCsv($file) {
        $rows = [];
        if (!file_exists($file) || !is_readable($file)) {
            $_SESSION['error'] = "File is not readable.";
            return [];
        }
        $handle = fopen($file,'r');
        if (!$handle) { $_SESSION['error'] = "Unable to open file."; return []; }
        $header = fgetcsv($handle);
        if ($header) {
            $header = array_map('strtolower', array_map('trim',$header));
        }
        $regIdx = array_search('reg_no',$header);
        $nameIdx = array_search('fullname',$header);
        $nicIdx = array_search('nic',$header);
        if ($regIdx===false || $nameIdx===false) {
            $_SESSION['error'] = "CSV must have reg_no and fullname columns.";
            fclose($handle);
            return [];
        }
        while(($data=fgetcsv($handle))!==false){
            if (empty(array_filter($data))) continue;
            $rows[] = [
                'reg_no' => $data[$regIdx] ?? '',
                'fullname' => $data[$nameIdx] ?? '',
                'nic' => $nicIdx!==false ? ($data[$nicIdx] ?? '') : ''
            ];
        }
        fclose($handle);
        if (empty($rows)) $_SESSION['error'] = "No valid data found in CSV.";
        return $rows;
    }

    public function getVersionsByCourse() {
        $this->requireAdminOrTeacher();
        
        $course_id = $_GET['course_id'] ?? 0;
        
        if ($course_id > 0) {
            $versions = $this->version->getAllVersions(['course_id' => $course_id]);
            header('Content-Type: application/json');
            echo json_encode($versions);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
        exit();
    }

    public function getBatchesByCourse() {
        $this->requireAdminOrTeacher();
        
        $course_id = $_GET['course_id'] ?? 0;
        
        if ($course_id > 0) {
            $batches = $this->batch->getAll(['course_id' => $course_id]);
            header('Content-Type: application/json');
            echo json_encode($batches);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
        exit();
    }

    public function getBatchesByVersion() {
        $this->requireAdminOrTeacher();
        
        $version_id = $_GET['version_id'] ?? 0;
        
        if ($version_id > 0) {
            $batches = $this->batch->getAll(['version_id' => $version_id]);
            header('Content-Type: application/json');
            echo json_encode($batches);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
        exit();
    }

    public function getStudentsByBatch() {
        $this->requireAdminOrTeacher();
        
        $batch_id = $_GET['batch_id'] ?? 0;
        $version_id = $_GET['version_id'] ?? 0;
        $course_id = $_GET['course_id'] ?? 0;
        
        if ($batch_id > 0) {
            $filters = ['batch_id' => $batch_id];
            if ($version_id > 0) {
                $filters['version_id'] = $version_id;
            }
            if ($course_id > 0) {
                $filters['course_id'] = $course_id;
            }
            $students = $this->student->getAll($filters);
            header('Content-Type: application/json');
            echo json_encode($students);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
        exit();
    }

    public function downloadSample() {
        $this->requireAdminOrTeacher();
        
        // Sample data with proper formatting - NO SPACES, NO BLANK LINES
        $sampleData = [
            ['reg_no', 'fullname', 'nic'],
            ['STU001', 'John Doe', '123456789V'],
            ['STU002', 'Jane Smith', '987654321V'],
            ['STU003', 'Robert Johnson', '456789123V'],
            ['STU004', 'Emily Davis', '789123456V'],
            ['STU005', 'Michael Brown', '321654987V'],
            ['STU006', 'Sarah Wilson', '654987321V'],
            ['STU007', 'David Miller', '147258369V'],
            ['STU008', 'Lisa Anderson', '258369147V']
        ];
        
        // Clean any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for CSV download - MUST be first output
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="student_import_sample.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output CSV - use php://output for direct download
        $output = fopen('php://output', 'w');
        
        // DO NOT add BOM - it causes encoding issues
        // Write header row first (this is critical - must be exact: reg_no,fullname,nic)
        // NO SPACES before or after
        fputcsv($output, $sampleData[0], ',', '"');
        
        // Write data rows
        for ($i = 1; $i < count($sampleData); $i++) {
            fputcsv($output, $sampleData[$i], ',', '"');
        }
        
        fclose($output);
        exit();
    }

    public function search() {
        $this->requireAdminOrTeacher();
        $query = $_GET['q'] ?? '';
        $students = [];
        if (strlen($query) >= 2) {
            $students = $this->student->getAll(['search' => $query]);
        }
        header('Content-Type: application/json');
        echo json_encode($students);
        exit();
    }

    public function getAll() {
        $this->requireAdminOrTeacher();
        $exclude_exam_id = $_GET['exclude_exam_id'] ?? 0;
        
        $students = $this->student->getAll();
        
        // Filter out students already allocated to the exam if exclude_exam_id is provided
        if ($exclude_exam_id > 0) {
            require_once __DIR__ . '/../models/ExamResult.php';
            $examResult = new ExamResult();
            $existingResults = $examResult->getByExam($exclude_exam_id);
            $existingStudentIds = array_column($existingResults, 'student_id');
            
            $students = array_filter($students, function($student) use ($existingStudentIds) {
                return !in_array($student['id'], $existingStudentIds);
            });
            $students = array_values($students); // Re-index array
        }
        
        header('Content-Type: application/json');
        echo json_encode($students);
        exit();
    }
}
?>

