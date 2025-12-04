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
        $usertype = isset($_POST['usertype']) ? $_POST['usertype'] : 'user';

        $sql = "INSERT INTO admin (username, password, usertype) VALUES (:username, :password, :usertype)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':usertype', $usertype, PDO::PARAM_STR);
        $query->execute();

        echo '<script>alert("Admin added successfully.");</script>';
    }

    // Update Admin
    if (isset($_POST['update_admin'])) {
        $id = intval($_POST['admin_id']);
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        $usertype = isset($_POST['usertype']) ? $_POST['usertype'] : 'user';

        $sql = "UPDATE admin SET username = :username, password = :password, usertype=:usertype WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':usertype', $usertype, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        echo '<script>alert("Admin updated successfully.");</script>';
    }

    // Update only username and role
    if (isset($_POST['update_userrole'])) {
        $id = intval($_POST['admin_id']);
        $username = $_POST['username'];
        $usertype = isset($_POST['usertype']) ? $_POST['usertype'] : 'user';

        $sql = "UPDATE admin SET username = :username, usertype=:usertype WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':usertype', $usertype, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        echo '<script>alert("Username/Role updated successfully.");</script>';
    }

    // Reset password only
    if (isset($_POST['reset_password'])) {
        $id = intval($_POST['admin_id']);
        $newpass = md5($_POST['new_password']);
        $sql = "UPDATE admin SET password=:p WHERE id=:id";
        $q = $dbh->prepare($sql);
        $q->bindParam(':p', $newpass, PDO::PARAM_STR);
        $q->bindParam(':id', $id, PDO::PARAM_INT);
        $q->execute();
        echo '<script>alert("Password reset successfully.");</script>';
    }

    // Delete Admin
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);

        $sql = "DELETE FROM admin WHERE id = :id";
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
    $schemaStmt = $dbh->query('SELECT DATABASE() as db');
    $schemaRow = $schemaStmt->fetch(PDO::FETCH_OBJ);
    $schema = $schemaRow ? $schemaRow->db : '';
    $usertypeMissing = false;
    try {
        $ck = $dbh->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=:s AND TABLE_NAME='admin' AND COLUMN_NAME='usertype'");
        $ck->bindParam(':s', $schema, PDO::PARAM_STR);
        $ck->execute();
        $usertypeMissing = ($ck->fetchColumn() == 0);
    } catch (Exception $e) { $usertypeMissing = false; }

    $currentUser = isset($_SESSION['alogin']) ? $_SESSION['alogin'] : '';
    $displayUser = $currentUser;
    $displayRole = '';
    try {
        $st = $dbh->prepare("SELECT COALESCE(username, UserName) as uname FROM admin WHERE (username=:u OR UserName=:u) LIMIT 1");
        $st->bindParam(':u', $currentUser, PDO::PARAM_STR);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_OBJ);
        if ($row && isset($row->uname) && $row->uname) { $displayUser = $row->uname; }
    } catch (Exception $e) {}
    try {
        $rt = $dbh->prepare("SELECT usertype FROM admin WHERE (username=:u OR UserName=:u) LIMIT 1");
        $rt->bindParam(':u', $currentUser, PDO::PARAM_STR);
        $rt->execute();
        $rrow = $rt->fetch(PDO::FETCH_OBJ);
        if ($rrow && isset($rrow->usertype) && $rrow->usertype) { $displayRole = strtoupper($rrow->usertype); }
    } catch (Exception $e) { $displayRole = ''; }
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
        <style>
            .form-control { height:44px; border-radius:10px; }
            .btn-modern { background:#2563eb; border-color:#2563eb; border-radius:10px; color:#fff; }
            .btn-modern:hover { background:#1d4ed8; border-color:#1d4ed8; }
            .panel { border-radius:14px; overflow:hidden; }
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
                                <div class="col-md-7">
                                    <h2 class="title">Admin Management</h2>
                                </div>
                                <div class="col-md-5" style="text-align:right;">
                                    <?php if ($displayUser) { ?>
                                        <span class="label label-default" style="background:#111827;color:#fff;border-radius:999px;padding:6px 10px;display:inline-block;margin-right:6px;"><i class="fa fa-user"></i> <?php echo htmlentities($displayUser); ?></span>
                                    <?php } ?>
                                    <?php if ($displayRole) { ?>
                                        <span class="label label-default" style="background:#2563eb;color:#fff;border-radius:999px;padding:6px 10px;display:inline-block;"><?php echo htmlentities($displayRole); ?></span>
                                    <?php } ?>
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
                                                <?php if ($usertypeMissing) { ?>
                                                    <div class="alert alert-warning">Role column (admin.usertype) not found. <a href="../scripts/migrate_admin_usertype.php">Run migration</a> to enable roles.</div>
                                                <?php } ?>
                                                <form method="POST">
                                                    <div class="form-group">
                                                        <label for="username">Username</label>
                                                        <input type="text" name="username" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="password">Password</label>
                                                        <input type="password" name="password" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usertype">Role</label>
                                                        <select name="usertype" class="form-control" required>
                                                            <option value="admin">Admin</option>
                                                            <option value="user">User</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit" name="add_admin" class="btn btn-modern">Add User</button>
                                                </form>
                                                <hr>
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Username</th>
                                                            <th>Role</th>
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
                                                                    <td><?php echo htmlentities(isset($result->username)&&$result->username ? $result->username : (isset($result->UserName)?$result->UserName:'')); ?></td>
                                                                    <td><?php echo htmlentities(isset($result->usertype)&&$result->usertype ? $result->usertype : ($usertypeMissing ? 'user' : '')); ?></td>

                                                                    <td>
                                                                        <button class="btn btn-info btn-xs" data-toggle="modal" data-target="#editUserRoleModal" data-id="<?php echo $result->id; ?>" data-username="<?php echo htmlentities(isset($result->username)&&$result->username ? $result->username : (isset($result->UserName)?$result->UserName:'')); ?>" data-role="<?php echo htmlentities(isset($result->usertype)?$result->usertype:'user'); ?>">Edit</button>
                                                                        <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#resetPasswordModal" data-id="<?php echo $result->id; ?>">Reset Password</button>
                                                                        <a href="?delete=<?php echo htmlentities($result->id); ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure?')">Delete</a>
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
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Username/Role Modal -->
        <div class="modal fade" id="editUserRoleModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Edit Username & Role</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="admin_id" id="edit_admin_id">
                            <div class="form-group"><label>Username</label><input class="form-control" name="username" id="edit_username" required></div>
                            <div class="form-group"><label>Role</label>
                                <select class="form-control" name="usertype" id="edit_usertype" required>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" name="update_userrole" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Reset Password</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="admin_id" id="reset_admin_id">
                            <div class="form-group"><label>New Password</label><input type="password" class="form-control" name="new_password" id="reset_new_password" required></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" name="reset_password" class="btn btn-warning">Reset</button>
                        </div>
                    </form>
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
        (function(){
            $('#editUserRoleModal').on('show.bs.modal', function(e){
                var b = $(e.relatedTarget);
                $('#edit_admin_id').val(b.data('id'));
                $('#edit_username').val(b.data('username'));
                $('#edit_usertype').val(b.data('role'));
            });
            $('#resetPasswordModal').on('show.bs.modal', function(e){
                var b = $(e.relatedTarget);
                $('#reset_admin_id').val(b.data('id'));
                $('#reset_new_password').val('');
            });
        })();
        </script>
    </body>

    </html>
<?php } ?>