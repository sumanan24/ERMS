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
        min-width: 300px;
    }

    .filter-group label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: 500;
        font-size: 14px;
    }

    .filter-group input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .filter-group input:focus {
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

        .btn-primary {
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
            min-width: 600px;
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
        <h1 class="page-title">Course Management</h1>
        <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'teacher'])): ?>
            <a href="index.php?action=courses&sub=create" class="btn-primary">+ Add New Course</a>
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

    <!-- Filter Form -->
    <form method="GET" action="index.php" class="filter-container">
        <input type="hidden" name="action" value="courses">
        
        <div class="filter-group">
            <label for="filter_search">Search Course Name</label>
            <input type="text" id="filter_search" name="filter_search" 
                   placeholder="Search by course name..."
                   value="<?php echo htmlspecialchars($_GET['filter_search'] ?? ''); ?>">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-filter">Filter</button>
            <a href="index.php?action=courses" class="btn-reset">Reset</a>
        </div>
    </form>

    <?php if (!empty($courses)): ?>
        <div style="margin-bottom: 15px; color: #666; font-size: 14px;">
            Showing <?php echo count($courses); ?> course(s)
        </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #999;">
                            No courses found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['cname']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($course['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'teacher'])): ?>
                                        <a href="index.php?action=courses&sub=edit&id=<?php echo $course['id']; ?>" class="btn-edit">Edit</a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                        <a href="index.php?action=courses&sub=delete&id=<?php echo $course['id']; ?>" 
                                           class="btn-delete" 
                                           data-delete="this course"
                                           data-item-type="course"
                                           data-item-id="<?php echo $course['id']; ?>">Delete</a>
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
