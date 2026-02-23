<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<style>
    .main-content { margin-left: 280px; margin-top: 70px; padding: 30px; background: white; min-height: calc(100vh - 70px); overflow: auto; }
    .profile-card { max-width: 560px; background: #f8f9fa; padding: 24px; border-radius: 12px; margin-bottom: 24px; border: 1px solid #e9ecef; }
    .profile-card h2 { margin: 0 0 16px 0; font-size: 18px; color: #333; }
    .profile-row { display: flex; margin-bottom: 12px; }
    .profile-label { width: 140px; font-weight: 600; color: #555; }
    .profile-value { color: #333; }
    .password-form { max-width: 400px; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #333; }
    .form-group input { width: 100%; padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; }
    .form-group input:focus { border-color: #667eea; outline: none; }
    .btn-update { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .btn-update:hover { background: #5568d3; }
</style>

<div class="main-content">
    <h1 style="margin-bottom: 20px;">My Profile</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="profile-card">
        <h2>Account details</h2>
        <div class="profile-row">
            <span class="profile-label">Username</span>
            <span class="profile-value"><?php echo htmlspecialchars($user->username ?? ''); ?></span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Email</span>
            <span class="profile-value"><?php echo htmlspecialchars($user->email ?? ''); ?></span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Full name</span>
            <span class="profile-value"><?php echo htmlspecialchars($user->full_name ?? ''); ?></span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Role</span>
            <span class="profile-value"><?php echo htmlspecialchars(ucfirst($user->role ?? '')); ?></span>
        </div>
    </div>

    <div class="profile-card">
        <h2>Update password</h2>
        <form method="POST" action="index.php?action=profile&sub=updatePassword" class="password-form">
            <div class="form-group">
                <label for="current_password">Current password *</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password" placeholder="Enter current password">
            </div>
            <div class="form-group">
                <label for="new_password">New password *</label>
                <input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password" placeholder="At least 6 characters">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm new password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password" placeholder="Re-enter new password">
            </div>
            <button type="submit" class="btn-update">Update password</button>
        </form>
    </div>
</div>
