<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
} else {
    if (isset($_POST['submit'])) {
        $batch_id = $_POST['batch_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Debugging: Output the submitted values for checking
        // echo "Batch ID: $batch_id, Start Date: $start_date, End Date: $end_date";

        try {
            $sql = "UPDATE batch SET start_date = :start_date, end_date = :end_date WHERE id = :batch_id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':start_date', $start_date, PDO::PARAM_STR);
            $query->bindParam(':end_date', $end_date, PDO::PARAM_STR);
            $query->bindParam(':batch_id', $batch_id, PDO::PARAM_INT);

            $query->execute();

            // Debugging: Output the number of affected rows
            $affectedRows = $query->rowCount();

            if ($affectedRows > 0) {
                $msg = "Batch updated successfully.";
            } 
        } catch (PDOException $e) {
            // Capture and display database errors
            $error = "Database error: " . $e->getMessage();
        }
    }


    // Fetch batch details if editing an existing batch
    if (isset($_GET['batchid'])) {
        $batch_id = $_GET['batchid'];
        $sql = "SELECT b.id, b.batch_no, b.start_date, b.end_date, d.dname, c.cname FROM batch b JOIN course c ON b.cid = c.id JOIN department d ON c.did = d.id WHERE  b.id = :batch_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':batch_id', $batch_id, PDO::PARAM_INT);
        $stmt->execute();
        $batch_details = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($batch_details) {
            $dname = $batch_details['dname'];
            $cname = $batch_details['cname'];
            $batch_no = $batch_details['batch_no'];
            $start_date = $batch_details['start_date'];
            $end_date = $batch_details['end_date'];
            $id = $batch_details['id'];
        } else {
            $error = "Batch not found.";
        }
    }
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
                                    <h2 class="title">Edit Batch</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="manage.php">Batches</a></li>
                                        <li class="active">Edit Batch</li>
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
                                                    <h5>Edit Batch</h5>
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
                                                <form method="post">
                                                    <input type="hidden" name="batch_id" value="<?php echo htmlentities($id); ?>">
                                                    <div class="form-group">
                                                        <label for="department">Department</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlentities($dname); ?>" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="course">Course</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlentities($cname); ?>" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="batch_no">Batch Number</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlentities($batch_no); ?>" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="start_date">Start Date</label>
                                                        <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlentities($start_date); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="end_date">End Date</label>
                                                        <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlentities($end_date); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <button type="submit" name="submit" class="btn btn-success">Update</button>
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