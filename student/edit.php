<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: index.php");
} else {
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $reg_no = $_POST['reg_no'];
        $fullname = $_POST['fullname'];
        $nic = $_POST['nic'];

        // Only update personal data (fullname, nic, reg_no)
        $sql = "UPDATE student SET reg_no=:reg_no, fullname=:fullname, nic=:nic WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':reg_no', $reg_no, PDO::PARAM_STR);
        $query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $query->bindParam(':nic', $nic, PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() > 0) {
            $msg = "Student Updated Successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }
    }

    $id = intval($_GET['studentid']);
    $sql = "SELECT * FROM student WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit Student</title>
        <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
        <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" href="../css/main.css" media="screen">
        <script src="../js/modernizr/modernizr.min.js"></script>
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
                                    <h2 class="title">Edit Student</h2>
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
                                                    <h5>Edit Student</h5>
                                                </div>
                                            </div>

                                            <div class="panel-body">
                                                <?php if ($msg) { ?>
                                                    <div class="alert alert-success left-icon-alert" role="alert">
                                                        <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                                        <meta http-equiv='refresh' content='1.5'>
                                                    </div>
                                                <?php } ?>
                                                <form method="post">
                                                    <input type="hidden" name="id" value="<?php echo htmlentities($result->id); ?>">

                                                    <div class="form-group">
                                                        <label for="reg_no">Registration Number</label>
                                                        <input type="text" name="reg_no" class="form-control" value="<?php echo htmlentities($result->reg_no); ?>" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="fullname">Full Name</label>
                                                        <input type="text" name="fullname" class="form-control" value="<?php echo htmlentities($result->fullname); ?>" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="nic">NIC</label>
                                                        <input type="text" name="nic" class="form-control" value="<?php echo htmlentities($result->nic); ?>" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <button type="submit" name="update" class="btn btn-primary">Update</button>
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