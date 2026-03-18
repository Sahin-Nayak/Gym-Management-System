<?php
define('CURRENT_PAGE', 'athlete-facts');
require_once '../../includes/auth.php';
requireAdmin();

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS athlete_facts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    icon VARCHAR(10) DEFAULT '💡',
    title VARCHAR(120) NOT NULL,
    fact_text TEXT NOT NULL,
    category VARCHAR(60) DEFAULT 'General',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed default facts if empty
$cnt = $conn->query("SELECT COUNT(*) as c FROM athlete_facts")->fetch_assoc()['c'];
if ($cnt == 0) {
    $defaults = [
        ['🫀','Heart Beats 100,000x Daily','Your heart beats approximately 100,000 times per day, pumping about 2,000 gallons of blood throughout your body.','Cardio',1],
        ['💪','Muscle vs Fat Calorie Burn','Muscle tissue burns 3 times more calories than fat tissue, even while at rest. More muscle = faster metabolism.','Strength',2],
        ['🧠','Exercise Boosts Brain Power','30 minutes of cardio increases BDNF (brain-derived neurotrophic factor) by up to 200%, improving memory and focus.','Science',3],
        ['🦴','Bones Respond to Resistance','Weight-bearing exercise increases bone density and can reduce osteoporosis risk by up to 40% over a lifetime.','Strength',4],
        ['😴','Sleep Accelerates Gains','80% of muscle repair and growth happens during deep sleep. 7–9 hours per night is essential for peak performance.','Recovery',5],
        ['🔥','EPOC — Afterburn Effect','High-intensity training keeps your metabolism elevated for up to 38 hours post-workout, burning extra calories while you rest.','Cardio',6],
        ['💧','Dehydration Cuts Performance','Even 2% dehydration reduces athletic performance by up to 25%. Drink water before, during, and after every session.','Nutrition',7],
        ['🧬','Muscle Memory Is Real','Once you build muscle, the nuclei in muscle cells remain even after you stop training — making it faster to rebuild.','Science',8],
        ['🥗','Protein Timing Matters','Consuming 20–40g of protein within 30 minutes post-workout maximizes muscle protein synthesis by up to 50%.','Nutrition',9],
    ];
    $stmt = $conn->prepare("INSERT INTO athlete_facts (icon,title,fact_text,category,sort_order) VALUES (?,?,?,?,?)");
    foreach ($defaults as $d) { $stmt->bind_param("ssssi",$d[0],$d[1],$d[2],$d[3],$d[4]); $stmt->execute(); }
}

// Handle actions
$msg = ''; $msgType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
    $id     = (int)($_POST['id'] ?? 0);
    $icon   = trim($_POST['icon'] ?? '💡');
    $title  = trim($_POST['title'] ?? '');
    $text   = trim($_POST['fact_text'] ?? '');
    $cat    = trim($_POST['category'] ?? 'General');
    $sort   = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if (!$title || !$text) { $msg = 'Title and fact text are required.'; $msgType = 'danger'; }
    else {
        $icon  = $conn->real_escape_string($icon);
        $title = $conn->real_escape_string($title);
        $text  = $conn->real_escape_string($text);
        $cat   = $conn->real_escape_string($cat);

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO athlete_facts (icon,title,fact_text,category,sort_order,is_active) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssssii", $icon, $title, $text, $cat, $sort, $active);
            $stmt->execute();
            $msg = 'Fact added successfully!';
        } else {
            $stmt = $conn->prepare("UPDATE athlete_facts SET icon=?,title=?,fact_text=?,category=?,sort_order=?,is_active=? WHERE id=?");
            $stmt->bind_param("ssssiii", $icon, $title, $text, $cat, $sort, $active, $id);
            $stmt->execute();
            $msg = 'Fact updated!';
        }
    }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("DELETE FROM athlete_facts WHERE id=$id");
        $msg = 'Fact deleted.'; $msgType = 'warning';
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("UPDATE athlete_facts SET is_active = 1 - is_active WHERE id=$id");
        header('Location: athlete-facts.php'); exit;
    }
}

$facts = $conn->query("SELECT * FROM athlete_facts ORDER BY sort_order ASC, id ASC");
$categories = ['General','Cardio','Strength','Nutrition','Science','Recovery'];
$editFact = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $r = $conn->query("SELECT * FROM athlete_facts WHERE id=$eid");
    if ($r && $r->num_rows) $editFact = $r->fetch_assoc();
}
?>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Athlete Facts — FitZone Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<div class="app-layout">
<?php include '../../includes/sidebar.php'; ?>
<div class="main-content">
    <header class="top-header">
        <button class="hamburger" onclick="toggleSidebar()">☰</button>
        <h2 class="page-title">ATHLETE FACTS</h2>
        <div class="top-header-actions">
            <a href="?add=1" class="btn btn-primary btn-sm">+ Add Fact</a>
            <a href="../../pages/website/facts.php" target="_blank" class="btn btn-primary btn-sm">🌐 View Facts ↗</a>
        </div>
    </header>
    <div class="page-content">

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?>" style="margin-bottom:20px"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Add / Edit Form -->
    <?php if (isset($_GET['add']) || $editFact): ?>
    <div class="card" style="margin-bottom:28px;">
        <h3 class="card-title"><?= $editFact ? 'Edit Fact' : 'Add New Fact' ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editFact ? 'edit' : 'add' ?>">
            <?php if ($editFact): ?><input type="hidden" name="id" value="<?= $editFact['id'] ?>"><?php endif; ?>
            <div style="display:grid;grid-template-columns:80px 1fr 1fr 80px;gap:16px;margin-bottom:16px;">
                <div class="form-group" style="margin:0">
                    <label>Icon</label>
                    <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($editFact['icon'] ?? '💡') ?>" maxlength="10">
                </div>
                <div class="form-group" style="margin:0">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editFact['title'] ?? '') ?>" required>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Category</label>
                    <select name="category" class="form-control">
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c ?>" <?= ($editFact['category'] ?? 'General') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Sort</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= (int)($editFact['sort_order'] ?? 0) ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Fact Text *</label>
                <textarea name="fact_text" class="form-control" rows="3" required><?= htmlspecialchars($editFact['fact_text'] ?? '') ?></textarea>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:0.88rem;cursor:pointer;">
                    <input type="checkbox" name="is_active" <?= ($editFact['is_active'] ?? 1) ? 'checked' : '' ?>> Active (show on website)
                </label>
                <button type="submit" class="btn btn-primary">Save Fact</button>
                <a href="athlete-facts.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Facts Table -->
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Icon</th><th>Title</th><th>Category</th><th>Preview</th><th>Sort</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $facts->data_seek(0);
                while ($f = $facts->fetch_assoc()):
                ?>
                <tr>
                    <td style="font-size:1.5rem;text-align:center"><?= htmlspecialchars($f['icon']) ?></td>
                    <td style="font-weight:600"><?= htmlspecialchars($f['title']) ?></td>
                    <td><span style="background:var(--primary-alpha);color:var(--primary);padding:3px 10px;border-radius:50px;font-size:0.72rem;font-weight:700"><?= htmlspecialchars($f['category']) ?></span></td>
                    <td style="font-size:0.82rem;color:var(--text-secondary);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars(substr($f['fact_text'],0,80)) ?>…</td>
                    <td style="text-align:center"><?= $f['sort_order'] ?></td>
                    <td>
                        <form method="POST" style="margin:0">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                            <button type="submit" class="badge <?= $f['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer;border:none;padding:4px 10px;">
                                <?= $f['is_active'] ? 'Active' : 'Hidden' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div style="display:flex;gap:8px;">
                            <a href="?edit=<?= $f['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <form method="POST" style="margin:0" onsubmit="return confirm('Delete this fact?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    </div><!-- /page-content -->
</div><!-- /main-content -->
</div><!-- /app-layout -->
<script src="../../assets/js/main.js"></script>
</body></html>
