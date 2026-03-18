<?php
define('CURRENT_PAGE', 'dashboard');
require_once '../../includes/auth.php';
requireAdmin();

// Fetch dashboard stats
$totalMembers = $conn->query("SELECT COUNT(*) as cnt FROM members")->fetch_assoc()['cnt'];
$activeMembers = $conn->query("SELECT COUNT(*) as cnt FROM members WHERE status='active'")->fetch_assoc()['cnt'];
$totalTrainers = $conn->query("SELECT COUNT(*) as cnt FROM trainers WHERE status='active'")->fetch_assoc()['cnt'];

$todayAttendance = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE date = CURDATE()")->fetch_assoc()['cnt'];

$totalRevenue = $conn->query("SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE status='paid'")->fetch_assoc()['total'];
$monthRevenue = $conn->query("SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE status='paid' AND MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())")->fetch_assoc()['total'];

$newMembersMonth = $conn->query("SELECT COUNT(*) as cnt FROM members WHERE MONTH(join_date) = MONTH(CURDATE()) AND YEAR(join_date) = YEAR(CURDATE())")->fetch_assoc()['cnt'];

$expiredMemberships = $conn->query("SELECT COUNT(*) as cnt FROM member_memberships WHERE end_date < CURDATE() AND status='active'")->fetch_assoc()['cnt'];

// Expiring soon (within 7 days)
$expiringSoon = $conn->query("
    SELECT m.first_name, m.last_name, mm.end_date, DATEDIFF(mm.end_date, CURDATE()) as days_left
    FROM member_memberships mm
    JOIN members m ON mm.member_id = m.id
    WHERE mm.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND mm.status = 'active'
    ORDER BY mm.end_date ASC
    LIMIT 10
");

// Monthly revenue data for chart (last 6 months)
$revenueData = $conn->query("
    SELECT DATE_FORMAT(payment_date, '%b') as month, SUM(amount) as total
    FROM payments WHERE status='paid'
    AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(payment_date), MONTH(payment_date)
    ORDER BY payment_date ASC
");
$months = [];
$revenues = [];
while ($row = $revenueData->fetch_assoc()) {
    $months[] = $row['month'];
    $revenues[] = $row['total'];
}

// Recent members
$recentMembers = $conn->query("SELECT * FROM members ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">DASHBOARD</h2>
                <div class="top-header-actions">
                    <div class="notification-bell" onclick="window.location.href='notifications.php'">
                        🔔
                        <?php if ($expiredMemberships > 0): ?><span class="dot"></span><?php endif; ?>
                    </div>
                </div>
            </header>

            <div class="page-content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card red">
                        <div class="stat-info">
                            <h4>Total Members</h4>
                            <div class="stat-number"><?= $totalMembers ?></div>
                        </div>
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-info">
                            <h4>Active Members</h4>
                            <div class="stat-number"><?= $activeMembers ?></div>
                        </div>
                        <div class="stat-icon">✅</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-info">
                            <h4>Trainers</h4>
                            <div class="stat-number"><?= $totalTrainers ?></div>
                        </div>
                        <div class="stat-icon">🏋️</div>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-info">
                            <h4>Today's Attendance</h4>
                            <div class="stat-number"><?= $todayAttendance ?></div>
                        </div>
                        <div class="stat-icon">📅</div>
                    </div>
                    <div class="stat-card purple">
                        <div class="stat-info">
                            <h4>Month Revenue</h4>
                            <div class="stat-number">₹<?= number_format($monthRevenue) ?></div>
                        </div>
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-card yellow">
                        <div class="stat-info">
                            <h4>New This Month</h4>
                            <div class="stat-number"><?= $newMembersMonth ?></div>
                        </div>
                        <div class="stat-icon">🆕</div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="chart-row">
                    <div class="card">
                        <div class="card-header">
                            <h3>Revenue Trend</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3>⚠️ Expiring Memberships</h3>
                            <span class="badge-status badge-expired"><?= $expiringSoon->num_rows ?> Members</span>
                        </div>
                        <ul class="expiry-list">
                            <?php if ($expiringSoon->num_rows > 0): ?>
                                <?php while ($exp = $expiringSoon->fetch_assoc()): ?>
                                    <li>
                                        <div>
                                            <strong><?= htmlspecialchars($exp['first_name'] . ' ' . $exp['last_name']) ?></strong>
                                            <br><span class="text-muted" style="font-size:0.8rem;">Expires: <?= date('d M Y', strtotime($exp['end_date'])) ?></span>
                                        </div>
                                        <span class="expiry-days <?= $exp['days_left'] <= 3 ? 'urgent' : 'warning' ?>">
                                            <?= $exp['days_left'] ?> days
                                        </span>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="text-center text-muted" style="padding:20px;">No memberships expiring soon</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Recent Members -->
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Members</h3>
                        <a href="members.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Join Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentMembers->num_rows > 0): ?>
                                    <?php while ($member = $recentMembers->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($member['phone']) ?></td>
                                            <td><?= date('d M Y', strtotime($member['join_date'])) ?></td>
                                            <td>
                                                <span class="badge-status badge-<?= $member['status'] ?>">
                                                    <?= ucfirst($member['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">No members yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: <?= json_encode($revenues) ?>,
                    backgroundColor: 'rgba(230, 57, 70, 0.6)',
                    borderColor: '#E63946',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(42, 48, 64, 0.5)' },
                        ticks: { color: '#8892A4' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#8892A4' }
                    }
                }
            }
        });
    </script>
</body>
</html>
