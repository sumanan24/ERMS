<?php
$current_action = $_GET['action'] ?? 'dashboard';
$user_role = $_SESSION['role'] ?? 'student';
$user_name = $_SESSION['full_name'] ?? 'User';
?>
<?php require_once __DIR__ . '/bootstrap.php'; ?>

<style>
    /* Global Full Screen Scrolling */
    * {
        box-sizing: border-box;
    }

    :root {
        --sidebar-bg: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
        --sidebar-width: 280px;
        --sidebar-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        --menu-item-hover: rgba(255, 255, 255, 0.15);
        --menu-item-active: rgba(255, 255, 255, 0.25);
        --text-primary: rgba(255, 255, 255, 0.95);
        --text-secondary: rgba(255, 255, 255, 0.7);
    }

    /* Ensure body and html don't interfere with sidebar */
    html,
    body {
        height: 100%;
        height: 100vh;
        height: 100dvh;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .sidebar {
        width: var(--sidebar-width);
        background: var(--sidebar-bg);
        background-size: 200% 200%;
        animation: gradientShift 15s ease infinite;
        height: 100vh;
        height: 100dvh;
        /* Dynamic viewport height for mobile browsers */
        max-height: 100vh;
        max-height: 100dvh;
        min-height: 100vh;
        min-height: 100dvh;
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        right: auto;
        box-shadow: var(--sidebar-shadow);
        z-index: 1000;
        backdrop-filter: blur(10px);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    @keyframes gradientShift {

        0%,
        100% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }
    }

    .sidebar-header {
        padding: 30px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        flex-shrink: 0;
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .sidebar-logo-icon {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header h2 {
        color: var(--text-primary);
        font-size: 22px;
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .sidebar-content {
        flex: 1 1 0;
        overflow-y: auto;
        overflow-x: hidden;
        padding-bottom: 50px;
        padding-top: 10px;
        -webkit-overflow-scrolling: touch;
        /* Smooth scrolling on iOS */
        min-height: 0;
        /* Important for flexbox scrolling */
        height: 100%;
        /* 100% height for full screen scrolling */
        max-height: 100%;
        /* Full height for scrolling */
    }

    .user-profile-section {
        padding: 20px;
        margin: 20px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin: 0 auto 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        border: 3px solid rgba(255, 255, 255, 0.3);
    }

    .user-name {
        color: var(--text-primary);
        font-size: 15px;
        font-weight: 600;
        text-align: center;
        margin-bottom: 5px;
    }

    .user-role-badge {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-primary);
        margin: 0 auto;
        display: block;
        width: fit-content;
    }

    .sidebar-menu {
        list-style: none;
        padding: 10px 0 50px 0;
        margin: 0;
        min-height: fit-content;
    }

    .sidebar-menu li {
        margin: 0;
        padding: 0 15px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 14px 18px;
        color: var(--text-secondary);
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
        margin: 4px 0;
        position: relative;
        font-size: 14px;
        font-weight: 500;
    }

    .sidebar-menu a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 0;
        background: white;
        border-radius: 0 4px 4px 0;
        transition: height 0.3s ease;
    }

    .sidebar-menu a:hover {
        background: var(--menu-item-hover);
        color: var(--text-primary);
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .sidebar-menu a:hover::before {
        height: 60%;
    }

    .sidebar-menu a.active {
        background: var(--menu-item-active);
        color: var(--text-primary);
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .sidebar-menu a.active::before {
        height: 70%;
        background: white;
    }

    .sidebar-menu-icon {
        margin-right: 14px;
        font-size: 20px;
        width: 24px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .menu-section {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .menu-section-title {
        padding: 8px 20px 12px;
        color: var(--text-secondary);
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1.5px;
        opacity: 0.8;
    }

    /* Bootstrap Offcanvas Custom Styles */
    .offcanvas {
        background: var(--sidebar-bg);
        background-size: 200% 200%;
        animation: gradientShift 15s ease infinite;
        width: var(--sidebar-width) !important;
        height: 100vh !important;
        height: 100dvh !important;
        /* Dynamic viewport height for mobile */
        max-height: 100vh !important;
        max-height: 100dvh !important;
        min-height: 100vh !important;
        min-height: 100dvh !important;
        backdrop-filter: blur(10px);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        flex-direction: column;
    }

    .offcanvas-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        padding: 25px 20px;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
    }

    .offcanvas-title {
        color: var(--text-primary);
        font-size: 22px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .offcanvas-logo-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .btn-close {
        filter: invert(1);
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .btn-close:hover {
        opacity: 1;
        transform: rotate(90deg);
    }

    /* Mobile Toggle Button */
    .mobile-menu-toggle {
        display: none;
    }

    @media (max-width: 991.98px) {
        .sidebar {
            display: none;
        }

        .mobile-menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1045;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            font-size: 20px;
        }

        .mobile-menu-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .mobile-menu-toggle:active {
            transform: scale(0.95);
        }
    }

    @media (max-width: 480px) {
        .offcanvas {
            width: 260px !important;
        }
    }

    /* Scrollbar Styling */
    .sidebar-content::-webkit-scrollbar,
    .offcanvas-body::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-content::-webkit-scrollbar-track,
    .offcanvas-body::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }

    .sidebar-content::-webkit-scrollbar-thumb,
    .offcanvas-body::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.25);
        border-radius: 10px;
        transition: background 0.3s ease;
    }

    .sidebar-content::-webkit-scrollbar-thumb:hover,
    .offcanvas-body::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.4);
    }

    /* Firefox Scrollbar */
    .sidebar-content,
    .offcanvas-body {
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.25) rgba(255, 255, 255, 0.05);
    }

    /* Ensure offcanvas body scrolls */
    #sidebarOffcanvas .offcanvas-body {
        flex: 1 1 0;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        min-height: 0;
        height: 100%;
        /* 100% height for full screen scrolling */
        max-height: 100%;
        /* Full height for scrolling */
        padding-bottom: 50px;
        scrollbar-gutter: stable;
    }

    #sidebarOffcanvas .offcanvas-header {
        flex-shrink: 0;
    }

    #sidebarOffcanvas .offcanvas-body .sidebar-menu {
        padding-bottom: 50px;
    }

    /* Ensure last menu item is accessible */
    .sidebar-menu .menu-section:last-child {
        margin-bottom: 30px;
        padding-bottom: 20px;
    }

    .sidebar-menu li:last-child {
        margin-bottom: 15px;
    }

    /* Ensure all content is scrollable */
    .sidebar-content>* {
        flex-shrink: 0;
    }

    /* Force scrollbar to always be visible when content overflows */
    .sidebar-content {
        scrollbar-gutter: stable;
    }
</style>

<!-- Mobile Menu Toggle Button -->
<button class="btn mobile-menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
    <i class="bi bi-list"></i>
</button>

<!-- Desktop Sidebar -->
<div class="sidebar d-none d-lg-block">


    <!-- Scrollable Content Area -->
    <div class="sidebar-content">
        <!-- User Profile Section -->
        <div class="user-profile-section">
            <div class="user-avatar">
                <i class="bi bi-person-fill" style="color: #667eea;"></i>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <span class="user-role-badge"><?php echo ucfirst(htmlspecialchars($user_role)); ?></span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="index.php?action=dashboard" class="<?php echo $current_action == 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-house-door-fill sidebar-menu-icon"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($user_role == 'admin'): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Administration</div>
                    <li>
                        <a href="index.php?action=users" class="<?php echo $current_action == 'users' ? 'active' : ''; ?>">
                            <i class="bi bi-people-fill sidebar-menu-icon"></i>
                            <span>Manage Users</span>
                        </a>
                    </li>

                </div>
            <?php endif; ?>

            <?php if (in_array($user_role, ['admin', 'teacher'])): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Management</div>

                    <li>
                        <a href="index.php?action=courses" class="<?php echo $current_action == 'courses' ? 'active' : ''; ?>">
                            <i class="bi bi-journal-bookmark-fill sidebar-menu-icon"></i>
                            <span>Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=versions" class="<?php echo $current_action == 'versions' ? 'active' : ''; ?>">
                            <i class="bi bi-tags-fill sidebar-menu-icon"></i>
                            <span>Course Versions</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=modules" class="<?php echo $current_action == 'modules' ? 'active' : ''; ?>">
                            <i class="bi bi-grid-3x3-gap-fill sidebar-menu-icon"></i>
                            <span>Modules</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=batch" class="<?php echo $current_action == 'batch' ? 'active' : ''; ?>">
                            <i class="bi bi-collection-fill sidebar-menu-icon"></i>
                            <span>Batches</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=students" class="<?php echo $current_action == 'students' ? 'active' : ''; ?>">
                            <i class="bi bi-mortarboard-fill sidebar-menu-icon"></i>
                            <span>Students</span>
                        </a>
                    </li>

                </div>
            <?php endif; ?>

            <?php if (in_array($user_role, ['admin', 'teacher'])): ?>
                <div class="menu-section">

                    <div class="menu-section-title">Examination</div>
                    <li>
                        <a href="index.php?action=exams" class="<?php echo $current_action == 'exams' ? 'active' : ''; ?>">
                            <i class="bi bi-calendar-check-fill sidebar-menu-icon"></i>
                            <span>Exam Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=exam_results&sub=admissionCards" class="<?php echo ($current_action == 'exam_results' && isset($_GET['sub']) && $_GET['sub'] == 'admissionCards') ? 'active' : ''; ?>">
                            <i class="bi bi-card-list sidebar-menu-icon"></i>
                            <span>Admission Cards</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="index.php?action=exam_results&sub=marksSummary" class="<?php echo ($current_action == 'exam_results' && isset($_GET['sub']) && $_GET['sub'] == 'marksSummary') ? 'active' : ''; ?>">
                            <i class="bi bi-table sidebar-menu-icon"></i>
                            <span>Marks Summary (Excel)</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=exam_results&sub=printTranscripts" class="<?php echo ($current_action == 'exam_results' && isset($_GET['sub']) && $_GET['sub'] == 'printTranscripts') ? 'active' : ''; ?>">
                            <i class="bi bi-file-earmark-text-fill sidebar-menu-icon"></i>
                            <span>Print Transcripts</span>
                        </a>
                    </li>
                </div>
            <?php endif; ?>

            <div class="menu-section">
                <div class="menu-section-title">Account</div>
                <li>
                    <a href="index.php?action=profile" class="<?php echo $current_action == 'profile' ? 'active' : ''; ?>">
                        <i class="bi bi-person-circle sidebar-menu-icon"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?action=logout">
                        <i class="bi bi-box-arrow-right sidebar-menu-icon"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </div>
        </ul>
    </div>
</div>

<!-- Mobile Offcanvas Sidebar (Bootstrap) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">
            <div class="offcanvas-logo-icon">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            Exam System
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- User Profile Section -->
        <div class="user-profile-section">
            <div class="user-avatar">
                <i class="bi bi-person-fill" style="color: #667eea;"></i>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <span class="user-role-badge"><?php echo ucfirst(htmlspecialchars($user_role)); ?></span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="index.php?action=dashboard" class="<?php echo $current_action == 'dashboard' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                    <i class="bi bi-house-door-fill sidebar-menu-icon"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($user_role == 'admin'): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Administration</div>
                    <li>
                        <a href="index.php?action=users" class="<?php echo $current_action == 'users' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-people-fill sidebar-menu-icon"></i>
                            <span>Manage Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=users&sub=create" class="<?php echo ($current_action == 'users' && isset($_GET['sub']) && $_GET['sub'] == 'create') ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-person-plus-fill sidebar-menu-icon"></i>
                            <span>Add New User</span>
                        </a>
                    </li>
                </div>
            <?php endif; ?>

            <?php if (in_array($user_role, ['admin', 'teacher'])): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Management</div>
                    <li>
                        <a href="index.php?action=courses" class="<?php echo $current_action == 'courses' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-journal-bookmark-fill sidebar-menu-icon"></i>
                            <span>Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=versions" class="<?php echo $current_action == 'versions' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-tags-fill sidebar-menu-icon"></i>
                            <span>Course Versions</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=modules" class="<?php echo $current_action == 'modules' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-grid-3x3-gap-fill sidebar-menu-icon"></i>
                            <span>Modules</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=batch" class="<?php echo $current_action == 'batch' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-collection-fill sidebar-menu-icon"></i>
                            <span>Batches</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=students" class="<?php echo $current_action == 'students' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-mortarboard-fill sidebar-menu-icon"></i>
                            <span>Students</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=exams" class="<?php echo $current_action == 'exams' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-calendar-check-fill sidebar-menu-icon"></i>
                            <span>Exam Schedule</span>
                        </a>
                    </li>
                </div>
            <?php endif; ?>

            <?php if (in_array($user_role, ['admin', 'teacher'])): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Exam Results</div>
                    <li>
                        <a href="index.php?action=exam_results" class="<?php echo $current_action == 'exam_results' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-clipboard-data-fill sidebar-menu-icon"></i>
                            <span>Exam Results</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=exam_results&sub=printTranscripts" class="<?php echo ($current_action == 'exam_results' && isset($_GET['sub']) && $_GET['sub'] == 'printTranscripts') ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-file-earmark-text-fill sidebar-menu-icon"></i>
                            <span>Print Transcripts</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=exam_results&sub=admissionCards" class="<?php echo ($current_action == 'exam_results' && isset($_GET['sub']) && $_GET['sub'] == 'admissionCards') ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-card-list sidebar-menu-icon"></i>
                            <span>Admission Cards</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=exam_results&sub=marksSummary" class="<?php echo ($current_action == 'exam_results' && isset($_GET['sub']) && $_GET['sub'] == 'marksSummary') ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                            <i class="bi bi-table sidebar-menu-icon"></i>
                            <span>Marks Summary (Excel)</span>
                        </a>
                    </li>
                </div>
            <?php endif; ?>

            <div class="menu-section">
                <div class="menu-section-title">Account</div>
                <li>
                    <a href="index.php?action=profile" class="<?php echo $current_action == 'profile' ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                        <i class="bi bi-person-circle sidebar-menu-icon"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?action=logout" data-bs-dismiss="offcanvas">
                        <i class="bi bi-box-arrow-right sidebar-menu-icon"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </div>
        </ul>
    </div>
</div>