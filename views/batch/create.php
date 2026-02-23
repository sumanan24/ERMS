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
        <h1 style="margin-bottom: 20px;">Create Batch</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=batch&sub=create">
            <div class="form-group">
                <label for="batch_no">Batch No *</label>
                <input type="text" id="batch_no" name="batch_no" required value="<?php echo htmlspecialchars($_POST['batch_no'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="cid">Course *</label>
                <select id="cid" name="cid" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo (($_POST['cid'] ?? '')==$course['id'])?'selected':''; ?>>
                            <?php echo htmlspecialchars($course['cname']); ?>
                        </option>
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
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-submit">Create</button>
                <a href="index.php?action=batch" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Store initial values from POST data (if form was submitted with errors)
const initialCourseId = document.getElementById('cid').value;
const initialVersionId = '<?php echo htmlspecialchars($_POST['version_id'] ?? '', ENT_QUOTES); ?>';

// Function to load versions for a course
function loadVersions(courseId, selectedVersionId = null) {
    const versionSelect = document.getElementById('version_id');
    if (courseId) {
        versionSelect.disabled = false;
        versionSelect.innerHTML = '<option value="">Loading versions...</option>';
        fetch('index.php?action=batch&sub=getVersions&course_id=' + courseId)
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

// Load initial versions if course is already selected (e.g., after form validation errors)
if (initialCourseId) {
    loadVersions(initialCourseId, initialVersionId);
}

// Update versions dropdown when course changes
document.getElementById('cid')?.addEventListener('change', function() {
    const courseId = this.value;
    loadVersions(courseId);
});
</script>
