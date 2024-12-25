<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['examid'])) {
    die('Exam ID is required.');
}

$examid = intval($_GET['examid']);

// Fetch exam details
$sql = "SELECT e.date, e.time, m.mname as module_name, d.dname as department_name, b.batch_no
        FROM exam e
        JOIN module m ON e.mid = m.id
        JOIN course c ON c.id = m.cid 
        JOIN department d ON c.did = d.id
        JOIN batch b ON e.bid = b.id
        WHERE e.id = :examid";
$query = $dbh->prepare($sql);
$query->bindParam(':examid', $examid, PDO::PARAM_INT);
$query->execute();
$examDetails = $query->fetch(PDO::FETCH_OBJ);

if (!$examDetails) {
    die('Exam not found.');
}

// Fetch students and their results for the batch
$sql = "SELECT s.id as student_id, s.reg_no, s.fullname, r.attempt, r.marks 
        FROM student s
        JOIN batch b ON s.bid = b.batch_no
        LEFT JOIN results r ON r.studentid = s.id 
        WHERE r.examid = :examid
        ORDER BY s.id";
$query = $dbh->prepare($sql);
$query->bindParam(':examid', $examid, PDO::PARAM_INT);
$query->execute();
$students = $query->fetchAll(PDO::FETCH_OBJ);

// Handle adding repeat student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reg_no']) && !empty($_POST['reg_no'])) {
    $reg_no = $_POST['reg_no'];

    // Find student by reg_no
    $sql = "SELECT * FROM student WHERE reg_no = :reg_no";
    $query = $dbh->prepare($sql);
    $query->bindParam(':reg_no', $reg_no, PDO::PARAM_STR);
    $query->execute();
    $student = $query->fetch(PDO::FETCH_OBJ);

    if ($student) {
        // Check if the student is already in the exam batch
        $sql = "SELECT * FROM results WHERE examid = :examid AND studentid = :studentid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':examid', $examid, PDO::PARAM_INT);
        $query->bindParam(':studentid', $student->id, PDO::PARAM_INT);
        $query->execute();
        $existingResult = $query->fetch(PDO::FETCH_OBJ);

        // Increment attempt number if result exists, otherwise set to 1
        $attempt = ($existingResult) ? $existingResult->attempt + 1 : 1;

        // Insert result for the student or update if already exists
        if ($existingResult) {
            $sql = "UPDATE results SET attempt = :attempt WHERE examid = :examid AND studentid = :studentid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':attempt', $attempt, PDO::PARAM_INT);
            $query->bindParam(':examid', $examid, PDO::PARAM_INT);
            $query->bindParam(':studentid', $student->id, PDO::PARAM_INT);
        } else {
            $sql = "INSERT INTO results (examid, studentid, attempt) VALUES (:examid, :studentid, :attempt)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':examid', $examid, PDO::PARAM_INT);
            $query->bindParam(':studentid', $student->id, PDO::PARAM_INT);
            $query->bindParam(':attempt', $attempt, PDO::PARAM_INT);
        }
        $query->execute();
        $msg = "Student added successfully!";
    } else {
        $msg = "Student with registration number $reg_no not found!";
    }
}

// Handle saving results
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['data']) && !empty($_POST['data'])) {
    $data = $_POST['data'];
    foreach ($data as $result) {
        $studentId = $result['student_id'];
        $attempt = $result['attempt'];
        $marks = $result['marks'];

        // Check if result already exists
        $sql = "SELECT id FROM results WHERE examid = :examid AND studentid = :studentid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':examid', $examid, PDO::PARAM_INT);
        $query->bindParam(':studentid', $studentId, PDO::PARAM_INT);
        $query->execute();
        $existingResult = $query->fetch(PDO::FETCH_OBJ);

        if ($existingResult) {
            // Update existing result
            $sql = "UPDATE results SET attempt = :attempt, marks = :marks WHERE id = :id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':attempt', $attempt, PDO::PARAM_INT);
            $query->bindParam(':marks', $marks, PDO::PARAM_STR);
            $query->bindParam(':id', $existingResult->id, PDO::PARAM_INT);
        } else {
            // Insert new result
            $sql = "INSERT INTO results (examid, studentid, attempt, marks) VALUES (:examid, :studentid, :attempt, :marks)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':examid', $examid, PDO::PARAM_INT);
            $query->bindParam(':studentid', $studentId, PDO::PARAM_INT);
            $query->bindParam(':attempt', $attempt, PDO::PARAM_INT);
            $query->bindParam(':marks', $marks, PDO::PARAM_STR);
        }
        $query->execute();
    }

    $msg = "Results saved successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ERMS - Exams</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" type="text/css" href="../js/DataTables/datatables.min.css" />
    <link rel="stylesheet" href="../css/main.css" media="screen">
    <script src="../js/modernizr/modernizr.min.js"></script>
</head>

<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <?php include('../includes/topbar.php'); ?>
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('../includes/leftbar.php'); ?>
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Manage Exams</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="manage.php">Exams</a></li>
                                    <li class="active">Manage Results</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel">
                                        <div class="panel-body p-20">
                                            <!-- Display success or error message -->
                                            <?php if (isset($msg)) { ?>
                                                <div class="alert alert-success"><?php echo $msg; ?></div>
                                                <meta http-equiv='refresh' content='1.5'>
                                            <?php } ?>

                                            <div class="card mt-4">
                                                <div class="card-body">
                                                    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                                                        <tr>
                                                            <th>Department</th>
                                                            <td><?php echo htmlentities($examDetails->department_name); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Module</th>
                                                            <td><?php echo htmlentities($examDetails->module_name); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Date</th>
                                                            <td><?php echo htmlentities($examDetails->date); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Time</th>
                                                            <td><?php echo htmlentities($examDetails->time); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Batch</th>
                                                            <td><?php echo htmlentities($examDetails->batch_no); ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>

                                            <br>
                                            <!-- Registration number form -->
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <form method="post" class="mt-4">
                                                        <div class="form-group">
                                                            <label for="reg_no">Enter Repeat Students Registration Number:</label>
                                                            <input type="text" name="reg_no" id="reg_no" class="form-control" value="<?php echo htmlentities($reg_no); ?>" required>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Add Student</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <br>
                                            <!-- Results table -->
                                            <form method="post" class="mt-4">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Registration No</th>
                                                            <th>Student Name</th>
                                                            <th>Attempt</th>
                                                            <th>Marks</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if ($students) {
                                                            foreach ($students as $student) { ?>
                                                                <tr>
                                                                    <td><?php echo htmlentities($student->reg_no); ?></td>
                                                                    <td>
                                                                        <?php echo htmlentities($student->fullname); ?>
                                                                        <input type="hidden" name="data[<?php echo $student->student_id; ?>][student_id]" value="<?php echo $student->student_id; ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" name="data[<?php echo $student->student_id; ?>][attempt]" class="form-control" value="<?php echo htmlentities($student->attempt ?? 1); ?>" required>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" name="data[<?php echo $student->student_id; ?>][marks]" class="form-control" value="<?php echo htmlentities($student->marks ?? ''); ?>" required>
                                                                    </td>
                                                                </tr>
                                                            <?php }
                                                        } else { ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center">No students found for this batch.</td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                                <button type="submit" class="btn btn-primary">Save Results</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/jquery/jquery-2.2.4.min.js"></script>
    <script src="../js/bootstrap/bootstrap.min.js"></script>
    <script src="../js/pace/pace.min.js"></script>
    <script src="../js/lobipanel/lobipanel.min.js"></script>
    <script src="../js/iscroll/iscroll.js"></script>
    <script src="../js/DataTables/datatables.min.js"></script>
    <script src="../js/main.js"></script>
</body>

</html>