<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left:280px; margin-top:70px; padding:30px; background:white; min-height:calc(100vh - 70px); overflow:auto; }
    .form-container { max-width:600px; background:white; padding:30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; margin-bottom:6px; color:#333; font-weight:500; }
    .form-group input, .form-group select { width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; }
    .btn-submit { padding:12px 24px; background:#667eea; color:white; border:none; border-radius:8px; font-weight:600; }
    .btn-cancel { padding:12px 24px; background:#6c757d; color:white; text-decoration:none; border-radius:8px; font-weight:600; margin-left:8px; }
</style>

<div class="main-content">
    <div class="form-container">
        <h1 style="margin-bottom: 20px;">Create Student</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=students&sub=create">
            <div class="form-group">
                <label for="reg_no">Reg No *</label>
                <input type="text" id="reg_no" name="reg_no" required value="<?php echo htmlspecialchars($_POST['reg_no'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="fullname">Full Name *</label>
                <input type="text" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="nic">NIC</label>
                <input type="text" id="nic" name="nic" value="<?php echo htmlspecialchars($_POST['nic'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="cid">Course *</label>
                <select id="cid" name="cid" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo (($_POST['cid'] ?? '')==$course['id'])?'selected':''; ?>><?php echo htmlspecialchars($course['cname']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="version_id">Version</label>
                <select id="version_id" name="version_id" disabled>
                    <option value="">-- Select Course First --</option>
                </select>
            </div>
            <div class="form-group">
                <label for="bid">Batch *</label>
                <select id="bid" name="bid" required disabled>
                    <option value="">-- Select Course First --</option>
                </select>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-submit">Create</button>
                <a href="index.php?action=students" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Store initial values from POST data (if form was submitted with errors)
const initialCourseId = document.getElementById('cid').value;
const initialVersionId = '<?php echo htmlspecialchars($_POST['version_id'] ?? '', ENT_QUOTES); ?>';
const initialBatchId = '<?php echo htmlspecialchars($_POST['bid'] ?? '', ENT_QUOTES); ?>';

// Function to load versions for a course
function loadVersions(courseId, selectedVersionId = null) {
    const versionSelect = document.getElementById('version_id');
    if (courseId) {
        versionSelect.disabled = false;
        versionSelect.innerHTML = '<option value="">Loading versions...</option>';
        fetch('index.php?action=students&sub=getVersions&course_id=' + courseId)
            .then(response => response.json())
            .then(versions => {
                versionSelect.innerHTML = '<option value="">-- Select Version --</option>';
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
function loadBatches(courseId, selectedBatchId = null) {
    const batchSelect = document.getElementById('bid');
    if (courseId) {
        batchSelect.disabled = false;
        batchSelect.innerHTML = '<option value="">Loading batches...</option>';
        fetch('index.php?action=students&sub=getBatches&course_id=' + courseId)
            .then(response => response.json())
            .then(batches => {
                batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
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

// Load initial versions and batches if course is already selected (e.g., after form validation errors)
if (initialCourseId) {
    loadVersions(initialCourseId, initialVersionId);
    loadBatches(initialCourseId, initialBatchId);
}

// Update versions and batches dropdowns when course changes
document.getElementById('cid')?.addEventListener('change', function() {
    const courseId = this.value;
    loadVersions(courseId);
    loadBatches(courseId);
});
</script>
