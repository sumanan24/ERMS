<?php
session_start();
error_reporting(0);
include('../includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: ../index.php");
} else {

    // Add Admin
    if (isset($_POST['add_admin'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']);

        $sql = "INSERT INTO admin (username, password) VALUES (:username, :password)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->execute();

        echo '<script>alert("Admin added successfully.");</script>';
    }

    // Update Admin
    if (isset($_POST['update_admin'])) {
        $id = intval($_POST['admin_id']);
        $username = $_POST['username'];
        $password = md5($_POST['password']);

        $sql = "UPDATE admin SET username = :username, password = :password WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        echo '<script>alert("Admin updated successfully.");</script>';
    }

    // Delete Admin
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);

        $sql = "DELETE FROM admins WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        echo '<script>alert("Admin deleted successfully.");</script>';
    }

    // Fetch Admins
    $sql = "SELECT * FROM admin";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Management</title>
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
                                    <h2 class="title">Admin Management</h2>
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
                                                    <h5>Manage Admins</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body">
                                                <form method="POST">
                                                    <div class="form-group">
                                                        <label for="username">Username</label>
                                                        <input type="text" name="username" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="password">Password</label>
                                                        <input type="password" name="password" class="form-control" required>
                                                    </div>
                                                    <button type="submit" name="add_admin" class="btn btn-success">Add Admin</button>
                                                </form>
                                                <hr>
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Username</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $result) {
                                                        ?>
                                                                <tr>
                                                                    <td><?php echo htmlentities($result->id); ?></td>
                                                                    <td><?php echo htmlentities($result->UserName); ?></td>
                                                                    <td>
                                                                        <form method="POST" style="display:inline;">
                                                                            <input type="hidden" name="admin_id" value="<?php echo htmlentities($result->id); ?>">
                                                                            <input type="text" name="username" placeholder="New Username" required>
                                                                            <input type="password" name="password" placeholder="New Password" required>
                                                                            <button type="submit" name="update_admin" class="btn btn-primary">Update</button>
                                                                        </form>
                                                                        <a href="?delete=<?php echo htmlentities($result->id); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                                                    </td>
                                                                </tr>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
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
    </body>

    </html>
<?php } ?>