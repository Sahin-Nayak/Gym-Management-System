<?php
define('CURRENT_PAGE', 'classes');
require_once '../../includes/auth.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_class') {
        $name = sanitize($_POST['class_name']);
        $desc = sanitize($_POST['description']);
        $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;
        $day = sanitize($_POST['schedule_day']);
        $startTime = sanitize($_POST['start_time']);
        $endTime = sanitize($_POST['end_time']);
        $capacity = (int)$_POST['max_capacity'];

        $stmt = $conn->prepare("INSERT INTO gym_classes (class_name, description, trainer_id, schedule_day, start_time, end_time, max_capacity) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssi", $name, $desc, $trainerId, $day, $startTime, $endTime, $capacity);
        $success = $stmt->execute() ? "Class added!" : "Failed.";
    }

    if ($_POST['action'] === 'edit_class') {
        $id = (int)$_POST['class_id'];
        $name = sanitize($_POST['class_name']);
        $desc = sanitize($_POST['description']);
        $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;
        $day = sanitize($_POST['schedule_day']);
        $startTime = sanitize($_POST['start_time']);
        $endTime = sanitize($_POST['end_time']);
        $capacity = (int)$_POST['max_capacity'];
        $active = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE gym_classes SET class_name=?, description=?, trainer_id=?, schedule_day=?, start_time=?, end_time=?, max_capacity=?, is_active=? WHERE id=?");
        $stmt->bind_param("ssisssiii", $name, $desc, $trainerId, $day, $startTime, $endTime, $capacity, $active, $id);
        $success = $stmt->execute() ? "Class updated!" : "Failed.";
    }
}

if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM gym_classes WHERE id = " . (int)$_GET['delete']);
    $success = "Class deleted.";
}

$classes = $conn->query("
    SELECT gc.*, t.first_name as trainer_fname, t.last_name as trainer_lname,
    (SELECT COUNT(*) FROM class_enrollments ce WHERE ce.class_id = gc.id) as enrolled
    FROM gym_classes gc
    LEFT JOIN trainers t ON gc.trainer_id = t.id
    ORDER BY FIELD(gc.schedule_day, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday'), gc.start_time
");

$trainers = $conn->query("SELECT * FROM trainers WHERE status='active'");
$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

// Pre-load enrollments: class_id → array of member names + join date
$enrollmentData = [];
$enRes = $conn->query("
    SELECT ce.class_id,
           CONCAT(m.first_name, ' ', m.last_name) AS member_name,
           m.phone,
           ce.enrolled_at
    FROM class_enrollments ce
    JOIN members m ON ce.member_id = m.id
    ORDER BY ce.enrolled_at DESC
");
while ($row = $enRes->fetch_assoc()) {
    $enrollmentData[$row['class_id']][] = [
        'name'        => $row['member_name'],
        'phone'       => $row['phone'],
        'enrolled_at' => $row['enrolled_at'],
    ];
}
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
                <h2 class="page-title">CLASS SCHEDULING</h2>
                <div class="top-header-actions">
                    <button class="btn btn-primary btn-sm" onclick="openModal('addClassModal')">+ Add Class</button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

                <div class="card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Trainer</th>
                                    <th>Capacity</th>
                                    <th>Enrolled</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($c = $classes->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($c['class_name']) ?></strong></td>
                                        <td><?= ucfirst($c['schedule_day']) ?></td>
                                        <td><?= date('h:i A', strtotime($c['start_time'])) ?> - <?= date('h:i A', strtotime($c['end_time'])) ?></td>
                                        <td><?= $c['trainer_fname'] ? htmlspecialchars($c['trainer_fname'] . ' ' . $c['trainer_lname']) : '-' ?></td>
                                        <td><?= $c['max_capacity'] ?></td>
                                        <td>
                                            <?php
                                            $members = $enrollmentData[$c['id']] ?? [];
                                            $count   = count($members);
                                            $dataJson = htmlspecialchars(json_encode($members), ENT_QUOTES);
                                            ?>
                                            <button class="btn btn-sm <?= $count > 0 ? 'btn-primary' : 'btn-outline' ?>"
                                                    onclick="viewEnrolled(<?= $c['id'] ?>, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>', this)"
                                                    data-members="<?= $dataJson ?>"
                                                    title="<?= $count > 0 ? 'View enrolled members' : 'No members enrolled' ?>">
                                                👥 <?= $count ?>/<?= $c['max_capacity'] ?>
                                            </button>
                                        </td>
                                        <td><span class="badge-status badge-<?= $c['is_active'] ? 'active' : 'inactive' ?>"><?= $c['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-secondary" onclick='editClass(<?= json_encode($c) ?>)'>Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('classes.php?delete=<?= $c['id'] ?>', '<?= htmlspecialchars($c['class_name']) ?>')">Delete</button>
                                            </div>
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

    <!-- Add Class Modal -->
    <div class="modal-overlay" id="addClassModal">
        <div class="modal">
            <div class="modal-header"><h3>Add Class</h3><button class="modal-close" onclick="closeModal('addClassModal')">&times;</button></div>
            <form method="POST">
                <input type="hidden" name="action" value="add_class">
                <div class="modal-body">
                    <div class="form-group"><label>Class Name *</label><input type="text" name="class_name" class="form-control" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="form-row">
                        <div class="form-group"><label>Day *</label>
                            <select name="schedule_day" class="form-control" required>
                                <?php foreach ($days as $d): ?><option value="<?= $d ?>"><?= ucfirst($d) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label>Trainer</label>
                            <select name="trainer_id" class="form-control">
                                <option value="">-- None --</option>
                                <?php $trainers->data_seek(0); while ($t = $trainers->fetch_assoc()): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Start Time *</label><input type="time" name="start_time" class="form-control" required></div>
                        <div class="form-group"><label>End Time *</label><input type="time" name="end_time" class="form-control" required></div>
                    </div>
                    <div class="form-group"><label>Max Capacity</label><input type="number" name="max_capacity" class="form-control" value="20" min="1"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addClassModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Class</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div class="modal-overlay" id="editClassModal">
        <div class="modal">
            <div class="modal-header"><h3>Edit Class</h3><button class="modal-close" onclick="closeModal('editClassModal')">&times;</button></div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_class">
                <input type="hidden" name="class_id" id="ec_id">
                <div class="modal-body">
                    <div class="form-group"><label>Class Name</label><input type="text" name="class_name" id="ec_name" class="form-control" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description" id="ec_desc" class="form-control" rows="2"></textarea></div>
                    <div class="form-row">
                        <div class="form-group"><label>Day</label>
                            <select name="schedule_day" id="ec_day" class="form-control">
                                <?php foreach ($days as $d): ?><option value="<?= $d ?>"><?= ucfirst($d) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label>Trainer</label>
                            <select name="trainer_id" id="ec_trainer" class="form-control">
                                <option value="">-- None --</option>
                                <?php $trainers->data_seek(0); while ($t = $trainers->fetch_assoc()): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Start Time</label><input type="time" name="start_time" id="ec_start" class="form-control"></div>
                        <div class="form-group"><label>End Time</label><input type="time" name="end_time" id="ec_end" class="form-control"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Max Capacity</label><input type="number" name="max_capacity" id="ec_cap" class="form-control"></div>
                        <div class="form-group"><label>Status</label>
                            <div class="form-check mt-1"><input type="checkbox" name="is_active" id="ec_active" value="1"><label>Active</label></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editClassModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enrolled Members Modal -->
    <div class="modal-overlay" id="enrolledModal">
        <div class="modal" style="max-width:520px;">
            <div class="modal-header">
                <h3 id="enrolledModalTitle">Enrolled Members</h3>
                <button class="modal-close" onclick="closeModal('enrolledModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding:0;">
                <div id="enrolledModalBody" style="min-height:80px;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('enrolledModal')">Close</button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script>
        function viewEnrolled(classId, className, btn) {
            var members = JSON.parse(btn.getAttribute('data-members') || '[]');
            document.getElementById('enrolledModalTitle').textContent = className + ' — Enrolled Members';

            var body = document.getElementById('enrolledModalBody');
            if (members.length === 0) {
                body.innerHTML = '<div style="text-align:center;padding:40px 20px;color:var(--text-secondary);">No members enrolled in this class yet.</div>';
            } else {
                var rows = members.map(function(m, i) {
                    var date = m.enrolled_at ? m.enrolled_at.split(' ')[0] : '—';
                    return '<tr>' +
                        '<td style="padding:10px 16px;color:var(--text-secondary);font-size:0.82rem;">' + (i+1) + '</td>' +
                        '<td style="padding:10px 16px;font-weight:600;">' + escHtml(m.name) + '</td>' +
                        '<td style="padding:10px 16px;color:var(--text-secondary);">' + escHtml(m.phone || '—') + '</td>' +
                        '<td style="padding:10px 16px;color:var(--text-secondary);font-size:0.82rem;">' + date + '</td>' +
                    '</tr>';
                }).join('');
                body.innerHTML =
                    '<table style="width:100%;border-collapse:collapse;">' +
                    '<thead><tr style="background:var(--bg-sidebar);font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;">' +
                        '<th style="padding:10px 16px;text-align:left;color:var(--text-secondary);">#</th>' +
                        '<th style="padding:10px 16px;text-align:left;color:var(--text-secondary);">Member Name</th>' +
                        '<th style="padding:10px 16px;text-align:left;color:var(--text-secondary);">Phone</th>' +
                        '<th style="padding:10px 16px;text-align:left;color:var(--text-secondary);">Enrolled On</th>' +
                    '</tr></thead>' +
                    '<tbody>' + rows + '</tbody>' +
                    '</table>';
            }
            openModal('enrolledModal');
        }

        function escHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function editClass(d) {
            document.getElementById('ec_id').value = d.id;
            document.getElementById('ec_name').value = d.class_name;
            document.getElementById('ec_desc').value = d.description || '';
            document.getElementById('ec_day').value = d.schedule_day;
            document.getElementById('ec_trainer').value = d.trainer_id || '';
            document.getElementById('ec_start').value = d.start_time;
            document.getElementById('ec_end').value = d.end_time;
            document.getElementById('ec_cap').value = d.max_capacity;
            document.getElementById('ec_active').checked = d.is_active == 1;
            openModal('editClassModal');
        }
    </script>
</body>
</html>
