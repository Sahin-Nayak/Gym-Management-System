<?php
define('CURRENT_PAGE', 'payments');
require_once '../../includes/auth.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'record_payment') {
    $memberId = (int)$_POST['member_id'];
    $amount = (float)$_POST['amount'];
    $paymentDate = sanitize($_POST['payment_date']);
    $paymentMode = sanitize($_POST['payment_mode']);
    $notes = sanitize($_POST['notes']);
    $invoiceNo = generateInvoiceNumber();
    $membershipId = !empty($_POST['membership_id']) ? (int)$_POST['membership_id'] : null;

    $stmt = $conn->prepare("INSERT INTO payments (member_id, membership_id, amount, payment_date, payment_mode, status, invoice_number, notes) VALUES (?, ?, ?, ?, ?, 'paid', ?, ?)");
    $stmt->bind_param("iidssss", $memberId, $membershipId, $amount, $paymentDate, $paymentMode, $invoiceNo, $notes);

    if ($stmt->execute()) {
        $success = "Payment recorded! Invoice: $invoiceNo";
    } else {
        $error = "Failed to record payment.";
    }
}

// Filters
$dateFrom = isset($_GET['from']) ? sanitize($_GET['from']) : date('Y-m-01');
$dateTo = isset($_GET['to']) ? sanitize($_GET['to']) : date('Y-m-d');

$payments = $conn->query("
    SELECT p.*, m.first_name, m.last_name, mp.plan_name
    FROM payments p
    JOIN members m ON p.member_id = m.id
    LEFT JOIN member_memberships mm ON p.membership_id = mm.id
    LEFT JOIN membership_plans mp ON mm.plan_id = mp.id
    WHERE p.payment_date BETWEEN '$dateFrom' AND '$dateTo'
    ORDER BY p.payment_date DESC
");

$totalPaid = $conn->query("SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE status='paid' AND payment_date BETWEEN '$dateFrom' AND '$dateTo'")->fetch_assoc()['total'];
$totalPending = $conn->query("SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE status='pending' AND payment_date BETWEEN '$dateFrom' AND '$dateTo'")->fetch_assoc()['total'];

$members = $conn->query("SELECT id, first_name, last_name FROM members WHERE status='active' ORDER BY first_name");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">PAYMENTS</h2>
                <div class="top-header-actions">
                    <button class="btn btn-primary btn-sm" onclick="openModal('recordPaymentModal')">+ Record Payment</button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="stat-card green">
                        <div class="stat-info"><h4>Total Collected</h4><div class="stat-number">₹<?= number_format($totalPaid) ?></div></div>
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-card yellow">
                        <div class="stat-info"><h4>Pending</h4><div class="stat-number">₹<?= number_format($totalPending) ?></div></div>
                        <div class="stat-icon">⏳</div>
                    </div>
                </div>

                <!-- Date Filter -->
                <form method="GET" class="search-bar">
                    <input type="date" name="from" class="form-control" value="<?= $dateFrom ?>" style="max-width:200px;">
                    <span style="color:var(--text-muted);align-self:center;">to</span>
                    <input type="date" name="to" class="form-control" value="<?= $dateTo ?>" style="max-width:200px;">
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="exportToCSV('paymentsTable', 'payments_report')">Export CSV</button>
                </form>

                <div class="card">
                    <div class="table-container">
                        <table class="data-table" id="paymentsTable">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Member</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($payments->num_rows > 0): ?>
                                    <?php while ($p = $payments->fetch_assoc()): ?>
                                        <tr>
                                            <td style="font-family:monospace;font-size:0.8rem;"><?= $p['invoice_number'] ?></td>
                                            <td><strong><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($p['plan_name'] ?: '-') ?></td>
                                            <td><strong>₹<?= number_format($p['amount']) ?></strong></td>
                                            <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                                            <td><?= strtoupper($p['payment_mode']) ?></td>
                                            <td><span class="badge-status badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline" onclick="printReceipt(<?= htmlspecialchars(json_encode($p)) ?>)">Print</button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center text-muted">No payments in this period</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div class="modal-overlay" id="recordPaymentModal">
        <div class="modal">
            <div class="modal-header"><h3>Record Payment</h3><button class="modal-close" onclick="closeModal('recordPaymentModal')">&times;</button></div>
            <form method="POST">
                <input type="hidden" name="action" value="record_payment">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Member *</label>
                        <select name="member_id" class="form-control" required>
                            <option value="">-- Select Member --</option>
                            <?php while ($m = $members->fetch_assoc()): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Amount (₹) *</label><input type="number" name="amount" class="form-control" min="0" step="0.01" required></div>
                        <div class="form-group"><label>Payment Date *</label><input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                    </div>
                    <div class="form-group">
                        <label>Payment Mode *</label>
                        <select name="payment_mode" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('recordPaymentModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script>
        function printReceipt(data) {
            const w = window.open('', '_blank');
            w.document.write(`<html><head><title>Receipt</title><style>body{font-family:Arial;padding:40px;max-width:500px;margin:auto;}h1{color:#E63946;font-size:24px;}.row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eee;}.total{font-size:20px;font-weight:bold;margin-top:16px;}</style></head><body>`);
            w.document.write(`<h1>💪 FITZONE GYM</h1><p>Payment Receipt</p><hr>`);
            w.document.write(`<div class="row"><span>Invoice:</span><span>${data.invoice_number}</span></div>`);
            w.document.write(`<div class="row"><span>Member:</span><span>${data.first_name} ${data.last_name}</span></div>`);
            w.document.write(`<div class="row"><span>Date:</span><span>${data.payment_date}</span></div>`);
            w.document.write(`<div class="row"><span>Mode:</span><span>${data.payment_mode.toUpperCase()}</span></div>`);
            w.document.write(`<div class="row"><span>Status:</span><span>${data.status.toUpperCase()}</span></div>`);
            w.document.write(`<hr><p class="total">Amount: ₹${parseFloat(data.amount).toLocaleString('en-IN')}</p>`);
            w.document.write(`<p style="margin-top:30px;color:#888;font-size:12px;">Thank you for your payment!</p>`);
            w.document.write(`</body></html>`);
            w.document.close();
            w.print();
        }
    </script>
</body>
</html>
