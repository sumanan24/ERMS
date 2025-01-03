<?php
session_start();
include('includes/config.php');

if (isset($_POST['search'])) {
    $searchKey = trim($_POST['searchKey']);

    // Fetch student information
    $studentInfoSQL = "SELECT s.fullname, s.nic, c.cname, s.reg_no, b.batch_no 
                        FROM student s
                        JOIN course c ON s.cid = c.id
                        JOIN batch b ON s.bid = b.batch_no
                        WHERE s.nic = :searchKey OR s.reg_no = :searchKey";
    $studentInfoQuery = $dbh->prepare($studentInfoSQL);
    $studentInfoQuery->bindParam(':searchKey', $searchKey, PDO::PARAM_STR);
    $studentInfoQuery->execute();
    $studentInfo = $studentInfoQuery->fetch(PDO::FETCH_OBJ);

    // Fetch results semester-wise
    $resultsSQL = "SELECT r.marks, e.date, m.mcode AS module_code, m.mname AS module_name, m.semester, m.credit 
                   FROM results r
                   JOIN exam e ON r.examid = e.id
                   JOIN module m ON e.mid = m.id
                   JOIN student s ON r.studentid = s.id
                   WHERE (s.nic = :searchKey OR s.reg_no = :searchKey) AND e.status = 'Released'
                   ORDER BY m.semester, m.mcode";
    $resultsQuery = $dbh->prepare($resultsSQL);
    $resultsQuery->bindParam(':searchKey', $searchKey, PDO::PARAM_STR);
    $resultsQuery->execute();
    $results = $resultsQuery->fetchAll(PDO::FETCH_OBJ);

    // Group results by semester
    $semesterResults = [];
    foreach ($results as $result) {
        $semesterResults[$result->semester][] = $result;
    }
}

// Function to calculate grade
function calculateGrade($marks)
{
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
        return null; // Invalid marks
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            color: #333;
            margin-top: 20px;
            background-image: url('images/bg.jpg');
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
        }

        .form-control {
            font-size: 18px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .btn-primary {
            font-weight: bold;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .table {
            margin-top: 20px;
        }

        .table th {
            background-color: #f8f9fa;
        }

        .alert {
            text-align: center;
            margin-top: 20px;
        }

        .semester-header {
            margin-top: 20px;
            font-weight: bold;
        }

        .overall-gpa {
            margin-top: 20px;
            font-weight: bold;
            color: #28a745;
        }

        .navbar-brand img {
            height: 40px;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="images/header.png" style="width: 100%;" alt="Header">
    </div>

    <div class="container mt-4">
        <h5 style="text-align: center;">Search Student Result</h5>
        <form method="POST">
            <div class="form-row align-items-center">
                <div class="col-md-10">
                    <input type="text" name="searchKey" class="form-control m-1" placeholder="Enter Registration Number or NIC" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="search" class="btn btn-primary btn-block" style="background-color: #111184;">Search</button>
                </div>
            </div>
            <small style="color: #111184;">Sample Format - JF/MNT/21/01 OR (980350096V / 199803500069)</small>
        </form>

        <?php if (isset($studentInfo) && $studentInfo) { ?>
            <table class="table table-bordered mt-4">
                <tr>
                    <th>Full Name</th>
                    <td><?= htmlentities($studentInfo->fullname) ?></td>
                </tr>
                <tr>
                    <th>NIC</th>
                    <td><?= htmlentities($studentInfo->nic) ?></td>
                </tr>
                <tr>
                    <th>Registration Number</th>
                    <td><?= htmlentities($studentInfo->reg_no) ?></td>
                </tr>
                <tr>
                    <th>Course Name</th>
                    <td><?= htmlentities($studentInfo->cname) ?></td>
                </tr>
                <tr>
                    <th>Batch Number</th>
                    <td><?= htmlentities($studentInfo->batch_no) ?></td>
                </tr>
            </table>

            <h5>Results </h5>
            <?php
            $overallWeightedGradePoints = 0;
            $totalCredits = 0;

            foreach ($semesterResults as $semester => $results) {
                $invalidMarks = false;
                foreach ($results as $result) {
                    if (calculateGrade($result->marks) === null) {
                        $invalidMarks = true;
                        break;
                    }
                }

                if ($invalidMarks) {
                    echo "<div class='alert alert-danger'>You has offence Semester $semester.</div>";
                    continue;
                }

                $semesterWeightedGradePoints = 0;
                $semesterCredits = 0;
            ?>
                <h5 class="semester-header">Semester: <?= htmlentities($semester) ?></h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Module Code</th>
                            <th>Module Name</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($results as $result) {
                            $gradeData = calculateGrade($result->marks);
                            $semesterWeightedGradePoints += $gradeData['gradePoint'] * $result->credit;
                            $semesterCredits += $result->credit;
                        ?>
                            <tr>
                                <td><?= htmlentities($result->module_code) ?></td>
                                <td><?= htmlentities($result->module_name) ?></td>
                                <td><?= $gradeData['grade'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php
                if ($semesterCredits > 0) {
                    $semesterGPA = $semesterWeightedGradePoints / $semesterCredits;
                    echo "<p><strong>Semester GPA:</strong> " . number_format($semesterGPA, 2) . "</p>";
                }

                $overallWeightedGradePoints += $semesterWeightedGradePoints;
                $totalCredits += $semesterCredits;
            }

            if ($totalCredits > 0) {
                $overallGPA = $overallWeightedGradePoints / $totalCredits;
                echo "<p class='overall-gpa'><strong>Overall GPA:</strong> " . number_format($overallGPA, 2) . "</p>";
            }
            ?>
            <p style="text-align: center; color: red; font-weight: bold;"> This document is computer-generated and is not valid for legal purposes. </p>

        <?php } elseif (isset($_POST['search'])) { ?>
            <div class="alert alert-danger">No results found for the provided NIC or Registration Number.</div>
        <?php } ?>

    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
