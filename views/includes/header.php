<style>
    .main-header {
        background: white;
        padding: 15px 30px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        position: fixed;
        top: 0;
        left: 280px;
        right: 0;
        z-index: 999;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title {
        font-size: 20px;
        color: #333;
        font-weight: 600;
    }

    .header-user {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-info {
        text-align: right;
    }

    .user-name {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .user-role {
        font-size: 12px;
        color: #666;
    }

    .btn-logout {
        padding: 8px 15px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-size: 13px;
        transition: background 0.3s ease;
    }

    .btn-logout:hover {
        background: #5568d3;
    }

    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        .main-header {
            left: 0;
            padding: 12px 15px;
            padding-left: 60px; /* Space for menu toggle */
            z-index: 1040; /* Below Bootstrap offcanvas */
        }

        .header-title {
            font-size: 16px;
        }

        .header-user {
            gap: 10px;
        }

        .user-info {
            display: none; /* Hide user info on small screens */
        }

        .btn-logout {
            padding: 6px 12px;
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .main-header {
            padding: 10px 12px;
            padding-left: 55px;
        }

        .header-title {
            font-size: 14px;
        }

        .btn-logout {
            padding: 5px 10px;
            font-size: 11px;
        }
    }
</style>

<div class="main-header">
    <div class="header-title">
        <?php
        $page_titles = [
            'dashboard' => 'Dashboard',
            'users' => 'User Management',
            'students' => 'Student Management',
            'subjects' => 'Subject Management',
            'exams' => 'Exam Schedule Management',
            'exam_results' => 'Exam Results Management',
            'courses' => 'Course Management',
            'versions' => 'Version Management',
            'batch' => 'Batch Management',
            'modules' => 'Module Management',
            'results' => 'Exam Results',
            'profile' => 'My Profile'
        ];
        $current_action = $_GET['action'] ?? 'dashboard';
        echo $page_titles[$current_action] ?? 'Dashboard';
        ?>
    </div>
    <div class="header-user">
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></div>
            <div class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['role'] ?? 'student')); ?></div>
        </div>
        <a href="index.php?action=logout" class="btn-logout">Logout</a>
    </div>
</div>

