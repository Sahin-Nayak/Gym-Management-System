<?php
if (!defined('CURRENT_PAGE')) define('CURRENT_PAGE', '');
$basePath = '';
$scriptPath = $_SERVER['SCRIPT_FILENAME'];
if (strpos($scriptPath, '/pages/admin/') !== false || strpos($scriptPath, '/pages/trainer/') !== false || strpos($scriptPath, '/pages/member/') !== false) {
    $basePath = '../../';
} elseif (strpos($scriptPath, '/pages/') !== false) {
    $basePath = '../';
}

// Unread enquiries count for badge
$_unreadEnquiries = 0;
if (isAdmin()) {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'gym_enquiries'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $r = $conn->query("SELECT COUNT(*) as c FROM gym_enquiries WHERE status='unread'");
        if ($r) $_unreadEnquiries = (int)$r->fetch_assoc()['c'];
    }
}
?>
<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <div class="sidebar-logo">💪</div>
        <div class="sidebar-brand-text">
            <h1>FITZONE</h1>
            <span>Gym Management</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if (isAdmin()): ?>

        <div class="sidebar-nav-label">Main</div>
        <a href="<?= $basePath ?>pages/admin/dashboard.php" class="<?= CURRENT_PAGE === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>

        <div class="sidebar-nav-label">Management</div>
        <a href="<?= $basePath ?>pages/admin/members.php" class="<?= CURRENT_PAGE === 'members' ? 'active' : '' ?>">
            <span class="nav-icon">👤</span> Members
        </a>
        <a href="<?= $basePath ?>pages/admin/trainers.php" class="<?= CURRENT_PAGE === 'trainers' ? 'active' : '' ?>">
            <span class="nav-icon">🏋️</span> Trainers
        </a>
        <a href="<?= $basePath ?>pages/admin/plans.php" class="<?= CURRENT_PAGE === 'plans' ? 'active' : '' ?>">
            <span class="nav-icon">💳</span> Plans
        </a>
        <a href="<?= $basePath ?>pages/admin/payments.php" class="<?= CURRENT_PAGE === 'payments' ? 'active' : '' ?>">
            <span class="nav-icon">💰</span> Payments
        </a>
        <a href="<?= $basePath ?>pages/admin/inventory.php" class="<?= CURRENT_PAGE === 'inventory' ? 'active' : '' ?>">
            <span class="nav-icon">🏋️</span> Inventory
        </a>

        <div class="sidebar-nav-label">Operations</div>
        <a href="<?= $basePath ?>pages/admin/attendance.php" class="<?= CURRENT_PAGE === 'attendance' ? 'active' : '' ?>">
            <span class="nav-icon">📅</span> Attendance
        </a>
        <a href="<?= $basePath ?>pages/admin/classes.php" class="<?= CURRENT_PAGE === 'classes' ? 'active' : '' ?>">
            <span class="nav-icon">📆</span> Classes
        </a>
        <a href="<?= $basePath ?>pages/admin/reports.php" class="<?= CURRENT_PAGE === 'reports' ? 'active' : '' ?>">
            <span class="nav-icon">🧾</span> Reports
        </a>
        <a href="<?= $basePath ?>pages/admin/notifications.php" class="<?= CURRENT_PAGE === 'notifications' ? 'active' : '' ?>">
            <span class="nav-icon">🔔</span> Notifications
        </a>

        <div class="sidebar-nav-label">Website</div>
        <a href="<?= $basePath ?>pages/admin/website-content.php" class="<?= CURRENT_PAGE === 'website-content' ? 'active' : '' ?>">
            <span class="nav-icon">🌐</span> Website Content
        </a>
        <a href="<?= $basePath ?>pages/admin/blogs.php" class="<?= CURRENT_PAGE === 'blogs' ? 'active' : '' ?>">
            <span class="nav-icon">📝</span> Blog Manager
        </a>
        <a href="<?= $basePath ?>pages/admin/athlete-facts.php" class="<?= CURRENT_PAGE === 'athlete-facts' ? 'active' : '' ?>">
            <span class="nav-icon">⚡</span> Athlete Facts
        </a>
        <a href="<?= $basePath ?>pages/admin/gym-videos.php" class="<?= CURRENT_PAGE === 'gym-videos' ? 'active' : '' ?>">
            <span class="nav-icon">🎥</span> Gym Videos
        </a>
        <a href="<?= $basePath ?>pages/admin/gym-gallery.php" class="<?= CURRENT_PAGE === 'gym-gallery' ? 'active' : '' ?>">
            <span class="nav-icon">🖼️</span> Gym Gallery
        </a>
        <a href="<?= $basePath ?>pages/admin/enquiries.php" class="<?= CURRENT_PAGE === 'enquiries' ? 'active' : '' ?>">
            <span class="nav-icon">✉️</span> Enquiries
            <?php if ($_unreadEnquiries > 0): ?>
            <span style="margin-left:auto;background:var(--primary);color:#fff;font-size:0.68rem;font-weight:700;padding:2px 7px;border-radius:50px;min-width:20px;text-align:center;"><?= $_unreadEnquiries ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= $basePath ?>home.php" target="_blank">
            <span class="nav-icon">↗</span> View Website
        </a>

        <?php elseif (isTrainer()): ?>

        <div class="sidebar-nav-label">Trainer Panel</div>
        <a href="<?= $basePath ?>pages/trainer/dashboard.php" class="<?= CURRENT_PAGE === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="<?= $basePath ?>pages/trainer/my-members.php" class="<?= CURRENT_PAGE === 'my-members' ? 'active' : '' ?>">
            <span class="nav-icon">👤</span> My Members
        </a>
        <a href="<?= $basePath ?>pages/trainer/classes.php" class="<?= CURRENT_PAGE === 'classes' ? 'active' : '' ?>">
            <span class="nav-icon">📆</span> My Classes
        </a>

        <?php elseif (isMember()): ?>

        <div class="sidebar-nav-label">Member Panel</div>
        <a href="<?= $basePath ?>pages/member/dashboard.php" class="<?= CURRENT_PAGE === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="<?= $basePath ?>pages/member/profile.php" class="<?= CURRENT_PAGE === 'profile' ? 'active' : '' ?>">
            <span class="nav-icon">👤</span> My Profile
        </a>
        <a href="<?= $basePath ?>pages/member/classes.php" class="<?= CURRENT_PAGE === 'classes' ? 'active' : '' ?>">
            <span class="nav-icon">📆</span> Classes
        </a>
        <a href="<?= $basePath ?>pages/member/payments.php" class="<?= CURRENT_PAGE === 'payments' ? 'active' : '' ?>">
            <span class="nav-icon">💰</span> Payments
        </a>

        <?php endif; ?>

        <div class="sidebar-nav-label">Account</div>
        <a href="<?= $basePath ?>pages/change-password.php" class="<?= CURRENT_PAGE === 'change-password' ? 'active' : '' ?>">
            <span class="nav-icon">🔒</span> Change Password
        </a>
        <a href="<?= $basePath ?>logout.php">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="sidebar-user-info">
                <div class="name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                <div class="role"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></div>
            </div>
        </div>
        <div class="sidebar-theme-row">
            <span class="sidebar-theme-label">
                <?php $t = $_COOKIE['fitzone_theme'] ?? 'dark'; echo $t === 'dark' ? '🌙 Dark Mode' : '☀️ Light Mode'; ?>
            </span>
            <button class="theme-toggle" onclick="toggleTheme()" title="Toggle dark / light mode" aria-label="Toggle theme"></button>
        </div>
    </div>

</aside>

<script>
// Update label text after toggle
(function() {
    var origToggle = window.toggleTheme;
    window.toggleTheme = function() {
        origToggle && origToggle();
        var label = document.querySelector('.sidebar-theme-label');
        if (label) {
            var next = document.documentElement.getAttribute('data-theme') || 'dark';
            label.textContent = next === 'dark' ? '🌙 Dark Mode' : '☀️ Light Mode';
        }
    };
})();
</script>
