<?php
define('CURRENT_PAGE', 'trainers');
require_once '../../includes/auth.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_trainer') {
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $specialization = sanitize($_POST['specialization']);
        $experience = (int)$_POST['experience_years'];
        $bio = sanitize($_POST['bio']);

        $username = 'trainer_' . strtolower($firstName) . '_' . substr(uniqid(), -3);
        $regResult = registerUser($username, $email, 'trainer123', 'trainer');

        if ($regResult['success']) {
            $userId = $regResult['user_id'];
            $stmt = $conn->prepare("INSERT INTO trainers (user_id, first_name, last_name, phone, email, specialization, experience_years, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssis", $userId, $firstName, $lastName, $phone, $email, $specialization, $experience, $bio);

            if ($stmt->execute()) {
                $trainerId = $stmt->insert_id;
                if (!empty($_FILES['photo']['name'])) {
                    $ext   = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $photo = 'trainer_' . $trainerId . '.' . $ext;
                    if (!is_dir(UPLOAD_PATH_TRAINERS)) mkdir(UPLOAD_PATH_TRAINERS, 0755, true);
                    move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_PATH_TRAINERS . $photo);
                    $conn->query("UPDATE trainers SET photo='$photo' WHERE id=$trainerId");
                }
                $success = "Trainer added! Username: $username, Password: trainer123";
            } else {
                $error = "Failed to add trainer.";
            }
        } else {
            $error = $regResult['message'];
        }
    }

    if ($_POST['action'] === 'edit_trainer') {
        $id = (int)$_POST['trainer_id'];
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);
        $specialization = sanitize($_POST['specialization']);
        $experience = (int)$_POST['experience_years'];
        $bio = sanitize($_POST['bio']);
        $status = sanitize($_POST['status']);

        $stmt = $conn->prepare("UPDATE trainers SET first_name=?, last_name=?, phone=?, specialization=?, experience_years=?, bio=?, status=? WHERE id=?");
        $stmt->bind_param("ssssissi", $firstName, $lastName, $phone, $specialization, $experience, $bio, $status, $id);

        if ($stmt->execute()) {
            if (!empty($_FILES['photo']['name'])) {
                $ext   = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $photo = 'trainer_' . $id . '.' . $ext;
                if (!is_dir(UPLOAD_PATH_TRAINERS)) mkdir(UPLOAD_PATH_TRAINERS, 0755, true);
                move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_PATH_TRAINERS . $photo);
                $conn->query("UPDATE trainers SET photo='$photo' WHERE id=$id");
            }
            $success = "Trainer updated successfully!";
        } else {
            $error = "Failed to update trainer.";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $trainer = $conn->query("SELECT user_id FROM trainers WHERE id = $id")->fetch_assoc();
    if ($trainer) {
        $conn->query("DELETE FROM users WHERE id = {$trainer['user_id']}");
        $success = "Trainer deleted.";
    }
}

$trainers = $conn->query("SELECT t.*, (SELECT COUNT(*) FROM members m WHERE m.assigned_trainer_id = t.id) as member_count FROM trainers t ORDER BY t.created_at DESC");
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainers - FitZone</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <h2 class="page-title">TRAINERS</h2>
                <div class="top-header-actions">
                    <button class="btn btn-primary btn-sm" onclick="openModal('addTrainerModal')">+ Add Trainer</button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

                <div class="card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Specialization</th>
                                    <th>Experience</th>
                                    <th>Members</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; while ($t = $trainers->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td>
                                            <?php if ($t['photo']): ?>
                                                <img src="../../uploads/trainers/<?= htmlspecialchars($t['photo']) ?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--border);">
                                            <?php else: ?>
                                                <div style="width:38px;height:38px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;"><?= strtoupper(substr($t['first_name'],0,1)) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($t['phone']) ?></td>
                                        <td><?= htmlspecialchars($t['email']) ?></td>
                                        <td><?= htmlspecialchars($t['specialization'] ?: '-') ?></td>
                                        <td><?= $t['experience_years'] ?> yrs</td>
                                        <td><?= $t['member_count'] ?></td>
                                        <td><span class="badge-status badge-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-secondary" onclick='editTrainer(<?= json_encode($t) ?>)'>Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('trainers.php?delete=<?= $t['id'] ?>', '<?= htmlspecialchars($t['first_name']) ?>')">Delete</button>
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

    <!-- Add Trainer Modal -->
    <div class="modal-overlay" id="addTrainerModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Trainer</h3>
                <button class="modal-close" onclick="closeModal('addTrainerModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_trainer">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group"><label>First Name *</label><input type="text" name="first_name" class="form-control" required></div>
                        <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" class="form-control" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Phone *</label><input type="text" name="phone" class="form-control" required></div>
                        <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Specialization</label><input type="text" name="specialization" class="form-control" placeholder="e.g. Yoga, Weight Training"></div>
                        <div class="form-group"><label>Experience (years)</label><input type="number" name="experience_years" class="form-control" min="0"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Bio</label><textarea name="bio" class="form-control" rows="3"></textarea></div>
                        <div class="form-group"><label>Photo</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addTrainerModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Trainer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Trainer Modal -->
    <div class="modal-overlay" id="editTrainerModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Trainer</h3>
                <button class="modal-close" onclick="closeModal('editTrainerModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_trainer">
                <input type="hidden" name="trainer_id" id="et_id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group"><label>First Name</label><input type="text" name="first_name" id="et_fname" class="form-control" required></div>
                        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" id="et_lname" class="form-control" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Phone</label><input type="text" name="phone" id="et_phone" class="form-control" required></div>
                        <div class="form-group"><label>Specialization</label><input type="text" name="specialization" id="et_spec" class="form-control"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Experience</label><input type="number" name="experience_years" id="et_exp" class="form-control"></div>
                        <div class="form-group"><label>Status</label>
                            <select name="status" id="et_status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Bio</label><textarea name="bio" id="et_bio" class="form-control" rows="3"></textarea></div>
                        <div class="form-group"><label>Update Photo</label><input type="file" name="photo" class="form-control" accept="image/*"><small style="color:var(--text-secondary);font-size:0.78rem;">Leave empty to keep current photo</small></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editTrainerModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script>
        function editTrainer(d) {
            document.getElementById('et_id').value = d.id;
            document.getElementById('et_fname').value = d.first_name;
            document.getElementById('et_lname').value = d.last_name;
            document.getElementById('et_phone').value = d.phone;
            document.getElementById('et_spec').value = d.specialization || '';
            document.getElementById('et_exp').value = d.experience_years;
            document.getElementById('et_bio').value = d.bio || '';
            document.getElementById('et_status').value = d.status;
            openModal('editTrainerModal');
        }
    </script>
</body>
</html>
