<?php
define('CURRENT_PAGE', 'classes');
require_once '../../includes/auth.php';
requireLogin();

$userId = $_SESSION['user_id'];
$member = $conn->query("SELECT * FROM members WHERE user_id = $userId")->fetch_assoc();
if (!$member) { echo 'Profile not found.'; exit; }
$memberId = $member['id'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'enroll') {
        $classId = (int)$_POST['class_id'];
        // Check capacity
        $cls = $conn->query("SELECT max_capacity, (SELECT COUNT(*) FROM class_enrollments WHERE class_id = $classId) as enrolled FROM gym_classes WHERE id = $classId")->fetch_assoc();
        if ($cls && $cls['enrolled'] < $cls['max_capacity']) {
            $stmt = $conn->prepare("INSERT IGNORE INTO class_enrollments (class_id, member_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $classId, $memberId);
            $success = $stmt->execute() ? "Enrolled successfully!" : "Already enrolled.";
        } else {
            $error = "Class is full.";
        }
    }
    if ($_POST['action'] === 'unenroll') {
        $classId = (int)$_POST['class_id'];
        $conn->query("DELETE FROM class_enrollments WHERE class_id = $classId AND member_id = $memberId");
        $success = "Unenrolled.";
    }
}

$classes = $conn->query("
    SELECT gc.*, t.first_name as t_fname, t.last_name as t_lname,
    (SELECT COUNT(*) FROM class_enrollments ce WHERE ce.class_id = gc.id) as enrolled,
    (SELECT COUNT(*) FROM class_enrollments ce WHERE ce.class_id = gc.id AND ce.member_id = $memberId) as is_enrolled
    FROM gym_classes gc
    LEFT JOIN trainers t ON gc.trainer_id = t.id
    WHERE gc.is_active = 1
    ORDER BY FIELD(gc.schedule_day, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')
");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>
        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">GYM CLASSES</h2>
            </header>
            <div class="page-content">
                <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

                <div class="card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead><tr><th>Class</th><th>Day</th><th>Time</th><th>Trainer</th><th>Spots</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php while ($c = $classes->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($c['class_name']) ?></strong></td>
                                        <td><?= ucfirst($c['schedule_day']) ?></td>
                                        <td><?= date('h:i A', strtotime($c['start_time'])) ?> - <?= date('h:i A', strtotime($c['end_time'])) ?></td>
                                        <td><?= $c['t_fname'] ? htmlspecialchars($c['t_fname'].' '.$c['t_lname']) : '-' ?></td>
                                        <td><?= $c['enrolled'] ?>/<?= $c['max_capacity'] ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <?php if ($c['is_enrolled']): ?>
                                                    <input type="hidden" name="action" value="unenroll">
                                                    <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Leave</button>
                                                <?php else: ?>
                                                    <input type="hidden" name="action" value="enroll">
                                                    <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary" <?= $c['enrolled'] >= $c['max_capacity'] ? 'disabled' : '' ?>>
                                                        <?= $c['enrolled'] >= $c['max_capacity'] ? 'Full' : 'Enroll' ?>
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
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
