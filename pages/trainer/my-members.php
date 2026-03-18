<?php
define('CURRENT_PAGE', 'my-members');
require_once '../../includes/auth.php';
requireTrainer();

$userId = $_SESSION['user_id'];
$trainer = $conn->query("SELECT * FROM trainers WHERE user_id = $userId")->fetch_assoc();
$trainerId = $trainer['id'] ?? 0;

$members = $conn->query("SELECT * FROM members WHERE assigned_trainer_id = $trainerId ORDER BY first_name");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Members - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>
        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">MY MEMBERS</h2>
            </header>
            <div class="page-content">
                <div class="card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Age</th><th>Weight</th><th>Height</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if ($members->num_rows > 0): ?>
                                    <?php while ($m = $members->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></strong></td>
                                            <td><?= $m['phone'] ?></td>
                                            <td><?= $m['email'] ?></td>
                                            <td><?= $m['age'] ?: '-' ?></td>
                                            <td><?= $m['weight'] ? $m['weight'] . ' kg' : '-' ?></td>
                                            <td><?= $m['height'] ? $m['height'] . ' cm' : '-' ?></td>
                                            <td><span class="badge-status badge-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted">No members assigned to you</td></tr>
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
