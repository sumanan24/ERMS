<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        $dname = $_POST['dname'];
        $cid = $_POST['cid'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $batch_no = $_POST['batch_no'];

        // Insert batch information into the database
        $sql = "INSERT INTO batch (batch_no, start_date, end_date, cid) VALUES (:batch_no, :start_date, :end_date, :cid)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':batch_no', $batch_no, PDO::PARAM_STR);
        $query->bindParam(':start_date', $start_date, PDO::PARAM_STR);
        $query->bindParam(':end_date', $end_date, PDO::PARAM_STR);
        $query->bindParam(':cid', $cid, PDO::PARAM_INT);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if ($lastInsertId) {
            $msg = "Batch Created successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }
    }

    // Fetch all departments for the dropdown
    $sql = "SELECT * FROM department";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Batch Management</title>
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
                                    <h2 class="title">Create Batch</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="manage.php">Batches</a></li>
                                        <li class="active">Create Batch</li>
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
                                                    <h5>Create Batch</h5>
                                                </div>
                                            </div>
                                            <?php if ($msg) { ?>
                                                <div class="alert alert-success" role="alert">
                                                    <strong>Success!</strong> <?php echo htmlentities($msg); ?>
                                                </div>
                                            <?php } else if ($error) { ?>
                                                <div class="alert alert-danger" role="alert">
                                                    <strong>Error!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                            <?php } ?>
                                            <div class="panel-body">
                                                <form method="post" onsubmit="return validateBatchForm()">
                                                    <div class="form-group">
                                                        <label for="department">Department</label>
                                                        <select name="dname" id="department" class="form-control" required>
                                                            <option value="">Select Department</option>
                                                            <?php foreach ($departments as $department) { ?>
                                                                <option value="<?php echo $department['id']; ?>"><?php echo $department['dname']; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="course">Course</label>
                                                        <select name="cid" id="course" class="form-control" required>
                                                            <option value="">Select Course</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="batch_no">Batch No</label>
                                                        <input type="text" name="batch_no" id="batch_no" class="form-control" required readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="start_date">Start Date</label>
                                                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="end_date">End Date</label>
                                                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <button type="submit" name="submit" class="btn btn-success">Submit</button>
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
            // Fetch courses based on selected department
            $('#department').change(function() {
                var department_id = $(this).val();
                if (department_id) {
                    $.ajax({
                        url: 'get_courses.php',
                        method: 'POST',
                        data: {
                            department_id: department_id
                        },
                        success: function(data) {
                            $('#course').html(data);
                            // Reset batch number field
                            $('#batch_no').val('');
                        }
                    });
                } else {
                    $('#course').html('<option value="">Select Course</option>');
                }
            });

            // Fetch max batch_no for the selected course and generate next batch_no
            $('#course').change(function() {
                var course_id = $(this).val();
                if (course_id) {
                    $.ajax({
                        url: 'get_batch_no.php',
                        method: 'POST',
                        data: {
                            course_id: course_id
                        },
                        success: function(data) {
                            var batch_no = parseInt(data) + 1;
                            $('#batch_no').val(batch_no);
                        }
                    });
                }
            });
        </script>
    </body>

    </html>
<?php } ?>