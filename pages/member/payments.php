<?php
define('CURRENT_PAGE', 'payments');
require_once '../../includes/auth.php';
requireLogin();

$userId = $_SESSION['user_id'];
$member = $conn->query("SELECT * FROM members WHERE user_id = $userId")->fetch_assoc();
if (!$member) { echo 'Profile not found.'; exit; }

$payments = $conn->query("
    SELECT p.*, mp.plan_name
    FROM payments p
    LEFT JOIN member_memberships mm ON p.membership_id = mm.id
    LEFT JOIN membership_plans mp ON mm.plan_id = mp.id
    WHERE p.member_id = {$member['id']}
    ORDER BY p.payment_date DESC
");
$totalPaid = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id = {$member['id']} AND status='paid'")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payments - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>
        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">MY PAYMENTS</h2>
            </header>
            <div class="page-content">
                <div class="stat-card green mb-3" style="display:inline-flex;">
                    <div class="stat-info"><h4>Total Paid</h4><div class="stat-number">₹<?= number_format($totalPaid) ?></div></div>
                </div>

                <div class="card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead><tr><th>Invoice</th><th>Plan</th><th>Amount</th><th>Date</th><th>Mode</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if ($payments->num_rows > 0): ?>
                                    <?php while ($p = $payments->fetch_assoc()): ?>
                                        <tr>
                                            <td style="font-family:monospace;"><?= $p['invoice_number'] ?: '-' ?></td>
                                            <td><?= $p['plan_name'] ?: '-' ?></td>
                                            <td><strong>₹<?= number_format($p['amount']) ?></strong></td>
                                            <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                                            <td><?= strtoupper($p['payment_mode']) ?></td>
                                            <td><span class="badge-status badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">No payment records</td></tr>
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
