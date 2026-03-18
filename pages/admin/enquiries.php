<?php
define('CURRENT_PAGE', 'enquiries');
require_once '../../includes/auth.php';
requireAdmin();

$conn->query("CREATE TABLE IF NOT EXISTS gym_enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(30),
    message TEXT NOT NULL,
    status ENUM('unread','read','replied') DEFAULT 'unread',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($_POST['action'] === 'mark_read' && $id) {
        $conn->query("UPDATE gym_enquiries SET status='read' WHERE id=$id");
    } elseif ($_POST['action'] === 'mark_replied' && $id) {
        $conn->query("UPDATE gym_enquiries SET status='replied' WHERE id=$id");
    } elseif ($_POST['action'] === 'delete' && $id) {
        $conn->query("DELETE FROM gym_enquiries WHERE id=$id");
    } elseif ($_POST['action'] === 'mark_all_read') {
        $conn->query("UPDATE gym_enquiries SET status='read' WHERE status='unread'");
    }
    header('Location: enquiries.php');
    exit;
}

// Filter
$filter = $_GET['filter'] ?? 'all';
$where  = $filter === 'unread'   ? "WHERE status='unread'"
        : ($filter === 'read'    ? "WHERE status='read'"
        : ($filter === 'replied' ? "WHERE status='replied'" : ''));

$enquiries = $conn->query("SELECT * FROM gym_enquiries $where ORDER BY created_at DESC");
$counts    = $conn->query("SELECT status, COUNT(*) as c FROM gym_enquiries GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$countMap  = ['unread' => 0, 'read' => 0, 'replied' => 0, 'total' => 0];
foreach ($counts as $c) { $countMap[$c['status']] = (int)$c['c']; $countMap['total'] += (int)$c['c']; }
?>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiries — FitZone Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<div class="app-layout">
<?php include '../../includes/sidebar.php'; ?>
<div class="main-content">
    <header class="top-header">
        <button class="hamburger" onclick="toggleSidebar()">☰</button>
        <h2 class="page-title">ENQUIRIES</h2>
        <div class="top-header-actions">
            <?php if ($countMap['unread'] > 0): ?>
            <form method="POST" style="margin:0">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn btn-secondary btn-sm">Mark All Read</button>
            </form>
            <?php endif; ?>
            <a href="../../pages/website/contact.php" target="_blank" class=" btn btn-primary btn-sm">🌐 View Contact Page ↗</a>
        </div>
    </header>
    <div class="page-content">

    <!-- Summary Cards -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:28px;">
        <div class="stat-card">
            <div class="stat-number"><?= $countMap['total'] ?></div>
            <div class="stat-label">Total Enquiries</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:var(--warning)"><?= $countMap['unread'] ?></div>
            <div class="stat-label">Unread</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $countMap['read'] ?></div>
            <div class="stat-label">Read</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:var(--success)"><?= $countMap['replied'] ?></div>
            <div class="stat-label">Replied</div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card" style="margin-bottom:24px;padding:0;overflow:hidden;">
        <div style="display:flex;border-bottom:1px solid var(--border);">
            <?php
            $tabs = ['all' => 'All', 'unread' => 'Unread', 'read' => 'Read', 'replied' => 'Replied'];
            foreach ($tabs as $f => $label):
                $isActive = ($filter === $f);
                $cnt = ($f === 'all') ? $countMap['total'] : ($countMap[$f] ?? 0);
            ?>
            <a href="?filter=<?= $f ?>" style="
                padding:14px 22px;font-size:0.85rem;font-weight:600;text-decoration:none;
                display:flex;align-items:center;gap:8px;
                color:<?= $isActive ? 'var(--primary)' : 'var(--text-secondary)' ?>;
                border-bottom:2px solid <?= $isActive ? 'var(--primary)' : 'transparent' ?>;
                transition:all 0.2s;
            "><?= $label ?>
                <?php if ($cnt > 0): ?>
                <span style="
                    background:<?= $isActive ? 'var(--primary)' : 'var(--bg-surface)' ?>;
                    color:<?= $isActive ? '#fff' : 'var(--text-secondary)' ?>;
                    padding:2px 8px;border-radius:50px;font-size:0.72rem;
                "><?= $cnt ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Enquiries List -->
    <?php if ($enquiries->num_rows === 0): ?>
    <div class="card" style="text-align:center;padding:60px 20px;">
        <div style="font-size:3rem;margin-bottom:16px;">📭</div>
        <h3 style="color:var(--text-secondary);font-weight:500;">
            No enquiries<?= $filter !== 'all' ? " ($filter)" : '' ?>
        </h3>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px;">
        <?php while ($enq = $enquiries->fetch_assoc()):
            $borderStyle = $enq['status'] === 'unread' ? 'border-left:3px solid var(--primary);' : '';
        ?>
        <div class="card" style="<?= $borderStyle ?>">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap;">
                        <div style="font-weight:700;font-size:0.95rem;color:var(--text-primary)">
                            <?= htmlspecialchars($enq['name']) ?>
                        </div>
                        <?php
                        $statusBg    = $enq['status'] === 'unread'  ? 'rgba(245,158,11,0.12)'     : ($enq['status'] === 'replied' ? 'rgba(16,185,129,0.1)' : 'var(--bg-surface)');
                        $statusColor = $enq['status'] === 'unread'  ? 'var(--warning)'            : ($enq['status'] === 'replied' ? 'var(--success)'       : 'var(--text-secondary)');
                        ?>
                        <span style="padding:3px 10px;border-radius:50px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;background:<?= $statusBg ?>;color:<?= $statusColor ?>">
                            <?= ucfirst($enq['status']) ?>
                        </span>
                        <span style="font-size:0.78rem;color:var(--text-secondary)">
                            <?= date('M d, Y · g:i A', strtotime($enq['created_at'])) ?>
                        </span>
                    </div>
                    <div style="display:flex;gap:20px;margin-bottom:12px;font-size:0.83rem;color:var(--text-secondary);flex-wrap:wrap;">
                        <span>✉️ <?= htmlspecialchars($enq['email']) ?></span>
                        <?php if ($enq['phone']): ?>
                        <span>📞 <?= htmlspecialchars($enq['phone']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:0.9rem;color:var(--text-primary);line-height:1.7;background:var(--bg-surface);padding:14px 16px;border-radius:8px;border:1px solid var(--border);">
                        <?= nl2br(htmlspecialchars($enq['message'])) ?>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;min-width:140px;">
                    <a href="mailto:<?= htmlspecialchars($enq['email']) ?>" class="btn btn-primary btn-sm" style="text-align:center;">
                        Reply via Email
                    </a>
                    <?php if ($enq['status'] === 'unread'): ?>
                    <form method="POST" style="margin:0">
                        <input type="hidden" name="action" value="mark_read">
                        <input type="hidden" name="id" value="<?= $enq['id'] ?>">
                        <button type="submit" class="btn btn-secondary btn-sm" style="width:100%">Mark Read</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($enq['status'] !== 'replied'): ?>
                    <form method="POST" style="margin:0">
                        <input type="hidden" name="action" value="mark_replied">
                        <input type="hidden" name="id" value="<?= $enq['id'] ?>">
                        <button type="submit" class="btn btn-success btn-sm" style="width:100%">Mark Replied</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" style="margin:0" onsubmit="return confirm('Delete this enquiry?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $enq['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="width:100%">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
    </div><!-- /page-content -->
</div><!-- /main-content -->
</div><!-- /app-layout -->

<script src="../../assets/js/main.js"></script>
</body>
</html>
