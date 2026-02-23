<?php
require_once __DIR__ . '/../models/Course.php';

class CourseController {
    private $course;

    public function __construct() {
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
            'search' => $_GET['filter_search'] ?? ''
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== '';
        });
        
        $courses = $this->course->getAllCourses($filters);
        require_once __DIR__ . '/../views/courses/index.php';
    }

    public function create() {
        $this->requireAdminOrTeacher();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->course->cname = trim($_POST['cname'] ?? '');

            // Validation
            if (empty($this->course->cname)) {
                $_SESSION['error'] = "Course name is required.";
                header("Location: index.php?action=courses&sub=create");
                exit();
            }

            if ($this->course->cnameExists($this->course->cname)) {
                $_SESSION['error'] = "Course name already exists.";
                header("Location: index.php?action=courses&sub=create");
                exit();
            }

            if ($this->course->create()) {
                $_SESSION['success'] = "Course created successfully.";
                header("Location: index.php?action=courses");
                exit();
            } else {
                $_SESSION['error'] = "Failed to create course.";
                header("Location: index.php?action=courses&sub=create");
                exit();
            }
        } else {
            require_once __DIR__ . '/../views/courses/create.php';
        }
    }

    public function edit() {
        $this->requireAdminOrTeacher();

        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->course->id = $id;
            $this->course->cname = trim($_POST['cname'] ?? '');

            // Validation
            if (empty($this->course->cname)) {
                $_SESSION['error'] = "Course name is required.";
                header("Location: index.php?action=courses&sub=edit&id=" . $id);
                exit();
            }

            if ($this->course->cnameExists($this->course->cname, $id)) {
                $_SESSION['error'] = "Course name already exists.";
                header("Location: index.php?action=courses&sub=edit&id=" . $id);
                exit();
            }

            if ($this->course->update()) {
                $_SESSION['success'] = "Course updated successfully.";
                header("Location: index.php?action=courses");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update course.";
                header("Location: index.php?action=courses&sub=edit&id=" . $id);
                exit();
            }
        } else {
            if ($this->course->getCourseById($id)) {
                $course = $this->course;
                require_once __DIR__ . '/../views/courses/edit.php';
            } else {
                $_SESSION['error'] = "Course not found.";
                header("Location: index.php?action=courses");
                exit();
            }
        }
    }

    public function checkDependencies() {
        $this->requireAdminOrTeacher();
        
        $id = $_GET['id'] ?? 0;
        
        $hasModules = $this->course->hasModules($id);
        $hasVersions = $this->course->hasVersions($id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'hasModules' => $hasModules,
            'hasVersions' => $hasVersions
        ]);
        exit();
    }

    public function delete() {
        // Only admin can delete
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "Access denied. Admin privileges required to delete.";
            header("Location: index.php?action=courses");
            exit();
        }

        $id = $_GET['id'] ?? 0;

        // Check if course has modules allocated
        if ($this->course->hasModules($id)) {
            $_SESSION['error'] = "Cannot delete course. This course has modules allocated to it. Please delete all modules first before deleting the course.";
            header("Location: index.php?action=courses");
            exit();
        }

        // Check if course has versions
        if ($this->course->hasVersions($id)) {
            $_SESSION['error'] = "Cannot delete course. This course has versions allocated to it. Please delete all versions first before deleting the course.";
            header("Location: index.php?action=courses");
            exit();
        }

        if ($this->course->delete($id)) {
            $_SESSION['success'] = "Course deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete course.";
        }

        header("Location: index.php?action=courses");
        exit();
    }
}
?>
