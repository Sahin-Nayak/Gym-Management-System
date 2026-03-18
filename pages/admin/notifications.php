<?php
define('CURRENT_PAGE', 'notifications');
require_once '../../includes/auth.php';
requireAdmin();

$success = '';

// Auto-generate expiry notifications
if (isset($_GET['generate'])) {
    // Expiring in 3 days
    $expiring3 = $conn->query("
        SELECT m.id as member_id, m.first_name, m.last_name, mm.end_date, u.id as user_id
        FROM member_memberships mm
        JOIN members m ON mm.member_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE mm.end_date = DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND mm.status = 'active'
    ");
    while ($r = $expiring3->fetch_assoc()) {
        $title = "Membership Expiring Soon!";
        $msg = "Hi {$r['first_name']}, your membership expires on " . date('d M Y', strtotime($r['end_date'])) . ". Please renew to continue.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'expiry_alert')");
        $stmt->bind_param("iss", $r['user_id'], $title, $msg);
        $stmt->execute();
    }

    // Expiring in 7 days
    $expiring7 = $conn->query("
        SELECT m.id as member_id, m.first_name, m.last_name, mm.end_date, u.id as user_id
        FROM member_memberships mm
        JOIN members m ON mm.member_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE mm.end_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND mm.status = 'active'
    ");
    while ($r = $expiring7->fetch_assoc()) {
        $title = "Membership Renewal Reminder";
        $msg = "Hi {$r['first_name']}, your membership will expire on " . date('d M Y', strtotime($r['end_date'])) . ". Renew now to avoid interruption.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'expiry_alert')");
        $stmt->bind_param("iss", $r['user_id'], $title, $msg);
        $stmt->execute();
    }

    $success = "Notifications generated for expiring memberships!";
}

// Send custom notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_notification') {
    $userId = (int)$_POST['user_id'];
    $title = sanitize($_POST['title']);
    $message = sanitize($_POST['message']);
    $type = sanitize($_POST['type']);

    if ($userId === 0) {
        // Send to all active members
        $allUsers = $conn->query("SELECT u.id FROM users u JOIN members m ON u.id = m.user_id WHERE m.status = 'active'");
        while ($u = $allUsers->fetch_assoc()) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $u['id'], $title, $message, $type);
            $stmt->execute();
        }
        $success = "Notification sent to all active members!";
    } else {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $userId, $title, $message, $type);
        $success = $stmt->execute() ? "Notification sent!" : "Failed.";
    }
}

$notifications = $conn->query("
    SELECT n.*, u.username
    FROM notifications n
    JOIN users u ON n.user_id = u.id
    ORDER BY n.created_at DESC LIMIT 50
");

$allMembers = $conn->query("SELECT u.id, m.first_name, m.last_name FROM users u JOIN members m ON u.id = m.user_id ORDER BY m.first_name");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">NOTIFICATIONS</h2>
                <div class="top-header-actions">
                    <a href="?generate=1" class="btn btn-warning btn-sm">🔔 Generate Expiry Alerts</a>
                    <button class="btn btn-primary btn-sm" onclick="openModal('sendNotifModal')">+ Send Notification</button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

                <div class="card">
                    <div class="card-header"><h3>Recent Notifications</h3></div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr><th>Date</th><th>User</th><th>Title</th><th>Message</th><th>Type</th><th>Read</th></tr>
                            </thead>
                            <tbody>
                                <?php while ($n = $notifications->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('d M Y h:i A', strtotime($n['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($n['username']) ?></td>
                                        <td><strong><?= htmlspecialchars($n['title']) ?></strong></td>
                                        <td style="max-width:300px;"><?= htmlspecialchars($n['message']) ?></td>
                                        <td><span class="badge-status badge-<?= $n['type'] === 'expiry_alert' ? 'expired' : ($n['type'] === 'payment_reminder' ? 'pending' : 'active') ?>"><?= ucfirst(str_replace('_', ' ', $n['type'])) ?></span></td>
                                        <td><?= $n['is_read'] ? '✅' : '❌' ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Notification Modal -->
    <div class="modal-overlay" id="sendNotifModal">
        <div class="modal">
            <div class="modal-header"><h3>Send Notification</h3><button class="modal-close" onclick="closeModal('sendNotifModal')">&times;</button></div>
            <form method="POST">
                <input type="hidden" name="action" value="send_notification">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Recipient</label>
                        <select name="user_id" class="form-control">
                            <option value="0">📢 All Active Members</option>
                            <?php while ($m = $allMembers->fetch_assoc()): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" required></div>
                    <div class="form-group"><label>Message *</label><textarea name="message" class="form-control" rows="3" required></textarea></div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control">
                            <option value="general">General</option>
                            <option value="payment_reminder">Payment Reminder</option>
                            <option value="expiry_alert">Expiry Alert</option>
                            <option value="class_schedule">Class Schedule</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('sendNotifModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
