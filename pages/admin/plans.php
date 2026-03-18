<?php
define('CURRENT_PAGE', 'plans');
require_once '../../includes/auth.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_plan') {
        $name = sanitize($_POST['plan_name']);
        $duration = (int)$_POST['duration_months'];
        $price = (float)$_POST['price'];
        $desc = sanitize($_POST['description']);

        $stmt = $conn->prepare("INSERT INTO membership_plans (plan_name, duration_months, price, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sids", $name, $duration, $price, $desc);
        $success = $stmt->execute() ? "Plan added!" : "Failed.";
    }

    if ($_POST['action'] === 'edit_plan') {
        $id = (int)$_POST['plan_id'];
        $name = sanitize($_POST['plan_name']);
        $duration = (int)$_POST['duration_months'];
        $price = (float)$_POST['price'];
        $desc = sanitize($_POST['description']);
        $active = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE membership_plans SET plan_name=?, duration_months=?, price=?, description=?, is_active=? WHERE id=?");
        $stmt->bind_param("sidsii", $name, $duration, $price, $desc, $active, $id);
        $success = $stmt->execute() ? "Plan updated!" : "Failed.";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM membership_plans WHERE id = $id");
    $success = "Plan deleted.";
}

$plans = $conn->query("SELECT mp.*, (SELECT COUNT(*) FROM member_memberships mm WHERE mm.plan_id = mp.id AND mm.status = 'active') as active_subs FROM membership_plans mp ORDER BY mp.price ASC");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plans - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">MEMBERSHIP PLANS</h2>
                <div class="top-header-actions">
                    <button class="btn btn-primary btn-sm" onclick="openModal('addPlanModal')">+ Add Plan</button>
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
                                    <th>Plan Name</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Active Subscribers</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($p = $plans->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($p['plan_name']) ?></strong></td>
                                        <td><?= $p['duration_months'] ?> month<?= $p['duration_months'] > 1 ? 's' : '' ?></td>
                                        <td><strong>₹<?= number_format($p['price']) ?></strong></td>
                                        <td><?= $p['active_subs'] ?></td>
                                        <td><span class="badge-status badge-<?= $p['is_active'] ? 'active' : 'inactive' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-secondary" onclick='editPlan(<?= json_encode($p) ?>)'>Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('plans.php?delete=<?= $p['id'] ?>', '<?= htmlspecialchars($p['plan_name']) ?>')">Delete</button>
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

    <!-- Add Plan Modal -->
    <div class="modal-overlay" id="addPlanModal">
        <div class="modal">
            <div class="modal-header"><h3>Add Plan</h3><button class="modal-close" onclick="closeModal('addPlanModal')">&times;</button></div>
            <form method="POST">
                <input type="hidden" name="action" value="add_plan">
                <div class="modal-body">
                    <div class="form-group"><label>Plan Name *</label><input type="text" name="plan_name" class="form-control" required></div>
                    <div class="form-row">
                        <div class="form-group"><label>Duration (months) *</label><input type="number" name="duration_months" class="form-control" min="1" required></div>
                        <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" class="form-control" min="0" step="0.01" required></div>
                    </div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addPlanModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Plan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Plan Modal -->
    <div class="modal-overlay" id="editPlanModal">
        <div class="modal">
            <div class="modal-header"><h3>Edit Plan</h3><button class="modal-close" onclick="closeModal('editPlanModal')">&times;</button></div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_plan">
                <input type="hidden" name="plan_id" id="ep_id">
                <div class="modal-body">
                    <div class="form-group"><label>Plan Name</label><input type="text" name="plan_name" id="ep_name" class="form-control" required></div>
                    <div class="form-row">
                        <div class="form-group"><label>Duration (months)</label><input type="number" name="duration_months" id="ep_dur" class="form-control" required></div>
                        <div class="form-group"><label>Price (₹)</label><input type="number" name="price" id="ep_price" class="form-control" step="0.01" required></div>
                    </div>
                    <div class="form-group"><label>Description</label><textarea name="description" id="ep_desc" class="form-control" rows="3"></textarea></div>
                    <div class="form-check"><input type="checkbox" name="is_active" id="ep_active" value="1"><label>Active</label></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editPlanModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script>
        function editPlan(d) {
            document.getElementById('ep_id').value = d.id;
            document.getElementById('ep_name').value = d.plan_name;
            document.getElementById('ep_dur').value = d.duration_months;
            document.getElementById('ep_price').value = d.price;
            document.getElementById('ep_desc').value = d.description || '';
            document.getElementById('ep_active').checked = d.is_active == 1;
            openModal('editPlanModal');
        }
    </script>
</body>
</html>
