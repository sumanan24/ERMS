<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
} else {
    $currentRole = 'admin';
    try {
        $u = $_SESSION['alogin'];
        $st = $dbh->prepare("SELECT usertype FROM admin WHERE (username=:u OR UserName=:u) LIMIT 1");
        $st->bindParam(':u', $u, PDO::PARAM_STR);
        $st->execute();
        $r = $st->fetch(PDO::FETCH_OBJ);
        if ($r && isset($r->usertype)) { $currentRole = $r->usertype; }
    } catch (Exception $e) {}
    if ($currentRole === 'user') { header("Location: manage.php"); exit; }

    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $reg_no = $_POST['reg_no'];
        $fullname = $_POST['fullname'];
        $nic = $_POST['nic'];
        $courseId = isset($_POST['course']) ? $_POST['course'] : null;
        $batchNo = isset($_POST['batch']) ? $_POST['batch'] : null;

        $sql = "UPDATE student SET reg_no=:reg_no, fullname=:fullname, nic=:nic, cid=:cid, bid=:bid WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':reg_no', $reg_no, PDO::PARAM_STR);
        $query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $query->bindParam(':nic', $nic, PDO::PARAM_STR);
        $query->bindParam(':cid', $courseId, PDO::PARAM_INT);
        $query->bindParam(':bid', $batchNo, PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() > 0) {
            $msg = "Student Updated Successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }
    }

    $id = intval($_GET['studentid']);
    $sql = "SELECT s.*, c.did FROM student s LEFT JOIN course c ON s.cid=c.id WHERE s.id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    $departments = $dbh->query("SELECT * FROM department")->fetchAll(PDO::FETCH_OBJ);
    $currentDeptId = $result ? $result->did : null;
    $courses = [];
    if ($currentDeptId) {
        $cq = $dbh->prepare("SELECT * FROM course WHERE did=:did");
        $cq->bindParam(':did', $currentDeptId, PDO::PARAM_INT);
        $cq->execute();
        $courses = $cq->fetchAll(PDO::FETCH_OBJ);
    }
    $batches = [];
    if ($result && $result->cid) {
        $bq = $dbh->prepare("SELECT batch_no FROM batch WHERE cid=:cid ORDER BY batch_no");
        $bq->bindParam(':cid', $result->cid, PDO::PARAM_INT);
        $bq->execute();
        $batches = $bq->fetchAll(PDO::FETCH_OBJ);
    }
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit Student</title>
        <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
        <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
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
                                    <h2 class="title">Edit Student</h2>
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
                                                    <h5>Edit Student</h5>
                                                </div>
                                            </div>

                                            <div class="panel-body">
                                                <?php if ($msg) { ?>
                                                    <div class="alert alert-success left-icon-alert" role="alert">
                                                        <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                                        <meta http-equiv='refresh' content='1.5'>
                                                    </div>
                                                <?php } ?>
                                                <form method="post">
                                                    <input type="hidden" name="id" value="<?php echo htmlentities($result->id); ?>">

                                                    <div class="form-group">
                                                        <label for="reg_no">Registration Number</label>
                                                        <input type="text" name="reg_no" class="form-control" value="<?php echo htmlentities($result->reg_no); ?>" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="fullname">Full Name</label>
                                                        <input type="text" name="fullname" class="form-control" value="<?php echo htmlentities($result->fullname); ?>" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="nic">NIC</label>
                                                        <input type="text" name="nic" class="form-control" value="<?php echo htmlentities($result->nic); ?>" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="department1">Department</label>
                                                        <select id="department1" class="form-control">
                                                            <option value="">Select Department</option>
                                                            <?php foreach ($departments as $dept) { ?>
                                                                <option value="<?php echo $dept->id; ?>" <?php if ($currentDeptId == $dept->id) echo 'selected'; ?>><?php echo htmlentities($dept->dname); ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="course1">Course</label>
                                                        <select name="course" id="course1" class="form-control" required>
                                                            <option value="">Select Course</option>
                                                            <?php foreach ($courses as $course) { ?>
                                                                <option value="<?php echo $course->id; ?>" <?php if ($result->cid == $course->id) echo 'selected'; ?>><?php echo htmlentities($course->cname); ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="batch1">Batch</label>
                                                        <select name="batch" id="batch1" class="form-control" required>
                                                            <option value="">Select Batch</option>
                                                            <?php foreach ($batches as $batch) { ?>
                                                                <option value="<?php echo $batch->batch_no; ?>" <?php if ($result->bid == $batch->batch_no) echo 'selected'; ?>><?php echo htmlentities($batch->batch_no); ?></option>
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
                $('#department1').change(function() {
                    var deptId = $(this).val();
                    if (deptId) {
                        $.ajax({
                            url: 'get_courses.php',
                            method: 'POST',
                            data: { deptId: deptId },
                            success: function(data) {
                                $('#course1').html(data);
                                $('#batch1').html('<option value="">Select Batch</option>');
                            }
                        });
                    } else {
                        $('#course1').html('<option value="">Select Course</option>');
                        $('#batch1').html('<option value="">Select Batch</option>');
                    }
                });

                $('#course1').change(function() {
                    var courseId = $(this).val();
                    if (courseId) {
                        $.ajax({
                            url: 'get_batches.php',
                            method: 'POST',
                            data: { courseId: courseId },
                            success: function(data) {
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