<?php
define('CURRENT_PAGE', 'members');
require_once '../../includes/auth.php';
requireAdmin();

$memberId = (int)($_GET['id'] ?? 0);
if (!$memberId) redirect('members.php');

$member = $conn->query("
    SELECT m.*, t.first_name as trainer_fname, t.last_name as trainer_lname
    FROM members m
    LEFT JOIN trainers t ON m.assigned_trainer_id = t.id
    WHERE m.id = $memberId
")->fetch_assoc();

if (!$member) redirect('members.php');

// Membership info
$membership = $conn->query("
    SELECT mm.*, mp.plan_name, mp.price
    FROM member_memberships mm
    JOIN membership_plans mp ON mm.plan_id = mp.id
    WHERE mm.member_id = $memberId
    ORDER BY mm.created_at DESC LIMIT 1
")->fetch_assoc();

// Payment history
$payments = $conn->query("
    SELECT p.*, mp.plan_name
    FROM payments p
    LEFT JOIN member_memberships mm ON p.membership_id = mm.id
    LEFT JOIN membership_plans mp ON mm.plan_id = mp.id
    WHERE p.member_id = $memberId
    ORDER BY p.payment_date DESC LIMIT 10
");

// Attendance history
$attendance = $conn->query("
    SELECT * FROM attendance
    WHERE member_id = $memberId
    ORDER BY date DESC LIMIT 15
");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Profile - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">MEMBER PROFILE</h2>
                <div class="top-header-actions">
                    <a href="members.php" class="btn btn-outline btn-sm">← Back</a>
                </div>
            </header>

            <div class="page-content">
                <!-- Profile Header -->
                <div class="card mb-3">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php if ($member['photo']): ?>
                                <img src="../../uploads/members/<?= htmlspecialchars($member['photo']) ?>" alt="Photo">
                            <?php else: ?>
                                <?= strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="profile-info">
                            <h2><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></h2>
                            <p class="meta">
                                <span class="badge-status badge-<?= $member['status'] ?>"><?= ucfirst($member['status']) ?></span>
                                &nbsp; Member since <?= date('d M Y', strtotime($member['join_date'])) ?>
                            </p>
                        </div>
                    </div>

                    <div class="profile-details-grid">
                        <div class="detail-item">
                            <div class="label">Phone</div>
                            <div class="value"><?= htmlspecialchars($member['phone']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="label">Email</div>
                            <div class="value"><?= htmlspecialchars($member['email']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="label">Age</div>
                            <div class="value"><?= $member['age'] ?? '-' ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="label">Gender</div>
                            <div class="value"><?= ucfirst($member['gender']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="label">Weight</div>
                            <div class="value"><?= $member['weight'] ? $member['weight'] . ' kg' : '-' ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="label">Height</div>
                            <div class="value"><?= $member['height'] ? $member['height'] . ' cm' : '-' ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="label">Address</div>
                            <div class="value"><?= htmlspecialchars($member['address'] ?: '-') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="label">Trainer</div>
                            <div class="value"><?= $member['trainer_fname'] ? htmlspecialchars($member['trainer_fname'] . ' ' . $member['trainer_lname']) : 'Not assigned' ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="label">Emergency Contact</div>
                            <div class="value"><?= htmlspecialchars(($member['emergency_contact_name'] ?: '-') . ' / ' . ($member['emergency_contact_phone'] ?: '-')) ?></div>
                        </div>

                        <?php
                        // Get login credentials from users table
                        $userCreds = $conn->query("SELECT username, password FROM users WHERE id = {$member['user_id']} LIMIT 1")->fetch_assoc();
                        ?>
                        <?php if ($userCreds): ?>
                        <div class="detail-item">
                            <div class="label">Username</div>
                            <div class="value" style="font-family:monospace;font-weight:600;">
                                <?= htmlspecialchars($userCreds['username']) ?>
                            </div>
                        </div>
                        <!-- <div class="detail-item">
                            <div class="label">Password</div>
                            <div class="value" style="display:flex;align-items:center;gap:10px;">
                                <span id="memberPwdDisplay" style="font-family:monospace;letter-spacing:3px;">••••••••</span>
                                <button onclick="toggleMemberPwd()" 
                                        style="background:var(--primary-alpha);border:1px solid var(--border);color:var(--primary);padding:3px 10px;border-radius:6px;font-size:0.75rem;cursor:pointer;font-weight:600;"
                                        id="memberPwdBtn">Show</button>
                                <span id="memberPwdNote" style="font-size:0.72rem;color:var(--text-muted);display:none;">
                                    ⚠️ Hashed — cannot be reversed
                                </span>
                            </div>
                        </div> -->
                        <script>
                        function toggleMemberPwd() {
                            var display = document.getElementById('memberPwdDisplay');
                            var btn     = document.getElementById('memberPwdBtn');
                            var note    = document.getElementById('memberPwdNote');
                            if (btn.textContent === 'Show') {
                                display.textContent = '<?= addslashes($userCreds['password']) ?>';
                                display.style.letterSpacing = '0';
                                display.style.fontSize = '0.72rem';
                                display.style.wordBreak = 'break-all';
                                btn.textContent = 'Hide';
                                note.style.display = 'inline';
                            } else {
                                display.textContent = '••••••••';
                                display.style.letterSpacing = '3px';
                                display.style.fontSize = '';
                                display.style.wordBreak = '';
                                btn.textContent = 'Show';
                                note.style.display = 'none';
                            }
                        }
                        </script>
                        <?php endif; ?>
                        <?php if ($membership): ?>
                        <div class="detail-item">
                            <div class="label">Current Plan</div>
                            <div class="value"><?= htmlspecialchars($membership['plan_name']) ?> (₹<?= number_format($membership['price']) ?>)</div>
                        </div>
                        <div class="detail-item">
                            <div class="label">Membership Expiry</div>
                            <div class="value <?= strtotime($membership['end_date']) < time() ? 'text-danger' : 'text-success' ?>">
                                <?= date('d M Y', strtotime($membership['end_date'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3>Payment History</h3>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($payments->num_rows > 0): ?>
                                    <?php while ($p = $payments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['invoice_number'] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($p['plan_name'] ?: '-') ?></td>
                                            <td><strong>₹<?= number_format($p['amount']) ?></strong></td>
                                            <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                                            <td><?= strtoupper($p['payment_mode']) ?></td>
                                            <td><span class="badge-status badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">No payments recorded</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Attendance History -->
                <div class="card">
                    <div class="card-header">
                        <h3>Attendance Log</h3>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($attendance->num_rows > 0): ?>
                                    <?php while ($a = $attendance->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($a['date'])) ?></td>
                                            <td><?= date('h:i A', strtotime($a['check_in'])) ?></td>
                                            <td><?= $a['check_out'] ? date('h:i A', strtotime($a['check_out'])) : '<span class="text-warning">Still In</span>' ?></td>
                                            <td>
                                                <?php if ($a['check_out']): ?>
                                                    <?php
                                                    $diff = strtotime($a['check_out']) - strtotime($a['check_in']);
                                                    $hours = floor($diff / 3600);
                                                    $mins = floor(($diff % 3600) / 60);
                                                    echo "{$hours}h {$mins}m";
                                                    ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">No attendance records</td></tr>
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
