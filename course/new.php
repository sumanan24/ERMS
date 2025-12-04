<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) { // Ensures session is valid
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        $cname = $_POST['cname'];
        $deptId = $_POST['department'];

        $sql = "INSERT INTO course(cname, did) VALUES(:cname, :deptId)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':cname', $cname, PDO::PARAM_STR);
        $query->bindParam(':deptId', $deptId, PDO::PARAM_INT);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if ($lastInsertId) {
            $msg = "Course Created successfully";
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
    <title>Admin Create Course</title>
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
        body { background: #f5f7fb; color: #111827; }
        .modern-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 8px 18px rgba(0,0,0,0.05); overflow:hidden; }
        .modern-card .panel-heading { background:#fff; border-bottom:1px solid #e5e7eb; padding:16px 20px; }
        .modern-card .panel-title h5 { margin:0; font-weight:700; color:#111827; }
        .modern-card .panel-body { padding:22px; }
        .form-group label { font-size:13px; color:#6b7280; margin-bottom:6px; }
        .form-control { height:44px; border-radius:10px; border:1px solid #e5e7eb; box-shadow:none; }
        .form-control:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.15); }
        .btn-modern { background:#2563eb; border-color:#2563eb; border-radius:10px; padding:10px 16px; font-weight:600; }
        .btn-modern:hover, .btn-modern:focus { background:#1d4ed8; border-color:#1d4ed8; }
        .page-title-div .title { font-weight:700; color:#111827; }
        .breadcrumb-div { margin-top:6px; }
        @media (max-width: 767px){ .btn-block-sm { width:100%; display:block; } }
    </style>
</head>

<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <!-- Conditional Navbar -->
        <?php if ($_SESSION['alogin']) { ?>
            <?php include('../includes/topbar.php'); ?>
        <?php } else { ?>
            <p class="text-center">You are not logged in. Please log in to access the dashboard.</p>
        <?php } ?>
        
        <div class="content-wrapper">
            <div class="content-container">
                <?php if ($_SESSION['alogin']) { include('../includes/leftbar.php'); } ?>
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Create Course</h2>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="manage.php">Courses</a></li>
                                    <li class="active">Create Course</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-sm-12 col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
                                    <div class="panel modern-card">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>Create New Course</h5>
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
                                                    <label for="course_name" class="control-label">Course Name</label>
                                                    <input type="text" name="cname" class="form-control" required id="course_name" placeholder="e.g. BSc in IT">
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label">Department</label>
                                                    <select name="department" class="form-control" required>
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
                                                <div class="form-group" style="margin-top:16px;">
                                                    <button type="submit" name="submit" class="btn btn-modern btn-block-sm">
                                                        <i class="fa fa-save"></i> Save Course
                                                    </button>
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
