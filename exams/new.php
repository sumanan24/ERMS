<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: ../index.php");
} else {
    if (isset($_POST['submit'])) {
        $batchId = $_POST['batch'];
        $modules = $_POST['module']; // Array of module IDs
        $dates = $_POST['date']; // Array of dates
        $times = $_POST['time']; // Array of times

        $alert = false; // Flag to indicate duplicate entry
        foreach ($modules as $key => $moduleId) {
            // Check if the combination of mid and bid already exists
            $sqlCheck = "SELECT * FROM exam WHERE mid = :moduleId AND bid = :batchId";
            $queryCheck = $dbh->prepare($sqlCheck);
            $queryCheck->bindParam(':moduleId', $moduleId, PDO::PARAM_INT);
            $queryCheck->bindParam(':batchId', $batchId, PDO::PARAM_INT);
            $queryCheck->execute();

            if ($queryCheck->rowCount() > 0) {
                $alert = true; // Duplicate found
                break; // Exit the loop
            }
            
        }

        if ($alert) {
            $error = "An exam for the selected module and batch already exists!";
        } else {
            $sql = "INSERT INTO exam(mid, bid, date, time, Status) VALUES(:moduleId, :batchId, :examDate, :examTime, 'Pending')";
            $query = $dbh->prepare($sql);

            foreach ($modules as $key => $moduleId) {
                $examDate = date('Y-m-d', strtotime($dates[$key])); // Ensure correct format
                $examTime = $times[$key];
                $query->bindParam(':moduleId', $moduleId, PDO::PARAM_INT);
                $query->bindParam(':batchId', $batchId, PDO::PARAM_INT);
                $query->bindParam(':examDate', $examDate, PDO::PARAM_STR);
                $query->bindParam(':examTime', $examTime, PDO::PARAM_STR);
                $query->execute();
            }
            $msg = "Exam schedule created successfully!";
        }
    }
?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Exam Schedule</title>
        <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
        <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" href="../css/prism/prism.css" media="screen">
        <link rel="stylesheet" href="../css/main.css" media="screen">
        <script src="../js/modernizr/modernizr.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <style>
            .errorWrap {
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #dd3d36;
                box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            }

            .succWrap {
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #5cb85c;
                box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            }
        </style>
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
                                    <h2 class="title">Exam Schedule</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="manage.php">Exam Schedules</a></li>
                                        <li class="active">Create Exam Schedule</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-8 col-md-offset-2">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>Department</h5>
                                                </div>
                                            </div>
                                            <?php if ($msg) { ?>
                                                <div class="alert alert-success left-icon-alert" role="alert">
                                                    <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                                </div>
                                            <?php } else if ($error) { ?>
                                                <div class="alert alert-danger left-icon-alert" role="alert">
                                                    <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                            <?php } ?>

                                            <div class="panel-body">
                                                <form method="post">
                                                    <div class="form-group">
                                                        <label for="department">Select Department</label>
                                                        <select name="department" id="department" class="form-control" required>
                                                            <option value="">Select Department</option>
                                                            <?php
                                                            $sql = "SELECT * FROM department";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                            if ($query->rowCount() > 0) {
                                                                foreach ($results as $result) {
                                                                    echo "<option value='" . htmlentities($result->id) . "'>" . htmlentities($result->dname) . "</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="course">Select Course</label>
                                                        <select name="course" id="course" class="form-control" required>
                                                            <option value="">Select Course</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="semester">Select Semester</label>
                                                        <select name="semester" id="semester" class="form-control" required>
                                                            <option value="">Select Semester</option>
                                                            <!-- Semesters can be hardcoded or fetched dynamically from the database -->
                                                            <option value="1">Semester 1</option>
                                                            <option value="2">Semester 2</option>
                                                            <option value="3">Semester 3</option>
                                                            <option value="4">Semester 4</option>
                                                            <option value="5">Semester 5</option>
                                                            <option value="6">Semester 6</option>
                                                        </select>
                                                    </div>


                                                    <div class="form-group">
                                                        <label for="batch">Select Batch</label>
                                                        <select name="batch" id="batch" class="form-control" required>
                                                            <option value="">Select Batch</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Exam Schedule</label>
                                                        <table class="table table-bordered" id="module-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Module</th>
                                                                    <th>Date</th>
                                                                    <th>Time</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>

                                                    <button type="submit" name="submit" class="btn btn-primary">Submit Schedule</button>
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
        <script>
            $(document).ready(function() {
                // Load courses based on department
                $('#department').change(function() {
                    var deptId = $(this).val();
                    if (deptId) {
                        $.ajax({
                            url: "get_courses.php",
                            method: "POST",
                            data: {
                                deptId: deptId
                            },
                            success: function(data) {
                                $('#course').html(data);
                                $('#batch').html('<option value="">Select Batch</option>');
                                $('#module-table tbody').empty(); // Clear modules table
                                $('#semester').val(''); // Reset semester
                            }
                        });
                    } else {
                        $('#course').html('<option value="">Select Course</option>');
                        $('#batch').html('<option value="">Select Batch</option>');
                        $('#module-table tbody').empty();
                        $('#semester').val(''); // Reset semester
                    }
                });

                // Load batches based on course
                $('#course').change(function() {
                    var courseId = $(this).val();
                    if (courseId) {
                        $.ajax({
                            url: "get_batches.php",
                            method: "POST",
                            data: {
                                courseId: courseId
                            },
                            success: function(data) {
                                $('#batch').html(data);
                                $('#module-table tbody').empty(); // Clear modules table
                                $('#semester').val(''); // Reset semester
                            }
                        });
                    } else {
                        $('#batch').html('<option value="">Select Batch</option>');
                        $('#module-table tbody').empty();
                        $('#semester').val(''); // Reset semester
                    }
                });

                // Load modules based on course and semester
                $('#semester').change(function() {
                    var courseId = $('#course').val();
                    var semester = $(this).val();
                    if (courseId && semester) {
                        $.ajax({
                            url: "get_modules.php",
                            method: "POST",
                            data: {
                                courseId: courseId,
                                semester: semester
                            },
                            success: function(data) {
                                $('#module-table tbody').html(data);
                            }
                        });
                    } else {
                        $('#module-table tbody').empty();
                    }
                });
            });
        </script>
        <script src="../js/jquery/jquery-2.2.4.min.js"></script>
        <script src="../js/jquery-ui/jquery-ui.min.js"></script>
        <script src="../js/bootstrap/bootstrap.min.js"></script>
        <script src="../js/pace/pace.min.js"></script>
        <script src="../js/lobipanel/lobipanel.min.js"></script>
        <script src="../js/iscroll/iscroll.js"></script>
        <script src="../js/prism/prism.js"></script>
        <script src="../js/main.js"></script>
    </body>

    </html>
<?php } ?>