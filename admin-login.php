<?php
session_start();
error_reporting(0);
include('includes/config.php');
if ($_SESSION['alogin'] != '') {
    $_SESSION['alogin'] = '';
}
if (isset($_POST['login'])) {
    $uname = $_POST['username'];
    $password = md5($_POST['password']);
    $sql = "SELECT UserName,Password FROM admin WHERE UserName=:uname and Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uname', $uname, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    if ($query->rowCount() > 0) {
        $_SESSION['alogin'] = $_POST['username'];
        echo "<script type='text/javascript'> document.location = 'dashboard/dashboard.php'; </script>";
    } else {

        echo "<script>alert('Invalid Details');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="css/font-awesome.min.css" media="screen">
    <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen">
    <link rel="stylesheet" href="css/prism/prism.css" media="screen"> <!-- USED FOR DEMO HELP - YOU CAN REMOVE IT -->
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: #f5f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Helvetica Neue", Arial, sans-serif;
            color: #111827;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }
        .auth-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(0,0,0,0.06);
            color: #111827;
            overflow: hidden;
        }

        .brand {
            text-align: center;
            padding: 28px 28px 12px 28px;
        }
        .brand .logo {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            border: 1px solid #e5e7eb;
            color: #2563eb;
            margin-bottom: 12px;
            font-size: 22px;
        }

        .brand h1 {
            font-size: 22px;
            margin: 0;
            font-weight: 700;
        }
        .brand p { margin: 6px 0 0 0; color: #6b7280; }

        .form-area { padding: 24px 28px 28px 28px; }
        .form-group label { color: #374151; }

        .input-group {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
            overflow: hidden;
        }

        .input-group .input-group-addon {
            background: #f3f4f6;
            color: #6b7280;
            border: none;
            padding: 12px 14px;
        }

        .input-group input {
            background: #ffffff;
            border: none;
            color: #111827;
            padding: 12px 14px;
            width: 100%;
            outline: none;
        }

        .helper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 12px;
            color: #6b7280;
            font-size: 13px;
        }

        .btn-primary {
            background: #2563eb;
            border: 1px solid #1d4ed8;
            color: #fff;
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            font-weight: 600;
            transition: transform 0.08s ease, background 0.2s ease;
        }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
        .footer-note { text-align: center; color: #6b7280; font-size: 12px; padding: 0 12px 20px 12px; }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="brand">
                <div class="logo"><i class="fa fa-shield"></i></div>
                <h1>ERMS Admin</h1>
                
            </div>
            <div class="form-area">
                <form method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <input type="text" id="username" name="username" placeholder="Enter your username" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:14px;">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    <div class="helper">
                        <div>
                            <label style="cursor:pointer;">
                                <input type="checkbox" style="margin-right:6px;">Remember me
                            </label>
                        </div>
                        
                    </div>
                    <div style="margin-top:18px;">
                        <button type="submit" name="login" class="btn btn-primary">Sign in</button>
                    </div>
                </form>
            </div>
            <div class="footer-note">
                <small>University College Jaffna</small>
            </div>
        </div>
    </div>

    <!-- ========== COMMON JS FILES ========== -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/jquery-ui/jquery-ui.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>

    <!-- ========== PAGE JS FILES ========== -->

    <!-- ========== THEME JS ========== -->
    <script src="js/main.js"></script>
    <script>
        $(function() {

        });
    </script>

    <!-- ========== ADD custom.js FILE BELOW WITH YOUR CHANGES ========== -->
</body>

</html>