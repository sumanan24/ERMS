<?php
session_start();
error_reporting(0);
include('../includes/config.php');
if (strlen($_SESSION['alogin']) == 0) {
    header("Location: ../index.php");
    exit;
}
$studentId = isset($_GET['studentid']) ? intval($_GET['studentid']) : 0;
$errorMsg = '';
$searchKey = '';

// Handle search submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchKey = trim($_POST['searchKey']);
    if ($searchKey !== '') {
        $q = $dbh->prepare("SELECT id FROM student WHERE nic = :k OR reg_no = :k LIMIT 1");
        $q->bindParam(':k', $searchKey, PDO::PARAM_STR);
        $q->execute();
        $found = $q->fetch(PDO::FETCH_OBJ);
        if ($found && isset($found->id)) {
            header("Location: transcript.php?studentid=" . intval($found->id));
            exit;
        } else {
            $errorMsg = 'No student found for the provided NIC or Registration Number.';
        }
    }
}
if ($studentId <= 0) {
    $errorMsg = 'Invalid student.';
}
$studentInfo = null;
if (!$errorMsg) {
    $studentInfoSQL = "SELECT s.fullname, s.nic, c.cname, s.reg_no, s.bid as batch_no 
                        FROM student s
                        JOIN course c ON s.cid = c.id
                        WHERE s.id = :sid";
    $studentInfoQuery = $dbh->prepare($studentInfoSQL);
    $studentInfoQuery->bindParam(':sid', $studentId, PDO::PARAM_INT);
    $studentInfoQuery->execute();
    $studentInfo = $studentInfoQuery->fetch(PDO::FETCH_OBJ);
    if (!$studentInfo) { $errorMsg = 'Invalid student.'; }
}

$semesterResults = [];
if (!$errorMsg) {
    $resultsSQL = "SELECT r.marks, e.date, m.mcode AS module_code, m.mname AS module_name, m.semester, m.credit 
                   FROM results r
                   JOIN exam e ON r.examid = e.id
                   JOIN module m ON e.mid = m.id
                   WHERE r.studentid = :sid AND e.status = 'Released'
                   ORDER BY m.semester, m.mcode";
    $resultsQuery = $dbh->prepare($resultsSQL);
    $resultsQuery->bindParam(':sid', $studentId, PDO::PARAM_INT);
    $resultsQuery->execute();
    $results = $resultsQuery->fetchAll(PDO::FETCH_OBJ);
    foreach ($results as $result) {
        $semesterResults[$result->semester][] = $result;
    }
}
function calculateGrade($marks) {
    if (is_numeric($marks)) {
        if ($marks >= 85) return ['grade' => 'A+', 'gradePoint' => 4.0];
        if ($marks >= 80) return ['grade' => 'A', 'gradePoint' => 4.0];
        if ($marks >= 75) return ['grade' => 'A-', 'gradePoint' => 3.7];
        if ($marks >= 70) return ['grade' => 'B+', 'gradePoint' => 3.3];
        if ($marks >= 65) return ['grade' => 'B', 'gradePoint' => 3.0];
        if ($marks >= 60) return ['grade' => 'B-', 'gradePoint' => 2.7];
        if ($marks >= 50) return ['grade' => 'C+', 'gradePoint' => 2.3];
        if ($marks >= 40) return ['grade' => 'C', 'gradePoint' => 2.0];
        if ($marks >= 30) return ['grade' => 'C-', 'gradePoint' => 1.7];
        if ($marks >= 20) return ['grade' => 'D', 'gradePoint' => 1.3];
        return ['grade' => 'E', 'gradePoint' => 0.0];
    } elseif (in_array(strtolower($marks), ['ab', 'absent'])) {
        return ['grade' => 'AB', 'gradePoint' => 0.0];
    } else {
        return null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Transcript</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="../css/main.css" media="screen">
    <style>
        body { background:#f5f7fb; color:#111827; }
        .sheet { background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 8px 18px rgba(0,0,0,0.05); padding:24px; }
        .header { display:flex; align-items:center; margin-bottom:16px; }
        .header-center { flex:1; text-align:center; }
        .header img { max-height:48px; height:auto; width:auto; }
        .meta table { width:100%; }
        .meta th { width:220px; background:#f8f9fa; }
        .btn-print { background:#2563eb; border-color:#2563eb; color:#fff; border-radius:8px; padding:8px 14px; font-weight:600; }
        .overall { font-weight:700; color:#16a34a; }
        @media print {
            body { background:#fff; }
            .no-print { display:none !important; }
            .sheet { border:none; box-shadow:none; padding:0; }
            .left-sidebar { display:none !important; }
        }
    </style>
</head>
<body class="top-navbar-fixed">
<div class="main-wrapper no-print">
    <?php include('../includes/topbar.php'); ?>
</div>
<div class="content-wrapper">
    <div class="content-container">
        <?php include('../includes/leftbar.php'); ?>
        <div class="main-page">
        <div class="container-fluid">
            <div class="row" style="margin-top:16px;">
                <div class="col-md-12">
                    <div class="sheet">
                        <div class="header">
                            <div class="header-center">
                                <img src="../images/header.png" alt="Header">
                            </div>
                            <?php if (!$errorMsg) { ?>
                                <button onclick="window.print()" class="btn btn-print no-print"><i class="fa fa-print"></i> Print / Save PDF</button>
                            <?php } ?>
                        </div>
                        <form method="POST" class="no-print" style="margin-bottom:12px;">
                            <div class="form-row align-items-center">
                                <div class="col-md-10">
                                    <input type="text" name="searchKey" value="<?= htmlentities($searchKey) ?>" class="form-control m-1" placeholder="Enter Registration Number or NIC" required>
                                    <small style="color:#6b7280; margin-left:6px;">Sample Format - JF/MNT/21/01 OR (980350096V / 199803500069)</small>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="search" class="btn btn-primary btn-block" style="background-color: #111184;">Search</button>
                                </div>
                            </div>
                        </form>
                        <?php if ($errorMsg) { ?>
                            <div class="no-print" style="color:#b91c1c; font-size:12px; text-align:right; margin-top:4px;">
                                <?php echo htmlentities($errorMsg); ?>
                            </div>
                        <?php } else { ?>
                        <h3 style="margin-top:0;">Official Transcript</h3>
                        <div class="meta">
                            <table class="table table-bordered">
                                <tr><th>Full Name</th><td><?= htmlentities($studentInfo->fullname) ?></td></tr>
                                <tr><th>NIC</th><td><?= htmlentities($studentInfo->nic) ?></td></tr>
                                <tr><th>Registration Number</th><td><?= htmlentities($studentInfo->reg_no) ?></td></tr>
                                <tr><th>Course</th><td><?= htmlentities($studentInfo->cname) ?></td></tr>
                                <tr><th>Batch</th><td><?= htmlentities($studentInfo->batch_no) ?></td></tr>
                            </table>
                        </div>
                        <?php
                        $overallWeightedGradePoints = 0;
                        $totalCredits = 0;
                        if (!empty($semesterResults)) {
                            foreach ($semesterResults as $semester => $rows) {
                                $invalidMarks = false;
                                foreach ($rows as $r) { if (calculateGrade($r->marks) === null) { $invalidMarks = true; break; } }
                                if ($invalidMarks) { echo "<div class='alert alert-danger'>There is an offence in Semester ".htmlentities($semester).".</div>"; continue; }
                                $semesterWeighted = 0; $semesterCredits = 0;
                                echo '<h4 style="margin-top:18px;">Semester: '.htmlentities($semester).'</h4>';
                                echo '<table class="table table-bordered"><thead><tr><th>Module Code</th><th>Module Name</th><th>Grade</th></tr></thead><tbody>';
                                foreach ($rows as $row) {
                                    $g = calculateGrade($row->marks);
                                    $semesterWeighted += $g['gradePoint'] * $row->credit;
                                    $semesterCredits += $row->credit;
                                    echo '<tr>';
                                    echo '<td>'.htmlentities($row->module_code).'</td>';
                                    echo '<td>'.htmlentities($row->module_name).'</td>';
                                    echo '<td>'.$g['grade'].'</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                                if ($semesterCredits > 0) {
                                    $semesterGPA = $semesterWeighted / $semesterCredits;
                                    echo '<p><strong>Semester GPA:</strong> '.number_format($semesterGPA, 2).'</p>';
                                }
                                $overallWeightedGradePoints += $semesterWeighted;
                                $totalCredits += $semesterCredits;
                            }
                            if ($totalCredits > 0) {
                                $overallGPA = $overallWeightedGradePoints / $totalCredits;
                                echo '<p class="overall"><strong>Overall GPA:</strong> '.number_format($overallGPA, 2).'</p>';
                            }
                        } else {
                            echo '<div class="alert alert-info">No released results found.</div>';
                        }
                        ?>
                        
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
<script src="../js/jquery/jquery-2.2.4.min.js"></script>
<script src="../js/bootstrap/bootstrap.min.js"></script>
<script src="../js/pace/pace.min.js"></script>
<script src="../js/lobipanel/lobipanel.min.js"></script>
<script src="../js/iscroll/iscroll.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
