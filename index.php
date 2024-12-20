<?php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    if (isset($_POST['search'])) {
        $searchKey = $_POST['searchKey'];

        // Fetch student information and results based on NIC or registration number
        $studentInfoSQL = "SELECT fullname, nic, reg_no FROM student WHERE nic = :searchKey OR reg_no = :searchKey";
        $studentInfoQuery = $dbh->prepare($studentInfoSQL);
        $studentInfoQuery->bindParam(':searchKey', $searchKey, PDO::PARAM_STR);
        $studentInfoQuery->execute();
        $studentInfo = $studentInfoQuery->fetch(PDO::FETCH_OBJ);

        $resultsSQL = "SELECT r.marks, e.date, m.mname as module_name FROM results r 
                      JOIN exam e ON r.examid = e.id 
                      JOIN module m ON e.mid = m.id 
                      JOIN student s ON r.studentid = s.id 
                      WHERE s.nic = :searchKey OR s.reg_no = :searchKey";
        $resultsQuery = $dbh->prepare($resultsSQL);
        $resultsQuery->bindParam(':searchKey', $searchKey, PDO::PARAM_STR);
        $resultsQuery->execute();
        $results = $resultsQuery->fetchAll(PDO::FETCH_OBJ);
    }

    // Function to calculate grade and grade point based on marks
    function calculateGrade($marks)
    {
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
        return ['grade' => 'E', 'gradePoint' => 0];
    }
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Details</title>

        <link rel="stylesheet" href="css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

        <style>
            .errorWrap {
                padding: 10px;
                background: #fff;
                border-left: 4px solid #dd3d36;
            }

            .succWrap {
                padding: 10px;
                background: #fff;
                border-left: 4px solid #5cb85c;
            }

            .table th,
            .table td {
                vertical-align: middle;
            }

            .grade-orange {
                background-color: orange;
                color: #fff;
            }

            .grade-red {
                background-color: red;
                color: #fff;
            }
        </style>
    </head>

    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">University College Jaffna</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav mr-auto">

                </ul>
                <span class="navbar-text">
                    <a class="nav-link" href="admin-login.php">Admin</a>
                </span>
            </div>
        </nav>
        <div class="container mt-5">
            <h2 class="text-center mb-4">Student Details</h2>
            <form method="POST" action="" class="mb-4">
                <div class="form-row align-items-center">
                    <div class="col-md-10">
                        <label class="sr-only" for="searchKey">NIC or Registration Number</label>
                        <input type="text" class="form-control form-control-sm mb-2" id="searchKey" name="searchKey" placeholder="Enter NIC or Registration Number" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="search" class="btn btn-primary btn-sm btn-block mb-2">Search</button>
                    </div>
                </div>
            </form>

            <?php if (isset($studentInfo) && $studentInfo) { ?>
                <table class="table table-bordered table-sm">
                    <tbody>
                        <tr>
                            <th>Student Name</th>
                            <td><?php echo htmlentities($studentInfo->fullname); ?></td>
                        </tr>
                        <tr>
                            <th>NIC</th>
                            <td><?php echo htmlentities($studentInfo->nic); ?></td>
                        </tr>
                        <tr>
                            <th>Registration Number</th>
                            <td><?php echo htmlentities($studentInfo->reg_no); ?></td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="mt-4">Results</h3>
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Module</th>
                            <th>Grade</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result) {
                            $gradeData = calculateGrade($result->marks);
                            $gradeClass = '';
                            if ($gradeData['grade'] === 'C-' || $gradeData['grade'] === 'D') {
                                $gradeClass = 'grade-orange';
                            } elseif ($gradeData['grade'] === 'E') {
                                $gradeClass = 'grade-red';
                            }
                        ?>
                            <tr class="<?php echo $gradeClass; ?>">
                                <td><?php echo htmlentities($result->module_name); ?></td>
                                <td><?php echo $gradeData['grade']; ?></td>

                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } elseif (isset($_POST['search'])) { ?>
                <div class="alert alert-danger text-center">No results found for the provided NIC or Registration Number.</div>
            <?php } ?>
        </div>
        <script src="js/jquery/jquery-2.2.4.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
    </body>

    </html>
<?php } ?>