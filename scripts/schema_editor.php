<?php
// exam_schedule.php - rewritten to match schema: date DATE NOT NULL, time INT NOT NULL

session_start();

// Debug flag: set true only for local debugging
$DEBUG = false;
if ($DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

require_once __DIR__ . '/../includes/config.php'; // ensure this sets $dbh (PDO)

// Simple logger (production safe): writes details to ../logs/exam_schedule_errors.log
function log_error_detail($msg) {
    $logFile = __DIR__ . '/../logs/exam_schedule_errors.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $time = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $_SESSION['alogin'] ?? 'guest';
    $entry = "[$time] IP:$ip USER:$user MSG: $msg\n-------------------------\n";
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

// Convert "HH:MM" (string) to minutes (int). Returns integer minutes or FALSE on invalid.
function time_to_minutes($t) {
    if ($t === null || $t === '') return false;
    // Accept formats like "09:30" or "9:30" or "09:30:00"
    $parts = explode(':', $t);
    if (count($parts) < 2) return false;
    $h = (int) $parts[0];
    $m = (int) $parts[1];
    if ($h < 0 || $h > 23 || $m < 0 || $m > 59) return false;
    return $h * 60 + $m;
}

// Ensure $dbh is PDO
if (!isset($dbh) || !($dbh instanceof PDO)) {
    if ($DEBUG) {
        die('Database connection ($dbh) is not defined or not a PDO instance. Check ../includes/config.php');
    } else {
        http_response_code(500);
        die('Server configuration error. Please contact the administrator.');
    }
}

$msg = '';
$error = '';

if (empty($_SESSION['alogin'])) {
    header("Location: ../index.php");
    exit;
}

// POST handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // sanitize basic inputs
    $batchId = isset($_POST['batch']) ? (int) $_POST['batch'] : 0;
    $modules = isset($_POST['module']) ? $_POST['module'] : [];
    $dates = isset($_POST['date']) ? $_POST['date'] : [];
    $times = isset($_POST['time']) ? $_POST['time'] : [];

    // basic validations
    if ($batchId <= 0) {
        $error = 'Please select a valid Batch.';
    } elseif (empty($modules) || !is_array($modules)) {
        $error = 'Please select at least one module.';
    } else {
        try {
            // PDO exceptions
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Normalize module IDs and remove duplicates
            $modules = array_values(array_unique(array_map('intval', $modules)));
            if (empty($modules)) {
                throw new Exception('Module IDs are invalid.');
            }

            // Duplicate check: find any existing entries for this batch and modules in a single query
            $placeholders = implode(',', array_fill(0, count($modules), '?'));
            $sqlCheck = "SELECT mid FROM exam WHERE bid = ? AND mid IN ($placeholders)";
            $stmtCheck = $dbh->prepare($sqlCheck);
            $params = array_merge([$batchId], $modules);
            $stmtCheck->execute($params);
            $existing = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($existing)) {
                $error = "An exam for at least one of the selected modules already exists for this batch. Module IDs: " . implode(', ', $existing);
            } else {
                // Insert using transaction
                $sqlInsert = "INSERT INTO exam (mid, bid, date, time, Status) VALUES (?, ?, ?, ?, 'Pending')";
                $stmtInsert = $dbh->prepare($sqlInsert);

                $dbh->beginTransaction();

                foreach ($modules as $idx => $moduleId) {
                    $moduleId = (int) $moduleId;
                    // pick corresponding date/time by index
                    $rawDate = isset($dates[$idx]) ? trim($dates[$idx]) : '';
                    $rawTime = isset($times[$idx]) ? trim($times[$idx]) : '';

                    // validate date (non-empty because DB date NOT NULL)
                    if ($rawDate === '') {
                        throw new Exception("Please provide a date for module ID {$moduleId}.");
                    }
                    $ts = strtotime($rawDate);
                    if ($ts === false) {
                        throw new Exception("Invalid date for module ID {$moduleId}: {$rawDate}");
                    }
                    $examDate = date('Y-m-d', $ts);

                    // convert time string (HH:MM) -> minutes integer for INT(11) column
                    $minutes = time_to_minutes($rawTime);
                    if ($minutes === false) {
                        throw new Exception("Invalid time for module ID {$moduleId}: {$rawTime}. Please use HH:MM format.");
                    }

                    // execute insert
                    $stmtInsert->execute([$moduleId, $batchId, $examDate, $minutes]);
                }

                $dbh->commit();
                $msg = "Exam schedule created successfully!";
            }
        } catch (Exception $ex) {
            if ($dbh->inTransaction()) {
                $dbh->rollBack();
            }
            // Log full details
            $errText = $ex->getMessage();
            if (method_exists($ex, 'getTraceAsString')) {
                $errText .= "\nTrace:\n" . $ex->getTraceAsString();
            }
            log_error_detail($errText);

            if ($DEBUG) {
                $error = "Error: " . $ex->getMessage();
            } else {
                $error = "Oh snap! An unexpected error occurred while saving. Please contact the administrator.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exam Schedule</title>

    <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
    <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="../css/prism/prism.css" media="screen">
    <link rel="stylesheet" href="../css/main.css" media="screen">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .errorWrap { padding:10px; margin:0 0 20px 0; background:#fff; border-left:4px solid #dd3d36; box-shadow:0 1px 1px rgba(0,0,0,0.1); }
        .succWrap { padding:10px; margin:0 0 20px 0; background:#fff; border-left:4px solid #5cb85c; box-shadow:0 1px 1px rgba(0,0,0,0.1); }
        body { background: #f5f7fb; color: #111827; }
        .modern-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 8px 18px rgba(0,0,0,0.05); overflow:hidden; }
        .panel-heading { padding:16px 20px; border-bottom:1px solid #e5e7eb; background:#fff; }
        .panel-title h5 { margin:0; font-weight:700; color:#111827; }
        .panel-body { padding:22px; }
        .form-group label { font-size:13px; color:#6b7280; margin-bottom:6px; }
        .form-control { height:44px; border-radius:10px; border:1px solid #e5e7eb; }
        .form-control:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.15); }
        .btn-modern { background:#2563eb; border-color:#2563eb; border-radius:10px; padding:10px 16px; font-weight:600; color:#fff; }
        .btn-modern:hover { background:#1d4ed8; }
        @media (max-width:767px){ .btn-block-sm{ width:100%; display:block; } .form-control{ height:auto; } }
        #module-table td input, #module-table td select { width:100%; box-sizing:border-box; }
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
                            <div class="col-md-6"><h2 class="title">Exam Schedule</h2></div>
                        </div>
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="manage.php">Exam Schedules</a></li>
                                    <li class="active">Create Exam Schedule</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-sm-12 col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
                                    <div class="panel modern-card">
                                        <div class="panel-heading"><div class="panel-title"><h5>Department</h5></div></div>

                                        <?php if (!empty($msg)) : ?>
                                            <div class="alert alert-success left-icon-alert" role="alert"><strong>Well done!</strong> <?php echo htmlentities($msg); ?></div>
                                        <?php elseif (!empty($error)) : ?>
                                            <div class="alert alert-danger left-icon-alert" role="alert"><strong>Oh snap!</strong> <?php echo htmlentities($error); ?></div>
                                        <?php endif; ?>

                                        <div class="panel-body">
                                            <form method="post" id="examForm" novalidate>
                                                <div class="form-group">
                                                    <label for="department">Select Department</label>
                                                    <select name="department" id="department" class="form-control" required>
                                                        <option value="">Select Department</option>
                                                        <?php
                                                        // fetch department list
                                                        try {
                                                            $stmt = $dbh->prepare("SELECT id, dname FROM department ORDER BY dname ASC");
                                                            $stmt->execute();
                                                            $deps = $stmt->fetchAll(PDO::FETCH_OBJ);
                                                            foreach ($deps as $d) {
                                                                echo '<option value="' . htmlentities($d->id) . '">' . htmlentities($d->dname) . '</option>';
                                                            }
                                                        } catch (Exception $e) {
                                                            if ($DEBUG) {
                                                                echo '<option value="">' . htmlentities($e->getMessage()) . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="course">Select Course</label>
                                                    <select name="course" id="course" class="form-control" required>
                                                        <option value="">Select Course</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="semester">Select Semester</label>
                                                    <select name="semester" id="semester" class="form-control" required>
                                                        <option value="">Select Semester</option>
                                                        <option value="1">Semester 1</option>
                                                        <option value="2">Semester 2</option>
                                                        <option value="3">Semester 3</option>
                                                        <option value="4">Semester 4</option>
                                                        <option value="5">Semester 5</option>
                                                        <option value="6">Semester 6</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="batch">Select Batch</label>
                                                    <select name="batch" id="batch" class="form-control" required>
                                                        <option value="">Select Batch</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label>Exam Schedule</label>
                                                    <table class="table table-bordered" id="module-table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:45%;">Module</th>
                                                                <th style="width:27%;">Date</th>
                                                                <th style="width:27%;">Time</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- get_modules.php should output rows like:
                                                              <tr>
                                                                <td><input type="checkbox" name="module[]" value="123"> Module Name</td>
                                                                <td><input type="date" name="date[]" class="form-control" value=""></td>
                                                                <td><input type="time" name="time[]" class="form-control" value=""></td>
                                                              </tr>
                                                            -->
                                                        </tbody>
                                                    </table>
                                                    <small class="text-muted">For the server to accept values your module rows must use the names <code>module[]</code>, <code>date[]</code> (YYYY-MM-DD), and <code>time[]</code> (HH:MM). Time will be stored as integer minutes.</small>
                                                </div>

                                                <button type="submit" name="submit" class="btn btn-modern btn-block-sm"><i class="fa fa-save"></i> Save Schedule</button>
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

    <script>
    $(document).ready(function(){
        // Load courses based on department
        $('#department').change(function() {
            var deptId = $(this).val();
            if (deptId) {
                $.ajax({
                    url: "get_courses.php",
                    method: "POST",
                    data: { deptId: deptId },
                    success: function(data) {
                        $('#course').html(data);
                        $('#batch').html('<option value="">Select Batch</option>');
                        $('#module-table tbody').empty();
                        $('#semester').val('');
                    },
                    error: function() {
                        $('#course').html('<option value="">Error loading courses</option>');
                    }
                });
            } else {
                $('#course').html('<option value="">Select Course</option>');
                $('#batch').html('<option value="">Select Batch</option>');
                $('#module-table tbody').empty();
                $('#semester').val('');
            }
        });

        // Load batches based on course
        $('#course').change(function() {
            var courseId = $(this).val();
            if (courseId) {
                $.ajax({
                    url: "get_batches.php",
                    method: "POST",
                    data: { courseId: courseId },
                    success: function(data) {
                        $('#batch').html(data);
                        $('#module-table tbody').empty();
                        $('#semester').val('');
                    },
                    error: function() {
                        $('#batch').html('<option value="">Error loading batches</option>');
                    }
                });
            } else {
                $('#batch').html('<option value="">Select Batch</option>');
                $('#module-table tbody').empty();
                $('#semester').val('');
            }
        });

        // Load modules based on course and semester
        $('#semester').change(function() {
            var courseId = $('#course').val();
            var semester = $(this).val();
            if (courseId && semester) {
                $.ajax({
                    url: "get_modules.php",
                    method: "POST",
                    data: { courseId: courseId, semester: semester },
                    success: function(data) {
                        $('#module-table tbody').html(data);
                    },
                    error: function() {
                        $('#module-table tbody').html('<tr><td colspan="3">Error loading modules</td></tr>');
                    }
                });
            } else {
                $('#module-table tbody').empty();
            }
        });

        // Prevent submit when no rows present
        $('#examForm').on('submit', function(e) {
            var rows = $('#module-table tbody tr').length;
            if (rows === 0) {
                e.preventDefault();
                alert('Please load modules (select course & semester) and choose at least one module before submitting.');
            }
        });
    });
    </script>

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
