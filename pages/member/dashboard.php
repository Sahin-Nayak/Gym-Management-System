<?php
define('CURRENT_PAGE', 'dashboard');
require_once '../../includes/auth.php';
requireLogin();

$userId = $_SESSION['user_id'];
$member = $conn->query("SELECT m.*, t.first_name as t_fname, t.last_name as t_lname FROM members m LEFT JOIN trainers t ON m.assigned_trainer_id = t.id WHERE m.user_id = $userId")->fetch_assoc();

if (!$member) { echo '<p>Profile not set up. Contact admin.</p>'; exit; }

$memberId = $member['id'];

$membership = $conn->query("SELECT mm.*, mp.plan_name, mp.price FROM member_memberships mm JOIN membership_plans mp ON mm.plan_id = mp.id WHERE mm.member_id = $memberId ORDER BY mm.created_at DESC LIMIT 1")->fetch_assoc();

$daysLeft = $membership ? max(0, (int)((strtotime($membership['end_date']) - time()) / 86400)) : 0;

$recentAttendance = $conn->query("SELECT * FROM attendance WHERE member_id = $memberId ORDER BY date DESC LIMIT 7");
$attendanceCount = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE member_id = $memberId AND MONTH(date) = MONTH(CURDATE())")->fetch_assoc()['cnt'];

$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $userId AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
// Mark as read
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $userId");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">MY DASHBOARD</h2>
            </header>

            <div class="page-content">
                <div class="card mb-3">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?= strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)) ?>
                        </div>
                        <div class="profile-info">
                            <h2>Welcome, <?= htmlspecialchars($member['first_name']) ?>! 💪</h2>
                            <p class="meta">
                                <?php if ($membership): ?>
                                    Plan: <strong><?= $membership['plan_name'] ?></strong> |
                                    Expires: <strong class="<?= $daysLeft <= 7 ? 'text-danger' : 'text-success' ?>"><?= date('d M Y', strtotime($membership['end_date'])) ?></strong>
                                    (<?= $daysLeft ?> days left)
                                <?php else: ?>
                                    No active membership
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="stat-card green">
                        <div class="stat-info"><h4>Days Left</h4><div class="stat-number"><?= $daysLeft ?></div></div>
                        <div class="stat-icon">📅</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-info"><h4>Visits This Month</h4><div class="stat-number"><?= $attendanceCount ?></div></div>
                        <div class="stat-icon">🏃</div>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-info"><h4>My Trainer</h4><div class="stat-number" style="font-size:1.2rem;"><?= $member['t_fname'] ? htmlspecialchars($member['t_fname'] . ' ' . $member['t_lname']) : 'None' ?></div></div>
                        <div class="stat-icon">🏋️</div>
                    </div>
                </div>

                <?php if ($notifications->num_rows > 0): ?>
                <div class="card mb-3">
                    <div class="card-header"><h3>🔔 Notifications</h3></div>
                    <?php while ($n = $notifications->fetch_assoc()): ?>
                        <div class="alert alert-<?= $n['type'] === 'expiry_alert' ? 'warning' : ($n['type'] === 'payment_reminder' ? 'danger' : 'info') ?>">
                            <strong><?= htmlspecialchars($n['title']) ?></strong> — <?= htmlspecialchars($n['message']) ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header"><h3>Recent Attendance</h3></div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Duration</th></tr></thead>
                            <tbody>
                                <?php if ($recentAttendance->num_rows > 0): ?>
                                    <?php while ($a = $recentAttendance->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($a['date'])) ?></td>
                                            <td><?= date('h:i A', strtotime($a['check_in'])) ?></td>
                                            <td><?= $a['check_out'] ? date('h:i A', strtotime($a['check_out'])) : '<span class="text-warning">In gym</span>' ?></td>
                                            <td><?php if ($a['check_out']): $d = strtotime($a['check_out'])-strtotime($a['check_in']); echo floor($d/3600).'h '.floor(($d%3600)/60).'m'; else: echo '-'; endif; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">No attendance records yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
