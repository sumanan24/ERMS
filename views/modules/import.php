<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
    .main-content {
        margin-left: 280px;
        margin-top: 70px;
        padding: 30px;
        background: white;
        min-height: calc(100vh - 70px);
        min-height: calc(100dvh - 70px);
        height: calc(100vh - 70px);
        height: calc(100dvh - 70px);
        overflow-y: auto;
        overflow-x: hidden;
    }

    .form-container {
        max-width: 700px;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
        font-size: 14px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .file-input-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .file-input-wrapper input[type=file] {
        padding: 12px 15px;
        border: 2px dashed #e0e0e0;
        border-radius: 8px;
        background: #f8f9fa;
        cursor: pointer;
    }

    .file-input-wrapper input[type=file]:hover {
        border-color: #667eea;
        background: #f0f4ff;
    }

    .btn-submit {
        padding: 12px 30px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn-submit:hover {
        background: #218838;
    }

    .btn-cancel {
        padding: 12px 30px;
        background: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        display: inline-block;
        margin-left: 10px;
    }

    .btn-cancel:hover {
        background: #5a6268;
    }

    .btn-download {
        padding: 10px 20px;
        background: #17a2b8;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        display: inline-block;
        transition: background 0.3s ease;
    }

    .btn-download:hover {
        background: #138496;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-error strong {
        display: block;
        margin-top: 5px;
        margin-bottom: 3px;
    }

    .alert-error code {
        background: #fff;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #667eea;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .info-box h4 {
        margin: 0 0 10px 0;
        color: #667eea;
    }

    .info-box ul {
        margin: 0;
        padding-left: 20px;
    }

    .info-box li {
        margin-bottom: 5px;
        color: #666;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            margin-top: 60px;
            padding: 20px 15px;
        }

        .form-container {
            padding: 20px;
            max-width: 100%;
        }

        .form-group input,
        .form-group select {
            padding: 12px;
            font-size: 16px;
        }

        .btn-submit,
        .btn-cancel {
            width: 100%;
            margin-left: 0;
            margin-top: 10px;
            padding: 14px;
        }
    }
</style>

<div class="main-content">
    <div class="form-container">
        <h1 style="margin-bottom: 30px; color: #333;">Import Modules from Excel</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    // Allow HTML in error messages for formatted output
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <h4>CSV File Format Requirements:</h4>
            <ul>
                
                <li><strong>Module Code (mcode):</strong> Required, unique per version</li>
                <li><strong>Module Name (mname):</strong> Required, full name (not truncated)</li>
                <li><strong>Semester:</strong> Optional (any text/number)</li>
                <li><strong>Credit:</strong> Optional (decimal number, e.g., 3.0, 3.5)</li>
            </ul>
            <div style="margin-top: 15px; padding: 15px; background: #fff; border-radius: 5px; border: 1px solid #ddd;">
                <strong style="display: block; margin-bottom: 10px;">Example CSV Format:</strong>
                <pre style="background: #f5f5f5; padding: 10px; border-radius: 3px; margin: 0; font-size: 12px; overflow-x: auto;">mcode,mname,semester,credit
CS101,Introduction to Programming,1,3.0
CS102,Data Structures and Algorithms,2,3.5</pre>
            </div>
            <div style="margin-top: 15px;">
                <a href="index.php?action=modules&sub=downloadSample" class="btn-download">📥 Download Sample CSV File</a>
                
            </div>
        </div>

        <form method="POST" action="index.php?action=modules&sub=import" enctype="multipart/form-data" id="importForm">
            <div class="form-group">
                <label for="course_id">Course *</label>
                <select id="course_id" name="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" 
                                <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['cname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="version_id">Version *</label>
                <select id="version_id" name="version_id" required>
                    <option value="">-- Select Version --</option>
                    <?php foreach ($versions as $version): ?>
                        <option value="<?php echo $version['id']; ?>" 
                                <?php echo (isset($_POST['version_id']) && $_POST['version_id'] == $version['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($version['version_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="excel_file">Excel File (CSV) *</label>
                <div class="file-input-wrapper">
                    <input type="file" id="excel_file" name="excel_file" accept=".csv,.xlsx,.xls" required>
                </div>
                <small style="color: #666; margin-top: 5px; display: block;">Accepted formats: CSV, XLSX, XLS</small>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn-submit">📥 Import Modules</button>
                <a href="index.php?action=modules" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Update versions dropdown when course changes
document.getElementById('course_id')?.addEventListener('change', function() {
    const courseId = this.value;
    const versionSelect = document.getElementById('version_id');
    
    if (courseId) {
        fetch('index.php?action=modules&sub=getVersions&course_id=' + courseId)
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
            .catch(error => console.error('Error:', error));
    } else {
        versionSelect.innerHTML = '<option value="">-- Select Version --</option>';
    }
});
</script>
