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
    .mode-options { margin-bottom: 18px; }
    .mode-options label { margin-right: 20px; cursor: pointer; }
    .student-section, .batch-section { margin-top: 15px; padding: 15px; background: white; border-radius: 8px; border: 1px solid #e0e0e0; }
    .btn-download { padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .btn-download:hover { background: #5568d3; }
    .form-group select:disabled { background: #f0f0f0; cursor: not-allowed; }
</style>

<div class="main-content">
    <h1 style="margin-bottom: 20px;">Download Admission Cards</h1>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="GET" action="index.php" id="admissionCardForm">
        <input type="hidden" name="action" value="exam_results">
        <input type="hidden" name="sub" value="downloadAdmissionCard">

        <div class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="date_from">Exam Date From *</label>
                    <input type="date" id="date_from" name="date_from" required value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="date_to">Exam Date To *</label>
                    <input type="date" id="date_to" name="date_to" required value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                </div>
            </div>
            <p style="margin: 0 0 12px 0; color: #666; font-size: 13px;">Only exams between these dates that the student is facing will appear on the admission card.</p>

            <div class="form-group mode-options">
                <label>Download</label><br>
                <label><input type="radio" name="mode" value="individual" <?php echo (($_GET['mode'] ?? 'individual') === 'individual') ? 'checked' : ''; ?> id="mode_individual"> Individual (one student)</label>
                <label><input type="radio" name="mode" value="batch" <?php echo (($_GET['mode'] ?? '') === 'batch') ? 'checked' : ''; ?> id="mode_batch"> Batch (all students in batch)</label>
            </div>

            <div class="student-section" id="studentSection">
                <div class="form-row">
                    <div class="form-group">
                        <label for="course_id_ind">Course *</label>
                        <select id="course_id_ind" name="course_id">
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['cname']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="version_id_ind">Version *</label>
                        <select id="version_id_ind" name="version_id" disabled>
                            <option value="">-- Select Course First --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="batch_id_ind">Batch *</label>
                        <select id="batch_id_ind" name="batch_id" disabled>
                            <option value="">-- Select Version First --</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="student_id">Student *</label>
                    <select id="student_id" name="student_id" disabled>
                        <option value="">-- Select Batch First --</option>
                    </select>
                </div>
            </div>

            <div class="batch-section" id="batchSection" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label for="course_id_batch">Course *</label>
                        <select id="course_id_batch" name="course_id_batch">
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['cname']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="version_id_batch">Version *</label>
                        <select id="version_id_batch" name="version_id_batch" disabled>
                            <option value="">-- Select Course First --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="batch_id_batch">Batch *</label>
                        <select id="batch_id_batch" name="batch_id_batch" disabled>
                            <option value="">-- Select Version First --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-download">Download Admission Card(s)</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modeIndividual = document.getElementById('mode_individual');
    const modeBatch = document.getElementById('mode_batch');
    const studentSection = document.getElementById('studentSection');
    const batchSection = document.getElementById('batchSection');
    const form = document.getElementById('admissionCardForm');

    function setMode() {
        if (modeIndividual.checked) {
            studentSection.style.display = 'block';
            batchSection.style.display = 'none';
            form.removeAttribute('data-batch');
        } else {
            studentSection.style.display = 'none';
            batchSection.style.display = 'block';
            form.setAttribute('data-batch', '1');
        }
    }
    modeIndividual.addEventListener('change', setMode);
    modeBatch.addEventListener('change', setMode);
    setMode();

    form.addEventListener('submit', function(e) {
        if (modeBatch.checked) {
            document.getElementById('course_id_batch').name = 'course_id';
            document.getElementById('version_id_batch').name = 'version_id';
            document.getElementById('batch_id_batch').name = 'batch_id';
            document.getElementById('student_id').removeAttribute('name');
            document.getElementById('course_id_ind').removeAttribute('name');
            document.getElementById('version_id_ind').removeAttribute('name');
            document.getElementById('batch_id_ind').removeAttribute('name');
        } else {
            document.getElementById('course_id_ind').name = 'course_id';
            document.getElementById('version_id_ind').name = 'version_id';
            document.getElementById('batch_id_ind').name = 'batch_id';
            document.getElementById('student_id').name = 'student_id';
            document.getElementById('course_id_batch').removeAttribute('name');
            document.getElementById('version_id_batch').removeAttribute('name');
            document.getElementById('batch_id_batch').removeAttribute('name');
        }
    });

    function loadVersions(versionSelect, courseId, urlSuffix) {
        versionSelect.innerHTML = '<option value="">Loading...</option>';
        fetch('index.php?action=exams&sub=getVersions&course_id=' + courseId)
            .then(r => r.json())
            .then(versions => {
                versionSelect.innerHTML = '<option value="">-- Select Version --</option>';
                versions.forEach(v => {
                    const o = document.createElement('option');
                    o.value = v.id;
                    o.textContent = v.version_name;
                    versionSelect.appendChild(o);
                });
            });
    }
    function loadBatches(batchSelect, versionId) {
        batchSelect.innerHTML = '<option value="">Loading...</option>';
        fetch('index.php?action=students&sub=getBatchesByVersion&version_id=' + versionId)
            .then(r => r.json())
            .then(batches => {
                batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
                batches.forEach(b => {
                    const o = document.createElement('option');
                    o.value = b.id;
                    o.textContent = b.batch_no;
                    batchSelect.appendChild(o);
                });
            });
    }
    function loadStudents(studentSelect, batchId) {
        studentSelect.innerHTML = '<option value="">Loading...</option>';
        fetch('index.php?action=exam_results&sub=getStudentsByBatch&batch_id=' + batchId + '&exam_id=0')
            .then(r => r.json())
            .then(students => {
                studentSelect.innerHTML = '<option value="">-- Select Student --</option>';
                (students || []).forEach(s => {
                    const o = document.createElement('option');
                    o.value = s.id;
                    o.textContent = (s.reg_no || '') + ' - ' + (s.fullname || s.student_name || '');
                    studentSelect.appendChild(o);
                });
                studentSelect.disabled = false;
            });
    }

    document.getElementById('course_id_ind').addEventListener('change', function() {
        const v = document.getElementById('version_id_ind');
        const b = document.getElementById('batch_id_ind');
        const s = document.getElementById('student_id');
        if (!this.value) { v.innerHTML = '<option value="">-- Select Course First --</option>'; v.disabled = true; b.disabled = true; s.disabled = true; return; }
        v.disabled = false;
        loadVersions(v, this.value);
        b.innerHTML = '<option value="">-- Select Version First --</option>'; b.disabled = true;
        s.innerHTML = '<option value="">-- Select Batch First --</option>'; s.disabled = true;
    });
    document.getElementById('version_id_ind').addEventListener('change', function() {
        const b = document.getElementById('batch_id_ind');
        const s = document.getElementById('student_id');
        if (!this.value) { b.disabled = true; s.disabled = true; return; }
        b.disabled = false;
        loadBatches(b, this.value);
        s.innerHTML = '<option value="">-- Select Batch First --</option>'; s.disabled = true;
    });
    document.getElementById('batch_id_ind').addEventListener('change', function() {
        const s = document.getElementById('student_id');
        if (!this.value) { s.disabled = true; return; }
        loadStudents(s, this.value);
    });

    document.getElementById('course_id_batch').addEventListener('change', function() {
        const v = document.getElementById('version_id_batch');
        const b = document.getElementById('batch_id_batch');
        if (!this.value) { v.innerHTML = '<option value="">-- Select Course First --</option>'; v.disabled = true; b.disabled = true; return; }
        v.disabled = false;
        loadVersions(v, this.value);
        b.innerHTML = '<option value="">-- Select Version First --</option>'; b.disabled = true;
    });
    document.getElementById('version_id_batch').addEventListener('change', function() {
        const b = document.getElementById('batch_id_batch');
        if (!this.value) { b.disabled = true; return; }
        b.disabled = false;
        loadBatches(b, this.value);
    });
});
</script>
