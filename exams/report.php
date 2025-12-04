<?php
session_start();
error_reporting(E_ALL); // Enable error reporting for debugging
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
    exit;
} else {
    if (isset($_POST['search'])) {
        $courseId = $_POST['course'];
        $batchId = $_POST['batch'];

        // Fetch all modules for the selected course
        $modulesSql = "SELECT * FROM module WHERE cid = :courseId";
        $modulesQuery = $dbh->prepare($modulesSql);
        $modulesQuery->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $modulesQuery->execute();
        $modules = $modulesQuery->fetchAll(PDO::FETCH_OBJ);

        // Fetch all students in the selected batch and course
        $studentsSql = "SELECT id, reg_no, fullname FROM student WHERE cid = :courseId AND bid = :batchId";
        $studentsQuery = $dbh->prepare($studentsSql);
        $studentsQuery->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $studentsQuery->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $studentsQuery->execute();
        $students = $studentsQuery->fetchAll(PDO::FETCH_OBJ);

        // Fetch marks for all students in the selected course and batch
        $marksSql = "SELECT sm.studentid, m.id AS module_id, sm.marks 
                     FROM results sm 
                     JOIN exam e ON e.id = sm.examid
                     JOIN module m ON m.id = e.mid
                     WHERE sm.studentid IN (SELECT id FROM student WHERE cid = :courseId AND bid = :batchId)order by m.mcode ";
        $marksQuery = $dbh->prepare($marksSql);
        $marksQuery->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $marksQuery->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $marksQuery->execute();
        $marksData = $marksQuery->fetchAll(PDO::FETCH_OBJ);

        // Prepare marks for the table (Student -> Module -> Marks)
        $marksByStudent = [];
        foreach ($marksData as $mark) {
            // Create an entry for each student and module
            $marksByStudent[$mark->studentid][$mark->module_id] = $mark->marks;
        }
    }

    // Fetch all courses
    $coursesSql = "SELECT * FROM course";
    $coursesQuery = $dbh->prepare($coursesSql);
    $coursesQuery->execute();
    $courses = $coursesQuery->fetchAll(PDO::FETCH_OBJ);
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Course and Batch Search</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
        <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" href="../css/main.css" media="screen">
        <script src="../js/modernizr/modernizr.min.js"></script>
        <style>
            body { background: #f5f7fb; color:#111827; }
            .modern-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 8px 18px rgba(0,0,0,0.05); overflow:hidden; }
            .modern-card .panel-heading { background:#fff; border-bottom:1px solid #e5e7eb; padding:16px 20px; }
            .modern-card .panel-title h5 { margin:0; font-weight:700; color:#111827; }
            .modern-card .panel-body { padding:22px; }
            .btn-modern { background:#2563eb; border-color:#2563eb; border-radius:10px; padding:10px 16px; font-weight:600; color:#fff; }
            .btn-modern:hover, .btn-modern:focus { background:#1d4ed8; border-color:#1d4ed8; }
            label { font-size:13px; color:#6b7280; margin-bottom:6px; }
            .form-control { height:44px; border-radius:10px; border:1px solid #e5e7eb; box-shadow:none; }
            .form-control:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.15); }
            .table thead th { background:#f8f9fa; }
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
                                    <h2 class="title">Course and Batch Search</h2>
                                </div>
                            </div>
                        </div>

                        <section class="section">
                            <div class="container-fluid">
                                <div class="panel modern-card">
                                    <div class="panel-heading"><div class="panel-title"><h5>Exam Report</h5></div></div>
                                    <div class="panel-body">
                                        <form method="post">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="course">Course</label>
                                                        <select name="course" id="course" class="form-control" required>
                                                            <option value="">Select Course</option>
                                                            <?php foreach ($courses as $course): ?>
                                                                <option value="<?php echo htmlentities($course->id); ?>"><?php echo htmlentities($course->cname); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="batch">Batch</label>
                                                        <select name="batch" id="batch" class="form-control" required>
                                                            <option value="">Select Batch</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group" style="margin-top:24px;">
                                                        <button type="submit" name="search" class="btn btn-modern" style="width:100%;">Search</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                        <?php if (isset($students) && count($students) > 0 && isset($modules) && count($modules) > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Reg No</th>
                                                            <th>Full Name</th>
                                                            <?php foreach ($modules as $module): ?>
                                                                <th><?php echo htmlentities($module->mname); ?></th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($students as $student): ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($student->reg_no); ?></td>
                                                                <td><?php echo htmlentities($student->fullname); ?></td>
                                                                <?php foreach ($modules as $module): ?>
                                                                    <td>
                                                                        <?php
                                                                        echo isset($marksByStudent[$student->id][$module->id]) ? htmlentities($marksByStudent[$student->id][$module->id]) : 'N/A';
                                                                        ?>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Excel Export -->
                                            <form method="post" action="export_to_excel.php">
                                                <input type="hidden" name="course" value="<?php echo $courseId; ?>">
                                                <input type="hidden" name="batch" value="<?php echo $batchId; ?>">
                                                <button type="submit" name="export_excel" class="btn btn-success">Export to Excel</button>
                                            </form>
                                        <?php else: ?>
                                            <p style="color:#6b7280;">No data found.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Dynamic batch selection based on course selection
            $('#course').change(function() {
                var courseId = $(this).val();
                if (courseId) {
                    $.ajax({
                        url: 'get_batch.php',
                        type: 'POST',
                        data: {
                            course_id: courseId
                        },
                        success: function(data) {
                            $('#batch').html(data);
                        }
                    });
                } else {
                    $('#batch').html('<option value="">Select Batch</option>');
                }
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
<?php } ?>