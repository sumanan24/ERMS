<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
} else {
    // Fetch total counts
    $departmentCount = $dbh->query("SELECT COUNT(*) FROM department")->fetchColumn();
    $courseCount = $dbh->query("SELECT COUNT(*) FROM course")->fetchColumn();
    $studentCount = $dbh->query("SELECT COUNT(*) FROM student")->fetchColumn();
    $examCount = $dbh->query("SELECT COUNT(*) FROM exam")->fetchColumn();
    $batchCount = $dbh->query("SELECT COUNT(*) FROM batch")->fetchColumn();

    // Fetch pass rates department and course-wise
    $passRates = $dbh->query(
        "SELECT d.dname, c.cname, ROUND(SUM(CASE WHEN r.marks >= 40 THEN 1 ELSE 0 END) * 100.0 / COUNT(r.id), 2) as pass_rate FROM results r JOIN exam e ON r.examid = e.id JOIN module m ON e.mid=m.id JOIN course c ON m.cid = c.id JOIN department d ON c.did = d.id GROUP BY d.dname, c.cname;"
    )->fetchAll(PDO::FETCH_OBJ);
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Dashboard</title>
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
                                    <h2 class="title">Dashboard</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <h3 class="panel-title">Overview</h3>
                                            </div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <div class="alert alert-info text-center">
                                                            <strong>Departments</strong><br>
                                                            <?php echo $departmentCount; ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="alert alert-success text-center">
                                                            <strong>Courses</strong><br>
                                                            <?php echo $courseCount; ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="alert alert-warning text-center">
                                                            <strong>Students</strong><br>
                                                            <?php echo $studentCount; ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="alert alert-danger text-center">
                                                            <strong>Exams</strong><br>
                                                            <?php echo $examCount; ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="alert text-center" style="background-color: #5cb85c; "> 
                                                            <strong>Batches</strong><br>
                                                            <?php echo $batchCount; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <h3 class="text-center">Pass Rates (Department and Course-wise)</h3>
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Department</th>
                                                                    <th>Course</th>
                                                                    <th>Pass Rate (%)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($passRates as $rate) { ?>
                                                                    <tr>
                                                                        <td><?php echo htmlentities($rate->dname); ?></td>
                                                                        <td><?php echo htmlentities($rate->cname); ?></td>
                                                                        <td><?php echo htmlentities($rate->pass_rate); ?>%</td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
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
