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
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
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
        .form-group select,
        .form-group textarea {
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

    @media (max-width: 480px) {
        .main-content {
            padding: 15px 10px;
        }

        .form-container {
            padding: 15px;
        }

        .form-container h1 {
            font-size: 20px;
            margin-bottom: 20px;
        }
    }
</style>

<div class="main-content">
    <div class="form-container">
        <h1 style="margin-bottom: 30px; color: #333;">Create New Version</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=versions&sub=create">
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
                <label for="version_name">Version Name *</label>
                <input type="text" id="version_name" name="version_name" required 
                       value="<?php echo htmlspecialchars($_POST['version_name'] ?? ''); ?>"
                       placeholder="Enter version name">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" 
                          placeholder="Enter version description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : 'selected'; ?>>Active</option>
                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn-submit">Create Version</button>
                <a href="index.php?action=versions" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
