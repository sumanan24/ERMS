<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: ../index.php");
} else {
    if (isset($_POST['submit'])) {
        $mcode = $_POST['mcode'];
        $mname = $_POST['mname'];
        $cid = $_POST['cid'];
        $semester = $_POST['semester']; // Get the selected semester

        $sql = "INSERT INTO module(mcode, mname, cid, semester) VALUES(:mcode, :mname, :cid, :semester)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':mcode', $mcode, PDO::PARAM_STR);
        $query->bindParam(':mname', $mname, PDO::PARAM_STR);
        $query->bindParam(':cid', $cid, PDO::PARAM_INT);
        $query->bindParam(':semester', $semester, PDO::PARAM_INT);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if ($lastInsertId) {
            $msg = "Module Created successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }
    }

    $deptSql = "SELECT * FROM department";
    $deptQuery = $dbh->prepare($deptSql);
    $deptQuery->execute();
    $departments = $deptQuery->fetchAll(PDO::FETCH_OBJ);
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Module Create</title>
        <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
        <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" href="../css/prism/prism.css" media="screen">
        <link rel="stylesheet" href="../css/main.css" media="screen">
        <script src="../js/modernizr/modernizr.min.js"></script>
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
                                    <h2 class="title">Create Module</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="manage.php">Modules</a></li>
                                        <li class="active">Create Module</li>
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
                                                    <h5>Module</h5>
                                                </div>
                                            </div>

                                            <!-- Trigger the Modal -->
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#excelModal" style="margin-left: 15px;">
                                                Upload Modules via Excel
                                            </button>

                                            <!-- Modal -->
                                            <div class="modal fade" id="excelModal" tabindex="-1" role="dialog" aria-labelledby="excelModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="excelModalLabel">Upload Modules via Excel</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="upload_excel.php" method="post" enctype="multipart/form-data">
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label for="department">Department</label>
                                                                    <select name="department_id" id="department" class="form-control" required>
                                                                        <option value="">Select Department</option>
                                                                        <?php foreach ($departments as $dept) { ?>
                                                                            <option value="<?php echo htmlentities($dept->id); ?>">
                                                                                <?php echo htmlentities($dept->dname); ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="course">Course</label>
                                                                    <select name="course_id" id="course" class="form-control" required>
                                                                        <option value="">Select Course</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="excelFile">Excel File</label>
                                                                    <input type="file" name="excelFile" class="form-control" id="excelFile" accept=".xls, .xlsx" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <a href="Module.xlsx"><u>Sample Excel File Click here</u></a>
                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-success">Upload</button>
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if (isset($_SESSION['success'])) { ?>
                                                <div class="alert alert-success">
                                                    <?php echo htmlentities($_SESSION['success']);
                                                    unset($_SESSION['success']); ?>
                                                </div>
                                                <meta http-equiv='refresh' content='1.5'>
                                            <?php } else if (isset($_SESSION['error'])) { ?>
                                                <div class="alert alert-danger">
                                                    <?php echo htmlentities($_SESSION['error']);
                                                    unset($_SESSION['error']); ?>
                                                </div>
                                            <?php } ?>

                                            <?php if ($msg) { ?>
                                                <div class="alert alert-success left-icon-alert" role="alert">
                                                    <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                                    <meta http-equiv='refresh' content='1.5'>
                                                </div>
                                            <?php } else if ($error) { ?>
                                                <div class="alert alert-danger left-icon-alert" role="alert">
                                                    <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                            <?php } ?>
                                            <div class="panel-body">
                                                <form method="post">
                                                    <div class="form-group">
                                                        <label for="department">Department</label>
                                                        <select class="form-control" id="department1" required>
                                                            <option value="">Select Department</option>
                                                            <?php foreach ($departments as $dept) { ?>
                                                                <option value="<?php echo htmlentities($dept->id); ?>">
                                                                    <?php echo htmlentities($dept->dname); ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="course">Course</label>
                                                        <select name="cid" class="form-control" id="course1" required>
                                                            <option value="">Select Course</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="mcode">Module Code</label>
                                                        <input type="text" name="mcode" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="mname">Module Name</label>
                                                        <input type="text" name="mname" class="form-control" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="mname">Module Credit</label>
                                                        <input type="number" name="credit" class="form-control" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="semester">Semester</label>
                                                        <select name="semester" class="form-control" id="semester" required>
                                                            <option value="" disabled selected>Select Semester</option>
                                                            <option value="1">Semester 1</option>
                                                            <option value="2">Semester 2</option>
                                                            <option value="3">Semester 3</option>
                                                            <option value="4">Semester 4</option>
                                                            <option value="5">Semester 5</option>
                                                            <option value="6">Semester 6</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <button type="submit" name="submit" class="btn btn-success">Submit</button>
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
            $(document).ready(function() {
                $('#department1').change(function() {
                    var deptId = $(this).val();
                    if (deptId) {
                        $.ajax({
                            url: 'get_courses.php',
                            method: 'POST',
                            data: {
                                department_id: deptId
                            },
                            dataType: 'json',
                            success: function(data) {
                                $('#course1').html('<option value="">Select Course</option>');
                                $.each(data, function(key, value) {
                                    $('#course1').append('<option value="' + value.id + '">' + value.cname + '</option>');
                                });
                            }
                        });
                    } else {
                        $('#course1').html('<option value="">Select Course</option>');
                    }
                });
            });
        </script>



        <script>
            $(document).ready(function() {
                $('#department').change(function() {
                    var deptId = $(this).val();
                    if (deptId) {
                        $.ajax({
                            url: 'get_courses.php',
                            method: 'POST',
                            data: {
                                department_id: deptId
                            },
                            dataType: 'json',
                            success: function(data) {
                                $('#course').html('<option value="">Select Course</option>');
                                $.each(data, function(key, value) {
                                    $('#course').append('<option value="' + value.id + '">' + value.cname + '</option>');
                                });
                            }
                        });
                    } else {
                        $('#course').html('<option value="">Select Course</option>');
                    }
                });
            });
        </script>
    </body>

    </html>
<?php } ?>