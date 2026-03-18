<?php
define('CURRENT_PAGE', 'profile');
require_once '../../includes/auth.php';
requireLogin();

$userId = $_SESSION['user_id'];
$member = $conn->query("SELECT m.*, t.first_name as t_fname, t.last_name as t_lname FROM members m LEFT JOIN trainers t ON m.assigned_trainer_id = t.id WHERE m.user_id = $userId")->fetch_assoc();
if (!$member) { echo 'Profile not found.'; exit; }

$membership = $conn->query("SELECT mm.*, mp.plan_name, mp.price FROM member_memberships mm JOIN membership_plans mp ON mm.plan_id = mp.id WHERE mm.member_id = {$member['id']} ORDER BY mm.created_at DESC LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>
        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">MY PROFILE</h2>
            </header>
            <div class="page-content">
                <div class="card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?= strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)) ?>
                        </div>
                        <div class="profile-info">
                            <h2><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></h2>
                            <p class="meta"><span class="badge-status badge-<?= $member['status'] ?>"><?= ucfirst($member['status']) ?></span></p>
                        </div>
                    </div>
                    <hr class="divider">
                    <div class="profile-details-grid">
                        <div class="detail-item"><div class="label">Phone</div><div class="value"><?= $member['phone'] ?></div></div>
                        <div class="detail-item"><div class="label">Email</div><div class="value"><?= $member['email'] ?></div></div>
                        <div class="detail-item"><div class="label">Age</div><div class="value"><?= $member['age'] ?: '-' ?></div></div>
                        <div class="detail-item"><div class="label">Gender</div><div class="value"><?= ucfirst($member['gender']) ?></div></div>
                        <div class="detail-item"><div class="label">Weight</div><div class="value"><?= $member['weight'] ? $member['weight'].' kg' : '-' ?></div></div>
                        <div class="detail-item"><div class="label">Height</div><div class="value"><?= $member['height'] ? $member['height'].' cm' : '-' ?></div></div>
                        <div class="detail-item"><div class="label">Join Date</div><div class="value"><?= date('d M Y', strtotime($member['join_date'])) ?></div></div>
                        <div class="detail-item"><div class="label">Trainer</div><div class="value"><?= $member['t_fname'] ? htmlspecialchars($member['t_fname'].' '.$member['t_lname']) : 'Not assigned' ?></div></div>
                        <?php if ($membership): ?>
                        <div class="detail-item"><div class="label">Plan</div><div class="value"><?= $membership['plan_name'] ?> (₹<?= number_format($membership['price']) ?>)</div></div>
                        <div class="detail-item"><div class="label">Expiry</div><div class="value"><?= date('d M Y', strtotime($membership['end_date'])) ?></div></div>
                        <?php endif; ?>
                        <div class="detail-item"><div class="label">Emergency Contact</div><div class="value"><?= htmlspecialchars(($member['emergency_contact_name'] ?: '-') . ' / ' . ($member['emergency_contact_phone'] ?: '-')) ?></div></div>
                        <div class="detail-item"><div class="label">Address</div><div class="value"><?= htmlspecialchars($member['address'] ?: '-') ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
