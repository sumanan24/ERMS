<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: ../index.php");
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

    if (isset($_GET['id'])) {
        if ($currentRole === 'user') { echo '<script>alert("You do not have permission to delete.");</script>'; }
        else {
            $classid = $_GET['id'];
            $sql = "DELETE FROM module WHERE id = :classid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':classid', $classid, PDO::PARAM_STR);
            $query->execute();
            echo '<script>alert("Module deleted successfully.");</script>';
        }
    }
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ERMS - Modules</title>
        <link rel="stylesheet" href="../css/bootstrap.min.css" media="screen">
        <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" type="text/css" href="../js/DataTables/datatables.min.css" />
        <link rel="stylesheet" href="../css/main.css" media="screen">
        <script src="../js/modernizr/modernizr.min.js"></script>
        <style>
            body { background: #f5f7fb; color: #111827; }
            .modern-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 8px 18px rgba(0,0,0,0.05); overflow:hidden; }
            .modern-card .panel-heading { background:#fff; border-bottom:1px solid #e5e7eb; padding:16px 20px; }
            .modern-card .panel-title h5 { margin:0; font-weight:700; color:#111827; }
            .modern-card .panel-body { padding:22px; }
            .btn-modern { background:#2563eb; border-color:#2563eb; border-radius:10px; padding:8px 14px; font-weight:600; color:#fff; }
            .btn-modern:hover, .btn-modern:focus { background:#1d4ed8; border-color:#1d4ed8; }
            .page-title-div .title { font-weight:700; color:#111827; }
            .breadcrumb-div { margin-top:6px; }
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
                                <div class="col-md-10">
                                    <h2 class="title">Manage Modules</h2>
                                </div>

                                <div class="col-md-2">
                                <a href="new.php" class="btn btn-modern">New Module</a>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li class="active">Modules</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel modern-card">
                                            <div class="panel-heading">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="panel-title">
                                                            <h5>View Modules Info <span style="float: right;">Filter by Course </span></h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                    <div class="form-group">
                                                   
                                                    <select id="courseFilter" class="form-control" style="margin-top: 10px; width: 97%; ">
                                                        <option value="">All Courses</option>
                                                        <?php
                                                        $sql = "SELECT * FROM course";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $courses = $query->fetchAll(PDO::FETCH_OBJ);
                                                        foreach ($courses as $course) {
                                                            echo '<option value="' . $course->id . '">' . $course->cname . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                    </div>
                                                </div>

                                           
                                            </div>
                                            <div class="panel-body p-20">
                                                <table id="moduleTable" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th></th>
                                                            <th>Module Code</th>
                                                            <th>Module Name</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Data will be loaded via AJAX -->
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
            $(document).ready(function() {
                var userRole = <?php echo json_encode($currentRole); ?>;
                const table = $('#moduleTable').DataTable({
                    ajax: {
                        url: 'fetch_modules.php',
                        data: function(d) {
                            d.courseId = $('#courseFilter').val();
                        },
                        dataSrc: ''
                    },
                    columns: [{
                            data: 'id'
                        },
                        {
                            data: 'mcode'
                        },
                        {
                            data: 'mname'
                        },
                        {
                            data: 'id',
                            render: function(data) {
                                var html = `<a href="edit.php?moduleid=${data}" class="btn btn-info btn-xs">Edit</a>`;
                                if (userRole === 'admin') {
                                    html += ` <a href="?id=${data}" onClick="return confirm('Are you sure you want to delete?')" class="btn btn-danger btn-xs">Delete</a>`;
                                }
                                return html;
                            }
                        }
                    ]
                });

                $('#courseFilter').change(function() {
                    table.ajax.reload();
                });
            });
        </script>
    </body>

    </html>
<?php } ?>