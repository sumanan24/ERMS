<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content { margin-left:280px; margin-top:70px; padding:30px; background:white; min-height:calc(100vh - 70px); overflow:auto; }
    .form-container { max-width:700px; background:white; padding:30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; margin-bottom:6px; color:#333; font-weight:500; }
    .form-group input, .form-group select { width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; }
    .btn-submit { padding:12px 24px; background:#28a745; color:white; border:none; border-radius:8px; font-weight:600; }
    .btn-cancel { padding:12px 24px; background:#6c757d; color:white; text-decoration:none; border-radius:8px; font-weight:600; margin-left:8px; }
    .btn-download { padding:10px 20px; background:#17a2b8; color:white; text-decoration:none; border-radius:5px; font-size:14px; font-weight:600; }
</style>

<div class="main-content">
    <div class="form-container">
        <h1 style="margin-bottom: 20px;">Import Students from CSV</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['import_errors']) && !empty($_SESSION['import_errors'])): ?>
            <div class="alert alert-warning">
                <strong>Import Warnings:</strong><br>
                <?php foreach($_SESSION['import_errors'] as $err) { echo htmlspecialchars($err)."<br>"; } unset($_SESSION['import_errors']); ?>
            </div>
        <?php endif; ?>

        <div class="info-box" style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:20px; border:1px solid #e0e0e0;">
            <h4 style="margin-top:0; margin-bottom:15px;">CSV File Format Requirements:</h4>
            <ul style="margin-bottom:15px;">
                <li><strong>Registration Number (reg_no):</strong> Required, must be unique</li>
                <li><strong>Full Name (fullname):</strong> Required, complete name (not truncated)</li>
                <li><strong>NIC:</strong> Optional (National Identity Card number)</li>
            </ul>
            <div style="margin-top: 15px; padding: 15px; background: #fff; border-radius: 5px; border: 1px solid #ddd;">
                <strong style="display: block; margin-bottom: 10px;">Example CSV Format:</strong>
                <pre style="background: #f5f5f5; padding: 10px; border-radius: 3px; margin: 0; font-size: 12px; overflow-x: auto;">reg_no,fullname,nic
STU001,John Doe,123456789V
STU002,Jane Smith,987654321V</pre>
            </div>
            <div style="margin-top: 15px;">
                <a href="index.php?action=students&sub=downloadSample" class="btn-download">📥 Download Sample CSV File</a>
            </div>
        </div>

        <form method="POST" action="index.php?action=students&sub=import" enctype="multipart/form-data">
            <div class="form-group">
                <label for="course_id">Course *</label>
                <select id="course_id" name="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['cname']); ?></option>
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
                <label for="batch_id">Batch *</label>
                <select id="batch_id" name="batch_id" required disabled>
                    <option value="">-- Select Course First --</option>
                </select>
            </div>
            <div class="form-group">
                <label for="excel_file">CSV File *</label>
                <input type="file" id="excel_file" name="excel_file" accept=".csv" required>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-submit">Import</button>
                <a href="index.php?action=students" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Update versions dropdown when course changes
document.getElementById('course_id')?.addEventListener('change', function() {
    const courseId = this.value;
    const versionSelect = document.getElementById('version_id');
    const batchSelect = document.getElementById('batch_id');
    
    if (courseId) {
        // Enable version dropdown
        versionSelect.disabled = false;
        
        // Show loading state
        versionSelect.innerHTML = '<option value="">Loading versions...</option>';
        
        // Reset batch dropdown
        batchSelect.disabled = true;
        batchSelect.innerHTML = '<option value="">-- Select Version First --</option>';
        
        // Update versions dropdown
        fetch('index.php?action=students&sub=getVersions&course_id=' + courseId)
            .then(response => response.json())
            .then(versions => {
                versionSelect.innerHTML = '<option value="">-- Select Version --</option>';
                versions.forEach(version => {
                    const option = document.createElement('option');
                    option.value = version.id;
                    option.textContent = version.version_name;
                    versionSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading versions:', error);
                versionSelect.innerHTML = '<option value="">Error loading versions</option>';
            });
    } else {
        // Disable dropdowns and reset
        versionSelect.disabled = true;
        batchSelect.disabled = true;
        versionSelect.innerHTML = '<option value="">-- Select Course First --</option>';
        batchSelect.innerHTML = '<option value="">-- Select Course First --</option>';
    }
});

// Update batches dropdown when version changes
document.getElementById('version_id')?.addEventListener('change', function() {
    const versionId = this.value;
    const batchSelect = document.getElementById('batch_id');
    
    if (versionId) {
        // Enable batch dropdown
        batchSelect.disabled = false;
        
        // Show loading state
        batchSelect.innerHTML = '<option value="">Loading batches...</option>';
        
        // Update batches dropdown based on version
        fetch('index.php?action=students&sub=getBatchesByVersion&version_id=' + versionId)
            .then(response => response.json())
            .then(batches => {
                batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';
                batches.forEach(batch => {
                    const option = document.createElement('option');
                    option.value = batch.id;
                    option.textContent = batch.batch_no;
                    batchSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading batches:', error);
                batchSelect.innerHTML = '<option value="">Error loading batches</option>';
            });
    } else {
        // Disable batch dropdown and reset
        batchSelect.disabled = true;
        batchSelect.innerHTML = '<option value="">-- Select Version First --</option>';
    }
});
</script>
