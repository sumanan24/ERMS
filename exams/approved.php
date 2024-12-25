<?php
session_start();
error_reporting(0);
include('../includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: ../index.php");
} else {

    // Code for updating exam status
    if (isset($_POST['update_status'])) {
        $examIds = isset($_POST['exam_ids']) ? $_POST['exam_ids'] : [];
        if (!empty($examIds)) {
            $ids = implode(",", array_map('intval', $examIds));
            $sql = "UPDATE exam SET status = 'Released' WHERE id IN ($ids)";
            $query = $dbh->prepare($sql);
            $query->execute();
            echo '<script>alert("Exam status updated successfully.");</script>';
        } else {
            echo '<script>alert("No exams selected.");</script>';
        }
    }

    // Fetch filters
    $courseFilter = isset($_POST['course']) ? $_POST['course'] : '';
    $batchFilter = isset($_POST['batch']) ? $_POST['batch'] : '';
    $semesterFilter = isset($_POST['semester']) ? $_POST['semester'] : '';

    // Build SQL query based on filters
    $sql = "SELECT e.id, e.date, e.status, m.mname as module_name
            FROM exam e
            JOIN module m ON e.mid = m.id";

    $conditions = [];
    if ($courseFilter) {
        $conditions[] = "m.cid = :course";
    }
    if ($batchFilter) {
        $conditions[] = "e.bid = :batch";
    }
    if ($semesterFilter) {
        $conditions[] = "e.semester = :semester";
    }
    $conditions[] = "e.status = 'Pending'";

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
                                    <h2 class="title">Exams</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="../dashboard/dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li class="active">Result Not Released Exams</li>
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
                                                <div class="panel-title">
                                                    <h5>Pending Exams</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body p-20">
                                                <form method="POST">
                                                    <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <th><input type="checkbox" id="select_all" /></th>
                                                                <th>Module Name</th>
                                                                <th>Date</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tfoot>
                                                            <tr>
                                                                <th>Select</th>
                                                                <th>Module Name</th>
                                                                <th>Date</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </tfoot>
                                                        <tbody>
                                                            <?php
                                                            if ($query->rowCount() > 0) {
                                                                foreach ($results as $result) {
                                                            ?>
                                                                    <tr>
                                                                        <td><input type="checkbox" name="exam_ids[]" value="<?php echo htmlentities($result->id); ?>" class="checkbox" /></td>
                                                                        <td><?php echo htmlentities($result->module_name); ?></td>
                                                                        <td><?php echo htmlentities($result->date); ?></td>
                                                                        <td><?php echo htmlentities($result->status); ?></td>
                                                                    </tr>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                    <button type="submit" name="update_status" class="btn btn-success">Release Selected Exams</button>
                                                </form>
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
            $(function($) {
                $('#example').DataTable();

                // Select all checkboxes
                $('#select_all').on('click', function() {
                    if (this.checked) {
                        $('.checkbox').each(function() {
                            this.checked = true;
                        });
                    } else {
                        $('.checkbox').each(function() {
                            this.checked = false;
                        });
                    }
                });

                // Checkbox click behavior
                $('.checkbox').on('click', function() {
                    if ($('.checkbox:checked').length == $('.checkbox').length) {
                        $('#select_all').prop('checked', true);
                    } else {
                        $('#select_all').prop('checked', false);
                    }
                });
            });
        </script>
    </body>

    </html>
<?php } ?>
