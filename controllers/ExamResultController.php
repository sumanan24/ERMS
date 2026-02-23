<?php
require_once __DIR__ . '/../models/ExamResult.php';
require_once __DIR__ . '/../models/Exam.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Batch.php';
require_once __DIR__ . '/../models/Version.php';

class ExamResultController {
    private $examResult;
    private $exam;
    private $student;
    private $course;
    private $batch;
    private $version;

    public function __construct() {
        $this->examResult = new ExamResult();
        $this->exam = new Exam();
        $this->student = new Student();
        $this->course = new Course();
        $this->batch = new Batch();
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
            header("Location: index.php?action=exam_results");
            exit();
        }
    }

    public function index() {
        $this->requireAdminOrTeacher();
        $filters = [
            'exam_id' => $_GET['filter_exam'] ?? '',
            'student_id' => $_GET['filter_student'] ?? '',
            'status' => $_GET['filter_status'] ?? '',
            'search' => $_GET['filter_search'] ?? '',
            'one_per_student' => true
        ];
        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);
        $results = $this->examResult->getAll($filters);
        $exams = $this->exam->getAll();
        require_once __DIR__ . '/../views/exam_results/index.php';
    }

    public function addStudents() {
        $this->requireAdminOrTeacher();
        $exam_id = $_GET['exam_id'] ?? 0;
        
        if (!$exam_id || !$this->exam->getById($exam_id)) {
            $_SESSION['error'] = "Exam not found.";
            header("Location: index.php?action=exam_results");
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student_ids = $_POST['student_ids'] ?? [];
            $eligibility = $_POST['eligibility'] ?? 'eligible';
            $attempt = (int)($_POST['attempt'] ?? 1);
            
            if (empty($student_ids)) {
                $_SESSION['error'] = "Please select at least one student.";
                header("Location: index.php?action=exam_results&sub=addStudents&exam_id=" . $exam_id);
                exit();
            }
            
            $added = 0;
            $skipped = 0;
            foreach ($student_ids as $student_id) {
                // Check if result already exists for this exam+student+attempt
                if ($this->examResult->exists($exam_id, $student_id, $attempt)) {
                    $skipped++;
                    continue; // Skip if already exists with same attempt
                }
                
                // If attempt > 1, check if previous attempts exist
                if ($attempt > 1) {
                    $hasPrevious = false;
                    for ($i = 1; $i < $attempt; $i++) {
                        if ($this->examResult->exists($exam_id, $student_id, $i)) {
                            $hasPrevious = true;
                            break;
                        }
                    }
                    if (!$hasPrevious) {
                        $skipped++;
                        continue; // Can't have attempt 2 without attempt 1
                    }
                }
                
                $this->examResult->exam_id = $exam_id;
                $this->examResult->student_id = $student_id;
                $this->examResult->eligibility = $eligibility;
                $this->examResult->attempt = $attempt;
                $this->examResult->assessment_marks = 0;
                $this->examResult->final_exam_marks = 0;
                $this->examResult->final_marks = 0;
                $this->examResult->status = 'absent';
                
                if ($this->examResult->create()) {
                    $added++;
                } else {
                    $skipped++;
                }
            }
            
            if ($added > 0) {
                $_SESSION['success'] = "Added {$added} student(s) to exam. " . ($skipped > 0 ? "{$skipped} skipped (already exists)." : "");
            } else {
                $_SESSION['error'] = "No students added. They may already be enrolled.";
            }
            header("Location: index.php?action=exam_results&filter_exam=" . $exam_id);
            exit();
        } else {
            // Load exam details
            if (!$this->exam->getById($exam_id)) {
                $_SESSION['error'] = "Exam not found.";
                header("Location: index.php?action=exams");
                exit();
            }
            $exam = $this->exam;
            
            // Get batches for the exam's course
            $batches = $this->batch->getAll(['course_id' => $exam->course_id]);
            
            require_once __DIR__ . '/../views/exam_results/add_students.php';
        }
    }

    public function edit() {
        $this->requireAdminOrTeacher();
        $id = $_GET['id'] ?? 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->examResult->getById($id)) {
                $_SESSION['error'] = "Result not found.";
                header("Location: index.php?action=exam_results");
                exit();
            }
            
            $this->examResult->eligibility = $_POST['eligibility'] ?? 'eligible';
            $this->examResult->student_offense = trim($_POST['student_offense'] ?? '');
            $this->examResult->attempt = (int)($_POST['attempt'] ?? 1);
            
            // Handle marks - if NE or not eligible or offense exists, set to 0
            $assessment_marks_input = $_POST['assessment_marks'] ?? '0';
            $final_exam_marks_input = $_POST['final_exam_marks'] ?? '0';
            
            if ($this->examResult->eligibility == 'not_eligible' || !empty($this->examResult->student_offense) || $assessment_marks_input === 'NE' || $final_exam_marks_input === 'NE') {
                $this->examResult->assessment_marks = 0;
                $this->examResult->final_exam_marks = 0;
            } else {
                $this->examResult->assessment_marks = (float)$assessment_marks_input;
                $this->examResult->final_exam_marks = (float)$final_exam_marks_input;
            }
            
            // Get percentages from exam
            require_once __DIR__ . '/../models/Exam.php';
            $exam = new Exam();
            if ($exam->getById($this->examResult->exam_id)) {
                $assessment_percentage = $exam->assessment_percentage ?? 0;
                $final_exam_percentage = $exam->final_exam_percentage ?? 0;
            } else {
                $assessment_percentage = 0;
                $final_exam_percentage = 0;
            }
            
            // Calculate final marks
            $this->examResult->final_marks = $this->examResult->assessment_marks + $this->examResult->final_exam_marks;
            
            // Calculate weighted percentage for status determination
            $assessment_contribution = ($this->examResult->assessment_marks * $assessment_percentage) / 100;
            $final_exam_contribution = ($this->examResult->final_exam_marks * $final_exam_percentage) / 100;
            $weighted_percentage = $assessment_contribution + $final_exam_contribution;
            
            // Calculate grade to determine status
            // Helper function to get grade
            $grade = '';
            if ($this->examResult->final_exam_marks < 40 && $weighted_percentage >= 40) {
                // Special rule: If final exam marks < 40 but total percentage >= 40, grade is C-
                $grade = 'C-';
            } elseif ($weighted_percentage >= 85) {
                $grade = 'A+';
            } elseif ($weighted_percentage >= 80) {
                $grade = 'A';
            } elseif ($weighted_percentage >= 75) {
                $grade = 'A-';
            } elseif ($weighted_percentage >= 70) {
                $grade = 'B+';
            } elseif ($weighted_percentage >= 65) {
                $grade = 'B';
            } elseif ($weighted_percentage >= 60) {
                $grade = 'B-';
            } elseif ($weighted_percentage >= 50) {
                $grade = 'C+';
            } elseif ($weighted_percentage >= 40) {
                $grade = 'C';
            } elseif ($weighted_percentage >= 30) {
                $grade = 'C-';
            } elseif ($weighted_percentage >= 20) {
                $grade = 'D';
            } else {
                $grade = 'F';
            }
            
            // Determine status: 
            // - If student has offense or not eligible, status is fail
            // - If grade is C- or below (C-, D, F), status is fail
            // - If grade is C or above, status is pass
            // - If both marks are 0 and no offense, status is absent
            if (!empty($this->examResult->student_offense) || $this->examResult->eligibility == 'not_eligible') {
                $this->examResult->status = 'fail';
            } elseif ($this->examResult->final_exam_marks == 0 && $this->examResult->assessment_marks == 0) {
                $this->examResult->status = 'absent';
            } elseif (in_array($grade, ['C-', 'D', 'F'])) {
                $this->examResult->status = 'fail';
            } else {
                $this->examResult->status = 'pass';
            }
            
            if ($this->examResult->update()) {
                $_SESSION['success'] = "Result updated successfully.";
                $exam_id = $this->examResult->exam_id ?? 0;
                header("Location: index.php?action=exam_results&filter_exam=" . (int)$exam_id);
                exit();
            }
            $_SESSION['error'] = "Failed to update result.";
            header("Location: index.php?action=exam_results&sub=edit&id=" . $id);
            exit();
        } else {
            if ($this->examResult->getById($id)) {
                $result = $this->examResult;
                // Get exam to retrieve percentages
                require_once __DIR__ . '/../models/Exam.php';
                $exam = new Exam();
                $examPercentages = ['assessment_percentage' => 0, 'final_exam_percentage' => 0];
                if ($exam->getById($result->exam_id)) {
                    $examPercentages['assessment_percentage'] = $exam->assessment_percentage ?? 0;
                    $examPercentages['final_exam_percentage'] = $exam->final_exam_percentage ?? 0;
                }
                require_once __DIR__ . '/../views/exam_results/edit.php';
            } else {
                $_SESSION['error'] = "Result not found.";
                header("Location: index.php?action=exam_results");
                exit();
            }
        }
    }

    public function delete() {
        $this->requireAdmin();
        $id = $_GET['id'] ?? 0;
        if ($this->examResult->delete($id)) {
            $_SESSION['success'] = "Result deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete result.";
        }
        header("Location: index.php?action=exam_results");
        exit();
    }

    public function updateMarks() {
        $this->requireAdminOrTeacher();
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit();
        }
        
        $result_id = $_POST['result_id'] ?? 0;
        $assessment_marks = (float)($_POST['assessment_marks'] ?? 0);
        $final_exam_marks = (float)($_POST['final_exam_marks'] ?? 0);
        $final_marks = (float)($_POST['final_marks'] ?? 0);
        $status = $_POST['status'] ?? 'absent';
        
        // Validate marks are within 0-100 range
        if ($assessment_marks < 0 || $assessment_marks > 100 || $final_exam_marks < 0 || $final_exam_marks > 100) {
            echo json_encode(['success' => false, 'error' => 'Marks must be between 0 and 100']);
            exit();
        }
        
        if (!$this->examResult->getById($result_id)) {
            echo json_encode(['success' => false, 'error' => 'Result not found']);
            exit();
        }
        
        // Update marks and status
        $this->examResult->assessment_marks = $assessment_marks;
        $this->examResult->final_exam_marks = $final_exam_marks;
        $this->examResult->final_marks = $final_marks;
        $this->examResult->status = $status;
        
        if ($this->examResult->update()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update result']);
        }
        exit();
    }

    public function getStudentsByBatch() {
        $this->requireAdminOrTeacher();
        $batch_id = $_GET['batch_id'] ?? 0;
        $exam_id = $_GET['exam_id'] ?? 0;
        
        if ($batch_id > 0) {
            $students = $this->student->getAll(['batch_id' => $batch_id]);
            
            // Filter out students already allocated to this exam
            if ($exam_id > 0) {
                $existingResults = $this->examResult->getByExam($exam_id);
                $existingStudentIds = array_column($existingResults, 'student_id');
                
                $students = array_filter($students, function($student) use ($existingStudentIds) {
                    return !in_array($student['id'], $existingStudentIds);
                });
                $students = array_values($students); // Re-index array
            }
            
            header('Content-Type: application/json');
            echo json_encode($students);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
        exit();
    }

    public function downloadAttendance() {
        $this->requireAdminOrTeacher();
        $exam_id = $_GET['exam_id'] ?? 0;
        
        if (!$exam_id || !$this->exam->getById($exam_id)) {
            $_SESSION['error'] = "Exam not found.";
            header("Location: index.php?action=exam_results");
            exit();
        }
        
        $exam = $this->exam;
        $results = $this->examResult->getByExam($exam_id);
        
        // Get module and course information
        require_once __DIR__ . '/../models/Module.php';
        require_once __DIR__ . '/../models/Course.php';
        $module = new Module();
        $course = new Course();
        
        $moduleName = '';
        $moduleCode = '';
        $courseName = '';
        
        if ($module->getModuleById($exam->module_id)) {
            $moduleName = $module->mname;
            $moduleCode = $module->mcode;
        }
        
        if ($course->getCourseById($exam->course_id)) {
            $courseName = $course->cname;
        }
        
        // Separate regular students (attempt = 1) from repeaters (attempt > 1)
        $regularStudents = [];
        $repeaters = [];
        
        foreach ($results as $result) {
            if ($result['attempt'] > 1) {
                $repeaters[] = $result;
            } else {
                $regularStudents[] = $result;
            }
        }
        
        // Get exam period from date (e.g., "Dec-2025" from "2025-12-22")
        $examPeriod = '';
        if ($exam->exam_date) {
            $dateObj = new DateTime($exam->exam_date);
            $examPeriod = $dateObj->format('M-Y'); // e.g., "Dec-2025"
        }
        
        // Clean output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Output HTML attendance sheet as Excel file
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="attendance_sheet_exam_' . $exam_id . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 0;
            font-size: 11pt;
            width: 100%;
        }
        .container {
            width: 100%;
            max-width: 21cm;
            margin: 0 auto;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 3px 0;
        }
        .header p {
            font-size: 11pt;
            margin: 2px 0;
        }
        .exam-info {
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .exam-info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }
        .exam-info-left {
            display: table-cell;
            text-align: left;
            width: 50%;
            padding-right: 10px;
        }
        .exam-info-right {
            display: table-cell;
            text-align: right;
            width: 50%;
            padding-left: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 10pt;
        }
        .sno-col {
            width: 6%;
            text-align: center;
        }
        .name-col {
            width: 40%;
        }
        .reg-col {
            width: 25%;
        }
        .dm01-col {
            width: 29%;
            text-align: center;
        }
        .repeaters-header {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 12px 0 8px 0;
        }
        .supervisor-section {
            margin-top: 20px;
            font-size: 10pt;
        }
        .supervisor-line {
            border-bottom: 1px dotted #000;
            margin: 15px 0 5px 0;
            min-height: 18px;
            width: 100%;
        }
        .supervisor-label {
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="header">
        <h1>UNIVERSITY COLLEGE OF JAFFNA</h1>
        <p>Foundation</p>
        <p>End Examination-<?php echo $examPeriod; ?></p>
        <p><strong>Attendance Sheet</strong></p>
    </div>
    
    <div class="exam-info">
        <div class="exam-info-row">
            <div class="exam-info-left"><strong>Course:</strong> <?php echo htmlspecialchars($courseName); ?></div>
            <div class="exam-info-right"></div>
        </div>
        <?php if (!empty($moduleCode) || !empty($moduleName)): ?>
        <div class="exam-info-row">
            <div class="exam-info-left"><strong>Module Code:</strong> <?php echo htmlspecialchars($moduleCode); ?> | <strong>Module Name:</strong> <?php echo htmlspecialchars($moduleName); ?></div>
            <div class="exam-info-right"></div>
        </div>
        <?php endif; ?>
        <div class="exam-info-row">
            <div class="exam-info-left"><strong>Date:</strong> .................................</div>
            <div class="exam-info-right"><strong>Time:</strong> .................................</div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="sno-col">S.No</th>
                <th class="name-col">Name</th>
                <th class="reg-col">Registration No</th>
                <th class="dm01-col">Signature</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sno = 1;
            foreach ($regularStudents as $result): 
                $dm01 = '';
                if ($result['eligibility'] == 'not_eligible' || !empty($result['student_offense'])) {
                    $dm01 = 'NE';
                }
            ?>
            <tr>
                <td class="sno-col"><?php echo $sno++; ?></td>
                <td class="name-col"><?php echo htmlspecialchars($result['student_name']); ?></td>
                <td class="reg-col"><?php echo htmlspecialchars($result['reg_no']); ?></td>
                <td class="dm01-col"><?php echo htmlspecialchars($dm01); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (!empty($repeaters)): ?>
    <div class="repeaters-header">Repeaters</div>
    <table>
        <thead>
            <tr>
                <th class="sno-col">S.No</th>
                <th class="name-col">Name</th>
                <th class="reg-col">Registration No</th>
                <th class="dm01-col">Signature</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($repeaters as $result): 
                $dm01 = '';
                if ($result['eligibility'] == 'not_eligible' || !empty($result['student_offense'])) {
                    $dm01 = 'NE';
                }
            ?>
            <tr>
                <td class="sno-col"><?php echo $sno++; ?></td>
                <td class="name-col"><?php echo htmlspecialchars($result['student_name']); ?></td>
                <td class="reg-col"><?php echo htmlspecialchars($result['reg_no']); ?></td>
                <td class="dm01-col"><?php echo htmlspecialchars($dm01); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    
    <div class="supervisor-section">
        <div class="supervisor-line"></div>
        <div class="supervisor-label">Name of the Supervisor</div>
        <div class="supervisor-line" style="margin-top: 30px;"></div>
        <div class="supervisor-label">Supervisor's Signature</div>
    </div>
    </div>
</body>
</html>
        <?php
        exit();
    }

    public function downloadMarking() {
        $this->requireAdminOrTeacher();
        $exam_id = $_GET['exam_id'] ?? 0;
        $marking_type = $_GET['type'] ?? 'first'; // 'first' or 'second'
        
        if (!$exam_id || !$this->exam->getById($exam_id)) {
            $_SESSION['error'] = "Exam not found.";
            header("Location: index.php?action=exam_results");
            exit();
        }
        
        $exam = $this->exam;
        $results = $this->examResult->getByExam($exam_id);
        
        // Get module and course information
        require_once __DIR__ . '/../models/Module.php';
        require_once __DIR__ . '/../models/Course.php';
        $module = new Module();
        $course = new Course();
        
        $moduleName = '';
        $moduleCode = '';
        $courseName = '';
        
        if ($module->getModuleById($exam->module_id)) {
            $moduleName = $module->mname;
            $moduleCode = $module->mcode;
        }
        
        if ($course->getCourseById($exam->course_id)) {
            $courseName = $course->cname;
        }
        
        // Separate regular students (attempt = 1) from repeaters (attempt > 1)
        $regularStudents = [];
        $repeaters = [];
        
        foreach ($results as $result) {
            if ($result['attempt'] > 1) {
                $repeaters[] = $result;
            } else {
                $regularStudents[] = $result;
            }
        }
        
        // Get exam period from date (e.g., "Dec-2025" from "2025-12-22")
        $examPeriod = '';
        if ($exam->exam_date) {
            $dateObj = new DateTime($exam->exam_date);
            $examPeriod = $dateObj->format('M-Y'); // e.g., "Dec-2025"
        }
        
        // Clean output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Output HTML marking sheet as Excel file
        $markingNumber = ($marking_type == 'first' ? '1st' : '2nd');
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $markingNumber . '_marking_exam_' . $exam_id . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $markingNumber; ?> Marking Sheet</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 0;
            font-size: 11pt;
            width: 100%;
        }
        .container {
            width: 100%;
            max-width: 21cm;
            margin: 0 auto;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .exam-info {
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .exam-info-row {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10pt;
        }
        .no-col {
            width: 5%;
        }
        .reg-col {
            width: 18%;
        }
        .part1-col {
            width: 10%;
        }
        .part2-col {
            width: 8%;
        }
        .total-col {
            width: 12%;
        }
        .repeaters-header {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 12px 0 8px 0;
        }
        .footer-section {
            margin-top: 20px;
            font-size: 10pt;
        }
        .footer-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .footer-left {
            display: table-cell;
            text-align: left;
            width: 50%;
            padding-right: 10px;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
            width: 50%;
            padding-left: 10px;
        }
        .footer-line {
            border-bottom: 1px dotted #000;
            margin: 5px 0;
            min-height: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="header">
        <h1>University College of Jaffna</h1>
        <h2>End Examination-<?php echo $examPeriod; ?></h2>
        <h2><?php echo $markingNumber; ?> Marking</h2>
    </div>
    
    <div class="exam-info">
        <div class="exam-info-row"><strong>Level and Semester:</strong> Foundation</div>
        <div class="exam-info-row"><strong>Module Name & No:</strong> <?php echo htmlspecialchars($moduleName); ?> (<?php echo htmlspecialchars($moduleCode); ?>)</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th class="reg-col">Registration No</th>
                <th class="part1-col">Part I</th>
                <th colspan="5" class="part2-col">Part II</th>
                <th class="total-col">Total for End examination</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th class="part2-col">Q1</th>
                <th class="part2-col">Q2</th>
                <th class="part2-col">Q3</th>
                <th class="part2-col">Q4</th>
                <th class="part2-col">Q5</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sno = 1;
            foreach ($regularStudents as $result): 
                $part1 = '';
                $q1 = '';
                $q2 = '';
                $q3 = '';
                $q4 = '';
                $q5 = '';
                $total = '';
                
                // Show NE if not eligible or has offense
                if ($result['eligibility'] == 'not_eligible' || !empty($result['student_offense'])) {
                    $part1 = 'NE';
                    $q1 = 'NE';
                    $q2 = 'NE';
                    $q3 = 'NE';
                    $q4 = 'NE';
                    $q5 = 'NE';
                    $total = 'NE';
                }
            ?>
            <tr>
                <td class="no-col"><?php echo $sno++; ?></td>
                <td class="reg-col"><?php echo htmlspecialchars($result['reg_no']); ?></td>
                <td class="part1-col"><?php echo htmlspecialchars($part1); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q1); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q2); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q3); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q4); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q5); ?></td>
                <td class="total-col"><?php echo htmlspecialchars($total); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (!empty($repeaters)): ?>
    <div class="repeaters-header">Repeaters</div>
    <table>
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th class="reg-col">Registration No</th>
                <th class="part1-col">Part I</th>
                <th colspan="5" class="part2-col">Part II</th>
                <th class="total-col">Total for End examination</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th class="part2-col">Q1</th>
                <th class="part2-col">Q2</th>
                <th class="part2-col">Q3</th>
                <th class="part2-col">Q4</th>
                <th class="part2-col">Q5</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($repeaters as $result): 
                $part1 = '';
                $q1 = '';
                $q2 = '';
                $q3 = '';
                $q4 = '';
                $q5 = '';
                $total = '';
                
                // Show NE if not eligible or has offense
                if ($result['eligibility'] == 'not_eligible' || !empty($result['student_offense'])) {
                    $part1 = 'NE';
                    $q1 = 'NE';
                    $q2 = 'NE';
                    $q3 = 'NE';
                    $q4 = 'NE';
                    $q5 = 'NE';
                    $total = 'NE';
                }
            ?>
            <tr>
                <td class="no-col"><?php echo $sno++; ?></td>
                <td class="reg-col"><?php echo htmlspecialchars($result['reg_no']); ?></td>
                <td class="part1-col"><?php echo htmlspecialchars($part1); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q1); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q2); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q3); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q4); ?></td>
                <td class="part2-col"><?php echo htmlspecialchars($q5); ?></td>
                <td class="total-col"><?php echo htmlspecialchars($total); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    
    <div class="footer-section">
        <div class="footer-row">
            <div class="footer-left">
                <div><strong>Name of the Examiner:</strong></div>
                <div class="footer-line"></div>
            </div>
            <div class="footer-right">
                <div><strong>Date</strong></div>
                <div class="footer-line"></div>
            </div>
        </div>
        <div class="footer-row">
            <div class="footer-left">
                <div><strong>Signature</strong></div>
                <div class="footer-line"></div>
            </div>
        </div>
        <div class="footer-row">
            <div class="footer-left">
                <div><strong>Head of the Department</strong></div>
                <div class="footer-line"></div>
            </div>
            <div class="footer-right">
                <div><strong>Date</strong></div>
                <div class="footer-line"></div>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
        <?php
        exit();
    }

    public function view() {
        $this->requireAdminOrTeacher();
        $exam_id = $_GET['exam_id'] ?? 0;
        
        if (!$exam_id || !$this->exam->getById($exam_id)) {
            $_SESSION['error'] = "Exam not found.";
            header("Location: index.php?action=exam_results");
            exit();
        }
        
        $results = $this->examResult->getByExam($exam_id);
        $exam = $this->exam;
        
        require_once __DIR__ . '/../views/exam_results/view.php';
    }

    public function printReport() {
        $this->requireAdminOrTeacher();
        $exam_id = $_GET['exam_id'] ?? 0;
        
        if (!$exam_id || !$this->exam->getById($exam_id)) {
            $_SESSION['error'] = "Exam not found.";
            header("Location: index.php?action=exam_results");
            exit();
        }
        
        $results = $this->examResult->getByExam($exam_id);
        $exam = $this->exam;
        
        require_once __DIR__ . '/../views/exam_results/print_report.php';
    }

    public function printMarksSheets() {
        $this->requireAdminOrTeacher();
        $exam_id = $_GET['exam_id'] ?? 0;
        
        if (!$exam_id || !$this->exam->getById($exam_id)) {
            $_SESSION['error'] = "Exam not found.";
            header("Location: index.php?action=exam_results");
            exit();
        }
        
        $results = $this->examResult->getByExam($exam_id);
        $exam = $this->exam;
        
        require_once __DIR__ . '/../views/exam_results/print_marks_sheets.php';
    }

    public function printTranscripts() {
        $this->requireAdminOrTeacher();
        require_once __DIR__ . '/../views/exam_results/print_transcripts.php';
    }

    /**
     * Admission cards form: select date range and individual student or batch.
     */
    public function admissionCards() {
        $this->requireAdminOrTeacher();
        $courses = $this->course->getAllCourses();
        require_once __DIR__ . '/../views/exam_results/admission_cards.php';
    }

    /**
     * Download admission card(s): only exams between date_from and date_to that the student is facing.
     * Individual: one student. Batch: all students in course/version/batch who have exams in range.
     */
    public function downloadAdmissionCard() {
        $this->requireAdminOrTeacher();
        $date_from = $_GET['date_from'] ?? '';
        $date_to   = $_GET['date_to'] ?? '';
        $mode      = $_GET['mode'] ?? 'individual';
        if (empty($date_from) || empty($date_to)) {
            $_SESSION['error'] = "Please select exam date range (From and To).";
            header("Location: index.php?action=exam_results&sub=admissionCards");
            exit();
        }
        $students = [];
        if ($mode === 'individual') {
            $student_id = (int)($_GET['student_id'] ?? 0);
            if (!$student_id || !$this->student->getById($student_id)) {
                $_SESSION['error'] = "Student not found.";
                header("Location: index.php?action=exam_results&sub=admissionCards");
                exit();
            }
            $students = [['id' => $this->student->id, 'reg_no' => $this->student->reg_no, 'fullname' => $this->student->fullname]];
        } else {
            $course_id  = (int)($_GET['course_id'] ?? 0);
            $version_id = (int)($_GET['version_id'] ?? 0);
            $batch_id   = (int)($_GET['batch_id'] ?? 0);
            if (!$course_id || !$version_id || !$batch_id) {
                $_SESSION['error'] = "Please select Course, Version and Batch for batch download.";
                header("Location: index.php?action=exam_results&sub=admissionCards");
                exit();
            }
            $allInBatch = $this->student->getAll(['course_id' => $course_id, 'version_id' => $version_id, 'batch_id' => $batch_id]);
            $studentIdsInRange = $this->examResult->getStudentIdsWithExamsInDateRange($date_from, $date_to);
            $studentIdsInRange = array_flip($studentIdsInRange);
            foreach ($allInBatch as $s) {
                if (isset($studentIdsInRange[$s['id']])) {
                    $students[] = ['id' => $s['id'], 'reg_no' => $s['reg_no'], 'fullname' => $s['fullname']];
                }
            }
        }
        if (empty($students)) {
            $_SESSION['error'] = "No students found with exams in the selected date range.";
            header("Location: index.php?action=exam_results&sub=admissionCards");
            exit();
        }
        $cards = [];
        foreach ($students as $s) {
            $exams = $this->examResult->getExamsForStudentInDateRange($s['id'], $date_from, $date_to);
            if (!empty($exams)) {
                $cards[] = ['student' => $s, 'exams' => $exams];
            }
        }
        if (empty($cards)) {
            $_SESSION['error'] = "No exams found for the selected students in the selected date range.";
            header("Location: index.php?action=exam_results&sub=admissionCards");
            exit();
        }
        $collegeName = "University College of Jaffna, University of Vocational Technology";
        $examTitle   = "End Examination";
        $periodLabel = date('M/Y', strtotime($date_from)) . ' - ' . date('M/Y', strtotime($date_to));
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="admission_cards.html"');
        if (count($cards) > 1) {
            header('Content-Disposition: attachment; filename="admission_cards_' . $date_from . '_to_' . $date_to . '.html"');
        }
        require_once __DIR__ . '/../views/exam_results/admission_card_print.php';
        exit();
    }

    /**
     * Students marks summary: select course, semester, batch; export Excel with 2 tabs (Current batch, Repeaters).
     */
    public function marksSummary() {
        $this->requireAdminOrTeacher();
        $courses = $this->course->getAllCourses();
        require_once __DIR__ . '/../views/exam_results/marks_summary.php';
    }

    /**
     * Export marks summary as Excel (CSV with two sections: Current Batch, Repeaters).
     */
    public function exportMarksSummary() {
        $this->requireAdminOrTeacher();
        $course_id  = (int)($_GET['course_id'] ?? 0);
        $version_id = (int)($_GET['version_id'] ?? 0);
        $semester   = trim($_GET['semester'] ?? '');
        $batch_id   = (int)($_GET['batch_id'] ?? 0);
        if (!$course_id || !$version_id || !$batch_id) {
            $_SESSION['error'] = "Please select Course, Version and Batch.";
            header("Location: index.php?action=exam_results&sub=marksSummary");
            exit();
        }
        $modules = $this->exam->getExamsByCourseVersionSemester($course_id, $version_id, $semester);
        if (empty($modules)) {
            $_SESSION['error'] = "No exams found for the selected course, version and semester.";
            header("Location: index.php?action=exam_results&sub=marksSummary");
            exit();
        }
        $exam_ids = array_column($modules, 'exam_id');
        $results = $this->examResult->getResultsForMarksSummary($batch_id, $exam_ids);
        $studentsInBatch = $this->student->getAll(['course_id' => $course_id, 'version_id' => $version_id, 'batch_id' => $batch_id]);
        $studentIndex = [];
        foreach ($studentsInBatch as $s) {
            $studentIndex[$s['id']] = ['reg_no' => $s['reg_no'], 'fullname' => $s['fullname']];
        }
        // Build first attempt (attempt=1) per student per exam -> current batch
        $currentByStudent = [];
        $latestByStudent = [];
        foreach ($results as $r) {
            $sid = $r['student_id'];
            $eid = $r['exam_id'];
            if (!isset($latestByStudent[$sid][$eid]) || $r['attempt'] > ($latestByStudent[$sid][$eid]['attempt'] ?? 0)) {
                $latestByStudent[$sid][$eid] = $r;
            }
            if ((int)($r['attempt']) === 1) {
                $currentByStudent[$sid][$eid] = $r;
            }
        }
        $repeaterIds = [];
        foreach ($latestByStudent as $sid => $byExam) {
            foreach ($byExam as $r) {
                if ((int)($r['attempt']) > 1) {
                    $repeaterIds[$sid] = true;
                    break;
                }
            }
        }
        $courseName = '';
        if ($this->course->getCourseById($course_id)) $courseName = $this->course->cname ?? '';
        $batchNo = '';
        $this->batch->getById($batch_id);
        if ($this->batch->batch_no) $batchNo = $this->batch->batch_no;
        $summary = [
            'modules' => $modules,
            'exam_ids' => $exam_ids,
            'studentsInBatch' => $studentsInBatch,
            'studentIndex' => $studentIndex,
            'currentByStudent' => $currentByStudent,
            'latestByStudent' => $latestByStudent,
            'repeaterIds' => array_keys($repeaterIds),
            'courseName' => $courseName,
            'batchNo' => $batchNo,
            'semester' => $semester,
        ];
        $this->outputMarksSummaryCsv($summary);
        exit();
    }

    private function outputMarksSummaryCsv($summary) {
        $modules = $summary['modules'];
        $exam_ids = $summary['exam_ids'];
        $studentsInBatch = $summary['studentsInBatch'];
        $currentByStudent = $summary['currentByStudent'];
        $latestByStudent = $summary['latestByStudent'];
        $repeaterIds = array_flip($summary['repeaterIds']);
        $courseName = $summary['courseName'];
        $batchNo = $summary['batchNo'];
        $semester = $summary['semester'];
        $filename = 'marks_summary_' . preg_replace('/[^a-z0-9_-]/i', '_', $courseName) . '_' . $batchNo . '_' . date('Y-m-d') . '.csv';
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Students Marks Summary - ' . $courseName . ' - Batch ' . $batchNo . ($semester ? ' - Semester ' . $semester : '')]);
        fputcsv($out, []);
        $headerRow = ['No', 'Name of the Students', 'Registration No'];
        foreach ($modules as $m) {
            $label = $m['module_name'] . ' ' . $m['module_code'] . ' (' . ($m['credit'] ?? '') . ')';
            $headerRow[] = $label;
            for ($i = 0; $i < 5; $i++) $headerRow[] = '';
        }
        fputcsv($out, $headerRow);
        $subHeader = ['', '', ''];
        foreach ($modules as $m) {
            $subHeader = array_merge($subHeader, [
                'Assessment Marks',
                'Assessment Percentage (from Assessment %)',
                'Final Exam Marks',
                'Final Exam Percentage (from Final Exam %)',
                'Total Percentage',
                'Grade'
            ]);
        }
        fputcsv($out, $subHeader);
        $no = 0;
        foreach ($studentsInBatch as $s) {
            $no++;
            $row = [$no, $s['fullname'], $s['reg_no']];
            foreach ($modules as $m) {
                $eid = $m['exam_id'];
                $r = $currentByStudent[$s['id']][$eid] ?? null;
                $row = array_merge($row, $this->formatResultCells($r, $m));
            }
            fputcsv($out, $row);
        }
        fputcsv($out, []);
        fputcsv($out, ['Repeaters']);
        fputcsv($out, $headerRow);
        fputcsv($out, $subHeader);
        $no = 0;
        foreach ($studentsInBatch as $s) {
            if (!isset($repeaterIds[$s['id']])) continue;
            $no++;
            $row = [$no, $s['fullname'], $s['reg_no']];
            foreach ($modules as $m) {
                $eid = $m['exam_id'];
                $r = $latestByStudent[$s['id']][$eid] ?? null;
                $row = array_merge($row, $this->formatResultCells($r, $m));
            }
            fputcsv($out, $row);
        }
        fclose($out);
        exit();
    }

    private function formatResultCells($r, $module) {
        if (!$r) return ['', '', '', '', '', ''];
        $assessPct = (float)($r['assessment_percentage'] ?? 0);
        $finalPct = (float)($r['final_exam_percentage'] ?? 0);
        $assessMarks = (float)($r['assessment_marks'] ?? 0);
        $finalMarks = (float)($r['final_exam_marks'] ?? 0);
        $isNe = ($r['eligibility'] ?? '') === 'not_eligible' || !empty($r['student_offense']);
        if ($isNe) {
            return ['NE', 'NE', 'NE', 'NE', 'NE', 'NE'];
        }
        $assessContrib = $assessPct ? ($assessMarks * $assessPct / 100) : 0;
        $finalContrib = $finalPct ? ($finalMarks * $finalPct / 100) : 0;
        $totalPct = $assessContrib + $finalContrib;
        $grade = $this->getGradeFromPercentage($totalPct, $finalMarks);
        if (($r['status'] ?? '') === 'absent') {
            return ['', '', '', '', '', '-'];
        }
        return [
            number_format($assessMarks, 2),
            number_format($assessContrib, 2),
            number_format($finalMarks, 2),
            number_format($finalContrib, 2),
            number_format($totalPct, 2),
            $grade,
        ];
    }

    private function getGradeFromPercentage($percentage, $final_exam_marks = null) {
        if ($final_exam_marks !== null && $final_exam_marks < 40 && $percentage >= 40) return 'C-';
        if ($percentage >= 85) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 75) return 'A-';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 65) return 'B';
        if ($percentage >= 60) return 'B-';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        if ($percentage >= 30) return 'C-';
        if ($percentage >= 20) return 'D';
        return 'E';
    }

    public function printTranscriptsView() {
        $this->requireAdminOrTeacher();
        
        // Get all students with their exam results
        $filters = [];
        if (!empty($_GET['student_id'])) {
            $filters['student_id'] = $_GET['student_id'];
        }
        if (!empty($_GET['course_id'])) {
            $filters['course_id'] = $_GET['course_id'];
        }
        
        $results = $this->examResult->getAll($filters);
        
        // Group results by student
        $studentResults = [];
        foreach($results as $result) {
            $studentId = $result['student_id'];
            if (!isset($studentResults[$studentId])) {
                $studentResults[$studentId] = [
                    'student' => [
                        'id' => $result['student_id'],
                        'reg_no' => $result['reg_no'],
                        'name' => $result['student_name']
                    ],
                    'results' => []
                ];
            }
            $studentResults[$studentId]['results'][] = $result;
        }
        
        require_once __DIR__ . '/../views/exam_results/print_transcripts_view.php';
    }

    public function studentResultLookup() {
        $message = '';
        $student = null;
        $results = [];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $search = trim($_POST['search'] ?? '');
            
            if (empty($search)) {
                $message = "Please enter your Registration Number or NIC.";
            } else {
                if ($this->student->findByNicOrRegNo($search)) {
                    $student = $this->student;
                    $results = $this->examResult->getAll(['student_id' => $student->id]);
                } else {
                    $message = "No student found with the provided Registration Number or NIC.";
                }
            }
        }
        
        require_once __DIR__ . '/../views/student_result_lookup.php';
    }
}
?>
