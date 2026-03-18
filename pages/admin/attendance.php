<?php
define('CURRENT_PAGE', 'attendance');
require_once '../../includes/auth.php';
requireAdmin();

$error = '';
$success = '';

// Handle check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'checkin') {
        $memberId = (int)$_POST['member_id'];
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        // Check if already checked in today without checkout
        $existing = $conn->query("SELECT id FROM attendance WHERE member_id = $memberId AND date = '$today' AND check_out IS NULL")->fetch_assoc();
        if ($existing) {
            $error = "Member is already checked in today.";
        } else {
            $stmt = $conn->prepare("INSERT INTO attendance (member_id, check_in, date) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $memberId, $now, $today);
            $success = $stmt->execute() ? "Checked in successfully!" : "Failed.";
        }
    }

    if ($_POST['action'] === 'checkout') {
        $attId = (int)$_POST['attendance_id'];
        $now = date('Y-m-d H:i:s');
        $conn->query("UPDATE attendance SET check_out = '$now' WHERE id = $attId");
        $success = "Checked out successfully!";
    }
}

$dateFilter = isset($_GET['date']) ? sanitize($_GET['date']) : date('Y-m-d');

$attendance = $conn->query("
    SELECT a.*, m.first_name, m.last_name
    FROM attendance a
    JOIN members m ON a.member_id = m.id
    WHERE a.date = '$dateFilter'
    ORDER BY a.check_in DESC
");

$todayCount = $conn->query("SELECT COUNT(DISTINCT member_id) as cnt FROM attendance WHERE date = '$dateFilter'")->fetch_assoc()['cnt'];
$stillIn = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE date = '$dateFilter' AND check_out IS NULL")->fetch_assoc()['cnt'];

$activeMembers = $conn->query("SELECT id, first_name, last_name FROM members WHERE status='active' ORDER BY first_name");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">ATTENDANCE</h2>
                <div class="top-header-actions">
                    <button class="btn btn-primary btn-sm" onclick="openModal('checkInModal')">+ Check In</button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="stat-card blue">
                        <div class="stat-info"><h4>Total Today</h4><div class="stat-number"><?= $todayCount ?></div></div>
                        <div class="stat-icon">📅</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-info"><h4>Currently In</h4><div class="stat-number"><?= $stillIn ?></div></div>
                        <div class="stat-icon">🏃</div>
                    </div>
                </div>

                <form method="GET" class="search-bar">
                    <input type="date" name="date" class="form-control" value="<?= $dateFilter ?>" style="max-width:200px;">
                    <button type="submit" class="btn btn-secondary btn-sm">View Date</button>
                </form>

                <div class="card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Duration</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($attendance->num_rows > 0): ?>
                                    <?php while ($a = $attendance->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></strong></td>
                                            <td><?= date('h:i A', strtotime($a['check_in'])) ?></td>
                                            <td>
                                                <?= $a['check_out'] ? date('h:i A', strtotime($a['check_out'])) : '<span class="badge-status badge-active">Still In</span>' ?>
                                            </td>
                                            <td>
                                                <?php if ($a['check_out']): ?>
                                                    <?php
                                                    $diff = strtotime($a['check_out']) - strtotime($a['check_in']);
                                                    echo floor($diff/3600) . 'h ' . floor(($diff%3600)/60) . 'm';
                                                    ?>
                                                <?php else: ?>-<?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$a['check_out']): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="checkout">
                                                        <input type="hidden" name="attendance_id" value="<?= $a['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning">Check Out</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">Done</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">No attendance records for this date</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check In Modal -->
    <div class="modal-overlay" id="checkInModal">
        <div class="modal">
            <div class="modal-header"><h3>Member Check-In</h3><button class="modal-close" onclick="closeModal('checkInModal')">&times;</button></div>
            <form method="POST">
                <input type="hidden" name="action" value="checkin">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Member *</label>
                        <select name="member_id" class="form-control" required>
                            <option value="">-- Select Member --</option>
                            <?php while ($m = $activeMembers->fetch_assoc()): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('checkInModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Check In</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
