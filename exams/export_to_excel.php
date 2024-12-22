<?php
session_start();
include('../includes/config.php');

// Include PhpSpreadsheet
require '../vendor/autoload.php'; // Make sure to set the correct path to PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_POST['export_excel'])) {
    // Get course and batch details from POST request
    $courseId = $_POST['course'];
    $batchId = $_POST['batch'];

    // Fetch all modules for the selected course
    $modulesSql = "SELECT * FROM module WHERE cid = :courseId";
    $modulesQuery = $dbh->prepare($modulesSql);
    $modulesQuery->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $modulesQuery->execute();
    $modules = $modulesQuery->fetchAll(PDO::FETCH_OBJ);

    // Fetch all students in the selected batch and course
    $studentsSql = "SELECT id, reg_no, fullname FROM student WHERE cid = :courseId AND bid = :batchId";
    $studentsQuery = $dbh->prepare($studentsSql);
    $studentsQuery->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $studentsQuery->bindParam(':batchId', $batchId, PDO::PARAM_INT);
    $studentsQuery->execute();
    $students = $studentsQuery->fetchAll(PDO::FETCH_OBJ);

    // Fetch marks for all students in the selected course and batch
    $marksSql = "SELECT sm.studentid, m.id AS module_id, sm.marks 
                 FROM results sm 
                 JOIN exam e ON e.id = sm.examid
                 JOIN module m ON m.id = e.mid
                 WHERE sm.studentid IN (SELECT id FROM student WHERE cid = :courseId AND bid = :batchId)";
    $marksQuery = $dbh->prepare($marksSql);
    $marksQuery->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $marksQuery->bindParam(':batchId', $batchId, PDO::PARAM_INT);
    $marksQuery->execute();
    $marksData = $marksQuery->fetchAll(PDO::FETCH_OBJ);

    // Prepare marks for the table (Student -> Module -> Marks)
    $marksByStudent = [];
    foreach ($marksData as $mark) {
        $marksByStudent[$mark->studentid][$mark->module_id] = $mark->marks;
    }

    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set header row
    $sheet->setCellValue('A1', 'Reg No');
    $sheet->setCellValue('B1', 'Full Name');

    // Add module names as column headers
    $column = 3; // Start from column C
    foreach ($modules as $module) {
        $sheet->setCellValueByColumnAndRow($column, 1, $module->mname);
        $column++;
    }

    // Add student data to the sheet
    $row = 2; // Start from the second row
    foreach ($students as $student) {
        $sheet->setCellValue('A' . $row, $student->reg_no);
        $sheet->setCellValue('B' . $row, $student->fullname);

        // Fill marks for each module
        $column = 3; // Start from column C
        foreach ($modules as $module) {
            $mark = isset($marksByStudent[$student->id][$module->id]) ? $marksByStudent[$student->id][$module->id] : 'N/A';
            $sheet->setCellValueByColumnAndRow($column, $row, $mark);
            $column++;
        }
        $row++;
    }

    // Create Excel writer
    $writer = new Xlsx($spreadsheet);

    // Set header for downloading the Excel file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="course_batch_marks.xlsx"');
    header('Cache-Control: max-age=0');

    // Save the file to output
    $writer->save('php://output');
    exit;
}
