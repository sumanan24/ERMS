<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        $reg_no = $_POST['reg_no'];
        $fullname = $_POST['fullname'];
        $nic = $_POST['nic'];
        $courseId = $_POST['course'];
        $batchId = $_POST['batch'];

        $sql = "INSERT INTO student(reg_no, fullname, nic, cid, bid) VALUES(:reg_no, :fullname, :nic, :courseId, :batchId)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':reg_no', $reg_no, PDO::PARAM_STR);
        $query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $query->bindParam(':nic', $nic, PDO::PARAM_STR);
        $query->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $query->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if ($lastInsertId) {
            $msg = "Student Created Successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }
    }


?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Student </title>
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
                                <h2 class="title">Create Student</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="#">Students</a></li>
                                    <li class="active">Create Student</li>
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
                                                <h5>Student</h5>
                                            </div>
                                            &nbsp;&nbsp; <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#studentInsertModal">
                                                Import Excel Students
                                            </button>

                                            <!-- Modal -->
                                            <div class="modal fade" id="studentInsertModal" tabindex="-1" role="dialog" aria-labelledby="studentInsertModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="studentInsertModalLabel">Import Students</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form id="importStudentsForm" action="import_students.php" method="post" enctype="multipart/form-data">
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label for="department">Department</label>
                                                                    <select name="department" id="modalDepartment" class="form-control" required>
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
                                                                    <label for="course">Course</label>
                                                                    <select class="form-control" id="modalCourse" name="course" required>
                                                                        <option value="">Select Course</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="batch">Batch</label>
                                                                    <select class="form-control" id="modalBatch" name="batch" required>
                                                                        <option value="">Select Batch</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="excelFile">Upload Excel File</label>
                                                                    <input type="file" class="form-control-file" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Import</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="panel-body">
                                            <form method="post">
                                                <div class="form-group">
                                                    <label for="reg_no">Registration Number</label>
                                                    <input type="text" name="reg_no" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="fullname">Full Name</label>
                                                    <input type="text" name="fullname" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="nic">NIC</label>
                                                    <input type="text" name="nic" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="department">Department</label>
                                                    <select name="department" id="department1" class="form-control" required>
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
                                                    <label for="course">Course</label>
                                                    <select name="course" id="course1" class="form-control" required>
                                                        <option value="">Select Course</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="batch">Batch</label>
                                                    <select name="batch" id="batch1" class="form-control" required>
                                                        <option value="">Select Batch</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                                </div>
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
    <script src="../js/jquery-ui/jquery-ui.min.js"></script>
    <script src="../js/bootstrap/bootstrap.min.js"></script>
    <script src="../js/pace/pace.min.js"></script>
    <script src="../js/lobipanel/lobipanel.min.js"></script>
    <script src="../js/iscroll/iscroll.js"></script>
    <script src="../js/prism/prism.js"></script>
    <script src="../js/main.js"></script>

    <script>
        $(document).ready(function () {
            // Load courses based on department selection in modal
            $('#modalDepartment').change(function () {
                var deptId = $(this).val();
                if (deptId) {
                    $.ajax({
                        url: "get_courses.php",
                        method: "POST",
                        data: { deptId: deptId },
                        success: function (data) {
                            $('#modalCourse').html(data);
                            $('#modalBatch').html('<option value="">Select Batch</option>'); // Reset batch dropdown
                        }
                    });
                } else {
                    $('#modalCourse').html('<option value="">Select Course</option>');
                    $('#modalBatch').html('<option value="">Select Batch</option>');
                }
            });

            // Load batches based on course selection in modal
            $('#modalCourse').change(function () {
                var courseId = $(this).val();
                if (courseId) {
                    $.ajax({
                        url: "get_batches.php",
                        method: "POST",
                        data: { courseId: courseId },
                        success: function (data) {
                            $('#modalBatch').html(data);
                        }
                    });
                } else {
                    $('#modalBatch').html('<option value="">Select Batch</option>');
                }
            });

            // Load courses based on department selection outside modal
            $('#department1').change(function () {
                var deptId = $(this).val();
                if (deptId) {
                    $.ajax({
                        url: "get_courses.php",
                        method: "POST",
                        data: { deptId: deptId },
                        success: function (data) {
                            $('#course1').html(data);
                            $('#batch1').html('<option value="">Select Batch</option>'); // Reset batch dropdown
                        }
                    });
                } else {
                    $('#course1').html('<option value="">Select Course</option>');
                    $('#batch1').html('<option value="">Select Batch</option>');
                }
            });

            // Load batches based on course selection outside modal
            $('#course1').change(function () {
                var courseId = $(this).val();
                if (courseId) {
                    $.ajax({
                        url: "get_batches.php",
                        method: "POST",
                        data: { courseId: courseId },
                        success: function (data) {
                            $('#batch1').html(data);
                        }
                    });
                } else {
                    $('#batch1').html('<option value="">Select Batch</option>');
                }
            });
        });
    </script>
</body>


    </html>

<?php } ?>