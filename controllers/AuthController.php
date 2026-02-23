<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $_SESSION['error'] = "Please fill in all fields";
                header("Location: index.php?action=login");
                exit();
            }

            if ($this->user->login($username, $password)) {
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                $_SESSION['full_name'] = $this->user->full_name;
                $_SESSION['role'] = $this->user->role;

                header("Location: index.php?action=dashboard");
                exit();
            } else {
                $_SESSION['error'] = "Invalid username or password";
                header("Location: index.php?action=login");
                exit();
            }
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: index.php?action=login");
            exit();
        }
    }

    /**
     * My Profile page: show user info and update password form.
     */
    public function profile() {
        $this->requireLogin();
        $user_id = $_SESSION['user_id'] ?? 0;
        if (!$user_id || !$this->user->getUserById($user_id)) {
            $_SESSION['error'] = "User not found.";
            header("Location: index.php?action=dashboard");
            exit();
        }
        $user = $this->user;
        require_once __DIR__ . '/../views/profile.php';
    }

    /**
     * Update password (from My Profile). Requires current password.
     */
    public function updatePassword() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=profile");
            exit();
        }
        $user_id = $_SESSION['user_id'] ?? 0;
        $current = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (empty($current) || empty($new_pass) || empty($confirm)) {
            $_SESSION['error'] = "Please fill in all password fields.";
            header("Location: index.php?action=profile");
            exit();
        }
        if (strlen($new_pass) < 6) {
            $_SESSION['error'] = "New password must be at least 6 characters.";
            header("Location: index.php?action=profile");
            exit();
        }
        if ($new_pass !== $confirm) {
            $_SESSION['error'] = "New password and confirmation do not match.";
            header("Location: index.php?action=profile");
            exit();
        }
        if (!$this->user->verifyPassword($user_id, $current)) {
            $_SESSION['error'] = "Current password is incorrect.";
            header("Location: index.php?action=profile");
            exit();
        }
        if ($this->user->updatePasswordOnly($user_id, $new_pass)) {
            $_SESSION['success'] = "Password updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update password.";
        }
        header("Location: index.php?action=profile");
        exit();
    }
}
?>

