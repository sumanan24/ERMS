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

    // Code for Deletion
    if (isset($_GET['id'])) {
        if ($currentRole === 'user') { echo '<script>alert("You do not have permission to delete.");</script>'; } else {
            $examid = $_GET['id'];
            try {
                $dbh->beginTransaction();
                $delRes = $dbh->prepare("DELETE FROM results WHERE examid = :examid");
                $delRes->bindParam(':examid', $examid, PDO::PARAM_STR);
                $delRes->execute();
                $delExam = $dbh->prepare("DELETE FROM exam WHERE id = :examid");
                $delExam->bindParam(':examid', $examid, PDO::PARAM_STR);
                $delExam->execute();
                $dbh->commit();
                echo '<script>alert("Exam and related results deleted successfully.");</script>';
            } catch (Exception $e) {
                $dbh->rollBack();
                $msg = addslashes($e->getMessage());
                echo '<script>alert("Delete failed: ' . $msg . '");</script>';
            }
        }
    }

    // Fetch filters
    $courseFilter = isset($_POST['course']) ? $_POST['course'] : '';
    $batchFilter = isset($_POST['batch']) ? $_POST['batch'] : '';
    $semesterFilter = isset($_POST['semester']) ? $_POST['semester'] : '';

    // Build SQL query based on filters
    $sql = "SELECT e.id, e.date, m.mname as module_name, b.batch_no, c.cname as course_name,
                (SELECT COUNT(*) FROM results r WHERE r.examid = e.id) as total_students,
                (SELECT COUNT(*) FROM results r WHERE r.examid = e.id AND r.marks > 40) as above_40
            FROM exam e
            JOIN module m ON e.mid = m.id
            JOIN batch b ON e.bid = b.id
            JOIN course c ON b.cid = c.id";

    $conditions = [];
    if ($courseFilter) {
        $conditions[] = "c.id = :course";
    }
    if ($batchFilter) {
        $conditions[] = "b.id = :batch";
    }
    if ($semesterFilter) {
        $conditions[] = "m.semester = :semester";
    }

    if (count($conditions) > 0) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $query = $dbh->prepare($sql);

    if ($courseFilter) {
        $query->bindParam(':course', $courseFilter, PDO::PARAM_STR);
    }
    if ($batchFilter) {
        $query->bindParam(':batch', $batchFilter, PDO::PARAM_STR);
    }
    if ($semesterFilter) {
        $query->bindParam(':semester', $semesterFilter, PDO::PARAM_STR);
    }

    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ERMS - Exams</title>
        <link rel="stylesheet" href="../css/bootstrap.min.css" media="screen">
        <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" type="text/css" href="../js/DataTables/datatables.min.css" />
        <link rel="stylesheet" href="../css/main.css" media="screen">
        <script src="../js/modernizr/modernizr.min.js"></script>
        <style>
            .errorWrap {
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #dd3d36;
                -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            }

            .succWrap {
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #5cb85c;
                -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            }
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
                                    <h2 class="title">Manage Exams</h2>
                                </div>
                                <div class="col-md-2" style="text-align:right;">
                                    <a href="new.php" class="btn btn-modern">New Exam</a>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li class="active">Manage Exams</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="modern-card">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>Filter Exams</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body">
                                                <form method="POST">
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <div class="form-group">
                                                                <label for="course">Course</label>
                                                                <select name="course" id="course" class="form-control">
                                                                    <option value="">Select Course</option>
                                                                    <?php
                                                                    $courseSql = "SELECT id, cname FROM course";
                                                                    $courseQuery = $dbh->prepare($courseSql);
                                                                    $courseQuery->execute();
                                                                    $courses = $courseQuery->fetchAll(PDO::FETCH_OBJ);
                                                                    foreach ($courses as $course) {
                                                                        echo '<option value="' . $course->id . '">' . $course->cname . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <div class="form-group">
                                                                <label for="batch">Batch</label>
                                                                <select name="batch" id="batch" class="form-control">
                                                                    <option value="">Select Batch</option>
                                                                    <?php
                                                                    $batchSql = "SELECT id, batch_no FROM batch";
                                                                    $batchQuery = $dbh->prepare($batchSql);
                                                                    $batchQuery->execute();
                                                                    $batches = $batchQuery->fetchAll(PDO::FETCH_OBJ);
                                                                    foreach ($batches as $batch) {
                                                                        echo '<option value="' . $batch->id . '">' . $batch->batch_no . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <div class="form-group">
                                                                <label for="semester">Semester</label>
                                                                <select name="semester" id="semester" class="form-control">
                                                                    <option value="">Select Semester</option>
                                                                    <option value="1">Semester 1</option>
                                                                    <option value="2">Semester 2</option>
                                                                    <option value="3">Semester 3</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-2">
                                                            <button type="submit" class="btn btn-modern" style="width: 100%;">Search Exam</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="modern-card">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>View Exams Info</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body p-20">
                                                <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Module Name</th>
                                                            <th>Batch No</th>
                                                            <th>Date</th>
                                                            <th>Total Students</th>
                                                            <th>Above 40</th>
                                                            <th>Percentage Above 40</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tfoot>
                                                        <tr>
                                                            <th>Module Name</th>
                                                            <th>Batch No</th>
                                                            <th>Date</th>
                                                            <th>Total Students</th>
                                                            <th>Above 40</th>
                                                            <th>Percentage Above 40</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </tfoot>
                                                    <tbody>
                                                        <?php
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $result) {
                                                                $percentage = ($result->total_students > 0) ? round(($result->above_40 / $result->total_students) * 100, 2) : 0;
                                                        ?>
                                                                <tr>
                                                                    <td><?php echo htmlentities($result->module_name); ?></td>
                                                                    <td><?php echo htmlentities($result->batch_no); ?></td>
                                                                    <td><?php echo htmlentities($result->date); ?></td>
                                                                    <td><?php echo htmlentities($result->total_students); ?></td>
                                                                    <td><?php echo htmlentities($result->above_40); ?></td>
                                                                    <td>
                                                                        <div class="progress">
                                                                            <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentage; ?>%;">
                                                                                <?php echo $percentage; ?>%
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                    <button class="btn btn-info btn-xs" data-toggle="modal" data-target="#editExamModal" data-id="<?php echo $result->id; ?>" data-date="<?php echo $result->date; ?>" data-time="<?php echo $result->time; ?>">Edit</button>
                                                                        <a href="result.php?examid=<?php echo htmlentities($result->id); ?>" class="btn btn-primary btn-xs"> Add Result </a>
                                                                        <?php if ($currentRole==='admin') { ?>
                                                                            <a href="?id=<?php echo $result->id; ?>" onClick="return confirm('Are you sure you want to delete this exam?')" class="btn btn-danger btn-xs">Delete</a>
                                                                        <?php } ?>
                                                                    </td>
                                                                </tr>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
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

        <!-- Edit Exam Modal -->
        <div class="modal fade" id="editExamModal" tabindex="-1" role="dialog" aria-labelledby="editExamModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="editExamModalLabel">Edit Exam</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="examid" id="examid">
                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="date" class="form-control" name="date" id="date" required>
                            </div>
                            <div class="form-group">
                                <label for="time">Time</label>
                                <input type="time" class="form-control" name="time" id="time" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" name="update_exam" class="btn btn-modern">Save changes</button>
                        </div>
                    </form>
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
            $(function($) {
                $('#example').DataTable();
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#example').DataTable();

                // Populate modal with data
                $('#editExamModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    var id = button.data('id');
                    var date = button.data('date');
                    var time = button.data('time');

                    var modal = $(this);
                    modal.find('#examid').val(id);
                    modal.find('#date').val(date);
                    modal.find('#time').val(time);
                });
            });
        </script>
    </body>

    </html>
<?php } ?>