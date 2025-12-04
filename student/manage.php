<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: ../index.php");
    exit;
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$currentRole = 'admin';
try {
    $u = $_SESSION['alogin'];
    $st = $dbh->prepare("SELECT usertype FROM admin WHERE (username=:u OR UserName=:u) LIMIT 1");
    $st->bindParam(':u', $u, PDO::PARAM_STR);
    $st->execute();
    $r = $st->fetch(PDO::FETCH_OBJ);
    if ($r && isset($r->usertype)) {
        $currentRole = $r->usertype;
    }
} catch (Exception $e) {
    // silently fail (you can log $e->getMessage() to a server log)
}

// Handle Delete (POST) - safer than GET
$flashMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // Validate CSRF token
    $postedToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $flashMessage = '<div class="alert alert-danger">Invalid CSRF token.</div>';
    } elseif ($currentRole !== 'admin') {
        $flashMessage = '<div class="alert alert-danger">You do not have permission to delete students.</div>';
    } else {
        // Validate delete_id
        $deleteId = $_POST['delete_id'];
        if (!ctype_digit((string)$deleteId)) {
            $flashMessage = '<div class="alert alert-danger">Invalid student id.</div>';
        } else {
            try {
                $delStmt = $dbh->prepare("DELETE FROM student WHERE id = :id LIMIT 1");
                $delStmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
                if ($delStmt->execute()) {
                    // Optional: Also delete related records if needed (enrollments, transcripts, files) - not covered here
                    // Redirect to avoid resubmission
                    $_SESSION['flash_success'] = 'Student deleted successfully.';
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $flashMessage = '<div class="alert alert-danger">Unable to delete the student. Try again.</div>';
                }
            } catch (Exception $e) {
                $flashMessage = '<div class="alert alert-danger">Error while deleting student.</div>';
            }
        }
    }
}

// display flash from redirect
if (!empty($_SESSION['flash_success'])) {
    $flashMessage = '<div class="alert alert-success">'.htmlentities($_SESSION['flash_success']).'</div>';
    unset($_SESSION['flash_success']);
}

// Fetch Courses and Batches for Filters
try {
    $courseQuery = "SELECT id, cname FROM course ORDER BY cname";
    $courseStmt = $dbh->prepare($courseQuery);
    $courseStmt->execute();
    $courses = $courseStmt->fetchAll(PDO::FETCH_OBJ);

    $batchQuery = "SELECT DISTINCT batch_no FROM batch ORDER BY batch_no";
    $batchStmt = $dbh->prepare($batchQuery);
    $batchStmt->execute();
    $batches = $batchStmt->fetchAll(PDO::FETCH_OBJ);
} catch (Exception $e) {
    $courses = [];
    $batches = [];
}

// Get Selected Filters (POST or default)
$selectedCourse = isset($_POST['course']) ? trim($_POST['course']) : '';
$selectedBatch = isset($_POST['batch']) ? trim($_POST['batch']) : '';

// If filter form submitted, preserve it; otherwise no filters
// Fetch Students Based on Filters
$studentQuery = "SELECT s.*, c.cname FROM student s LEFT JOIN course c ON s.cid = c.id WHERE 1=1";
$params = [];

if ($selectedCourse !== '') {
    // ensure numeric id when possible
    if (ctype_digit((string)$selectedCourse)) {
        $studentQuery .= " AND s.cid = :course";
        $params[':course'] = (int)$selectedCourse;
    } else {
        // fallback to bind as string (if your cid is non-numeric)
        $studentQuery .= " AND s.cid = :course";
        $params[':course'] = $selectedCourse;
    }
}
if ($selectedBatch !== '') {
    $studentQuery .= " AND s.bid = :batch";
    $params[':batch'] = $selectedBatch;
}

$studentQuery .= " ORDER BY s.reg_no ASC";

try {
    $studentStmt = $dbh->prepare($studentQuery);
    foreach ($params as $k => $v) {
        if ($k === ':course' && is_int($v)) {
            $studentStmt->bindValue($k, $v, PDO::PARAM_INT);
        } else {
            $studentStmt->bindValue($k, $v, PDO::PARAM_STR);
        }
    }
    $studentStmt->execute();
    $students = $studentStmt->fetchAll(PDO::FETCH_OBJ);
} catch (Exception $e) {
    $students = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ERMS - Students</title>
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
        .mb-15 { margin-bottom: 15px; }
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
                                <h2 class="title">Manage Students</h2>
                            </div>
                            <div class="col-md-2" style="text-align:right;">
                                <a href="new.php" class="btn btn-modern">New Student</a>
                            </div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li class="active">Manage Students</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <section class="section">
                        <div class="container-fluid">
                            <?php echo $flashMessage; ?>

                            <form method="post" action="">
                                <div class="row mb-15">
                                    <div class="col-md-4">
                                        <label for="course">Filter by Course</label>
                                        <select name="course" id="course" class="form-control">
                                            <option value="">All Courses</option>
                                            <?php foreach ($courses as $course) : ?>
                                                <option value="<?php echo htmlentities($course->id); ?>" <?php if ($selectedCourse != '' && $selectedCourse == $course->id) echo 'selected'; ?>>
                                                    <?php echo htmlentities($course->cname); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="batch">Filter by Batch</label>
                                        <select name="batch" id="batch" class="form-control">
                                            <option value="">All Batches</option>
                                            <?php foreach ($batches as $batch) : ?>
                                                <option value="<?php echo htmlentities($batch->batch_no); ?>" <?php if ($selectedBatch != '' && $selectedBatch == $batch->batch_no) echo 'selected'; ?>>
                                                    <?php echo htmlentities($batch->batch_no); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-modern" style="width:100%;">Apply Filters</button>
                                    </div>
                                </div>
                            </form>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="panel modern-card">
                                        <div class="panel-heading">
                                            <h5>View Students Info</h5>
                                        </div>
                                        <div class="panel-body p-20">
                                            <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>Registration No</th>
                                                        <th>Full Name</th>
                                                        <th>NIC</th>
                                                        <th>Course</th>
                                                        <th>Batch No</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($students)) : ?>
                                                        <?php foreach ($students as $student) : ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($student->reg_no); ?></td>
                                                                <td><?php echo htmlentities($student->fullname); ?></td>
                                                                <td><?php echo htmlentities($student->nic); ?></td>
                                                                <td><?php echo htmlentities($student->cname); ?></td>
                                                                <td><?php echo htmlentities($student->bid); ?></td>
                                                                <td>
                                                                    <a href="transcript.php?studentid=<?php echo urlencode($student->id); ?>" class="btn btn-success btn-xs"> Transcript </a>
                                                                    <a href="edit.php?studentid=<?php echo urlencode($student->id); ?>" class="btn btn-info btn-xs"> Edit </a>

                                                                    <?php if ($currentRole === 'admin') : ?>
                                                                        <!-- Delete form (POST) -->
                                                                        <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                                            <input type="hidden" name="delete_id" value="<?php echo htmlentities($student->id); ?>">
                                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlentities($_SESSION['csrf_token']); ?>">
                                                                            <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                                                        </form>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center">No students found.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
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
    <script src="../js/bootstrap/bootstrap.min.js"></script>
    <script src="../js/pace/pace.min.js"></script>
    <script src="../js/lobipanel/lobipanel.min.js"></script>
    <script src="../js/iscroll/iscroll.js"></script>
    <script src="../js/DataTables/datatables.min.js"></script>
    <script src="../js/main.js"></script>
    <script>
        $(function($) {
            $('#example').DataTable({
                // You can add DataTables options here if you want
                "order": [[0, "asc"]]
            });
        });
    </script>
</body>

</html>
