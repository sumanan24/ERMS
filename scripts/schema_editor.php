<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
$role = 'user';
$user = isset($_SESSION['alogin']) ? $_SESSION['alogin'] : '';
$usertypeColumnMissing = false;
if ($user) {
    try {
        $st = $dbh->prepare("SELECT usertype FROM admin WHERE username=:u LIMIT 1");
        $st->bindParam(':u', $user, PDO::PARAM_STR);
        $st->execute();
        $ut = $st->fetch(PDO::FETCH_OBJ);
        if ($ut && isset($ut->usertype)) { $role = $ut->usertype; }
    } catch (Exception $e) {
        $usertypeColumnMissing = true;
        $role = 'admin';
    }
}
if (!$user) { header('Location: ../index.php'); exit; }
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$schemaStmt = $dbh->query('SELECT DATABASE() as db');
$schemaRow = $schemaStmt->fetch(PDO::FETCH_OBJ);
$schema = $schemaRow ? $schemaRow->db : '';
$msg = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_column'])) {
        $table = $_POST['table'] ?? '';
        $col = $_POST['column_name'] ?? '';
        $def = $_POST['definition'] ?? '';
        if ($table && $col && $def) {
            try {
                $sql = "ALTER TABLE `$table` ADD COLUMN `$col` $def";
                $dbh->exec($sql);
                $msg = 'Column added successfully';
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
    }
    if (isset($_POST['rename_column'])) {
        $table = $_POST['table'] ?? '';
        $old = $_POST['old_name'] ?? '';
        $new = $_POST['new_name'] ?? '';
        if ($table && $old && $new && $old !== $new) {
            try {
                $met = $dbh->prepare("SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=:s AND TABLE_NAME=:t AND COLUMN_NAME=:c");
                $met->execute([':s'=>$schema, ':t'=>$table, ':c'=>$old]);
                $m = $met->fetch(PDO::FETCH_OBJ);
                if ($m) {
                    $type = $m->COLUMN_TYPE;
                    $nullable = strtoupper($m->IS_NULLABLE) === 'YES' ? 'NULL' : 'NOT NULL';
                    $def = '';
                    if ($m->COLUMN_DEFAULT !== null) {
                        $defval = $m->COLUMN_DEFAULT;
                        if (is_numeric($defval)) { $def = " DEFAULT $defval"; }
                        else if (strtoupper($defval) === 'CURRENT_TIMESTAMP') { $def = " DEFAULT CURRENT_TIMESTAMP"; }
                        else { $def = " DEFAULT " . $dbh->quote($defval); }
                    }
                    $extra = $m->EXTRA ? ' ' . $m->EXTRA : '';
                    $sql = "ALTER TABLE `$table` CHANGE COLUMN `$old` `$new` $type $nullable$def$extra";
                    $dbh->exec($sql);
                    $msg = 'Column renamed successfully';
                } else { $error = 'Column metadata not found'; }
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
    }
}
$tables = $dbh->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=".$dbh->quote($schema)." ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
$activeTable = isset($_GET['table']) && in_array($_GET['table'], $tables) ? $_GET['table'] : (count($tables)?$tables[0]:'');
$cols = [];
if ($activeTable) {
    $stmt = $dbh->prepare("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=:s AND TABLE_NAME=:t ORDER BY ORDINAL_POSITION");
    $stmt->execute([':s'=>$schema, ':t'=>$activeTable]);
    $cols = $stmt->fetchAll(PDO::FETCH_OBJ);
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Schema Editor</title>
<link rel="stylesheet" href="../css/bootstrap.css">
<link rel="stylesheet" href="../css/font-awesome.min.css">
<style>
 body{background:#f5f7fb;color:#111827}
 .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 8px 18px rgba(0,0,0,.05)}
 .card-header{padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:700}
 .card-body{padding:18px}
 .form-control{height:42px;border-radius:10px;border:1px solid #e5e7eb}
 .btn-modern{background:#2563eb;border-color:#2563eb;border-radius:10px;color:#fff}
 .btn-modern:hover{background:#1d4ed8;border-color:#1d4ed8}
 .badge-role{background:#2563eb}
 table td,table th{vertical-align:middle!important}
</style>
</head>
<body class="top-navbar-fixed">
<div class="container-fluid" style="padding:20px;max-width:1200px">
    <div class="card" style="margin-bottom:16px">
        <div class="card-header">Database Tables</div>
        <div class="card-body">
            <form method="get" class="form-inline" style="gap:10px;display:flex;flex-wrap:wrap">
                <select name="table" class="form-control" style="min-width:260px">
                    <?php foreach($tables as $t){ ?><option value="<?php echo htmlspecialchars($t); ?>" <?php if($t===$activeTable) echo 'selected'; ?>><?php echo htmlspecialchars($t); ?></option><?php } ?>
                </select>
                <button class="btn btn-modern" type="submit">Open</button>
                <span class="label label-default" style="background:#111827;color:#fff;border-radius:999px;padding:6px 10px;margin-left:8px">User: <?php echo htmlspecialchars($user); ?> (<?php echo strtoupper($role); ?>)</span>
            </form>
            <?php if($msg){ ?><div class="alert alert-success" style="margin-top:12px"><?php echo htmlspecialchars($msg); ?></div><?php } ?>
            <?php if($error){ ?><div class="alert alert-danger" style="margin-top:12px"><?php echo htmlspecialchars($error); ?></div><?php } ?>
            <?php if($usertypeColumnMissing){ ?><div class="alert alert-warning" style="margin-top:12px">Notice: admin.usertype column not found. Access temporarily allowed. Run <a href="migrate_admin_usertype.php">migration</a> to enable role checks.</div><?php } ?>
        </div>
    </div>
    <?php if($activeTable){ ?>
    <div class="card" style="margin-bottom:16px">
        <div class="card-header">Columns · <?php echo htmlspecialchars($activeTable); ?></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead><tr><th>Name</th><th>Type</th><th>Null</th><th>Default</th><th>Key</th><th>Extra</th></tr></thead>
                    <tbody>
                        <?php foreach($cols as $c){ ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c->COLUMN_NAME); ?></td>
                            <td><?php echo htmlspecialchars($c->COLUMN_TYPE); ?></td>
                            <td><?php echo htmlspecialchars($c->IS_NULLABLE); ?></td>
                            <td><?php echo $c->COLUMN_DEFAULT===null?'NULL':htmlspecialchars($c->COLUMN_DEFAULT); ?></td>
                            <td><?php echo htmlspecialchars($c->COLUMN_KEY); ?></td>
                            <td><?php echo htmlspecialchars($c->EXTRA); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <div class="card" style="margin-bottom:16px">
                <div class="card-header">Add Column</div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="table" value="<?php echo htmlspecialchars($activeTable); ?>">
                        <div class="form-group"><label>Column Name</label><input name="column_name" class="form-control" required></div>
                        <div class="form-group"><label>Definition</label><input name="definition" class="form-control" placeholder="e.g. VARCHAR(191) NOT NULL DEFAULT ''" required></div>
                        <button class="btn btn-modern" type="submit" name="add_column">Add Column</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <div class="card" style="margin-bottom:16px">
                <div class="card-header">Rename Column</div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="table" value="<?php echo htmlspecialchars($activeTable); ?>">
                        <div class="form-group"><label>Old Name</label>
                            <select name="old_name" class="form-control" required>
                                <option value="">Select Column</option>
                                <?php foreach($cols as $c){ ?><option value="<?php echo htmlspecialchars($c->COLUMN_NAME); ?>"><?php echo htmlspecialchars($c->COLUMN_NAME); ?></option><?php } ?>
                            </select>
                        </div>
                        <div class="form-group"><label>New Name</label><input name="new_name" class="form-control" required></div>
                        <button class="btn btn-modern" type="submit" name="rename_column">Rename Column</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
<script src="../js/jquery/jquery-2.2.4.min.js"></script>
<script src="../js/bootstrap/bootstrap.min.js"></script>
</body>
</html>
