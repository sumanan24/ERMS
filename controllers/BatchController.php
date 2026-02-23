<?php
require_once __DIR__ . '/../models/Batch.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Version.php';

class BatchController {
    private $batch;
    private $course;
    private $version;

    public function __construct() {
        $this->batch = new Batch();
        $this->course = new Course();
        $this->version = new Version();
    }

    private function requireAdminOrTeacher() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
            $_SESSION['error'] = "Access denied. Admin or Teacher privileges required.";
            header("Location: index.php?action=dashboard");
            exit();
        }
    }

    private function requireAdmin() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "Access denied. Admin privileges required.";
            header("Location: index.php?action=batch");
            exit();
        }
    }

    public function index() {
        $this->requireAdminOrTeacher();
        $filters = [
            'course_id' => $_GET['filter_course'] ?? '',
            'search' => $_GET['filter_search'] ?? '',
            'start_from' => $_GET['filter_start_from'] ?? '',
            'start_to' => $_GET['filter_start_to'] ?? '',
        ];
        $filters = array_filter($filters, fn($v)=>$v!=='' && $v!==null);
        $batches = $this->batch->getAll($filters);
        $courses = $this->course->getAllCourses();
        require_once __DIR__ . '/../views/batch/index.php';
    }

    public function create() {
        $this->requireAdminOrTeacher();
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $this->batch->batch_no = trim($_POST['batch_no'] ?? '');
            $this->batch->start_date = $_POST['start_date'] ?? null;
            $this->batch->end_date = $_POST['end_date'] ?? null;
            $this->batch->cid = $_POST['cid'] ?? 0;
            $this->batch->version_id = !empty($_POST['version_id']) ? $_POST['version_id'] : null;

            if (empty($this->batch->batch_no) || empty($this->batch->cid)) {
                $_SESSION['error'] = "Batch no and Course are required.";
                header("Location: index.php?action=batch&sub=create");
                exit();
            }
            if ($this->batch->batchNoExists($this->batch->batch_no, $this->batch->cid)) {
                $_SESSION['error'] = "Batch number already exists for this course.";
                header("Location: index.php?action=batch&sub=create");
                exit();
            }
            if ($this->batch->create()) {
                $_SESSION['success'] = "Batch created successfully.";
                header("Location: index.php?action=batch");
                exit();
            }
            $_SESSION['error'] = "Failed to create batch.";
            header("Location: index.php?action=batch&sub=create");
            exit();
        } else {
            $courses = $this->course->getAllCourses();
            require_once __DIR__ . '/../views/batch/create.php';
        }
    }

    public function edit() {
        $this->requireAdminOrTeacher();
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $this->batch->id = $id;
            $this->batch->batch_no = trim($_POST['batch_no'] ?? '');
            $this->batch->start_date = $_POST['start_date'] ?? null;
            $this->batch->end_date = $_POST['end_date'] ?? null;
            $this->batch->cid = $_POST['cid'] ?? 0;
            $this->batch->version_id = !empty($_POST['version_id']) ? $_POST['version_id'] : null;

            if (empty($this->batch->batch_no) || empty($this->batch->cid)) {
                $_SESSION['error'] = "Batch no and Course are required.";
                header("Location: index.php?action=batch&sub=edit&id=".$id);
                exit();
            }
            if ($this->batch->batchNoExists($this->batch->batch_no, $this->batch->cid, $id)) {
                $_SESSION['error'] = "Batch number already exists for this course.";
                header("Location: index.php?action=batch&sub=edit&id=".$id);
                exit();
            }
            if ($this->batch->update()) {
                $_SESSION['success'] = "Batch updated successfully.";
                header("Location: index.php?action=batch");
                exit();
            }
            $_SESSION['error'] = "Failed to update batch.";
            header("Location: index.php?action=batch&sub=edit&id=".$id);
            exit();
        } else {
            if ($this->batch->getById($id)) {
                $batch = $this->batch;
                $courses = $this->course->getAllCourses();
                // Load versions only for the batch's course
                $versions = $this->version->getAllVersions(['course_id' => $batch->cid]);
                require_once __DIR__ . '/../views/batch/edit.php';
            } else {
                $_SESSION['error'] = "Batch not found.";
                header("Location: index.php?action=batch");
                exit();
            }
        }
    }

    public function delete() {
        $this->requireAdmin();
        $id = $_GET['id'] ?? 0;
        if ($this->batch->delete($id)) {
            $_SESSION['success'] = "Batch deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete batch.";
        }
        header("Location: index.php?action=batch");
        exit();
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
}
?>
