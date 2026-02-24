<?php
// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Define base path
define('BASE_PATH', __DIR__);

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/controllers/' . $class . '.php',
        BASE_PATH . '/models/' . $class . '.php',
        BASE_PATH . '/config/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

// Get action from URL
$action = $_GET['action'] ?? 'index';

// Route handling
switch ($action) {
    case 'index':
        // Student result lookup page - embedded directly in index.php
        require_once __DIR__ . '/models/Student.php';
        require_once __DIR__ . '/models/ExamResult.php';
        
        $message = '';
        $student = null;
        $results = [];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $search = trim($_POST['search'] ?? '');
            
            if (empty($search)) {
                $message = "Please enter your Registration Number or NIC.";
            } else {
                $studentModel = new Student();
                if ($studentModel->findByNicOrRegNo($search)) {
                    $student = $studentModel;
                    require_once __DIR__ . '/models/Exam.php';
                    $examModel = new Exam();
                    $examModel->ensureResultedStatusColumn();
                    $examResultModel = new ExamResult();
                    $results = $examResultModel->getAll(['student_id' => $student->id, 'approved_only' => true]);
                } else {
                    $message = "No student found with the provided Registration Number or NIC.";
                }
            }
        }
        
        // Helper function for grade calculation
        function getGradeFromPercentage($percentage, $final_exam_marks = null) {
            if ($final_exam_marks !== null && $final_exam_marks < 40 && $percentage >= 40) {
                return 'C-';
            }
            if ($percentage >= 85) return 'A+';
            elseif ($percentage >= 80) return 'A';
            elseif ($percentage >= 75) return 'A-';
            elseif ($percentage >= 70) return 'B+';
            elseif ($percentage >= 65) return 'B';
            elseif ($percentage >= 60) return 'B-';
            elseif ($percentage >= 50) return 'C+';
            elseif ($percentage >= 40) return 'C';
            elseif ($percentage >= 30) return 'C-';
            elseif ($percentage >= 20) return 'D';
            return 'F';
        }
        
        // Output HTML directly
        require_once __DIR__ . '/views/student_result_lookup.php';
        exit();
        break;

    case 'login':
    case 'adminlogin':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $authController = new AuthController();
            $authController->login();
        } else {
            // Check if already logged in
            if (isset($_SESSION['user_id'])) {
                header("Location: index.php?action=dashboard");
                exit();
            }
            require_once __DIR__ . '/views/login.php';
        }
        break;

    case 'logout':
        $authController = new AuthController();
        $authController->logout();
        break;

    case 'dashboard':
        $authController = new AuthController();
        $authController->requireLogin();
        require_once __DIR__ . '/views/dashboard.php';
        break;

    case 'profile':
        $authController = new AuthController();
        $sub = $_GET['sub'] ?? '';
        if ($sub === 'updatePassword') {
            $authController->updatePassword();
        } else {
            $authController->profile();
        }
        break;

    case 'users':
        $authController = new AuthController();
        $authController->requireLogin();
        $userController = new UserController();
        $sub = $_GET['sub'] ?? 'index';
        
        switch ($sub) {
            case 'create':
                $userController->create();
                break;
            case 'edit':
                $userController->edit();
                break;
            case 'delete':
                $userController->delete();
                break;
            default:
                $userController->index();
                break;
        }
        break;

    case 'courses':
        $authController = new AuthController();
        $authController->requireLogin();
        $courseController = new CourseController();
        $sub = $_GET['sub'] ?? 'index';
        
        switch ($sub) {
            case 'create':
                $courseController->create();
                break;
            case 'edit':
                $courseController->edit();
                break;
            case 'delete':
                $courseController->delete();
                break;
            case 'checkDependencies':
                $courseController->checkDependencies();
                break;
            default:
                $courseController->index();
                break;
        }
        break;

    case 'versions':
        $authController = new AuthController();
        $authController->requireLogin();
        $versionController = new VersionController();
        $sub = $_GET['sub'] ?? 'index';
        
        switch ($sub) {
            case 'create':
                $versionController->create();
                break;
            case 'edit':
                $versionController->edit();
                break;
            case 'delete':
                $versionController->delete();
                break;
            case 'checkDependencies':
                $versionController->checkDependencies();
                break;
            default:
                $versionController->index();
                break;
        }
        break;

    case 'modules':
        $authController = new AuthController();
        $authController->requireLogin();
        $moduleController = new ModuleController();
        $sub = $_GET['sub'] ?? 'index';
        
        switch ($sub) {
            case 'create':
                $moduleController->create();
                break;
            case 'edit':
                $moduleController->edit();
                break;
            case 'delete':
                $moduleController->delete();
                break;
            case 'import':
                $moduleController->import();
                break;
            case 'getVersions':
                $moduleController->getVersionsByCourse();
                break;
            case 'downloadSample':
                $moduleController->downloadSample();
                break;
            default:
                $moduleController->index();
                break;
        }
        break;

    case 'batch':
        $authController = new AuthController();
        $authController->requireLogin();
        $batchController = new BatchController();
        $sub = $_GET['sub'] ?? 'index';
        
        switch ($sub) {
            case 'create':
                $batchController->create();
                break;
            case 'edit':
                $batchController->edit();
                break;
            case 'delete':
                $batchController->delete();
                break;
            case 'getVersions':
                $batchController->getVersionsByCourse();
                break;
            default:
                $batchController->index();
                break;
        }
        break;

    case 'students':
        $authController = new AuthController();
        $authController->requireLogin();
        $studentController = new StudentController();
        $sub = $_GET['sub'] ?? 'index';
        switch ($sub) {
            case 'create':
                $studentController->create();
                break;
            case 'edit':
                $studentController->edit();
                break;
            case 'delete':
                $studentController->delete();
                break;
            case 'import':
                $studentController->import();
                break;
            case 'getVersions':
                $studentController->getVersionsByCourse();
                break;
            case 'getBatches':
                $studentController->getBatchesByCourse();
                break;
            case 'getBatchesByVersion':
                $studentController->getBatchesByVersion();
                break;
            case 'getStudentsByBatch':
                $studentController->getStudentsByBatch();
                break;
            case 'downloadSample':
                $studentController->downloadSample();
                break;
            case 'search':
                $studentController->search();
                break;
            case 'getAll':
                $studentController->getAll();
                break;
            default:
                $studentController->index();
                break;
        }
        break;

    case 'exams':
        $authController = new AuthController();
        $authController->requireLogin();
        $examController = new ExamController();
        $sub = $_GET['sub'] ?? 'index';
        
        switch ($sub) {
            case 'create':
                $examController->create();
                break;
            case 'edit':
                $examController->edit();
                break;
            case 'delete':
                $examController->delete();
                break;
            case 'getModules':
                $examController->getModulesByCourse();
                break;
            case 'getVersions':
                $examController->getVersionsByCourse();
                break;
            case 'getSemesters':
                $examController->getSemestersByVersion();
                break;
            case 'getScheduleTable':
                $examController->getScheduleTable();
                break;
            case 'schedule':
                $examController->schedule();
                break;
            case 'export':
                $examController->exportToExcel();
                break;
            case 'downloadSchedule':
                $examController->downloadSchedule();
                break;
            case 'setResultedStatus':
                $examController->setResultedStatus();
                break;
            default:
                $examController->index();
                break;
        }
        break;

    case 'exam_results':
        $authController = new AuthController();
        $authController->requireLogin();
        $examResultController = new ExamResultController();
        $sub = $_GET['sub'] ?? 'index';
        
        switch ($sub) {
            case 'addStudents':
                $examResultController->addStudents();
                break;
            case 'edit':
                $examResultController->edit();
                break;
            case 'delete':
                $examResultController->delete();
                break;
            case 'updateMarks':
                $examResultController->updateMarks();
                break;
            case 'getStudentsByBatch':
                $examResultController->getStudentsByBatch();
                break;
            case 'downloadAttendance':
                $examResultController->downloadAttendance();
                break;
            case 'downloadMarking':
                $examResultController->downloadMarking();
                break;
            case 'view':
                $examResultController->view();
                break;
            case 'printReport':
                $examResultController->printReport();
                break;
            case 'printMarksSheets':
                $examResultController->printMarksSheets();
                break;
            case 'printTranscripts':
                $examResultController->printTranscripts();
                break;
            case 'admissionCards':
                $examResultController->admissionCards();
                break;
            case 'downloadAdmissionCard':
                $examResultController->downloadAdmissionCard();
                break;
            case 'marksSummary':
                $examResultController->marksSummary();
                break;
            case 'exportMarksSummary':
                $examResultController->exportMarksSummary();
                break;
            case 'printTranscriptsView':
                $examResultController->printTranscriptsView();
                break;
            default:
                $examResultController->index();
                break;
        }
        break;

    default:
        header("Location: index.php?action=login");
        exit();
}
?>

