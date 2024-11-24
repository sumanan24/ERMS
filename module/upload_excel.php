<?php
session_start();
include('../includes/config.php');
require '../vendor/autoload.php'; // Load PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $departmentId = $_POST['department_id'];
    $courseId = $_POST['course_id'];

    if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['name'] != '') {
        $file = $_FILES['excelFile']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // Skip the header row
            $insertedCount = 0;
            for ($i = 1; $i < count($sheetData); $i++) {
                $row = $sheetData[$i];
                $mcode = $row[0]; // Module Code
                $mname = $row[1]; // Module Name
                $semester = $row[2]; // Semester

                if ($mcode && $mname && $semester) {
                    $sql = "INSERT INTO module (mcode, mname, cid, semester) VALUES (:mcode, :mname, :cid, :semester)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':mcode', $mcode, PDO::PARAM_STR);
                    $query->bindParam(':mname', $mname, PDO::PARAM_STR);
                    $query->bindParam(':cid', $courseId, PDO::PARAM_INT);
                    $query->bindParam(':semester', $semester, PDO::PARAM_INT);
                    $query->execute();
                    $insertedCount++;
                }
            }

            $_SESSION['success'] = "$insertedCount modules were successfully uploaded.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error processing the Excel file: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "No file selected.";
    }

    header("Location: new.php");
    exit;
}
?>
