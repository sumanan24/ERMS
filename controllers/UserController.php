<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function requireAdmin() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "Access denied. Admin privileges required.";
            header("Location: index.php?action=dashboard");
            exit();
        }
    }

    public function index() {
        $this->requireAdmin();
        
        $users = $this->user->getAllUsers();
        require_once __DIR__ . '/../views/users/index.php';
    }

    public function create() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->user->username = trim($_POST['username'] ?? '');
            $this->user->email = trim($_POST['email'] ?? '');
            $this->user->password = $_POST['password'] ?? '';
            $this->user->full_name = trim($_POST['full_name'] ?? '');
            $this->user->role = $_POST['role'] ?? 'student';

            // Validation
            if (empty($this->user->username) || empty($this->user->email) || 
                empty($this->user->password) || empty($this->user->full_name)) {
                $_SESSION['error'] = "All fields are required.";
                header("Location: index.php?action=users&sub=create");
                exit();
            }

            if ($this->user->usernameExists($this->user->username)) {
                $_SESSION['error'] = "Username already exists.";
                header("Location: index.php?action=users&sub=create");
                exit();
            }

            if ($this->user->emailExists($this->user->email)) {
                $_SESSION['error'] = "Email already exists.";
                header("Location: index.php?action=users&sub=create");
                exit();
            }

            if (strlen($this->user->password) < 6) {
                $_SESSION['error'] = "Password must be at least 6 characters.";
                header("Location: index.php?action=users&sub=create");
                exit();
            }

            if ($this->user->create()) {
                $_SESSION['success'] = "User created successfully.";
                header("Location: index.php?action=users");
                exit();
            } else {
                $_SESSION['error'] = "Failed to create user.";
                header("Location: index.php?action=users&sub=create");
                exit();
            }
        } else {
            require_once __DIR__ . '/../views/users/create.php';
        }
    }

    public function edit() {
        $this->requireAdmin();

        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->user->id = $id;
            $this->user->username = trim($_POST['username'] ?? '');
            $this->user->email = trim($_POST['email'] ?? '');
            $this->user->password = $_POST['password'] ?? '';
            $this->user->full_name = trim($_POST['full_name'] ?? '');
            $this->user->role = $_POST['role'] ?? 'student';

            // Validation
            if (empty($this->user->username) || empty($this->user->email) || 
                empty($this->user->full_name)) {
                $_SESSION['error'] = "All fields are required.";
                header("Location: index.php?action=users&sub=edit&id=" . $id);
                exit();
            }

            if ($this->user->usernameExists($this->user->username, $id)) {
                $_SESSION['error'] = "Username already exists.";
                header("Location: index.php?action=users&sub=edit&id=" . $id);
                exit();
            }

            if ($this->user->emailExists($this->user->email, $id)) {
                $_SESSION['error'] = "Email already exists.";
                header("Location: index.php?action=users&sub=edit&id=" . $id);
                exit();
            }

            if (!empty($this->user->password) && strlen($this->user->password) < 6) {
                $_SESSION['error'] = "Password must be at least 6 characters.";
                header("Location: index.php?action=users&sub=edit&id=" . $id);
                exit();
            }

            if ($this->user->update()) {
                $_SESSION['success'] = "User updated successfully.";
                header("Location: index.php?action=users");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update user.";
                header("Location: index.php?action=users&sub=edit&id=" . $id);
                exit();
            }
        } else {
            if ($this->user->getUserById($id)) {
                require_once __DIR__ . '/../views/users/edit.php';
            } else {
                $_SESSION['error'] = "User not found.";
                header("Location: index.php?action=users");
                exit();
            }
        }
    }

    public function delete() {
        $this->requireAdmin();

        $id = $_GET['id'] ?? 0;

        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account.";
            header("Location: index.php?action=users");
            exit();
        }

        if ($this->user->delete($id)) {
            $_SESSION['success'] = "User deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete user.";
        }

        header("Location: index.php?action=users");
        exit();
    }
}
?>

