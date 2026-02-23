<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left:280px; margin-top:70px; padding:30px; background:white; min-height:calc(100vh - 70px); overflow:auto; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
    .page-title { font-size:24px; color:#333; font-weight:600; }
    .btn-primary { padding:10px 20px; background:#667eea; color:white; text-decoration:none; border-radius:6px; font-size:14px; }
    .btn-primary:hover { background:#5568d3; }
    .filter-container { background:#f8f9fa; padding:15px; border-radius:8px; margin-bottom:15px; display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
    .filter-group { flex:1; min-width:200px; }
    .filter-group label { display:block; margin-bottom:5px; color:#333; font-size:13px; font-weight:500; }
    .filter-group input, .filter-group select { width:100%; padding:10px 12px; border:2px solid #e0e0e0; border-radius:6px; font-size:14px; }
    .btn-filter { padding:10px 16px; background:#667eea; color:white; border:none; border-radius:6px; font-weight:600; cursor:pointer; }
    .btn-reset { padding:10px 16px; background:#6c757d; color:white; text-decoration:none; border-radius:6px; font-weight:600; }
    .table-container { background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); overflow:hidden; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:12px 14px; border-bottom:1px solid #f0f0f0; text-align:left; }
    thead { background:#f8f9fa; }
    .btn-group { display:flex; gap:8px; }
    .btn-edit { padding:6px 12px; background:#667eea; color:white; border-radius:4px; text-decoration:none; font-size:12px; }
    .btn-delete { padding:6px 12px; background:#dc3545; color:white; border-radius:4px; text-decoration:none; font-size:12px; }
</style>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Student Management</h1>
        <?php if (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="index.php?action=students&sub=import" class="btn-primary" style="background:#28a745;">📥 Import CSV</a>
                <a href="index.php?action=students&sub=create" class="btn-primary">+ Add New Student</a>
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
        <input type="hidden" name="action" value="students">
        <div class="filter-group">
            <label for="filter_search">Search</label>
            <input type="text" id="filter_search" name="filter_search" placeholder="Reg no, name, NIC..." value="<?php echo htmlspecialchars($_GET['filter_search'] ?? ''); ?>">
        </div>
        <div class="filter-group">
            <label for="filter_course">Course</label>
            <select id="filter_course" name="filter_course">
                <option value="">All Courses</option>
                <?php foreach($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>" <?php echo (($_GET['filter_course'] ?? '')==$course['id'])?'selected':''; ?>><?php echo htmlspecialchars($course['cname']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter_version">Version</label>
            <select id="filter_version" name="filter_version" disabled>
                <option value="">-- Select Course First --</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter_batch">Batch</label>
            <select id="filter_batch" name="filter_batch" disabled>
                <option value="">-- Select Course First --</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-filter">Filter</button>
            <a href="index.php?action=students" class="btn-reset">Reset</a>
        </div>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Reg No</th>
                    <th>Full Name</th>
                    <th>NIC</th>
                    <th>Course</th>
                    <th>Version</th>
                    <th>Batch</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px; color:#777;">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach($students as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['reg_no']); ?></td>
                            <td><?php echo htmlspecialchars($s['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($s['nic']); ?></td>
                            <td><?php echo htmlspecialchars($s['course_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($s['version_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($s['batch_no'] ?? '-'); ?></td>
                            <td>
                                <div class="btn-group">
                                    <?php if (in_array($_SESSION['role'] ?? '', ['admin','teacher'])): ?>
                                        <a href="index.php?action=students&sub=edit&id=<?php echo $s['id']; ?>" class="btn-edit">Edit</a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role']=='admin'): ?>
                                        <a href="index.php?action=students&sub=delete&id=<?php echo $s['id']; ?>" class="btn-delete" data-delete="this student">Delete</a>
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

<script>
// Store initial values from GET parameters
const initialCourseId = document.getElementById('filter_course').value;
const initialVersionId = '<?php echo htmlspecialchars($_GET['filter_version'] ?? '', ENT_QUOTES); ?>';
const initialBatchId = '<?php echo htmlspecialchars($_GET['filter_batch'] ?? '', ENT_QUOTES); ?>';

// Function to load versions for a course
function loadVersionsForFilter(courseId, selectedVersionId = null) {
    const versionSelect = document.getElementById('filter_version');
    if (courseId) {
        versionSelect.disabled = false;
        versionSelect.innerHTML = '<option value="">Loading versions...</option>';
        fetch('index.php?action=students&sub=getVersions&course_id=' + courseId)
            .then(response => response.json())
            .then(versions => {
                versionSelect.innerHTML = '<option value="">All Versions</option>';
                versions.forEach(version => {
                    const option = document.createElement('option');
                    option.value = version.id;
                    option.textContent = version.version_name;
                    if (selectedVersionId && version.id == selectedVersionId) {
                        option.selected = true;
                    }
                    versionSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading versions:', error);
                versionSelect.innerHTML = '<option value="">Error loading versions</option>';
            });
    } else {
        versionSelect.disabled = true;
        versionSelect.innerHTML = '<option value="">-- Select Course First --</option>';
    }
}

// Function to load batches for a course
function loadBatchesForFilter(courseId, selectedBatchId = null) {
    const batchSelect = document.getElementById('filter_batch');
    if (courseId) {
        batchSelect.disabled = false;
        batchSelect.innerHTML = '<option value="">Loading batches...</option>';
        fetch('index.php?action=students&sub=getBatches&course_id=' + courseId)
            .then(response => response.json())
            .then(batches => {
                batchSelect.innerHTML = '<option value="">All Batches</option>';
                batches.forEach(batch => {
                    const option = document.createElement('option');
                    option.value = batch.id;
                    option.textContent = batch.batch_no;
                    if (selectedBatchId && batch.id == selectedBatchId) {
                        option.selected = true;
                    }
                    batchSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading batches:', error);
                batchSelect.innerHTML = '<option value="">Error loading batches</option>';
            });
    } else {
        batchSelect.disabled = true;
        batchSelect.innerHTML = '<option value="">-- Select Course First --</option>';
    }
}

// Load initial versions and batches if course is already selected
if (initialCourseId) {
    loadVersionsForFilter(initialCourseId, initialVersionId);
    loadBatchesForFilter(initialCourseId, initialBatchId);
}

// Update versions and batches dropdowns when course filter changes
document.getElementById('filter_course')?.addEventListener('change', function() {
    const courseId = this.value;
    loadVersionsForFilter(courseId);
    loadBatchesForFilter(courseId);
});
</script>
