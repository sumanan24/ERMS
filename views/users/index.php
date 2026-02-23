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

    .badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-admin {
        background: #fee;
        color: #c33;
    }

    .badge-teacher {
        background: #eef;
        color: #33c;
    }

    .badge-student {
        background: #efe;
        color: #3c3;
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

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            min-width: 800px;
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

        .badge {
            font-size: 10px;
            padding: 3px 8px;
        }
    }
</style>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">User Management</h1>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <a href="index.php?action=users&sub=create" class="btn-primary">+ Add New User</a>
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

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                            No users found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user['role']; ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                        <a href="index.php?action=users&sub=edit&id=<?php echo $user['id']; ?>" class="btn-edit">Edit</a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="index.php?action=users&sub=delete&id=<?php echo $user['id']; ?>" 
                                               class="btn-delete" 
                                               data-delete="this user">Delete</a>
                                        <?php endif; ?>
                                    <?php else: ?>
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

