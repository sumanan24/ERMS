<?php
if (!isset($dbh)) { include('../includes/config.php'); }
$currentRole = 'admin';

if (isset($_SESSION) && isset($_SESSION['alogin'])) {
    try {
        $uname = $_SESSION['alogin'];
        $st = $dbh->prepare("SELECT usertype FROM admin WHERE username=:u LIMIT 1");
        $st->bindParam(':u', $uname, PDO::PARAM_STR);
        $st->execute();
        $ut = $st->fetch(PDO::FETCH_OBJ);
        if ($ut && isset($ut->usertype)) { $currentRole = $ut->usertype; }
    } catch (Exception $e) { }
}
?>
<?php $active = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : ''; ?>
<style>
 .left-sidebar{background:#0f172a!important}
 .side-nav li a{color:#cbd5e1; padding:10px 14px; border-radius:10px; display:block}
 .side-nav li a:hover{background:#111827; color:#fff}
 .side-nav li.active > a{background:#1e293b; color:#fff}
 .child-nav li a{padding-left:28px}
 .nav-header span{color:#94a3b8; font-size:12px; letter-spacing:.4px}
 .user-info .title{color:#fff}
 .user-info .info{color:#94a3b8}
</style>
<div class="left-sidebar bg-black-300 box-shadow ">
    <div class="sidebar-content">
        <div class="user-info closed">
            <img src="http://placehold.it/90/c2c2c2?text=User" alt="John Doe" class="img-circle profile-img">
            <h6 class="title"><?php echo ucfirst($currentRole); ?></h6>
            <small class="info"><?php echo $currentRole==='admin'?'Full Access':'Data Entry'; ?></small>
        </div>
        <!-- /.user-info -->

        <div class="sidebar-nav">
            <ul class="side-nav color-gray">
                <li class="nav-header">
                    <span class="">Main Category</span>
                </li>
                <li class="<?php echo strpos($active,'/dashboard/dashboard.php')!==false?'active':''; ?>">
                    <a href="../dashboard/dashboard.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span> </a>
                </li>

                <li class="nav-header">
                    <span class="">Appearance</span>
                </li>

                <li class="has-children">
                    <a href="#"><i class="fa fa-building"></i> <span>Department</span> <i class="fa fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li class="<?php echo strpos($active,'/Department/new.php')!==false?'active':''; ?>"><a href="../Department/new.php"><i class="fa fa-bars"></i> <span>New</span></a></li>
                        <li class="<?php echo strpos($active,'/Department/manage.php')!==false?'active':''; ?>"><a href="../Department/manage.php"><i class="fa fa fa-server"></i> <span>Manage</span></a></li>
                    </ul>
                </li>

                <li class="has-children">
                    <a href="#"><i class="fa fa-book"></i> <span>Course</span> <i class="fa fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li class="<?php echo strpos($active,'/course/new.php')!==false?'active':''; ?>"><a href="../course/new.php"><i class="fa fa-bars"></i> <span>New</span></a></li>
                        <li class="<?php echo strpos($active,'/course/manage.php')!==false?'active':''; ?>"><a href="../course/manage.php"><i class="fa fa fa-server"></i> <span>Manage</span></a></li>
                    </ul>
                </li>

                <li class="has-children">
                    <a href="#"><i class="fa fa-file"></i> <span>Module</span> <i class="fa fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li class="<?php echo strpos($active,'/module/new.php')!==false?'active':''; ?>"><a href="../module/new.php"><i class="fa fa-bars"></i> <span>New</span></a></li>
                        <li class="<?php echo strpos($active,'/module/manage.php')!==false?'active':''; ?>"><a href="../module/manage.php"><i class="fa fa fa-server"></i> <span>Manage</span></a></li>
                    </ul>
                </li>

                <li class="has-children">
                    <a href="#"><i class="fa fa-child"></i> <span>Batch</span> <i class="fa fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li class="<?php echo strpos($active,'/batch/new.php')!==false?'active':''; ?>"><a href="../batch/new.php"><i class="fa fa-bars"></i> <span>New</span></a></li>
                        <li class="<?php echo strpos($active,'/batch/manage.php')!==false?'active':''; ?>"><a href="../batch/manage.php"><i class="fa fa fa-server"></i> <span>Manage</span></a></li>
                    </ul>
                </li>

                <li class="has-children">
                    <a href="#"><i class="fa fa-users"></i> <span>Students</span> <i class="fa fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li class="<?php echo strpos($active,'/student/new.php')!==false?'active':''; ?>"><a href="../student/new.php"><i class="fa fa-bars"></i> <span>New</span></a></li>
                        <li class="<?php echo strpos($active,'/student/manage.php')!==false?'active':''; ?>"><a href="../student/manage.php"><i class="fa fa fa-server"></i> <span>Manage</span></a></li>
                        <li class="<?php echo strpos($active,'/student/transcript.php')!==false?'active':''; ?>">
                            <a href="../student/transcript.php"><i class="fa fa-file-text-o"></i> <span>Student Transcript</span></a>
                        </li>
                    </ul>
                </li>

                <li class="has-children">
                    <a href="#"><i class="fa fa-graduation-cap"></i> <span>Exam Schedules</span> <i class="fa fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li class="<?php echo strpos($active,'/exams/new.php')!==false?'active':''; ?>"><a href="../exams/new.php"><i class="fa fa-bars"></i> <span>New</span></a></li>
                        <li class="<?php echo strpos($active,'/exams/manage.php')!==false?'active':''; ?>"><a href="../exams/manage.php"><i class="fa fa fa-server"></i> <span>Manage</span></a></li>
                        <li class="<?php echo strpos($active,'/exams/report.php')!==false?'active':''; ?>"><a href="../exams/report.php"><i class="fa fa fa-server"></i> <span>Report</span></a></li>
                        <li class="<?php echo strpos($active,'/exams/approved.php')!==false?'active':''; ?>"><a href="../exams/approved.php"><i class="fa fa fa-server"></i> <span>Approved</span></a></li>
                        
                    </ul>
                </li>

                <?php if ($currentRole==='admin') { ?>
                <li class="<?php echo strpos($active,'/Department/admin.php')!==false?'active':''; ?>">
                    <a href="../Department/admin.php"><i class="fa fa-user"></i> <span>Admin</span> </a>
                </li>
                <?php } ?>
                <li class="<?php echo strpos($active,'/scripts/schema_editor.php')!==false?'active':''; ?>">
                    <!-- <a href="../scripts/schema_editor.php"><i class="fa fa-database"></i> <span>Schema Editor</span> </a> -->
                </li>
            </ul>
        </div>
        <!-- /.sidebar-nav -->
    </div>
    <!-- /.sidebar-content -->
</div>