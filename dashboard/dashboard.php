<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header("Location: ../index.php");
} else {
    // Fetch total counts
    $departmentCount = $dbh->query("SELECT COUNT(*) FROM department")->fetchColumn();
    $courseCount = $dbh->query("SELECT COUNT(*) FROM course")->fetchColumn();
    $studentCount = $dbh->query("SELECT COUNT(*) FROM student")->fetchColumn();
    $examCount = $dbh->query("SELECT COUNT(*) FROM exam")->fetchColumn();
    $batchCount = $dbh->query("SELECT COUNT(*) FROM batch")->fetchColumn();

    $passRates = $dbh->query(
        "SELECT d.dname, c.cname, ROUND(SUM(CASE WHEN r.marks >= 40 THEN 1 ELSE 0 END) * 100.0 / COUNT(r.id), 2) as pass_rate FROM results r JOIN exam e ON r.examid = e.id JOIN module m ON e.mid=m.id JOIN course c ON m.cid = c.id JOIN department d ON c.did = d.id GROUP BY d.dname, c.cname;"
    )->fetchAll(PDO::FETCH_OBJ);
    $deptRates = $dbh->query(
        "SELECT d.dname, ROUND(SUM(CASE WHEN r.marks >= 40 THEN 1 ELSE 0 END) * 100.0 / COUNT(r.id), 2) as pass_rate FROM results r JOIN exam e ON r.examid = e.id JOIN module m ON e.mid = m.id JOIN course c ON m.cid = c.id JOIN department d ON c.did = d.id GROUP BY d.dname ORDER BY d.dname"
    )->fetchAll(PDO::FETCH_OBJ);
    $deptLabels = [];
    $deptData = [];
    foreach ($deptRates as $r) { $deptLabels[] = $r->dname; $deptData[] = (float)$r->pass_rate; }

    $batchRates = $dbh->query(
        "SELECT b.batch_no, ROUND(SUM(CASE WHEN r.marks >= 40 THEN 1 ELSE 0 END) * 100.0 / COUNT(r.id), 2) as pass_rate FROM results r JOIN exam e ON r.examid = e.id JOIN batch b ON e.bid = b.id GROUP BY b.batch_no ORDER BY b.batch_no"
    )->fetchAll(PDO::FETCH_OBJ);
    $batchLabels = [];
    $batchData = [];
    foreach ($batchRates as $r) { $batchLabels[] = $r->batch_no; $batchData[] = (float)$r->pass_rate; }
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="../css/bootstrap.css" media="screen">
        <link rel="stylesheet" href="../css/font-awesome.min.css" media="screen">
        <link rel="stylesheet" href="../css/animate-css/animate.min.css" media="screen">
        <link rel="stylesheet" href="../css/lobipanel/lobipanel.min.css" media="screen">
        <link rel="stylesheet" href="../css/prism/prism.css" media="screen">
        <link rel="stylesheet" href="../css/main.css" media="screen">
        <script src="../js/modernizr/modernizr.min.js"></script>
        <style>
            body { background: #f5f7fb; color: #111827; }
            .cards {
                display: grid;
                grid-template-columns: repeat(12, 1fr);
                gap: 16px;
            }
            @media (max-width: 991px) { .cards { grid-template-columns: repeat(6, 1fr);} }
            @media (max-width: 575px) { .cards { grid-template-columns: repeat(2, 1fr);} }
            .card-tile {
                grid-column: span 2;
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                box-shadow: 0 8px 18px rgba(0,0,0,0.05);
                padding: 18px;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .card-icon {
                width: 46px; height: 46px; border-radius: 10px;
                display: inline-flex; align-items: center; justify-content: center;
                background: #eff6ff; color: #2563eb; font-size: 20px;
            }
            .card-meta { font-size: 12px; color: #6b7280; margin: 0; }
            .card-value { font-size: 22px; font-weight: 700; margin: 2px 0 0 0; }
            .panel { border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 8px 18px rgba(0,0,0,0.05); }
            .panel-heading { background: #ffffff !important; border-bottom: 1px solid #e5e7eb; border-top-left-radius: 14px; border-top-right-radius: 14px; }
            .panel-title { color: #111827; font-weight: 700; }
            .table { background: #ffffff; }
            .table thead th { background: #f9fafb; color: #374151; border-bottom: 1px solid #e5e7eb; }
            .table tbody td { color: #111827; }
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
                                    <h2 class="title">Dashboard</h2>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
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
                                                <h3 class="panel-title">Overview</h3>
                                            </div>
                                            <div class="panel-body">
                                                <div class="cards">
                                                    <div class="card-tile">
                                                        <div class="card-icon"><i class="fa fa-sitemap"></i></div>
                                                        <div>
                                                            <p class="card-meta">Departments</p>
                                                            <p class="card-value"><?php echo $departmentCount; ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="card-tile">
                                                        <div class="card-icon" style="background:#ecfeff;color:#0891b2;"><i class="fa fa-book"></i></div>
                                                        <div>
                                                            <p class="card-meta">Courses</p>
                                                            <p class="card-value"><?php echo $courseCount; ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="card-tile">
                                                        <div class="card-icon" style="background:#fef3c7;color:#b45309;"><i class="fa fa-users"></i></div>
                                                        <div>
                                                            <p class="card-meta">Students</p>
                                                            <p class="card-value"><?php echo $studentCount; ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="card-tile">
                                                        <div class="card-icon" style="background:#fee2e2;color:#b91c1c;"><i class="fa fa-pencil-square-o"></i></div>
                                                        <div>
                                                            <p class="card-meta">Exams</p>
                                                            <p class="card-value"><?php echo $examCount; ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="card-tile">
                                                        <div class="card-icon" style="background:#ecfdf5;color:#047857;"><i class="fa fa-layer-group"></i></div>
                                                        <div>
                                                            <p class="card-meta">Batches</p>
                                                            <p class="card-value"><?php echo $batchCount; ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin-top:20px;">
                                                    <div class="col-md-12">
                                                        <h3 class="text-center" style="margin-bottom:12px;">Pass Rate by Department</h3>
                                                        <div style="height:260px;"><canvas id="deptPassRateChart"></canvas></div>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin-top:20px;">
                                                    <div class="col-md-12">
                                                        <h3 class="text-center" style="margin-bottom:12px;">Pass Rate by Batch</h3>
                                                        <div style="height:260px;"><canvas id="batchPassRateChart"></canvas></div>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin-top:20px;">
                                                    <div class="col-md-12">
                                                        <h3 class="text-center" style="margin-bottom:12px;">Pass Rates (Department and Course-wise)</h3>
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Department</th>
                                                                    <th>Course</th>
                                                                    <th>Pass Rate (%)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($passRates as $rate) { ?>
                                                                    <tr>
                                                                        <td><?php echo htmlentities($rate->dname); ?></td>
                                                                        <td><?php echo htmlentities($rate->cname); ?></td>
                                                                        <td><?php echo htmlentities($rate->pass_rate); ?>%</td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            (function(){
                var deptLabels = <?php echo json_encode($deptLabels); ?>;
                var deptData = <?php echo json_encode($deptData); ?>;
                var batchLabels = <?php echo json_encode($batchLabels); ?>;
                var batchData = <?php echo json_encode($batchData); ?>;
                function makeChart(el, labels, data){
                    var ctx = document.getElementById(el);
                    if (!ctx || !window.Chart) return;
                    new Chart(ctx.getContext('2d'), {
                        type: 'bar',
                        data: { labels: labels, datasets: [{
                            label: 'Pass Rate %',
                            data: data,
                            backgroundColor: data.map(function(v){
                                if (v >= 75) return '#10b981';
                                if (v >= 50) return '#3b82f6';
                                return '#f59e0b';
                            }),
                            borderWidth: 0,
                            borderRadius: 6,
                            maxBarThickness: 28
                        }]},
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx){ return (ctx.parsed.y || 0) + '%'; } } } },
                            scales: { y: { beginAtZero: true, max: 100, ticks: { callback: function(value){ return value + '%'; } } }, x: { ticks: { autoSkip: true, maxRotation: 45, minRotation: 0 } } }
                        }
                    });
                }
                makeChart('deptPassRateChart', deptLabels, deptData);
                makeChart('batchPassRateChart', batchLabels, batchData);
            })();
        </script>
    </body>

    </html>
<?php } ?>
