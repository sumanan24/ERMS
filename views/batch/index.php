<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left: 280px; margin-top: 70px; padding: 30px; background: white; min-height: calc(100vh - 70px); overflow: auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
    .page-title { font-size: 24px; color: #333; font-weight: 600; }
    .btn-primary { padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
    .btn-primary:hover { background: #5568d3; }
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
</style>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Batch Management</h1>
        <?php if (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
            <a href="index.php?action=batch&sub=create" class="btn-primary">+ Add New Batch</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="GET" action="index.php" class="filter-container">
        <input type="hidden" name="action" value="batch">
        <div class="filter-group">
            <label for="filter_search">Search</label>
            <input type="text" id="filter_search" name="filter_search" placeholder="Batch no or course..." value="<?php echo htmlspecialchars($_GET['filter_search'] ?? ''); ?>">
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
            <label for="filter_start_from">Start Date From</label>
            <input type="date" id="filter_start_from" name="filter_start_from" value="<?php echo htmlspecialchars($_GET['filter_start_from'] ?? ''); ?>">
        </div>
        <div class="filter-group">
            <label for="filter_start_to">Start Date To</label>
            <input type="date" id="filter_start_to" name="filter_start_to" value="<?php echo htmlspecialchars($_GET['filter_start_to'] ?? ''); ?>">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-filter">Filter</button>
            <a href="index.php?action=batch" class="btn-reset">Reset</a>
        </div>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Batch No</th>
                    <th>Course</th>
                    <th>Version</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:30px; color:#777;">No batches found.</td></tr>
                <?php else: ?>
                    <?php foreach($batches as $batch): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($batch['batch_no']); ?></td>
                            <td><?php echo htmlspecialchars($batch['course_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($batch['version_name'] ?? '-'); ?></td>
                            <td><?php echo $batch['start_date'] ?: '-'; ?></td>
                            <td><?php echo $batch['end_date'] ?: '-'; ?></td>
                            <td>
                                <div class="btn-group">
                                    <?php if (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
                                        <a href="index.php?action=batch&sub=edit&id=<?php echo $batch['id']; ?>" class="btn-edit">Edit</a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role']=='admin'): ?>
                                        <a href="index.php?action=batch&sub=delete&id=<?php echo $batch['id']; ?>" class="btn-delete" data-delete="this batch">Delete</a>
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
