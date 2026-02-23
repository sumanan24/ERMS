<?php
require_once __DIR__ . '/../models/Exam.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Module.php';
require_once __DIR__ . '/../models/Version.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/ExamResult.php';
require_once __DIR__ . '/../models/Batch.php';

class ExamController {
    private $exam;
    private $course;
    private $module;
    private $version;
    private $student;
    private $examResult;
    private $batch;

    public function __construct() {
        $this->exam = new Exam();
        $this->course = new Course();
        $this->module = new Module();
        $this->version = new Version();
        $this->student = new Student();
        $this->examResult = new ExamResult();
        $this->batch = new Batch();
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
            header("Location: index.php?action=exams");
            exit();
        }
    }

    public function index() {
        $this->requireAdminOrTeacher();
        $this->exam->ensureResultedStatusColumn();
        $filters = [
            'date_from' => $_GET['filter_date_from'] ?? '',
            'date_to' => $_GET['filter_date_to'] ?? '',
            'course_id' => $_GET['filter_course'] ?? '',
            'search' => $_GET['filter_search'] ?? ''
        ];
        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);
        if (($_SESSION['role'] ?? '') === 'student') {
            $filters['approved_only'] = true;
        }
        $exams = $this->exam->getAll($filters);
        $courses = $this->course->getAllCourses();
        require_once __DIR__ . '/../views/exams/index.php';
    }

    /** Set exam resulted status to pending or approved (admin/teacher only). */
    public function setResultedStatus() {
        $this->requireAdminOrTeacher();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=exams");
            exit();
        }
        $exam_id = (int)($_POST['exam_id'] ?? 0);
        $status = trim($_POST['resulted_status'] ?? '');
        if (!$exam_id || !in_array($status, ['pending', 'approved'])) {
            $_SESSION['error'] = "Invalid request.";
            header("Location: index.php?action=exams");
            exit();
        }
        if ($this->exam->updateResultedStatus($exam_id, $status)) {
            $_SESSION['success'] = "Resulted status set to " . ucfirst($status) . ".";
        } else {
            $_SESSION['error'] = "Failed to update status.";
        }
        $redirect = $_POST['redirect'] ?? 'index.php?action=exams';
        if (strpos($redirect, 'index.php') !== 0) {
            $redirect = 'index.php?action=exams';
        }
        header("Location: " . $redirect);
        exit();
    }

    public function create() {
        // Keep single-exam create for now, but primary schedule editing
        // is handled via the schedule() method.
        $this->requireAdminOrTeacher();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->exam->exam_date = $_POST['exam_date'] ?? '';
            $this->exam->time_slot = trim($_POST['time_slot'] ?? '');
            $this->exam->course_id = $_POST['course_id'] ?? 0;
            $this->exam->module_id = $_POST['module_id'] ?? 0;
            $this->exam->location = trim($_POST['location'] ?? '');
            $this->exam->assessment_percentage = !empty($_POST['assessment_percentage']) ? (float)$_POST['assessment_percentage'] : 0;
            $this->exam->final_exam_percentage = !empty($_POST['final_exam_percentage']) ? (float)$_POST['final_exam_percentage'] : 0;

            if (empty($this->exam->exam_date) || empty($this->exam->time_slot) || 
                empty($this->exam->course_id) || empty($this->exam->module_id) || empty($this->exam->location)) {
                $_SESSION['error'] = "All fields are required.";
                header("Location: index.php?action=exams&sub=create");
                exit();
            }

            // Validate percentages total 100
            if (abs(($this->exam->assessment_percentage + $this->exam->final_exam_percentage) - 100) > 0.01) {
                $_SESSION['error'] = "Assessment and Final Exam percentages must total 100.";
                header("Location: index.php?action=exams&sub=create");
                exit();
            }
            if ($this->exam->create()) {
                $_SESSION['success'] = "Exam created successfully.";
                header("Location: index.php?action=exams");
                exit();
            }
            $_SESSION['error'] = "Failed to create exam.";
            header("Location: index.php?action=exams&sub=create");
            exit();
        } else {
            $courses = $this->course->getAllCourses();
            require_once __DIR__ . '/../views/exams/create.php';
        }
    }

    public function edit() {
        $this->requireAdminOrTeacher();
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->exam->id = $id;
            $this->exam->exam_date = $_POST['exam_date'] ?? '';
            $this->exam->time_slot = trim($_POST['time_slot'] ?? '');
            $this->exam->course_id = $_POST['course_id'] ?? 0;
            $this->exam->module_id = $_POST['module_id'] ?? 0;
            $this->exam->location = trim($_POST['location'] ?? '');
            $this->exam->assessment_percentage = !empty($_POST['assessment_percentage']) ? (float)$_POST['assessment_percentage'] : 0;
            $this->exam->final_exam_percentage = !empty($_POST['final_exam_percentage']) ? (float)$_POST['final_exam_percentage'] : 0;

            if (empty($this->exam->exam_date) || empty($this->exam->time_slot) || 
                empty($this->exam->course_id) || empty($this->exam->module_id) || empty($this->exam->location)) {
                $_SESSION['error'] = "All fields are required.";
                header("Location: index.php?action=exams&sub=edit&id=" . $id);
                exit();
            }

            // Validate percentages total 100
            if (abs(($this->exam->assessment_percentage + $this->exam->final_exam_percentage) - 100) > 0.01) {
                $_SESSION['error'] = "Assessment and Final Exam percentages must total 100.";
                header("Location: index.php?action=exams&sub=edit&id=" . $id);
                exit();
            }
            if ($this->exam->update()) {
                $_SESSION['success'] = "Exam updated successfully.";
                header("Location: index.php?action=exams");
                exit();
            }
            $_SESSION['error'] = "Failed to update exam.";
            header("Location: index.php?action=exams&sub=edit&id=" . $id);
            exit();
        } else {
            if ($this->exam->getById($id)) {
                $exam = $this->exam;
                $courses = $this->course->getAllCourses();
                // Load modules for the exam's course
                $modules = $this->module->getAllModules(['course_id' => $exam->course_id]);
                require_once __DIR__ . '/../views/exams/edit.php';
            } else {
                $_SESSION['error'] = "Exam not found.";
                header("Location: index.php?action=exams");
                exit();
            }
        }
    }

    public function delete() {
        $this->requireAdmin();
        $id = $_GET['id'] ?? 0;
        if ($this->exam->delete($id)) {
            $_SESSION['success'] = "Exam deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete exam.";
        }
        header("Location: index.php?action=exams");
        exit();
    }

    public function getModulesByCourse() {
        $this->requireAdminOrTeacher();
        $course_id = $_GET['course_id'] ?? 0;
        if ($course_id > 0) {
            $modules = $this->module->getAllModules(['course_id' => $course_id]);
            header('Content-Type: application/json');
            echo json_encode($modules);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
        exit();
    }

    /**
     * Return versions for a given course (for dropdown).
     */
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

    /**
     * Return distinct semesters for a version (for schedule dropdown).
     */
    public function getSemestersByVersion() {
        $this->requireAdminOrTeacher();
        $version_id = $_GET['version_id'] ?? 0;
        if ($version_id > 0) {
            $semesters = $this->module->getSemestersByVersion($version_id);
            header('Content-Type: application/json');
            echo json_encode($semesters);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
        exit();
    }

    /**
     * Return schedule table HTML only (for AJAX load without full page refresh).
     */
    public function getScheduleTable() {
        $this->requireAdminOrTeacher();
        $selectedCourse   = $_GET['course_id'] ?? '';
        $selectedVersion  = $_GET['version_id'] ?? '';
        $selectedBatch    = $_GET['batch_id'] ?? '';
        $selectedSemester = $_GET['semester'] ?? '';

        $modules = [];
        $existingExams = [];

        if (!empty($selectedCourse) && !empty($selectedVersion) && $selectedSemester !== '') {
            $modules = $this->module->getAllModules([
                'course_id'  => $selectedCourse,
                'version_id' => $selectedVersion,
                'semester'   => $selectedSemester
            ]);
            $existingExams = $this->exam->getByCourseAndVersion($selectedCourse, $selectedVersion);
        }

        header('Content-Type: text/html; charset=utf-8');
        require_once __DIR__ . '/../views/exams/schedule_table.php';
        exit();
    }

    /**
     * Bulk exam schedule editor by course + version.
     * Select course + version, load all modules, then set date/time/location per module.
     */
    public function schedule() {
        $this->requireAdminOrTeacher();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $course_id  = $_POST['course_id'] ?? 0;
            $version_id = $_POST['version_id'] ?? 0;
            $batch_id   = $_POST['batch_id'] ?? 0;
            $semester   = $_POST['semester'] ?? '';
            $modules    = $_POST['modules'] ?? [];

            if (empty($course_id) || empty($version_id)) {
                $_SESSION['error'] = "Course and Version are required.";
                header("Location: index.php?action=exams&sub=schedule&course_id={$course_id}&version_id={$version_id}");
                exit();
            }

            $updated = 0;
            $studentsAdded = 0;
            $examIds = [];

            foreach ($modules as $module_id => $data) {
                $exam_date = trim($data['exam_date'] ?? '');
                $time_slot = trim($data['time_slot'] ?? '');
                $location  = trim($data['location'] ?? '');
                $assessment_percentage = !empty($data['assessment_percentage']) ? (float)$data['assessment_percentage'] : 0;
                $final_exam_percentage = !empty($data['final_exam_percentage']) ? (float)$data['final_exam_percentage'] : 0;

                // If all fields are empty, skip (don't create/update)
                if ($exam_date === '' && $time_slot === '' && $location === '') {
                    continue;
                }

                if ($exam_date === '' || $time_slot === '' || $location === '') {
                    // Partial row, skip and maybe add warning later
                    continue;
                }

                // Validate percentages total 100 if provided
                if (($assessment_percentage > 0 || $final_exam_percentage > 0) && 
                    abs(($assessment_percentage + $final_exam_percentage) - 100) > 0.01) {
                    // Skip this module if percentages don't total 100
                    continue;
                }

                // Save exam and get the exam ID
                $examId = $this->exam->saveForModule($course_id, (int)$module_id, $exam_date, $time_slot, $location, $assessment_percentage, $final_exam_percentage);
                if ($examId) {
                    $updated++;
                    $examIds[] = $examId;
                }
            }

            // Auto-add students from batch if batch is selected
            if (!empty($batch_id) && !empty($examIds)) {
                // Get all students from the batch and version
                $students = $this->student->getAll([
                    'batch_id' => $batch_id,
                    'version_id' => $version_id
                ]);

                foreach ($examIds as $examId) {
                    foreach ($students as $student) {
                        // Check if student already exists for this exam
                        if (!$this->examResult->exists($examId, $student['id'], 1)) {
                            $this->examResult->exam_id = $examId;
                            $this->examResult->student_id = $student['id'];
                            $this->examResult->eligibility = 'eligible';
                            $this->examResult->attempt = 1;
                            $this->examResult->assessment_marks = 0;
                            $this->examResult->final_exam_marks = 0;
                            $this->examResult->final_marks = 0;
                            $this->examResult->status = 'absent';
                            
                            if ($this->examResult->create()) {
                                $studentsAdded++;
                            }
                        }
                    }
                }
            }

            if ($updated > 0) {
                $message = "Exam schedule updated for {$updated} module(s).";
                if ($studentsAdded > 0) {
                    $message .= " Added {$studentsAdded} student enrollment(s) to the exams.";
                }
                $_SESSION['success'] = $message;
            } else {
                $_SESSION['error'] = "No exam entries were updated. Please fill date, time and location for at least one module.";
            }

            $batchParam = !empty($batch_id) ? "&batch_id={$batch_id}" : "";
            $semesterParam = !empty($_POST['semester']) ? "&semester=" . urlencode($_POST['semester']) : "";
            header("Location: index.php?action=exams&sub=schedule&course_id={$course_id}&version_id={$version_id}{$batchParam}{$semesterParam}");
            exit();
        } else {
            // GET - show schedule form
            $courses = $this->course->getAllCourses();

            $selectedCourse = $_GET['course_id'] ?? '';
            $selectedVersion = $_GET['version_id'] ?? '';
            $selectedBatch = $_GET['batch_id'] ?? '';
            $selectedSemester = $_GET['semester'] ?? '';

            $versions = [];
            $batches = [];
            $semesters = [];
            $modules = [];
            $existingExams = [];

            if (!empty($selectedCourse)) {
                $versions = $this->version->getAllVersions(['course_id' => $selectedCourse]);
            }

            if (!empty($selectedVersion)) {
                $batches = $this->batch->getAll(['version_id' => $selectedVersion]);
                $semesters = $this->module->getSemestersByVersion($selectedVersion);
            }

            if (!empty($selectedCourse) && !empty($selectedVersion) && $selectedSemester !== '') {
                // Load modules for this course + version + semester only
                $modules = $this->module->getAllModules([
                    'course_id'  => $selectedCourse,
                    'version_id' => $selectedVersion,
                    'semester'   => $selectedSemester
                ]);

                // Load existing exams keyed by module_id
                $existingExams = $this->exam->getByCourseAndVersion($selectedCourse, $selectedVersion);
            }

            require_once __DIR__ . '/../views/exams/schedule.php';
        }
    }

    public function downloadSchedule() {
        $this->requireAdminOrTeacher();
        
        $date_from = $_GET['date_from'] ?? date('Y-m-d');
        $date_to = $_GET['date_to'] ?? date('Y-m-d', strtotime('+30 days'));
        
        // Get all exams in date range
        $exams = $this->exam->getExamsForSchedule($date_from, $date_to);
        
        // Build unique courses list (each course appears only once)
        $courses = []; // key: course_id
        foreach ($exams as $exam) {
            $courseId = $exam['course_id'];
            if (!isset($courses[$courseId])) {
                $courses[$courseId] = [
                    'course_id' => $courseId,
                    'course_name' => $exam['course_name'] ?? ''
                ];
            }
        }
        
        // Group exams by date and time slot
        $schedule = [];
        foreach ($exams as $exam) {
            $key = $exam['exam_date'] . '|' . $exam['time_slot'];
            if (!isset($schedule[$key])) {
                $schedule[$key] = [
                    'date' => $exam['exam_date'],
                    'time' => $exam['time_slot'],
                    'exams' => []
                ];
            }
            $schedule[$key]['exams'][] = $exam;
        }
        
        // Sort schedule by date and time
        ksort($schedule);
        
        // Clean output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="exam_schedule_' . $date_from . '_to_' . $date_to . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output CSV (Excel will open this)
        $output = fopen('php://output', 'w');
        
        // HEADER ROW (row 1):
        // A1 = Date, B1 = Time, C1/D1/... = Course name only (each course once)
        $header = ['Date', 'Time'];
        foreach ($courses as $course) {
            $header[] = $course['course_name'];
        }
        fputcsv($output, $header, ',', '"');
        
        // Write data rows
        foreach ($schedule as $slot) {
            $row = [
                date('d.m.Y', strtotime($slot['date'])),
                $slot['time']
            ];
            
            // For each course column, find exams for that course at this date/time
            foreach ($courses as $course) {
                $courseExams = [];
                foreach ($slot['exams'] as $exam) {
                    if ($exam['course_id'] == $course['course_id']) {
                        $courseExams[] = $exam;
                    }
                }
                
                if (empty($courseExams)) {
                    $row[] = '';
                } else {
                    // Combine all modules for this course at this time
                    // Format: module code (line 1), module name (line 2), location (line 3)
                    // If multiple modules, combine them
                    $cellLines = [];
                    foreach ($courseExams as $exam) {
                        $moduleCode = $exam['module_code'] ?? '';
                        $moduleName = $exam['module_name'] ?? '';
                        $location = $exam['location'] ?? '';
                        
                        if ($moduleCode || $moduleName || $location) {
                            $moduleText = trim($moduleCode . "\n" . $moduleName . "\n" . $location);
                            $cellLines[] = $moduleText;
                        }
                    }
                    $row[] = implode("\n\n", $cellLines); // Double newline between multiple modules
                }
            }
            
            fputcsv($output, $row, ',', '"');
        }
        
        fclose($output);
        exit();
    }

    public function exportToExcel() {
        $this->requireAdminOrTeacher();
        
        // Get filters from GET parameters (same as index)
        $filters = [
            'date_from' => $_GET['filter_date_from'] ?? '',
            'date_to' => $_GET['filter_date_to'] ?? '',
            'course_id' => $_GET['filter_course'] ?? '',
            'search' => $_GET['filter_search'] ?? ''
        ];
        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);
        
        // Get all exams with filters
        $exams = $this->exam->getAll($filters);
        
        // Clean output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="exams_export_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output CSV
        $output = fopen('php://output', 'w');
        
        // Write header row
        $header = ['Date', 'Time', 'Course', 'Module Code & Name & Location'];
        fputcsv($output, $header, ',', '"');
        
        // Write data rows
        foreach ($exams as $exam) {
            // Combine module code, module name, and location in one cell
            $moduleInfo = '';
            if (!empty($exam['module_code'])) {
                $moduleInfo = $exam['module_code'];
            }
            if (!empty($exam['module_name'])) {
                $moduleInfo .= ($moduleInfo ? ' - ' : '') . $exam['module_name'];
            }
            if (!empty($exam['location'])) {
                $moduleInfo .= ($moduleInfo ? "\n" : '') . $exam['location'];
            }
            
            $row = [
                date('d.m.Y', strtotime($exam['exam_date'])),  // Date
                $exam['time_slot'],                              // Time
                $exam['course_name'] ?? '',                      // Course
                $moduleInfo                                      // Module Code - Module Name (newline) Location
            ];
            
            fputcsv($output, $row, ',', '"');
        }
        
        fclose($output);
        exit();
    }
}
?>
