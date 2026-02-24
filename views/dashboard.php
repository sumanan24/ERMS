<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
require_once __DIR__ . '/../config/database.php';

// Helper: safe table count (returns 0 on error)
function getTableCount($conn, $table) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM {$table}");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

// Get exam results statistics
function getResultsStats($conn) {
    try {
        $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM exam_results GROUP BY status");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats = ['pass' => 0, 'fail' => 0, 'absent' => 0];
        foreach ($results as $row) {
            $stats[$row['status']] = (int)$row['count'];
        }
        return $stats;
    } catch (Exception $e) {
        return ['pass' => 0, 'fail' => 0, 'absent' => 0];
    }
}

// Get students by course
function getStudentsByCourse($conn) {
    try {
        $stmt = $conn->prepare("SELECT c.cname, COUNT(s.id) as count 
                                FROM student s 
                                LEFT JOIN courses c ON s.cid = c.id 
                                GROUP BY c.id, c.cname 
                                ORDER BY count DESC 
                                LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Get exams by month
function getExamsByMonth($conn) {
    try {
        $stmt = $conn->prepare("SELECT DATE_FORMAT(exam_date, '%Y-%m') as month, COUNT(*) as count 
                                FROM exams 
                                WHERE exam_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                                GROUP BY month 
                                ORDER BY month ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

$db = new Database();
$conn = $db->getDbConnection();
if (!$conn) {
    echo '<p style="padding:20px;color:#c00;">Database connection failed. Please try again later.</p>';
    if (defined('LAYOUT_HTML_STARTED')) { require_once __DIR__ . '/includes/footer.php'; }
    return;
}

$counts = [
    'students' => getTableCount($conn, 'student'),
    'exams' => getTableCount($conn, 'exams'),
    'results' => getTableCount($conn, 'exam_results'),
    'subjects' => getTableCount($conn, 'subjects'),
    'users' => getTableCount($conn, 'users'),
    'courses' => getTableCount($conn, 'courses'),
    'versions' => getTableCount($conn, 'versions'),
    'modules' => getTableCount($conn, 'module'),
    'batches' => getTableCount($conn, 'batch'),
];

$resultsStats = getResultsStats($conn);
$studentsByCourse = getStudentsByCourse($conn);
$examsByMonth = getExamsByMonth($conn);
?>

<style>
    .main-content {
        margin-left: 280px;
        margin-top: 70px;
        padding: 30px;
        background: #f5f7fa;
        min-height: calc(100vh - 70px);
        overflow-y: auto;
        overflow-x: hidden;
    }

    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.35);
        margin-bottom: 30px;
    }

    .welcome-card h2 {
        color: #fff;
        margin-bottom: 10px;
        font-size: 26px;
    }

    .welcome-card p {
        color: #e6e6ff;
        line-height: 1.6;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        text-align: left;
        color: #fff;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
    }

    .stat-card h3 {
        font-size: 36px;
        margin-bottom: 6px;
        font-weight: 700;
    }

    .stat-card p {
        color: #f5f5f5;
        font-size: 14px;
        margin: 0;
        font-weight: 500;
    }

    .stat-icon {
        position: absolute;
        top: 16px;
        right: 16px;
        font-size: 32px;
        opacity: 0.2;
    }

    /* Enhanced Color themes with more vibrant gradients */
    .stat-blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-cyan { background: linear-gradient(135deg, #00c9ff 0%, #92fe9d 100%); }
    .stat-purple { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); }
    .stat-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-pink { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%); }
    .stat-teal { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-navy { background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); }
    .stat-red { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); }
    .stat-yellow { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
    .stat-indigo { background: linear-gradient(135deg, #5f72bd 0%, #9921e8 100%); }
    .stat-emerald { background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%); }

    /* Charts Section */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .chart-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .chart-card h3 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 18px;
        font-weight: 600;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 12px;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        .main-content {
            margin-left: 0;
            margin-top: 60px;
            padding: 20px 15px;
        }

        .welcome-card {
            padding: 20px;
            margin-bottom: 20px;
        }

        .welcome-card h2 {
            font-size: 20px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .stat-card {
            padding: 20px;
        }

        .stat-card h3 {
            font-size: 28px;
        }

        .charts-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .chart-container {
            height: 250px;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 15px 10px;
        }

        .welcome-card {
            padding: 15px;
        }

        .welcome-card h2 {
            font-size: 18px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .stat-card h3 {
            font-size: 24px;
        }

        .chart-container {
            height: 200px;
        }
    }
</style>

<div class="main-content">
    <div class="welcome-card">
        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>!</h2>
        <p>Here is a comprehensive overview of your exam management system with interactive charts and statistics.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <h3><?php echo $counts['students']; ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card stat-orange">
            <div class="stat-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
            <h3><?php echo $counts['courses']; ?></h3>
            <p>Total Courses</p>
        </div>
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-tags-fill"></i></div>
            <h3><?php echo $counts['versions']; ?></h3>
            <p>Total Versions</p>
        </div>
        <div class="stat-card stat-pink">
            <div class="stat-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
            <h3><?php echo $counts['modules']; ?></h3>
            <p>Total Modules</p>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h3><?php echo $counts['exams']; ?></h3>
            <p>Total Exams</p>
        </div>
        <div class="stat-card stat-teal">
            <div class="stat-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <h3><?php echo $counts['results']; ?></h3>
            <p>Total Results</p>
        </div>
        <div class="stat-card stat-cyan">
            <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
            <h3><?php echo $counts['batches']; ?></h3>
            <p>Total Batches</p>
        </div>
        <div class="stat-card stat-indigo">
            <div class="stat-icon"><i class="bi bi-person-badge-fill"></i></div>
            <h3><?php echo $counts['users']; ?></h3>
            <p>Total Users</p>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <h3>📊 Exam Results Status</h3>
            <div class="chart-container">
                <canvas id="resultsChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3>👥 Students by Course (Top 5)</h3>
            <div class="chart-container">
                <canvas id="studentsByCourseChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3>📅 Exams Over Time (Last 6 Months)</h3>
            <div class="chart-container">
                <canvas id="examsByMonthChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3>🎯 Results Distribution</h3>
            <div class="chart-container">
                <canvas id="resultsDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Exam Results Status Chart (Doughnut)
const resultsCtx = document.getElementById('resultsChart').getContext('2d');
new Chart(resultsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pass', 'Fail', 'Absent'],
        datasets: [{
            data: [
                <?php echo $resultsStats['pass']; ?>,
                <?php echo $resultsStats['fail']; ?>,
                <?php echo $resultsStats['absent']; ?>
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(108, 117, 125, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(108, 117, 125, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: { size: 12 }
                }
            }
        }
    }
});

// Students by Course Chart (Bar)
const studentsCtx = document.getElementById('studentsByCourseChart').getContext('2d');
const courseLabels = [<?php echo implode(',', array_map(function($c) { return "'" . addslashes($c['cname'] ?? 'Unknown') . "'"; }, $studentsByCourse)); ?>];
const courseData = [<?php echo implode(',', array_map(function($c) { return $c['count']; }, $studentsByCourse)); ?>];

new Chart(studentsCtx, {
    type: 'bar',
    data: {
        labels: courseLabels.length > 0 ? courseLabels : ['No Data'],
        datasets: [{
            label: 'Students',
            data: courseData.length > 0 ? courseData : [0],
            backgroundColor: [
                'rgba(102, 126, 234, 0.8)',
                'rgba(245, 101, 101, 0.8)',
                'rgba(161, 140, 209, 0.8)',
                'rgba(17, 153, 142, 0.8)',
                'rgba(79, 172, 254, 0.8)'
            ],
            borderColor: [
                'rgba(102, 126, 234, 1)',
                'rgba(245, 101, 101, 1)',
                'rgba(161, 140, 209, 1)',
                'rgba(17, 153, 142, 1)',
                'rgba(79, 172, 254, 1)'
            ],
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Exams by Month Chart (Line)
const examsCtx = document.getElementById('examsByMonthChart').getContext('2d');
const monthLabels = [<?php echo implode(',', array_map(function($m) { return "'" . date('M Y', strtotime($m['month'] . '-01')) . "'"; }, $examsByMonth)); ?>];
const monthData = [<?php echo implode(',', array_map(function($m) { return $m['count']; }, $examsByMonth)); ?>];

new Chart(examsCtx, {
    type: 'line',
    data: {
        labels: monthLabels.length > 0 ? monthLabels : ['No Data'],
        datasets: [{
            label: 'Exams',
            data: monthData.length > 0 ? monthData : [0],
            borderColor: 'rgba(79, 172, 254, 1)',
            backgroundColor: 'rgba(79, 172, 254, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(79, 172, 254, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Results Distribution Chart (Bar - Horizontal)
const distributionCtx = document.getElementById('resultsDistributionChart').getContext('2d');
new Chart(distributionCtx, {
    type: 'bar',
    data: {
        labels: ['Pass', 'Fail', 'Absent'],
        datasets: [{
            label: 'Count',
            data: [
                <?php echo $resultsStats['pass']; ?>,
                <?php echo $resultsStats['fail']; ?>,
                <?php echo $resultsStats['absent']; ?>
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(108, 117, 125, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(108, 117, 125, 1)'
            ],
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
