<?php
define('CURRENT_PAGE', 'members');
require_once '../../includes/auth.php';
requireAdmin();

$error = '';
$success = '';

// Handle Add Member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_member') {
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $address = sanitize($_POST['address']);
        $age = (int)$_POST['age'];
        $gender = sanitize($_POST['gender']);
        $weight = (float)$_POST['weight'];
        $height = (float)$_POST['height'];
        $emergName = sanitize($_POST['emergency_contact_name']);
        $emergPhone = sanitize($_POST['emergency_contact_phone']);
        $joinDate = sanitize($_POST['join_date']);
        $planId = (int)$_POST['plan_id'];
        $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;

        // Create user account
        $username = strtolower($firstName . '_' . substr(uniqid(), -4));
        $defaultPassword = 'member123';
        $regResult = registerUser($username, $email, $defaultPassword, 'member');

        if ($regResult['success']) {
            $userId = $regResult['user_id'];

            // Handle photo upload
            $photo = null;
            if (!empty($_FILES['photo']['name'])) {
                if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $photo = 'member_' . $userId . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_PATH . $photo);
            }

            $stmt = $conn->prepare("INSERT INTO members (user_id, first_name, last_name, phone, email, address, age, gender, weight, height, photo, emergency_contact_name, emergency_contact_phone, join_date, assigned_trainer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssissdssss" . ($trainerId ? "i" : "i"), $userId, $firstName, $lastName, $phone, $email, $address, $age, $gender, $weight, $height, $photo, $emergName, $emergPhone, $joinDate, $trainerId);

            if ($stmt->execute()) {
                $memberId = $stmt->insert_id;

                // Create membership if plan selected
                if ($planId > 0) {
                    $plan = $conn->query("SELECT duration_months, price FROM membership_plans WHERE id = $planId")->fetch_assoc();
                    $startDate = $joinDate;
                    $endDate = date('Y-m-d', strtotime("+{$plan['duration_months']} months", strtotime($startDate)));

                    $stmt2 = $conn->prepare("INSERT INTO member_memberships (member_id, plan_id, start_date, end_date) VALUES (?, ?, ?, ?)");
                    $stmt2->bind_param("iiss", $memberId, $planId, $startDate, $endDate);
                    $stmt2->execute();
                }

                $success = "Member added successfully! Username: $username, Password: $defaultPassword";
            } else {
                $error = "Failed to add member.";
            }
        } else {
            $error = $regResult['message'];
        }
    }

    if ($_POST['action'] === 'edit_member') {
        $memberId = (int)$_POST['member_id'];
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $address = sanitize($_POST['address']);
        $age = (int)$_POST['age'];
        $gender = sanitize($_POST['gender']);
        $weight = (float)$_POST['weight'];
        $height = (float)$_POST['height'];
        $status = sanitize($_POST['status']);
        $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;

        $stmt = $conn->prepare("UPDATE members SET first_name=?, last_name=?, phone=?, email=?, address=?, age=?, gender=?, weight=?, height=?, status=?, assigned_trainer_id=? WHERE id=?");
        $stmt->bind_param("sssssissdsii", $firstName, $lastName, $phone, $email, $address, $age, $gender, $weight, $height, $status, $trainerId, $memberId);

        if ($stmt->execute()) {
            // Handle photo upload on edit
            if (!empty($_FILES['photo']['name'])) {
                if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $photo = 'member_' . $memberId . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_PATH . $photo);
                $conn->query("UPDATE members SET photo = '$photo' WHERE id = $memberId");
            }
            $success = "Member updated successfully!";
        } else {
            $error = "Failed to update member.";
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $member = $conn->query("SELECT user_id FROM members WHERE id = $id")->fetch_assoc();
    if ($member) {
        $conn->query("DELETE FROM users WHERE id = {$member['user_id']}");
        $success = "Member deleted successfully.";
    }
}

// Fetch filters
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$searchQuery = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$sql = "SELECT m.*, t.first_name as trainer_fname, t.last_name as trainer_lname FROM members m LEFT JOIN trainers t ON m.assigned_trainer_id = t.id WHERE 1=1";
if ($statusFilter) $sql .= " AND m.status = '$statusFilter'";
if ($searchQuery) $sql .= " AND (m.first_name LIKE '%$searchQuery%' OR m.last_name LIKE '%$searchQuery%' OR m.phone LIKE '%$searchQuery%' OR m.email LIKE '%$searchQuery%')";
$sql .= " ORDER BY m.created_at DESC";

$members = $conn->query($sql);
$plans = $conn->query("SELECT * FROM membership_plans WHERE is_active = 1");
$trainers = $conn->query("SELECT * FROM trainers WHERE status = 'active'");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">MEMBERS</h2>
                <div class="top-header-actions">
                    <button class="btn btn-primary btn-sm" onclick="openModal('addMemberModal')">+ Add Member</button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($error): ?>
                    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Search & Filters -->
                <form method="GET" class="search-bar">
                    <input type="text" name="search" class="search-input" placeholder="Search members..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="expired" <?= $statusFilter === 'expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm">Search</button>
                </form>

                <!-- Members Table -->
                <div class="card">
                    <div class="table-container">
                        <table class="data-table" id="membersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Gender</th>
                                    <th>Join Date</th>
                                    <th>Trainer</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($members->num_rows > 0): ?>
                                    <?php $i = 1; while ($m = $members->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td>
                                                <a href="member-profile.php?id=<?= $m['id'] ?>" class="link" style="font-weight:600;">
                                                    <?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($m['phone']) ?></td>
                                            <td><?= htmlspecialchars($m['email']) ?></td>
                                            <td><?= ucfirst($m['gender']) ?></td>
                                            <td><?= date('d M Y', strtotime($m['join_date'])) ?></td>
                                            <td><?= $m['trainer_fname'] ? htmlspecialchars($m['trainer_fname'] . ' ' . $m['trainer_lname']) : '-' ?></td>
                                            <td><span class="badge-status badge-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="member-profile.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">View</a>
                                                    <button class="btn btn-sm btn-secondary" onclick="editMember(<?= htmlspecialchars(json_encode($m)) ?>)">Edit</button>
                                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete('members.php?delete=<?= $m['id'] ?>', '<?= htmlspecialchars($m['first_name']) ?>')">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center text-muted" style="padding:40px;">No members found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div class="modal-overlay" id="addMemberModal">
        <div class="modal" style="width:700px;">
            <div class="modal-header">
                <h3>Add New Member</h3>
                <button class="modal-close" onclick="closeModal('addMemberModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_member">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" name="age" class="form-control" min="10" max="80">
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" name="weight" class="form-control" step="0.1">
                        </div>
                        <div class="form-group">
                            <label>Height (cm)</label>
                            <input type="number" name="height" class="form-control" step="0.1">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Join Date *</label>
                            <input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Membership Plan</label>
                            <select name="plan_id" class="form-control">
                                <option value="0">-- Select Plan --</option>
                                <?php $plans->data_seek(0); while ($p = $plans->fetch_assoc()): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['plan_name']) ?> - ₹<?= number_format($p['price']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Assign Trainer</label>
                            <select name="trainer_id" class="form-control">
                                <option value="">-- No Trainer --</option>
                                <?php $trainers->data_seek(0); while ($t = $trainers->fetch_assoc()): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addMemberModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Member</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Member Modal -->
    <div class="modal-overlay" id="editMemberModal">
        <div class="modal" style="width:700px;">
            <div class="modal-header">
                <h3>Edit Member</h3>
                <button class="modal-close" onclick="closeModal('editMemberModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_member">
                <input type="hidden" name="member_id" id="edit_member_id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" name="age" id="edit_age" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" id="edit_gender" class="form-control">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" name="weight" id="edit_weight" class="form-control" step="0.1">
                        </div>
                        <div class="form-group">
                            <label>Height (cm)</label>
                            <input type="number" name="height" id="edit_height" class="form-control" step="0.1">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Assign Trainer</label>
                            <select name="trainer_id" id="edit_trainer_id" class="form-control">
                                <option value="">-- No Trainer --</option>
                                <?php $trainers->data_seek(0); while ($t = $trainers->fetch_assoc()): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Photo (leave empty to keep current)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editMemberModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Member</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script>
        function editMember(data) {
            document.getElementById('edit_member_id').value = data.id;
            document.getElementById('edit_first_name').value = data.first_name;
            document.getElementById('edit_last_name').value = data.last_name;
            document.getElementById('edit_phone').value = data.phone;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_address').value = data.address || '';
            document.getElementById('edit_age').value = data.age;
            document.getElementById('edit_gender').value = data.gender;
            document.getElementById('edit_weight').value = data.weight;
            document.getElementById('edit_height').value = data.height;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_trainer_id').value = data.assigned_trainer_id || '';
            openModal('editMemberModal');
        }
    </script>
</body>
</html>
