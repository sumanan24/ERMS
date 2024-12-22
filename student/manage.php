<?php
session_start();
error_reporting(0);
include('../includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: ../index.php");
} else {
    // Fetch Courses and Batches for Filters
    $courseQuery = "SELECT id, cname FROM course"; // Adjust `name` to your course name column
    $courseStmt = $dbh->prepare($courseQuery);
    $courseStmt->execute();
    $courses = $courseStmt->fetchAll(PDO::FETCH_OBJ);

    $batchQuery = "SELECT distinct batch_no FROM batch"; // Adjust `name` to your batch name column
    $batchStmt = $dbh->prepare($batchQuery);
    $batchStmt->execute();
    $batches = $batchStmt->fetchAll(PDO::FETCH_OBJ);

    // Get Selected Filters
    $selectedCourse = isset($_POST['course']) ? $_POST['course'] : '';
    $selectedBatch = isset($_POST['batch']) ? $_POST['batch'] : '';

    // Fetch Students Based on Filters
    $studentQuery = "SELECT * FROM student WHERE 1=1";
    if ($selectedCourse != '') {
        $studentQuery .= " AND cid = :course";
    }
    if ($selectedBatch != '') {
        $studentQuery .= " AND bid = :batch";
    }
    $studentStmt = $dbh->prepare($studentQuery);
    if ($selectedCourse != '') {
        $studentStmt->bindParam(':course', $selectedCourse, PDO::PARAM_STR);
    }
    if ($selectedBatch != '') {
        $studentStmt->bindParam(':batch', $selectedBatch, PDO::PARAM_STR);
    }
    $studentStmt->execute();
    $students = $studentStmt->fetchAll(PDO::FETCH_OBJ);
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ERMS - Students</title>
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
                                    <h2 class="title">Manage Students</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li class="active">Manage Students</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="container-fluid">
                                <form method="post" action="">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="course">Filter by Course</label>
                                            <select name="course" id="course" class="form-control">
                                                <option value="">All Courses</option>
                                                <?php foreach ($courses as $course) { ?>
                                                    <option value="<?php echo $course->id; ?>" <?php if ($selectedCourse == $course->id) echo 'selected'; ?>>
                                                        <?php echo htmlentities($course->cname); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="batch">Filter by Batch</label>
                                            <select name="batch" id="batch" class="form-control">
                                                <option value="">All Batches</option>
                                                <?php foreach ($batches as $batch) { ?>
                                                    <option value="<?php echo $batch->batch_no; ?>" <?php if ($selectedBatch == $batch->batch_no) echo 'selected'; ?>>
                                                        <?php echo htmlentities($batch->batch_no); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                                        </div>
                                    </div>
                                </form>
                                <br>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <h5>View Students Info</h5>
                                            </div>
                                            <div class="panel-body p-20">
                                                <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Registration No</th>
                                                            <th>Full Name</th>
                                                            <th>NIC</th>
                                                            <th>Course ID</th>
                                                            <th>Batch ID</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if ($students) {
                                                            foreach ($students as $student) { ?>
                                                                <tr>
                                                                    <td><?php echo htmlentities($student->reg_no); ?></td>
                                                                    <td><?php echo htmlentities($student->fullname); ?></td>
                                                                    <td><?php echo htmlentities($student->nic); ?></td>
                                                                    <td><?php echo htmlentities($student->cid); ?></td>
                                                                    <td><?php echo htmlentities($student->bid); ?></td>
                                                                    <td>
                                                                        <a href="edit.php?studentid=<?php echo htmlentities($student->id); ?>" class="btn btn-info btn-xs"> Edit </a>
                                                                        <a href="?id=<?php echo $student->id; ?>" onClick="return confirm('Are you sure you want to delete?')" class="btn btn-danger btn-xs">Delete</a>
                                                                    </td>
                                                                </tr>
                                                        <?php }
                                                        } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
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
        <script src="../js/DataTables/datatables.min.js"></script>
        <script src="../js/main.js"></script>
        <script>
            $(function($) {
                $('#example').DataTable();
            });
        </script>
    </body>

    </html>
<?php } ?>
