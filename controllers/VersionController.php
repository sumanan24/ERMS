<?php
require_once __DIR__ . '/../models/Version.php';
require_once __DIR__ . '/../models/Course.php';

class VersionController {
    private $version;
    private $course;

    public function __construct() {
        $this->version = new Version();
        $this->course = new Course();
    }

    public function requireAdminOrTeacher() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
            $_SESSION['error'] = "Access denied. Admin or Teacher privileges required.";
            header("Location: index.php?action=dashboard");
            exit();
        }
    }

    public function index() {
        $this->requireAdminOrTeacher();
        
        // Get filter parameters
        $filters = [
            'course_id' => $_GET['filter_course'] ?? '',
            'status' => $_GET['filter_status'] ?? '',
            'search' => $_GET['filter_search'] ?? ''
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== '';
        });
        
        $versions = $this->version->getAllVersions($filters);
        $courses = $this->course->getAllCourses();
        
        require_once __DIR__ . '/../views/versions/index.php';
    }

    public function create() {
        $this->requireAdminOrTeacher();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->version->course_id = $_POST['course_id'] ?? 0;
            $this->version->version_name = trim($_POST['version_name'] ?? '');
            $this->version->description = trim($_POST['description'] ?? '');
            $this->version->status = $_POST['status'] ?? 'active';

            // Validation
            if (empty($this->version->course_id) || $this->version->course_id == 0) {
                $_SESSION['error'] = "Course selection is required.";
                header("Location: index.php?action=versions&sub=create");
                exit();
            }

            if (empty($this->version->version_name)) {
                $_SESSION['error'] = "Version name is required.";
                header("Location: index.php?action=versions&sub=create");
                exit();
            }

            if ($this->version->versionNameExists($this->version->version_name, $this->version->course_id)) {
                $_SESSION['error'] = "Version name already exists for this course.";
                header("Location: index.php?action=versions&sub=create");
                exit();
            }

            if ($this->version->create()) {
                $_SESSION['success'] = "Version created successfully.";
                header("Location: index.php?action=versions");
                exit();
            } else {
                $_SESSION['error'] = "Failed to create version.";
                header("Location: index.php?action=versions&sub=create");
                exit();
            }
        } else {
            $courses = $this->course->getAllCourses();
            require_once __DIR__ . '/../views/versions/create.php';
        }
    }

    public function edit() {
        $this->requireAdminOrTeacher();

        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->version->id = $id;
            $this->version->course_id = $_POST['course_id'] ?? 0;
            $this->version->version_name = trim($_POST['version_name'] ?? '');
            $this->version->description = trim($_POST['description'] ?? '');
            $this->version->status = $_POST['status'] ?? 'active';

            // Validation
            if (empty($this->version->course_id) || $this->version->course_id == 0) {
                $_SESSION['error'] = "Course selection is required.";
                header("Location: index.php?action=versions&sub=edit&id=" . $id);
                exit();
            }

            if (empty($this->version->version_name)) {
                $_SESSION['error'] = "Version name is required.";
                header("Location: index.php?action=versions&sub=edit&id=" . $id);
                exit();
            }

            if ($this->version->versionNameExists($this->version->version_name, $this->version->course_id, $id)) {
                $_SESSION['error'] = "Version name already exists for this course.";
                header("Location: index.php?action=versions&sub=edit&id=" . $id);
                exit();
            }

            if ($this->version->update()) {
                $_SESSION['success'] = "Version updated successfully.";
                header("Location: index.php?action=versions");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update version.";
                header("Location: index.php?action=versions&sub=edit&id=" . $id);
                exit();
            }
        } else {
            if ($this->version->getVersionById($id)) {
                $version = $this->version;
                $courses = $this->course->getAllCourses();
                require_once __DIR__ . '/../views/versions/edit.php';
            } else {
                $_SESSION['error'] = "Version not found.";
                header("Location: index.php?action=versions");
                exit();
            }
        }
    }

    public function checkDependencies() {
        $this->requireAdminOrTeacher();
        
        $id = $_GET['id'] ?? 0;
        
        $hasModules = $this->version->hasModules($id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'hasModules' => $hasModules
        ]);
        exit();
    }

    public function delete() {
        // Only admin can delete
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "Access denied. Admin privileges required to delete.";
            header("Location: index.php?action=versions");
            exit();
        }

        $id = $_GET['id'] ?? 0;

        // Check if version has modules allocated
        if ($this->version->hasModules($id)) {
            $_SESSION['error'] = "Cannot delete version. This version has modules allocated to it. Please delete all modules first before deleting the version.";
            header("Location: index.php?action=versions");
            exit();
        }

        if ($this->version->delete($id)) {
            $_SESSION['success'] = "Version deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete version.";
        }

        header("Location: index.php?action=versions");
        exit();
    }
}
?>
