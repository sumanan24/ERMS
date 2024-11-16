<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: ../index.php");
} else {
    if (isset($_POST['update'])) {
        $dname = $_POST['dname'];
        $cid = intval($_GET['classid']);
        $sql = "UPDATE department SET dname=:dname WHERE id=:cid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':dname', $dname, PDO::PARAM_STR);
        $query->bindParam(':cid', $cid, PDO::PARAM_INT);
        $query->execute();
        $msg = "Data has been updated successfully";
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMS Admin Update Department</title>
    <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
    <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="../css/prism/prism.css" media="screen">
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
                                <h2 class="title">Update Department</h2>
                            </div>
                        </div>

                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="#">Departments</a></li>
                                    <li class="active">Update Department</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-8 col-md-offset-2">
                                    <div class="panel" style="padding: 10px;">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>Update Department Info</h5>
                                            </div>
                                        </div>
                                        <?php if ($msg) { ?>
                                            <div class="alert alert-success left-icon-alert" role="alert">
                                                <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($error) { ?>
                                            <div class="alert alert-danger left-icon-alert" role="alert">
                                                <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                            </div>
                                        <?php } ?>
                                        <form method="post">
                                            <?php
                                            $cid = intval($_GET['classid']);
                                            $sql = "SELECT * FROM department WHERE id=:cid";
                                            $query = $dbh->prepare($sql);
                                            $query->bindParam(':cid', $cid, PDO::PARAM_INT);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $result) { ?>
                                                    <div class="form-group has-success">
                                                        <label for="success" class="control-label">Department Name</label>
                                                        <div>
                                                            <input type="text" name="dname" class="form-control" value="<?php echo htmlentities($result->dname); ?>" required>
                                                        </div>
                                                    </div>
                                            <?php }
                                            } ?>
                                            <div class="form-group has-success">
                                                <button type="submit" name="update" class="btn btn-success btn-labeled">
                                                    Update <span class="btn-label btn-label-right"><i class="fa fa-check"></i></span>
                                                </button>
                                            </div>
                                        </form>
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
