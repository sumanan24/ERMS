<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left: 280px; margin-top: 70px; padding: 30px; background: white; min-height: calc(100vh - 70px); overflow: auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
    .page-title { font-size: 24px; color: #333; font-weight: 600; }
    .btn-primary { padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
    .btn-primary:hover { background: #5568d3; }
    .btn-download { padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
    .btn-download:hover { background: #218838; }
    .filter-container { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
    .filter-group { flex: 1; min-width: 200px; }
    .filter-group label { display: block; margin-bottom: 6px; color: #333; font-weight: 500; font-size: 13px; }
    .filter-group input, .filter-group select { width: 100%; padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; }
    .btn-filter { padding: 10px 16px; background: #667eea; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
    .btn-reset { padding: 10px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
    .table-container { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 14px; border-bottom: 1px solid #f0f0f0; text-align: left; }
    thead { background: #f8f9fa; }
    .btn-group { display: flex; gap: 8px; }
    .btn-edit { padding: 6px 12px; background: #667eea; color: white; border-radius: 4px; text-decoration: none; font-size: 12px; }
    .btn-delete { padding: 6px 12px; background: #dc3545; color: white; border-radius: 4px; text-decoration: none; font-size: 12px; }
    .btn-delete:hover { background: #c82333; }
    .status-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-select { padding: 4px 8px; border-radius: 4px; font-size: 12px; border: 1px solid #ddd; }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Exam Schedule Management</h1>
        <?php if (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <?php 
                $date_from = $_GET['filter_date_from'] ?? date('Y-m-d');
                $date_to = $_GET['filter_date_to'] ?? date('Y-m-d', strtotime('+30 days'));
                // Build export URL with current filters
                $exportParams = http_build_query([
                    'action' => 'exams',
                    'sub' => 'export',
                    'filter_date_from' => $_GET['filter_date_from'] ?? '',
                    'filter_date_to' => $_GET['filter_date_to'] ?? '',
                    'filter_course' => $_GET['filter_course'] ?? '',
                    'filter_search' => $_GET['filter_search'] ?? ''
                ]);
                ?>
               
                <a href="index.php?action=exams&sub=downloadSchedule&date_from=<?php echo htmlspecialchars($date_from); ?>&date_to=<?php echo htmlspecialchars($date_to); ?>" class="btn-download" style="background:#17a2b8;">📅 Download Schedule (Excel)</a>
                <a href="index.php?action=exam_results" class="btn-primary" style="background:#28a745;">👥 Students & Results</a>
                <a href="index.php?action=exams&sub=schedule" class="btn-primary">✏️ Edit Schedule by Course</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="GET" action="index.php" class="filter-container">
        <input type="hidden" name="action" value="exams">
        <div class="filter-group">
            <label for="filter_date_from">Date From</label>
            <input type="date" id="filter_date_from" name="filter_date_from" value="<?php echo htmlspecialchars($_GET['filter_date_from'] ?? ''); ?>">
        </div>
        <div class="filter-group">
            <label for="filter_date_to">Date To</label>
            <input type="date" id="filter_date_to" name="filter_date_to" value="<?php echo htmlspecialchars($_GET['filter_date_to'] ?? ''); ?>">
        </div>
        <div class="filter-group">
            <label for="filter_course">Course</label>
            <select id="filter_course" name="filter_course">
                <option value="">All Courses</option>
                <?php foreach($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>" <?php echo (($_GET['filter_course'] ?? '')==$course['id'])?'selected':''; ?>>
                        <?php echo htmlspecialchars($course['cname']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter_search">Search</label>
            <input type="text" id="filter_search" name="filter_search" placeholder="Module, location..." value="<?php echo htmlspecialchars($_GET['filter_search'] ?? ''); ?>">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-filter">Filter</button>
            <a href="index.php?action=exams" class="btn-reset">Reset</a>
        </div>
    </form>

    <div class="table-container">
        <table id="examsTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Course</th>
                    <th>Module</th>
                    <th>Location</th>
                    <th>Resulted status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($exams)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px; color:#777;">No exams found.</td></tr>
                <?php else: ?>
                    <?php 
                    $isStudent = (($_SESSION['role'] ?? '') === 'student');
                    foreach($exams as $exam): 
                        $status = $exam['resulted_status'] ?? 'pending';
                        $statusLabel = $status === 'approved' ? 'Approved' : 'Pending';
                    ?>
                        <tr>
                            <td><?php echo date('d.m.Y', strtotime($exam['exam_date'])); ?></td>
                            <td><?php echo htmlspecialchars($exam['time_slot']); ?></td>
                            <td><?php echo htmlspecialchars($exam['course_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($exam['module_code'] ?? '-') . ' - ' . htmlspecialchars($exam['module_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($exam['location']); ?></td>
                            <td>
                                <?php if ($isStudent): ?>
                                    <span class="status-badge status-approved">Approved</span>
                                <?php else: ?>
                                    <?php $redirectUrl = 'index.php?action=exams' . (isset($_GET['filter_date_from']) && $_GET['filter_date_from'] !== '' ? '&filter_date_from='.urlencode($_GET['filter_date_from']) : '') . (isset($_GET['filter_date_to']) && $_GET['filter_date_to'] !== '' ? '&filter_date_to='.urlencode($_GET['filter_date_to']) : '') . (isset($_GET['filter_course']) && $_GET['filter_course'] !== '' ? '&filter_course='.urlencode($_GET['filter_course']) : '') . (isset($_GET['filter_search']) && $_GET['filter_search'] !== '' ? '&filter_search='.urlencode($_GET['filter_search']) : ''); ?>
                                    <form method="POST" action="index.php?action=exams&sub=setResultedStatus" style="display:inline;" class="resulted-status-form">
                                        <input type="hidden" name="exam_id" value="<?php echo (int)$exam['id']; ?>">
                                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectUrl); ?>">
                                        <select name="resulted_status" class="status-select" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <?php if (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
                                        <a href="index.php?action=exams&sub=edit&id=<?php echo $exam['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="index.php?action=exam_results&filter_exam=<?php echo $exam['id']; ?>" class="btn-edit" style="background:#28a745;">+ Students</a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role']=='admin'): ?>
                                        <a href="index.php?action=exams&sub=delete&id=<?php echo $exam['id']; ?>" class="btn-delete" data-delete="this exam">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var t = document.getElementById('examsTable');
    if (t && t.getElementsByTagName('tbody')[0].rows.length > 0 && !t.getElementsByTagName('tbody')[0].rows[0].querySelector('td[colspan]')) {
        $('#examsTable').DataTable({
            order: [[0, 'asc']],
            pageLength: 25,
            language: { search: 'Search:', lengthMenu: 'Show _MENU_ entries' }
        });
    }
});
</script>
