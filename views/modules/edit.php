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
        max-width: 600px;
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

    .btn-submit {
        padding: 12px 30px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn-submit:hover {
        background: #5568d3;
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

        div[style*="margin-top: 30px"] {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<div class="main-content">
    <div class="form-container">
        <h1 style="margin-bottom: 30px; color: #333;">Edit Module</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=modules&sub=edit&id=<?php echo $module->id; ?>" id="moduleForm">
            <div class="form-group">
                <label for="cid">Course *</label>
                <select id="cid" name="cid" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" 
                                <?php echo ($module->cid == $course['id']) ? 'selected' : ''; ?>>
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
                                <?php echo ($module->version_id == $version['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($version['version_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="mcode">Module Code *</label>
                <input type="text" id="mcode" name="mcode" required 
                       value="<?php echo htmlspecialchars($module->mcode ?? ''); ?>"
                       placeholder="e.g., CS101">
            </div>

            <div class="form-group">
                <label for="mname">Module Name *</label>
                <input type="text" id="mname" name="mname" required 
                       value="<?php echo htmlspecialchars($module->mname ?? ''); ?>"
                       placeholder="Enter module name">
            </div>

            <div class="form-group">
                <label for="semester">Semester</label>
                <input type="text" id="semester" name="semester" 
                       value="<?php echo htmlspecialchars($module->semester ?? ''); ?>"
                       placeholder="e.g., 1, 2, 3, etc.">
            </div>

            <div class="form-group">
                <label for="credit">Credit</label>
                <input type="number" id="credit" name="credit" step="0.1" min="0" 
                       value="<?php echo htmlspecialchars($module->credit ?? '0'); ?>"
                       placeholder="e.g., 3.0">
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn-submit">Update Module</button>
                <a href="index.php?action=modules" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Update versions dropdown when course changes
document.getElementById('cid')?.addEventListener('change', function() {
    const courseId = this.value;
    const versionSelect = document.getElementById('version_id');
    
    if (courseId) {
        fetch('index.php?action=modules&sub=getVersions&course_id=' + courseId)
            .then(response => response.json())
            .then(versions => {
                const currentVersionId = versionSelect.value;
                versionSelect.innerHTML = '<option value="">-- Select Version --</option>';
                versions.forEach(version => {
                    const option = document.createElement('option');
                    option.value = version.id;
                    option.textContent = version.version_name;
                    if (version.id == currentVersionId) {
                        option.selected = true;
                    }
                    versionSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    } else {
        versionSelect.innerHTML = '<option value="">-- Select Version --</option>';
    }
});

</script>
