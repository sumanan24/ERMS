<?php
session_start();
include('../includes/config.php');
require '../vendor/autoload.php'; // Load PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course = isset($_POST['course']) ? $_POST['course'] : null;
    $batch = isset($_POST['batch']) ? $_POST['batch'] : null;

    if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['name'] != '') {
        $file = $_FILES['excelFile']['tmp_name'];

        try {
            // Load the spreadsheet file
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); // Load as an associative array
            
            // Skip the header row
            $insertedCount = 0;

            foreach ($sheetData as $index => $row) {
                if ($index === 1) continue; // Skip header row (assumes it's the first row)
                
                $reg_no = isset($row['A']) ? trim($row['A']) : null;
                $fullname = isset($row['B']) ? trim($row['B']) : null;
                $nic = isset($row['C']) ? trim($row['C']) : null;

                if ($reg_no && $fullname && $nic) {
                    $sql = "INSERT INTO student (`reg_no`, `fullname`, `nic`, `cid`, `bid`) 
                            VALUES (:reg_no, :fullname, :nic, :course, :batch)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':reg_no', $reg_no, PDO::PARAM_STR);
                    $query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
                    $query->bindParam(':nic', $nic, PDO::PARAM_STR);
                    $query->bindParam(':course', $course, PDO::PARAM_INT);
                    $query->bindParam(':batch', $batch, PDO::PARAM_INT);
                    $query->execute();
                    $insertedCount++;
                }
            }

            $_SESSION['success'] = "$insertedCount students were successfully uploaded.";
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
