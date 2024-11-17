<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        $mcode = $_POST['mcode'];
        $mname = $_POST['mname'];
        $cid = $_POST['cid'];

        $sql = "INSERT INTO module(mcode, mname, cid) VALUES(:mcode, :mname, :cid)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':mcode', $mcode, PDO::PARAM_STR);
        $query->bindParam(':mname', $mname, PDO::PARAM_STR);
        $query->bindParam(':cid', $cid, PDO::PARAM_INT);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if ($lastInsertId) {
            $msg = "Module Created successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }
    }

    // Fetch departments for the first dropdown
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
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="#">Modules</a></li>
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
                                                        <select class="form-control" id="department" required>
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
                                                        <select name="cid" class="form-control" id="course" required>
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