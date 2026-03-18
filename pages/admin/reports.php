<?php
define('CURRENT_PAGE', 'reports');
require_once '../../includes/auth.php';
requireAdmin();

$reportType = isset($_GET['type']) ? $_GET['type'] : 'revenue';
$dateFrom = isset($_GET['from']) ? sanitize($_GET['from']) : date('Y-m-01');
$dateTo = isset($_GET['to']) ? sanitize($_GET['to']) : date('Y-m-d');
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">REPORTS</h2>
            </header>

            <div class="page-content">
                <!-- Report Type Tabs -->
                <div class="login-tabs mb-3" style="max-width:500px;">
                    <a href="?type=revenue&from=<?= $dateFrom ?>&to=<?= $dateTo ?>" class="login-tab <?= $reportType === 'revenue' ? 'active' : '' ?>" style="text-decoration:none;">Revenue</a>
                    <a href="?type=members&from=<?= $dateFrom ?>&to=<?= $dateTo ?>" class="login-tab <?= $reportType === 'members' ? 'active' : '' ?>" style="text-decoration:none;">Members</a>
                    <a href="?type=attendance&from=<?= $dateFrom ?>&to=<?= $dateTo ?>" class="login-tab <?= $reportType === 'attendance' ? 'active' : '' ?>" style="text-decoration:none;">Attendance</a>
                </div>

                <!-- Date Filter -->
                <form method="GET" class="search-bar">
                    <input type="hidden" name="type" value="<?= $reportType ?>">
                    <input type="date" name="from" class="form-control" value="<?= $dateFrom ?>" style="max-width:200px;">
                    <span style="color:var(--text-muted);align-self:center;">to</span>
                    <input type="date" name="to" class="form-control" value="<?= $dateTo ?>" style="max-width:200px;">
                    <button type="submit" class="btn btn-secondary btn-sm">Generate</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="exportToCSV('reportTable', '<?= $reportType ?>_report')">Export CSV</button>
                </form>

                <div class="card">
                    <?php if ($reportType === 'revenue'): ?>
                        <?php
                        $data = $conn->query("
                            SELECT p.*, m.first_name, m.last_name
                            FROM payments p
                            JOIN members m ON p.member_id = m.id
                            WHERE p.payment_date BETWEEN '$dateFrom' AND '$dateTo'
                            ORDER BY p.payment_date DESC
                        ");
                        $total = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE status='paid' AND payment_date BETWEEN '$dateFrom' AND '$dateTo'")->fetch_assoc()['t'];
                        ?>
                        <div class="card-header">
                            <h3>Revenue Report</h3>
                            <span style="font-size:1.2rem;font-weight:700;color:var(--success);">Total: ₹<?= number_format($total) ?></span>
                        </div>
                        <div class="table-container">
                            <table class="data-table" id="reportTable">
                                <thead><tr><th>Date</th><th>Member</th><th>Amount</th><th>Mode</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php while ($r = $data->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($r['payment_date'])) ?></td>
                                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                            <td>₹<?= number_format($r['amount']) ?></td>
                                            <td><?= strtoupper($r['payment_mode']) ?></td>
                                            <td><span class="badge-status badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($reportType === 'members'): ?>
                        <?php
                        $data = $conn->query("
                            SELECT m.*, mm.end_date, mp.plan_name
                            FROM members m
                            LEFT JOIN member_memberships mm ON mm.member_id = m.id AND mm.status = 'active'
                            LEFT JOIN membership_plans mp ON mm.plan_id = mp.id
                            WHERE m.join_date BETWEEN '$dateFrom' AND '$dateTo'
                            ORDER BY m.join_date DESC
                        ");
                        ?>
                        <div class="card-header"><h3>Members Report</h3></div>
                        <div class="table-container">
                            <table class="data-table" id="reportTable">
                                <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Join Date</th><th>Plan</th><th>Expiry</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php while ($r = $data->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                            <td><?= $r['phone'] ?></td>
                                            <td><?= $r['email'] ?></td>
                                            <td><?= date('d M Y', strtotime($r['join_date'])) ?></td>
                                            <td><?= $r['plan_name'] ?: '-' ?></td>
                                            <td><?= $r['end_date'] ? date('d M Y', strtotime($r['end_date'])) : '-' ?></td>
                                            <td><span class="badge-status badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($reportType === 'attendance'): ?>
                        <?php
                        $data = $conn->query("
                            SELECT a.date, COUNT(DISTINCT a.member_id) as total_checkins,
                            AVG(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out)) as avg_duration
                            FROM attendance a
                            WHERE a.date BETWEEN '$dateFrom' AND '$dateTo'
                            GROUP BY a.date ORDER BY a.date DESC
                        ");
                        ?>
                        <div class="card-header"><h3>Attendance Report</h3></div>
                        <div class="table-container">
                            <table class="data-table" id="reportTable">
                                <thead><tr><th>Date</th><th>Total Check-ins</th><th>Avg Duration</th></tr></thead>
                                <tbody>
                                    <?php while ($r = $data->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('d M Y (D)', strtotime($r['date'])) ?></td>
                                            <td><?= $r['total_checkins'] ?></td>
                                            <td><?= $r['avg_duration'] ? round($r['avg_duration']) . ' min' : '-' ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
