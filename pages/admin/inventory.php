<?php
define('CURRENT_PAGE', 'inventory');
require_once '../../includes/auth.php';
requireAdmin();

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS gym_inventory (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    serial_number   VARCHAR(100),
    category        VARCHAR(80),
    quantity        INT DEFAULT 1,
    purchase_date   DATE,
    purchase_price  DECIMAL(10,2),
    status          ENUM('working','not_working','under_maintenance') DEFAULT 'working',
    notes           TEXT,
    image           VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$uploadDir = __DIR__ . '/../../uploads/equipment/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$error = '';
$success = '';

// ── Handle POST actions ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Helper: save uploaded image
    function saveEquipmentImage($field, $prefix) {
        global $uploadDir;
        if (!empty($_FILES[$field]['name'])) {
            $ext  = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed)) {
                $filename = $prefix . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $filename);
                return $filename;
            }
        }
        return null;
    }

    if ($_POST['action'] === 'add') {
        $name     = sanitize($_POST['name']);
        $serial   = sanitize($_POST['serial_number']);
        $category = sanitize($_POST['category']);
        $qty      = (int)$_POST['quantity'];
        $pdate    = !empty($_POST['purchase_date']) ? sanitize($_POST['purchase_date']) : null;
        $price    = !empty($_POST['purchase_price']) ? (float)$_POST['purchase_price'] : null;
        $status   = sanitize($_POST['status']);
        $notes    = sanitize($_POST['notes']);
        $image    = saveEquipmentImage('image', 'equip');

        $stmt = $conn->prepare("INSERT INTO gym_inventory (name, serial_number, category, quantity, purchase_date, purchase_price, status, notes, image) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssisssss", $name, $serial, $category, $qty, $pdate, $price, $status, $notes, $image);
        if ($stmt->execute()) {
            $success = "Equipment added successfully!";
        } else {
            $error = "Failed to add equipment.";
        }
    }

    if ($_POST['action'] === 'edit') {
        $id       = (int)$_POST['item_id'];
        $name     = sanitize($_POST['name']);
        $serial   = sanitize($_POST['serial_number']);
        $category = sanitize($_POST['category']);
        $qty      = (int)$_POST['quantity'];
        $pdate    = !empty($_POST['purchase_date']) ? sanitize($_POST['purchase_date']) : null;
        $price    = !empty($_POST['purchase_price']) ? (float)$_POST['purchase_price'] : null;
        $status   = sanitize($_POST['status']);
        $notes    = sanitize($_POST['notes']);

        // Check for new image
        $newImage = saveEquipmentImage('image', 'equip');
        if ($newImage) {
            // Delete old image
            $old = $conn->query("SELECT image FROM gym_inventory WHERE id=$id")->fetch_assoc();
            if ($old['image'] && file_exists($uploadDir . $old['image'])) {
                unlink($uploadDir . $old['image']);
            }
            $imgSql = ", image='$newImage'";
        } else {
            $imgSql = '';
        }

        $stmt = $conn->prepare("UPDATE gym_inventory SET name=?, serial_number=?, category=?, quantity=?, purchase_date=?, purchase_price=?, status=?, notes=? $imgSql WHERE id=?");
        $stmt->bind_param("sssissssi", $name, $serial, $category, $qty, $pdate, $price, $status, $notes, $id);
        if ($stmt->execute()) {
            $success = "Equipment updated successfully!";
        } else {
            $error = "Failed to update equipment.";
        }
    }

    if ($_POST['action'] === 'delete') {
        $id  = (int)$_POST['item_id'];
        $old = $conn->query("SELECT image FROM gym_inventory WHERE id=$id")->fetch_assoc();
        if ($old && $old['image'] && file_exists($uploadDir . $old['image'])) {
            unlink($uploadDir . $old['image']);
        }
        $conn->query("DELETE FROM gym_inventory WHERE id=$id");
        $success = "Equipment deleted.";
    }
}

// ── Filter ─────────────────────────────────────────────────────────────────
$filter  = $_GET['filter'] ?? 'all';
$search  = trim($_GET['search'] ?? '');
$where   = [];
if ($filter === 'working')            $where[] = "status='working'";
elseif ($filter === 'not_working')    $where[] = "status='not_working'";
elseif ($filter === 'under_maintenance') $where[] = "status='under_maintenance'";
if ($search) $where[] = "(name LIKE '%".addslashes($search)."%' OR serial_number LIKE '%".addslashes($search)."%' OR category LIKE '%".addslashes($search)."%')";
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$items = $conn->query("SELECT * FROM gym_inventory $whereSql ORDER BY created_at DESC");

// Stats
$statsRes = $conn->query("SELECT status, COUNT(*) as c, SUM(quantity) as q FROM gym_inventory GROUP BY status");
$stats = ['working'=>['c'=>0,'q'=>0],'not_working'=>['c'=>0,'q'=>0],'under_maintenance'=>['c'=>0,'q'=>0]];
$totalItems = 0; $totalQty = 0;
while ($s = $statsRes->fetch_assoc()) {
    $stats[$s['status']] = ['c'=>(int)$s['c'],'q'=>(int)$s['q']];
    $totalItems += (int)$s['c'];
    $totalQty   += (int)$s['q'];
}

// Categories for dropdown
$catRes = $conn->query("SELECT DISTINCT category FROM gym_inventory WHERE category != '' ORDER BY category");
$existingCategories = [];
while ($c = $catRes->fetch_assoc()) $existingCategories[] = $c['category'];
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory — FitZone Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .equip-img { width:52px;height:52px;border-radius:10px;object-fit:cover;border:2px solid var(--border); }
        .equip-placeholder { width:52px;height:52px;border-radius:10px;background:var(--bg-surface);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.4rem; }
        .status-badge-working       { background:rgba(16,185,129,0.12);color:var(--success); }
        .status-badge-not_working   { background:rgba(239,68,68,0.12); color:var(--danger); }
        .status-badge-under_maintenance { background:rgba(245,158,11,0.12);color:var(--warning); }
        .img-preview-wrap { position:relative;display:inline-block;margin-top:8px; }
        .img-preview-wrap img { width:80px;height:80px;border-radius:8px;object-fit:cover;border:2px solid var(--border); }
        .search-bar { display:flex;gap:10px;align-items:center; }
        .search-bar input { padding:8px 14px;border-radius:8px;border:1px solid var(--border);background:var(--bg-surface);color:var(--text-primary);font-size:0.85rem;width:240px; }
        .search-bar button { padding:8px 16px;border-radius:8px;border:none;background:var(--primary);color:#fff;cursor:pointer;font-size:0.85rem; }
    </style>
</head>
<body>
<div class="app-layout">
<?php include '../../includes/sidebar.php'; ?>
<div class="main-content">
    <header class="top-header">
        <button class="hamburger" onclick="toggleSidebar()">☰</button>
        <h2 class="page-title">INVENTORY</h2>
        <div class="top-header-actions">
            <button class="btn btn-primary btn-sm" onclick="openModal('addItemModal')">+ Add Equipment</button>
        </div>
    </header>

    <div class="page-content">
        <?php if ($error):   ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:28px;">
            <div class="stat-card">
                <div class="stat-number"><?= $totalItems ?></div>
                <div class="stat-label">Total Equipment Types</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:var(--success)"><?= $stats['working']['q'] ?></div>
                <div class="stat-label">Working Units</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:var(--danger)"><?= $stats['not_working']['q'] ?></div>
                <div class="stat-label">Not Working</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:var(--warning)"><?= $stats['under_maintenance']['q'] ?></div>
                <div class="stat-label">Under Maintenance</div>
            </div>
        </div>

        <!-- Filter Tabs + Search -->
        <div class="card" style="margin-bottom:24px;padding:0;overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;border-bottom:1px solid var(--border);padding-right:16px;">
                <div style="display:flex;">
                    <?php
                    $tabs = ['all'=>'All', 'working'=>'Working', 'not_working'=>'Not Working', 'under_maintenance'=>'Under Maintenance'];
                    $tabCounts = ['all'=>$totalItems,'working'=>$stats['working']['c'],'not_working'=>$stats['not_working']['c'],'under_maintenance'=>$stats['under_maintenance']['c']];
                    foreach ($tabs as $f => $label):
                        $isActive = ($filter === $f);
                    ?>
                    <a href="?filter=<?= $f ?><?= $search ? '&search='.urlencode($search) : '' ?>" style="
                        padding:14px 20px;font-size:0.85rem;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:7px;
                        color:<?= $isActive ? 'var(--primary)' : 'var(--text-secondary)' ?>;
                        border-bottom:2px solid <?= $isActive ? 'var(--primary)' : 'transparent' ?>;
                        transition:all 0.2s;">
                        <?= $label ?>
                        <?php if ($tabCounts[$f] > 0): ?>
                        <span style="background:<?= $isActive ? 'var(--primary)' : 'var(--bg-surface)' ?>;color:<?= $isActive ? '#fff' : 'var(--text-secondary)' ?>;padding:2px 7px;border-radius:50px;font-size:0.72rem;"><?= $tabCounts[$f] ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <form method="GET" class="search-bar" style="padding:8px 0;">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name, serial, category…">
                    <button type="submit">Search</button>
                    <?php if ($search): ?><a href="?filter=<?= $filter ?>" class="btn btn-sm btn-outline">Clear</a><?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Equipment Name</th>
                            <th>Serial Number</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Purchase Date</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items->num_rows === 0): ?>
                        <tr><td colspan="10" style="text-align:center;padding:50px;color:var(--text-secondary);">
                            <?= $search ? "No results for \"$search\"" : "No equipment added yet." ?>
                        </td></tr>
                        <?php else: $i=1; while ($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <?php if ($item['image']): ?>
                                    <img src="../../uploads/equipment/<?= htmlspecialchars($item['image']) ?>" class="equip-img" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                    <div class="equip-placeholder">🏋️</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                <?php if ($item['notes']): ?>
                                <div style="font-size:0.78rem;color:var(--text-secondary);margin-top:3px;"><?= htmlspecialchars(mb_strimwidth($item['notes'],0,60,'…')) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-family:monospace;font-size:0.85rem;"><?= $item['serial_number'] ? htmlspecialchars($item['serial_number']) : '<span style="color:var(--text-secondary)">—</span>' ?></td>
                            <td><?= $item['category'] ? htmlspecialchars($item['category']) : '<span style="color:var(--text-secondary)">—</span>' ?></td>
                            <td style="font-weight:700;"><?= $item['quantity'] ?></td>
                            <td><?= $item['purchase_date'] ? date('M d, Y', strtotime($item['purchase_date'])) : '—' ?></td>
                            <td><?= $item['purchase_price'] ? '₹'.number_format($item['purchase_price'],0) : '—' ?></td>
                            <td>
                                <?php
                                $sMap = ['working'=>'Working','not_working'=>'Not Working','under_maintenance'=>'Maintenance'];
                                $sClass = 'status-badge-' . $item['status'];
                                ?>
                                <span class="badge-status <?= $sClass ?>" style="border-radius:50px;padding:4px 12px;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;">
                                    <?= $sMap[$item['status']] ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-secondary" onclick='openEditModal(<?= json_encode($item) ?>)'>Edit</button>
                                    <form method="POST" style="margin:0" onsubmit="return confirm('Delete this equipment?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /page-content -->
</div><!-- /main-content -->
</div><!-- /app-layout -->

<!-- ── Add Equipment Modal ──────────────────────────────────────────────── -->
<div class="modal-overlay" id="addItemModal">
    <div class="modal" style="width:680px;">
        <div class="modal-header">
            <h3>Add Equipment</h3>
            <button class="modal-close" onclick="closeModal('addItemModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Equipment Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Treadmill, Dumbbell Set">
                    </div>
                    <div class="form-group">
                        <label>Serial Number</label>
                        <input type="text" name="serial_number" class="form-control" placeholder="e.g. TRD-2024-001">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" class="form-control" list="cat-list" placeholder="e.g. Cardio, Strength, Free Weights">
                        <datalist id="cat-list">
                            <?php foreach ($existingCategories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>">
                            <?php endforeach; ?>
                            <option value="Cardio">
                            <option value="Strength">
                            <option value="Free Weights">
                            <option value="Flexibility">
                            <option value="Accessories">
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Purchase Price (₹)</label>
                        <input type="number" name="purchase_price" class="form-control" placeholder="0.00" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="working">✅ Working</option>
                            <option value="not_working">❌ Not Working</option>
                            <option value="under_maintenance">🔧 Under Maintenance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Equipment Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this,'add-preview')">
                        <div class="img-preview-wrap" id="add-preview" style="display:none;">
                            <img src="" alt="preview">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Any additional details…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addItemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Equipment</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Edit Equipment Modal ─────────────────────────────────────────────── -->
<div class="modal-overlay" id="editItemModal">
    <div class="modal" style="width:680px;">
        <div class="modal-header">
            <h3>Edit Equipment</h3>
            <button class="modal-close" onclick="closeModal('editItemModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="item_id" id="ei_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Equipment Name *</label>
                        <input type="text" name="name" id="ei_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Serial Number</label>
                        <input type="text" name="serial_number" id="ei_serial" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" id="ei_category" class="form-control" list="cat-list">
                    </div>
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" id="ei_qty" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Date</label>
                        <input type="date" name="purchase_date" id="ei_pdate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Purchase Price (₹)</label>
                        <input type="number" name="purchase_price" id="ei_price" class="form-control" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" id="ei_status" class="form-control">
                            <option value="working">✅ Working</option>
                            <option value="not_working">❌ Not Working</option>
                            <option value="under_maintenance">🔧 Under Maintenance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Update Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this,'edit-preview')">
                        <div class="img-preview-wrap" id="edit-preview" style="display:none;">
                            <img src="" alt="preview">
                        </div>
                        <div id="ei_current_img" style="margin-top:6px;"></div>
                        <small style="color:var(--text-secondary);font-size:0.78rem;">Leave empty to keep current image</small>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" id="ei_notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editItemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Equipment</button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
function openEditModal(d) {
    document.getElementById('ei_id').value       = d.id;
    document.getElementById('ei_name').value     = d.name;
    document.getElementById('ei_serial').value   = d.serial_number || '';
    document.getElementById('ei_category').value = d.category || '';
    document.getElementById('ei_qty').value      = d.quantity;
    document.getElementById('ei_pdate').value    = d.purchase_date || '';
    document.getElementById('ei_price').value    = d.purchase_price || '';
    document.getElementById('ei_status').value   = d.status;
    document.getElementById('ei_notes').value    = d.notes || '';

    // Show current image thumbnail
    var imgWrap = document.getElementById('ei_current_img');
    if (d.image) {
        imgWrap.innerHTML = '<img src="../../uploads/equipment/' + d.image + '" style="width:60px;height:60px;border-radius:8px;object-fit:cover;border:2px solid var(--border);" alt="current">';
    } else {
        imgWrap.innerHTML = '';
    }
    // Hide new preview
    document.getElementById('edit-preview').style.display = 'none';

    openModal('editItemModal');
}

function previewImg(input, previewId) {
    var wrap = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            wrap.querySelector('img').src = e.target.result;
            wrap.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
