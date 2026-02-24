<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;

    /** Full URL to index.php for redirects (works on server). */
    private static function url($action, $extra = '') {
        $base = defined('BASE_URL') ? BASE_URL : '';
        if ($base === '') {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $dir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
            $dir = ($dir === '/' || $dir === '\\') ? '' : $dir;
            $base = $protocol . '://' . $host . rtrim(str_replace('\\', '/', $dir), '/');
        }
        return $base . '/index.php?action=' . $action . ($extra ? '&' . ltrim($extra, '&') : '');
    }

    /** Redirect and exit; cleans output buffer so header works on server. */
    private static function redirect($action, $extra = '') {
        if (ob_get_level()) ob_end_clean();
        header("Location: " . self::url($action, $extra));
        exit();
    }

    public function __construct() {
        $this->user = new User();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $_SESSION['error'] = "Please fill in all fields";
                self::redirect('login');
            }

            if ($this->user->login($username, $password)) {
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                $_SESSION['full_name'] = $this->user->full_name;
                $_SESSION['role'] = $this->user->role;

                self::redirect('dashboard');
            } else {
                $_SESSION['error'] = "Invalid username or password";
                self::redirect('login');
            }
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        self::redirect('login');
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            self::redirect('login');
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
            self::redirect('dashboard');
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
            self::redirect('profile');
        }
        $user_id = $_SESSION['user_id'] ?? 0;
        $current = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (empty($current) || empty($new_pass) || empty($confirm)) {
            $_SESSION['error'] = "Please fill in all password fields.";
            self::redirect('profile');
        }
        if (strlen($new_pass) < 6) {
            $_SESSION['error'] = "New password must be at least 6 characters.";
            self::redirect('profile');
        }
        if ($new_pass !== $confirm) {
            $_SESSION['error'] = "New password and confirmation do not match.";
            self::redirect('profile');
        }
        if (!$this->user->verifyPassword($user_id, $current)) {
            $_SESSION['error'] = "Current password is incorrect.";
            self::redirect('profile');
        }
        if ($this->user->updatePasswordOnly($user_id, $new_pass)) {
            $_SESSION['success'] = "Password updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update password.";
        }
        self::redirect('profile');
    }
}
?>

