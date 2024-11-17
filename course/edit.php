<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) { // Ensures session is valid
    header("Location: index.php");
} else {
    $courseid = intval($_GET['courseid']); // Get course ID from URL

    if (isset($_POST['update'])) {
        $cname = $_POST['cname'];
        $deptId = $_POST['department'];

        $sql = "UPDATE course SET cname=:cname, did=:deptId WHERE id=:courseid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':cname', $cname, PDO::PARAM_STR);
        $query->bindParam(':deptId', $deptId, PDO::PARAM_INT);
        $query->bindParam(':courseid', $courseid, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() > 0) {
            $msg = "Course updated successfully";
        } else {
            $error = "No changes made or something went wrong. Please try again.";
        }
    }

    // Fetch existing course details for pre-filling the form
    $sql = "SELECT * FROM course WHERE id=:courseid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':courseid', $courseid, PDO::PARAM_INT);
    $query->execute();
    $course = $query->fetch(PDO::FETCH_OBJ);

    if (!$course) {
        $error = "Course not found.";
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Update Course</title>
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
        <?php if ($_SESSION['alogin']) { include('../includes/topbar.php'); } ?>

        <div class="content-wrapper">
            <div class="content-container">
                <?php if ($_SESSION['alogin']) { include('../includes/leftbar.php'); } ?>

                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Update Course</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="#">Courses</a></li>
                                    <li class="active">Update Course</li>
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
                                                <h5>Update Course Info</h5>
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
                                                <div class="form-group has-success">
                                                    <label for="success" class="control-label">Course Name</label>
                                                    <div>
                                                        <input type="text" name="cname" class="form-control" value="<?php echo htmlentities($course->cname); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="form-group has-success">
                                                    <label for="success" class="control-label">Department</label>
                                                    <div>
                                                        <select name="department" class="form-control" required>
                                                            <option value="">Select Department</option>
                                                            <?php
                                                            $sql = "SELECT * FROM department";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                            if ($query->rowCount() > 0) {
                                                                foreach ($results as $result) {
                                                                    $selected = $course->did == $result->id ? "selected" : "";
                                                                    echo "<option value='" . htmlentities($result->id) . "' $selected>" . htmlentities($result->dname) . "</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group has-success">
                                                    <div>
                                                        <button type="submit" name="update" class="btn btn-success btn-labeled">
                                                            Update <span class="btn-label btn-label-right"><i class="fa fa-check"></i></span>
                                                        </button>
                                                    </div>
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
</body>

</html>
<?php } ?>
