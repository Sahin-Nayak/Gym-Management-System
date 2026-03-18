<?php
define('CURRENT_PAGE', 'change-password');
require_once '../includes/auth.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = changePassword($_SESSION['user_id'], $_POST['current_password'], $_POST['new_password']);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - FitZone</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">CHANGE PASSWORD</h2>
            </header>

            <div class="page-content">
                <div class="card" style="max-width:500px;">
                    <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" id="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="return validatePasswords()">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        function validatePasswords() {
            const np = document.querySelector('[name="new_password"]').value;
            const cp = document.getElementById('confirm_password').value;
            if (np !== cp) { alert('Passwords do not match!'); return false; }
            return true;
        }
    </script>
</body>
</html>
