<?php
session_start();
error_reporting(0);
include('../includes/config.php');
if (strlen($_SESSION['alogin']) == 0) {
    header("Location: ../admin-login.php");
    exit;
}
$msg = '';
$error = '';
if (isset($_POST['submit'])) {
    $username = $_SESSION['alogin'];
    $current = md5($_POST['current_password']);
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirm password do not match.';
    } else {
        $sql = "SELECT Password FROM admin WHERE (UserName=:u OR username=:u) AND Password=:p LIMIT 1";
        $q = $dbh->prepare($sql);
        $q->bindParam(':u', $username, PDO::PARAM_STR);
        $q->bindParam(':p', $current, PDO::PARAM_STR);
        $q->execute();
        $row = $q->fetch(PDO::FETCH_OBJ);
        if ($row) {
            $update = $dbh->prepare("UPDATE admin SET Password=:np WHERE (UserName=:u OR username=:u) LIMIT 1");
            $np = md5($new);
            $update->bindParam(':np', $np, PDO::PARAM_STR);
            $update->bindParam(':u', $username, PDO::PARAM_STR);
            $update->execute();
            $msg = 'Your password has been changed successfully.';
        } else {
            $error = 'Your current password is incorrect.';
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
    <title>Change Password</title>
    <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
    <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
    <link rel="stylesheet" href="../css/main.css" media="screen">
    <script src="../js/modernizr/modernizr.min.js"></script>
    <style>
        body { background: #f5f7fb; color: #111827; }
        .modern-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 8px 18px rgba(0,0,0,0.05); overflow:hidden; }
        .modern-card .panel-heading { background:#fff; border-bottom:1px solid #e5e7eb; padding:16px 20px; }
        .modern-card .panel-title h5 { margin:0; font-weight:700; color:#111827; }
        .modern-card .panel-body { padding:22px; }
        .form-control { height:44px; border-radius:10px; border:1px solid #e5e7eb; box-shadow:none; }
        .form-control:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.15); }
        .btn-modern { background:#2563eb; border-color:#2563eb; border-radius:10px; padding:10px 16px; font-weight:600; color:#fff; }
        .btn-modern:hover, .btn-modern:focus { background:#1d4ed8; border-color:#1d4ed8; }
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
                            <h2 class="title">Change Password</h2>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-12 col-md-8 col-lg-6">
                                <div class="panel modern-card">
                                    <div class="panel-heading"><div class="panel-title"><h5>Update Password</h5></div></div>
                                    <div class="panel-body">
                                        <?php if ($msg) { ?><div class="alert alert-success"><?php echo htmlentities($msg); ?></div><?php } ?>
                                        <?php if ($error) { ?><div class="alert alert-danger"><?php echo htmlentities($error); ?></div><?php } ?>
                                        <form method="post">
                                            <div class="form-group">
                                                <label for="current_password">Current Password</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="new_password">New Password</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="confirm_password">Confirm New Password</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                            <button type="submit" name="submit" class="btn btn-modern">Change Password</button>
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
<script src="../js/bootstrap/bootstrap.min.js"></script>
<script src="../js/pace/pace.min.js"></script>
<script src="../js/lobipanel/lobipanel.min.js"></script>
<script src="../js/iscroll/iscroll.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
