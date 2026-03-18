<?php
define('CURRENT_PAGE', 'classes');
require_once '../../includes/auth.php';
requireTrainer();

$userId = $_SESSION['user_id'];
$trainer = $conn->query("SELECT * FROM trainers WHERE user_id = $userId")->fetch_assoc();
$trainerId = $trainer['id'] ?? 0;

$classes = $conn->query("
    SELECT gc.*, (SELECT COUNT(*) FROM class_enrollments ce WHERE ce.class_id = gc.id) as enrolled
    FROM gym_classes gc
    WHERE gc.trainer_id = $trainerId
    ORDER BY FIELD(gc.schedule_day, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')
");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>
        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">MY CLASSES</h2>
            </header>
            <div class="page-content">
                <div class="card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead><tr><th>Class</th><th>Day</th><th>Time</th><th>Enrolled</th><th>Capacity</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if ($classes->num_rows > 0): ?>
                                    <?php while ($c = $classes->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($c['class_name']) ?></strong></td>
                                            <td><?= ucfirst($c['schedule_day']) ?></td>
                                            <td><?= date('h:i A', strtotime($c['start_time'])) ?> - <?= date('h:i A', strtotime($c['end_time'])) ?></td>
                                            <td><?= $c['enrolled'] ?></td>
                                            <td><?= $c['max_capacity'] ?></td>
                                            <td><span class="badge-status badge-<?= $c['is_active'] ? 'active' : 'inactive' ?>"><?= $c['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">No classes assigned</td></tr>
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
