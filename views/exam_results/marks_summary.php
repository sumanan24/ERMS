<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left: 280px; margin-top: 70px; padding: 30px; background: white; min-height: calc(100vh - 70px); overflow: auto; }
    .form-container { max-width: 700px; background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 20px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; margin-bottom: 6px; color: #333; font-weight: 500; }
    .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 6px; }
    .form-row { display: flex; gap: 15px; flex-wrap: wrap; }
    .form-row .form-group { flex: 1; min-width: 180px; }
    .btn-export { padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .btn-export:hover { background: #218838; }
    .form-group select:disabled { background: #f0f0f0; cursor: not-allowed; }
    .form-note { margin-top: 12px; color: #666; font-size: 13px; }
</style>

<div class="main-content">
    <h1 style="margin-bottom: 20px;">Students Marks Summary (Export Excel)</h1>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="GET" action="index.php" id="marksSummaryForm">
        <input type="hidden" name="action" value="exam_results">
        <input type="hidden" name="sub" value="exportMarksSummary">
        <input type="hidden" name="format" value="csv">

        <div class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="course_id">Course *</label>
                    <select id="course_id" name="course_id" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['cname']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="version_id">Version *</label>
                    <select id="version_id" name="version_id" disabled required>
                        <option value="">-- Select Course First --</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="semester">Semester *</label>
                    <select id="semester" name="semester" disabled required>
                        <option value="">-- Select Version First --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="batch_id">Batch *</label>
                    <select id="batch_id" name="batch_id" disabled required>
                        <option value="">-- Select Version First --</option>
                    </select>
                </div>
            </div>
            <p class="form-note">Export includes two sections in one file: <strong>Current batch</strong> (first-attempt marks) and <strong>Repeaters</strong> (repeat-exam students with their latest attempt marks).</p>
            <button type="submit" class="btn-export">Export to Excel (CSV)</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function loadVersions() {
        var courseId = document.getElementById('course_id').value;
        var versionSelect = document.getElementById('version_id');
        var semesterSelect = document.getElementById('semester');
        var batchSelect = document.getElementById('batch_id');
        if (!courseId) {
            versionSelect.innerHTML = '<option value="">-- Select Course First --</option>';
            versionSelect.disabled = true;
            semesterSelect.innerHTML = '<option value="">-- Select Version First --</option>';
            semesterSelect.disabled = true;
            batchSelect.innerHTML = '<option value="">-- Select Version First --</option>';
            batchSelect.disabled = true;
            return;
        }
        versionSelect.innerHTML = '<option value="">Loading...</option>';
        versionSelect.disabled = false;
        fetch('index.php?action=exams&sub=getVersions&course_id=' + courseId)
            .then(function(r) { return r.json(); })
            .then(function(versions) {
                versionSelect.innerHTML = '<option value="">-- Select Version --</option>';
                versions.forEach(function(v) {
                    var o = document.createElement('option');
                    o.value = v.id;
                    o.textContent = v.version_name;
                    versionSelect.appendChild(o);
                });
                semesterSelect.innerHTML = '<option value="">-- Select Version First --</option>';
                semesterSelect.disabled = true;
                batchSelect.innerHTML = '<option value="">-- Select Version First --</option>';
                batchSelect.disabled = true;
            });
    }
    function loadSemestersAndBatches() {
        var versionId = document.getElementById('version_id').value;
        var semesterSelect = document.getElementById('semester');
        var batchSelect = document.getElementById('batch_id');
        if (!versionId) {
            semesterSelect.innerHTML = '<option value="">-- Select Version First --</option>';
            semesterSelect.disabled = true;
            batchSelect.innerHTML = '<option value="">-- Select Version First --</option>';
            batchSelect.disabled = true;
            return;
        }
        semesterSelect.innerHTML = '<option value="">Loading...</option>';
        semesterSelect.disabled = false;
        batchSelect.innerHTML = '<option value="">Loading...</option>';
        batchSelect.disabled = false;
        Promise.all([
            fetch('index.php?action=exams&sub=getSemesters&version_id=' + versionId).then(function(r) { return r.json(); }),
            fetch('index.php?action=students&sub=getBatchesByVersion&version_id=' + versionId).then(function(r) { return r.json(); })
        ]).then(function(arr) {
            var semesters = arr[0] || [];
            var batches = arr[1] || [];
            semesterSelect.innerHTML = '<option value="">-- Select Semester --</option>';
            semesters.forEach(function(s) {
                var o = document.createElement('option');
                o.value = s;
                o.textContent = s;
                semesterSelect.appendChild(o);
            });
            batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
            batches.forEach(function(b) {
                var o = document.createElement('option');
                o.value = b.id;
                o.textContent = b.batch_no;
                batchSelect.appendChild(o);
            });
        });
    }
    document.getElementById('course_id').addEventListener('change', loadVersions);
    document.getElementById('version_id').addEventListener('change', loadSemestersAndBatches);
});
</script>
