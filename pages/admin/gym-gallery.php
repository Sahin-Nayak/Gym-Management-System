<?php
define('CURRENT_PAGE', 'gym-gallery');
require_once '../../includes/auth.php';
requireAdmin();

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS gym_gallery (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    image_path  VARCHAR(255) NOT NULL,
    caption     VARCHAR(200),
    sort_order  INT DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$uploadDir    = __DIR__ . '/../../uploads/gallery/';
$uploadUrlBase = '../../uploads/gallery/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$error   = '';
$success = '';

// ── Handle POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'upload') {
        if (empty($_FILES['images']['name'][0])) {
            $error = 'Please select at least one image.';
        } else {
            $caption = sanitize($_POST['caption'] ?? '');
            $order   = (int)($_POST['sort_order'] ?? 0);
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $uploaded = 0;
            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                $origName = $_FILES['images']['name'][$i];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed) || !$tmp) continue;
                $filename = 'gallery_' . time() . '_' . $i . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadDir . $filename)) {
                    $stmt = $conn->prepare("INSERT INTO gym_gallery (image_path, caption, sort_order) VALUES (?,?,?)");
                    $stmt->bind_param("ssi", $filename, $caption, $order);
                    $stmt->execute();
                    $uploaded++;
                }
            }
            $success = $uploaded > 0 ? "$uploaded image(s) uploaded!" : 'Upload failed.';
        }
    }

    if ($_POST['action'] === 'edit') {
        $id      = (int)$_POST['item_id'];
        $caption = sanitize($_POST['caption']);
        $order   = (int)$_POST['sort_order'];
        $stmt    = $conn->prepare("UPDATE gym_gallery SET caption=?, sort_order=? WHERE id=?");
        $stmt->bind_param("sii", $caption, $order, $id);
        $success = $stmt->execute() ? 'Updated!' : 'Update failed.';
    }

    if ($_POST['action'] === 'toggle') {
        $id  = (int)$_POST['item_id'];
        $cur = $conn->query("SELECT is_active FROM gym_gallery WHERE id=$id")->fetch_assoc()['is_active'] ?? 1;
        $new = $cur ? 0 : 1;
        $conn->query("UPDATE gym_gallery SET is_active=$new WHERE id=$id");
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'active' => $new]);
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $id  = (int)$_POST['item_id'];
        $row = $conn->query("SELECT image_path FROM gym_gallery WHERE id=$id")->fetch_assoc();
        if ($row && file_exists($uploadDir . $row['image_path'])) unlink($uploadDir . $row['image_path']);
        $conn->query("DELETE FROM gym_gallery WHERE id=$id");
        $success = 'Photo deleted.';
    }
}

$items  = $conn->query("SELECT * FROM gym_gallery ORDER BY sort_order ASC, id DESC");
$total  = $conn->query("SELECT COUNT(*) as c FROM gym_gallery")->fetch_assoc()['c'];
$active = $conn->query("SELECT COUNT(*) as c FROM gym_gallery WHERE is_active=1")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE['fitzone_theme'] ?? 'dark'; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Gallery — FitZone Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .gallery-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .gallery-thumb-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .gallery-thumb-card:hover { border-color: var(--primary); }
        .gallery-thumb-img {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            display: block;
            background: var(--bg-surface2);
        }
        .gallery-thumb-footer {
            padding: 10px 12px;
        }
        .gallery-thumb-caption {
            font-size: 0.82rem;
            color: var(--text-secondary);
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .thumb-inactive { opacity: 0.45; }
        .inactive-badge {
            background: var(--bg-surface2);
            color: var(--text-muted);
            font-size: 0.68rem;
            padding: 2px 8px;
            border-radius: 50px;
            font-weight: 700;
        }
        .drop-zone {
            border: 2px dashed var(--border);
            border-radius: var(--radius-sm);
            padding: 48px 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .drop-zone:hover, .drop-zone.drag-over {
            border-color: var(--primary);
            background: var(--primary-alpha);
        }
        .drop-zone input[type=file] { display: none; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-header">
            <button class="hamburger" onclick="toggleSidebar()">☰</button>
            <h2 class="page-title">GYM GALLERY</h2>
            <div class="top-header-actions">
                <button class="btn btn-primary btn-sm" onclick="openModal('uploadModal')">+ Upload Photos</button>
                <a href="../../home.php#gallery" target="_blank" class="btn btn-primary btn-sm">🌐 View Gallery ↗</a>
            </div>
        </header>

        <div class="page-content">
            <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:24px;">
                <div class="stat-card blue">
                    <div class="stat-info"><h4>Total Photos</h4><div class="stat-number"><?= $total ?></div></div>
                    <div class="stat-icon">🖼️</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-info"><h4>Visible</h4><div class="stat-number"><?= $active ?></div></div>
                    <div class="stat-icon">✅</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-info"><h4>Hidden</h4><div class="stat-number"><?= $total - $active ?></div></div>
                    <div class="stat-icon">🙈</div>
                </div>
            </div>

            <!-- Gallery Grid -->
            <div class="card">
                <div class="card-header">
                    <h3>🖼️ All Photos</h3>
                    <span style="color:var(--text-muted);font-size:0.82rem;">Click a photo to edit caption or toggle visibility</span>
                </div>

                <?php if ($items->num_rows > 0): ?>
                <div class="gallery-admin-grid" style="padding:20px;">
                    <?php while ($g = $items->fetch_assoc()): ?>
                    <div class="gallery-thumb-card <?= !$g['is_active'] ? 'thumb-inactive' : '' ?>">
                        <div style="position:relative;">
                            <img src="<?= $uploadUrlBase . htmlspecialchars($g['image_path']) ?>" class="gallery-thumb-img" alt="<?= htmlspecialchars($g['caption'] ?? '') ?>">
                            <?php if (!$g['is_active']): ?>
                            <span class="inactive-badge" style="position:absolute;top:8px;right:8px;">Hidden</span>
                            <?php endif; ?>
                        </div>
                        <div class="gallery-thumb-footer">
                            <div class="gallery-thumb-caption"><?= htmlspecialchars($g['caption'] ?: 'No caption') ?></div>
                            <div class="table-actions" style="margin-top:6px;">
                                <button class="btn btn-sm btn-outline" onclick="openGalleryEdit(<?= $g['id'] ?>,'<?= htmlspecialchars(addslashes($g['caption'] ?? '')) ?>',<?= (int)$g['sort_order'] ?>)">Edit</button>
                                <button class="btn btn-sm btn-outline" id="gtBtn-<?= $g['id'] ?>" onclick="toggleGallery(<?= $g['id'] ?>)">
                                    <?= $g['is_active'] ? 'Hide' : 'Show' ?>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteGallery(<?= $g['id'] ?>)">✕</button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
                    <div style="font-size:3rem;margin-bottom:16px;">🖼️</div>
                    <p>No photos yet.</p>
                    <button class="btn btn-primary" onclick="openModal('uploadModal')" style="margin-top:16px;">+ Upload First Photos</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- UPLOAD MODAL -->
<div class="modal-overlay" id="uploadModal">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <h3>Upload Photos</h3>
            <button class="modal-close" onclick="closeModal('uploadModal')">✕</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            <div class="modal-body">
                <div class="drop-zone" id="dropZone" onclick="document.getElementById('imgInput').click()">
                    <input type="file" name="images[]" id="imgInput" accept="image/*" multiple onchange="previewFiles(this)">
                    <div id="dropLabel">
                        <div style="font-size:2.5rem;margin-bottom:12px;">📸</div>
                        <p style="color:var(--text-primary);font-weight:600;">Click to select photos</p>
                        <p style="color:var(--text-muted);font-size:0.82rem;margin-top:6px;">JPG, PNG, WEBP, GIF — multiple files supported</p>
                    </div>
                    <div id="filePreviewList" style="display:none;"></div>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label>Caption <span style="color:var(--text-muted);font-size:0.8rem;">(applied to all uploaded photos)</span></label>
                    <input type="text" name="caption" class="form-control" placeholder="e.g. Weight Training Area">
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('uploadModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT CAPTION MODAL -->
<div class="modal-overlay" id="editGalleryModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h3>Edit Photo</h3>
            <button class="modal-close" onclick="closeModal('editGalleryModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="item_id" id="editGalleryId">
            <div class="modal-body">
                <div class="form-group">
                    <label>Caption</label>
                    <input type="text" name="caption" id="editGalleryCaption" class="form-control">
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" id="editGalleryOrder" class="form-control" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editGalleryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- HIDDEN FORMS -->
<form id="deleteGalleryForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="item_id" id="deleteGalleryId">
</form>
<form id="toggleGalleryForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="toggle">
    <input type="hidden" name="item_id" id="toggleGalleryId">
</form>

<script src="../../assets/js/main.js"></script>
<script>
function openGalleryEdit(id, caption, order) {
    document.getElementById('editGalleryId').value      = id;
    document.getElementById('editGalleryCaption').value = caption;
    document.getElementById('editGalleryOrder').value   = order;
    openModal('editGalleryModal');
}

function deleteGallery(id) {
    if (!confirm('Delete this photo permanently?')) return;
    document.getElementById('deleteGalleryId').value = id;
    document.getElementById('deleteGalleryForm').submit();
}

function toggleGallery(id) {
    var fd = new FormData();
    fd.append('action', 'toggle');
    fd.append('item_id', id);
    fetch('', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                var btn  = document.getElementById('gtBtn-' + id);
                var card = btn.closest('.gallery-thumb-card');
                if (res.active) {
                    btn.textContent = 'Hide';
                    card.classList.remove('thumb-inactive');
                } else {
                    btn.textContent = 'Show';
                    card.classList.add('thumb-inactive');
                }
            }
        });
}

function previewFiles(input) {
    var list = document.getElementById('filePreviewList');
    var label = document.getElementById('dropLabel');
    if (input.files.length === 0) return;
    list.innerHTML = '';
    list.style.display = 'block';
    label.style.display = 'none';
    Array.from(input.files).forEach(function(f) {
        var div = document.createElement('div');
        div.style.cssText = 'display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid var(--border);font-size:0.85rem;color:var(--text-primary);';
        div.textContent = '📷 ' + f.name;
        list.appendChild(div);
    });
}

// Drag & Drop
var dz = document.getElementById('dropZone');
if (dz) {
    dz.addEventListener('dragover', function(e) { e.preventDefault(); dz.classList.add('drag-over'); });
    dz.addEventListener('dragleave', function() { dz.classList.remove('drag-over'); });
    dz.addEventListener('drop', function(e) {
        e.preventDefault();
        dz.classList.remove('drag-over');
        var input = document.getElementById('imgInput');
        input.files = e.dataTransfer.files;
        previewFiles(input);
    });
}
</script>
</body>
</html>
