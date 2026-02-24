<?php
// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

try {
    require_once __DIR__ . '/config/install.php';

    $install = new Install();
    $message = '';
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['install'])) {
        if ($install->install()) {
            $success = true;
            $message = "Installation completed successfully! You can now login with username: admin and password: admin123";
        } else {
            $message = "Installation failed. Please check your database configuration and ensure MySQL is running.";
            if (!empty($install->lastError)) {
                $message .= " Error: " . htmlspecialchars($install->lastError);
            }
        }
    }
} catch (Exception $e) {
    error_log("Installation Error: " . $e->getMessage());
    $message = "An error occurred during installation. Please check the error logs.";
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Exam Result Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .install-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 600px;
        }

        .install-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .install-header h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .install-header p {
            color: #666;
            font-size: 14px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .info-box ul {
            margin-left: 20px;
            color: #666;
        }

        .info-box li {
            margin-bottom: 5px;
        }

        .btn-install {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-install:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .credentials-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .credentials-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .credentials-box p {
            color: #856404;
            margin: 5px 0;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .install-container {
                padding: 30px 20px;
                max-width: 100%;
                border-radius: 10px;
            }

            .install-header h1 {
                font-size: 24px;
            }

            .install-header p {
                font-size: 13px;
            }

            .info-box {
                padding: 12px;
            }

            .info-box h3 {
                font-size: 14px;
            }

            .info-box ul {
                margin-left: 15px;
                font-size: 13px;
            }

            .btn-install {
                padding: 14px;
                font-size: 16px;
            }

            .alert {
                padding: 12px;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .install-container {
                padding: 25px 15px;
            }

            .install-header h1 {
                font-size: 20px;
            }

            .credentials-box {
                padding: 12px;
            }

            .credentials-box h4 {
                font-size: 14px;
            }

            .credentials-box p {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>🔧 System Installation</h1>
            <p>First time setup for Exam Result Management System</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="credentials-box">
                <h4>Default Admin Credentials:</h4>
                <p><strong>Username:</strong> admin</p>
                <p><strong>Password:</strong> admin123</p>
                <p style="margin-top: 10px; font-size: 12px;">⚠️ Please change the default password after first login!</p>
            </div>
            <div class="back-link">
                <a href="index.php?action=login">Go to Login Page</a>
            </div>
        <?php else: ?>
            <div class="info-box">
                <h3>What will be installed:</h3>
                <ul>
                    <li>Database: exam_management</li>
                    <li>Users table (for authentication)</li>
                    <li>Students table (student information)</li>
                    <li>Subjects table (subject details)</li>
                    <li>Exams table (exam information)</li>
                    <li>Exam Results table (result records)</li>
                    <li>Default admin account</li>
                </ul>
            </div>

            <div class="alert alert-info">
                <strong>Note:</strong> Make sure your MySQL server is running and the database user has proper permissions.
            </div>

            <form method="POST">
                <button type="submit" name="install" class="btn-install">Install Now</button>
            </form>

            <div class="back-link">
                <a href="index.php?action=login">Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

