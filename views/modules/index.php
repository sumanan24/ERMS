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

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .page-title {
        font-size: 24px;
        color: #333;
        font-weight: 600;
    }

    .btn-primary {
        padding: 10px 20px;
        background: #667eea;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 14px;
        transition: background 0.3s ease;
    }

    .btn-primary:hover {
        background: #5568d3;
    }

    .btn-import {
        padding: 10px 20px;
        background: #28a745;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 14px;
        transition: background 0.3s ease;
    }

    .btn-import:hover {
        background: #218838;
    }

    .filter-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-group label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: 500;
        font-size: 14px;
    }

    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-filter {
        padding: 10px 20px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
        white-space: nowrap;
    }

    .btn-filter:hover {
        background: #5568d3;
    }

    .btn-reset {
        padding: 10px 20px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: background 0.3s ease;
        white-space: nowrap;
    }

    .btn-reset:hover {
        background: #5a6268;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
    }

    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #f8f9fa;
    }

    th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #e0e0e0;
    }

    td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #666;
    }

    tr:hover {
        background: #f8f9fa;
    }

    .btn-group {
        display: flex;
        gap: 10px;
    }

    .btn-edit {
        padding: 5px 15px;
        background: #667eea;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 12px;
    }

    .btn-edit:hover {
        background: #5568d3;
    }

    .btn-delete {
        padding: 5px 15px;
        background: #dc3545;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 12px;
        border: none;
        cursor: pointer;
    }

    .btn-delete:hover {
        background: #c82333;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            margin-top: 60px;
            padding: 20px 15px;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 20px;
        }

        .btn-primary,
        .btn-import {
            width: 100%;
            text-align: center;
            padding: 12px;
        }

        .filter-container {
            flex-direction: column;
            gap: 10px;
        }

        .filter-group {
            min-width: 100%;
        }

        .filter-actions {
            width: 100%;
            justify-content: stretch;
        }

        .btn-filter,
        .btn-reset {
            flex: 1;
            text-align: center;
        }

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            min-width: 1000px;
        }

        th, td {
            padding: 10px 8px;
            font-size: 13px;
        }

        .btn-group {
            flex-direction: column;
            gap: 5px;
        }

        .btn-edit, .btn-delete {
            width: 100%;
            text-align: center;
            padding: 8px;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 15px 10px;
        }

        .page-title {
            font-size: 18px;
        }

        th, td {
            padding: 8px 5px;
            font-size: 12px;
        }
    }
</style>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Module Management</h1>
        <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'teacher'])): ?>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="index.php?action=modules&sub=import" class="btn-import">📥 Import Excel</a>
                <a href="index.php?action=modules&sub=create" class="btn-primary">+ Add New Module</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
                echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['import_errors']) && !empty($_SESSION['import_errors'])): ?>
        <div class="alert alert-warning">
            <strong>Import Warnings:</strong><br>
            <?php 
                foreach ($_SESSION['import_errors'] as $error) {
                    echo htmlspecialchars($error) . "<br>";
                }
                unset($_SESSION['import_errors']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="GET" action="index.php" class="filter-container" id="filterForm">
        <input type="hidden" name="action" value="modules">
        
        <div class="filter-group">
            <label for="filter_search">Search</label>
            <input type="text" id="filter_search" name="filter_search" 
                   placeholder="Search by code, name, course..."
                   value="<?php echo htmlspecialchars($_GET['filter_search'] ?? ''); ?>">
        </div>

        <div class="filter-group">
            <label for="filter_course">Course</label>
            <select id="filter_course" name="filter_course">
                <option value="">All Courses</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>" 
                            <?php echo (isset($_GET['filter_course']) && $_GET['filter_course'] == $course['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['cname']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="filter_version">Version</label>
            <select id="filter_version" name="filter_version">
                <option value="">All Versions</option>
                <?php foreach ($versions as $version): ?>
                    <option value="<?php echo $version['id']; ?>" 
                            <?php echo (isset($_GET['filter_version']) && $_GET['filter_version'] == $version['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($version['version_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="filter_semester">Semester</label>
            <input type="text" id="filter_semester" name="filter_semester" 
                   placeholder="Filter by semester..."
                   value="<?php echo htmlspecialchars($_GET['filter_semester'] ?? ''); ?>">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-filter">Filter</button>
            <a href="index.php?action=modules" class="btn-reset">Reset</a>
        </div>
    </form>

    <?php if (!empty($modules)): ?>
        <div style="margin-bottom: 15px; color: #666; font-size: 14px;">
            Showing <?php echo count($modules); ?> module(s)
        </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Module Code</th>
                    <th>Module Name</th>
                    <th>Course</th>
                    <th>Version</th>
                    <th>Semester</th>
                    <th>Credit</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($modules)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                            No modules found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($modules as $module): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($module['mcode']); ?></td>
                            <td><?php echo htmlspecialchars($module['mname']); ?></td>
                            <td><?php echo htmlspecialchars($module['course_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($module['version_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($module['semester'] ?? '-'); ?></td>
                            <td><?php echo $module['credit'] ? $module['credit'] : '0'; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($module['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'teacher'])): ?>
                                        <a href="index.php?action=modules&sub=edit&id=<?php echo $module['id']; ?>" class="btn-edit">Edit</a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                        <a href="index.php?action=modules&sub=delete&id=<?php echo $module['id']; ?>" 
                                           class="btn-delete" 
                                           data-delete="this module">Delete</a>
                                    <?php endif; ?>
                                    <?php if (!in_array($_SESSION['role'] ?? '', ['admin', 'teacher'])): ?>
                                        <span style="color: #999; font-size: 12px;">View Only</span>
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
// Update versions dropdown when course changes
document.getElementById('filter_course')?.addEventListener('change', function() {
    const courseId = this.value;
    const versionSelect = document.getElementById('filter_version');
    
    if (courseId) {
        fetch('index.php?action=modules&sub=getVersions&course_id=' + courseId)
            .then(response => response.json())
            .then(versions => {
                versionSelect.innerHTML = '<option value="">All Versions</option>';
                versions.forEach(version => {
                    const option = document.createElement('option');
                    option.value = version.id;
                    option.textContent = version.version_name;
                    versionSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    } else {
        versionSelect.innerHTML = '<option value="">All Versions</option>';
    }
});
</script>
