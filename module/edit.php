<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: ../index.php");
} else {
    $moduleid = intval($_GET['moduleid']); // Get module ID from URL

    // Update logic
    if (isset($_POST['update'])) {
        $mcode = $_POST['mcode'];
        $mname = $_POST['mname'];
        $cid = $_POST['cid'];
        $semester = $_POST['semester'];
        $credit=$_POST['credit'];

        $sql = "UPDATE module SET mcode = :mcode, mname = :mname, cid = :cid, semester = :semester, credit =:credit WHERE id = :moduleid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':mcode', $mcode, PDO::PARAM_STR);
        $query->bindParam(':mname', $mname, PDO::PARAM_STR);
        $query->bindParam(':cid', $cid, PDO::PARAM_INT);
        $query->bindParam(':semester', $semester, PDO::PARAM_INT);
        $query->bindParam(':credit', $credit, PDO::PARAM_INT);
        $query->bindParam(':moduleid', $moduleid, PDO::PARAM_INT);
        $query->execute();

        $msg = "Module updated successfully";
    }

    // Fetch module details
    $sql = "SELECT * FROM module WHERE id = :moduleid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':moduleid', $moduleid, PDO::PARAM_INT);
    $query->execute();
    $module = $query->fetch(PDO::FETCH_OBJ);

    // Fetch all departments
    $deptSql = "SELECT * FROM department";
    $deptQuery = $dbh->prepare($deptSql);
    $deptQuery->execute();
    $departments = $deptQuery->fetchAll(PDO::FETCH_OBJ);

    // Fetch courses for the department of the selected module
    $courseSql = "SELECT * FROM course WHERE did = (SELECT did FROM course WHERE id = :cid)";
    $courseQuery = $dbh->prepare($courseSql);
    $courseQuery->bindParam(':cid', $module->cid, PDO::PARAM_INT);
    $courseQuery->execute();
    $courses = $courseQuery->fetchAll(PDO::FETCH_OBJ);
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Module Update</title>
        <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
        <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" href="../css/prism/prism.css" media="screen">
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
                                    <h2 class="title">Update Module</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="manage.php">Modules</a></li>
                                        <li class="active">Update Module</li>
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
                                                    <h5>Update Module</h5>
                                                </div>
                                            </div>
                                            <?php if ($msg) { ?>
                                                <div class="alert alert-success left-icon-alert" role="alert">
                                                    <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                                    <meta http-equiv='refresh' content='1.5'>
                                                </div>
                                            <?php } ?>
                                            <div class="panel-body">
                                                <form method="post">
                                                    <div class="form-group">
                                                        <label for="department">Department</label>
                                                        <select class="form-control" id="department">
                                                            <option value="">Select Department</option>
                                                            <?php foreach ($departments as $dept) { ?>
                                                                <option value="<?php echo htmlentities($dept->id); ?>" <?php echo ($dept->id == $module->did) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlentities($dept->dname); ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="course">Course</label>
                                                        <select name="cid" class="form-control" id="course" required>
                                                            <option value="">Select Course</option>
                                                            <?php foreach ($courses as $course) { ?>
                                                                <option value="<?php echo htmlentities($course->id); ?>" <?php echo ($course->id == $module->cid) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlentities($course->cname); ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="mcode">Module Code</label>
                                                        <input type="text" name="mcode" class="form-control" value="<?php echo htmlentities($module->mcode); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="mname">Module Name</label>
                                                        <input type="text" name="mname" class="form-control" value="<?php echo htmlentities($module->mname); ?>" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="mname">Module Credit</label>
                                                        <input type="number" name="credit" class="form-control" value="<?php echo htmlentities($module->credit); ?>" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="semester">Semester</label>
                                                        <select name="semester" class="form-control" required>
                                                            <option value="" disabled>Select Semester</option>
                                                            <?php for ($i = 1; $i <= 6; $i++) { ?>
                                                                <option value="<?php echo $i; ?>" <?php echo ($i == $module->Semester) ? 'selected' : ''; ?>><?php echo "Semester " . $i; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <button type="submit" name="update" class="btn btn-primary">Update</button>
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