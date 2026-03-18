<?php
define('CURRENT_PAGE', 'dashboard');
require_once '../../includes/auth.php';
requireTrainer();

$userId = $_SESSION['user_id'];
$trainer = $conn->query("SELECT * FROM trainers WHERE user_id = $userId")->fetch_assoc();
$trainerId = $trainer['id'] ?? 0;

$myMembers = $conn->query("SELECT * FROM members WHERE assigned_trainer_id = $trainerId AND status = 'active' ORDER BY first_name");
$myClasses = $conn->query("SELECT * FROM gym_classes WHERE trainer_id = $trainerId AND is_active = 1 ORDER BY FIELD(schedule_day, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')");
$memberCount = $conn->query("SELECT COUNT(*) as cnt FROM members WHERE assigned_trainer_id = $trainerId AND status='active'")->fetch_assoc()['cnt'];
$classCount = $conn->query("SELECT COUNT(*) as cnt FROM gym_classes WHERE trainer_id = $trainerId AND is_active=1")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">TRAINER DASHBOARD</h2>
            </header>

            <div class="page-content">
                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="stat-card blue">
                        <div class="stat-info"><h4>My Members</h4><div class="stat-number"><?= $memberCount ?></div></div>
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-info"><h4>My Classes</h4><div class="stat-number"><?= $classCount ?></div></div>
                        <div class="stat-icon">📆</div>
                    </div>
                </div>

                <div class="chart-row">
                    <div class="card">
                        <div class="card-header"><h3>My Members</h3></div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead><tr><th>Name</th><th>Phone</th><th>Gender</th><th>Join Date</th></tr></thead>
                                <tbody>
                                    <?php if ($myMembers->num_rows > 0): ?>
                                        <?php while ($m = $myMembers->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></strong></td>
                                                <td><?= $m['phone'] ?></td>
                                                <td><?= ucfirst($m['gender']) ?></td>
                                                <td><?= date('d M Y', strtotime($m['join_date'])) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center text-muted">No members assigned</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h3>My Classes</h3></div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead><tr><th>Class</th><th>Day</th><th>Time</th><th>Capacity</th></tr></thead>
                                <tbody>
                                    <?php if ($myClasses->num_rows > 0): ?>
                                        <?php while ($c = $myClasses->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($c['class_name']) ?></strong></td>
                                                <td><?= ucfirst($c['schedule_day']) ?></td>
                                                <td><?= date('h:i A', strtotime($c['start_time'])) ?> - <?= date('h:i A', strtotime($c['end_time'])) ?></td>
                                                <td><?= $c['max_capacity'] ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center text-muted">No classes assigned</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
