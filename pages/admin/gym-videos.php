<?php
define('CURRENT_PAGE', 'gym-videos');
require_once '../../includes/auth.php';
requireAdmin();

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS gym_videos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200),
    youtube_url VARCHAR(300) NOT NULL,
    sort_order  INT DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add') {
        $title  = sanitize($_POST['title']);
        $url    = trim($_POST['youtube_url']);
        $order  = (int)($_POST['sort_order'] ?? 0);
        if (!$url) {
            $error = 'YouTube URL is required.';
        } else {
            $stmt = $conn->prepare("INSERT INTO gym_videos (title, youtube_url, sort_order) VALUES (?,?,?)");
            $stmt->bind_param("ssi", $title, $url, $order);
            $success = $stmt->execute() ? 'Video added successfully!' : 'Failed to add video.';
        }
    }

    if ($_POST['action'] === 'edit') {
        $id    = (int)$_POST['video_id'];
        $title = sanitize($_POST['title']);
        $url   = trim($_POST['youtube_url']);
        $order = (int)($_POST['sort_order'] ?? 0);
        $stmt  = $conn->prepare("UPDATE gym_videos SET title=?, youtube_url=?, sort_order=? WHERE id=?");
        $stmt->bind_param("ssii", $title, $url, $order, $id);
        $success = $stmt->execute() ? 'Video updated!' : 'Update failed.';
    }

    if ($_POST['action'] === 'toggle') {
        $id  = (int)$_POST['video_id'];
        $cur = $conn->query("SELECT is_active FROM gym_videos WHERE id=$id")->fetch_assoc()['is_active'] ?? 1;
        $new = $cur ? 0 : 1;
        $conn->query("UPDATE gym_videos SET is_active=$new WHERE id=$id");
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'active' => $new]);
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['video_id'];
        $conn->query("DELETE FROM gym_videos WHERE id=$id");
        $success = 'Video deleted.';
    }
}

$videos = $conn->query("SELECT * FROM gym_videos ORDER BY sort_order ASC, id DESC");
$total  = $conn->query("SELECT COUNT(*) as c FROM gym_videos")->fetch_assoc()['c'];
$active = $conn->query("SELECT COUNT(*) as c FROM gym_videos WHERE is_active=1")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE['fitzone_theme'] ?? 'dark'; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Videos — FitZone Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .yt-thumb { width: 120px; height: 68px; object-fit: cover; border-radius: 6px; background: var(--bg-surface2); }
        .yt-placeholder { width: 120px; height: 68px; background: var(--bg-surface2); border-radius: 6px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; }
        .status-pill { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:50px; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
        .pill-active { background:rgba(34,197,94,0.15); color:#22c55e; }
        .pill-inactive { background:rgba(239,68,68,0.15); color:#ef4444; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-header">
            <button class="hamburger" onclick="toggleSidebar()">☰</button>
            <h2 class="page-title">GYM VIDEOS</h2>
            <div class="top-header-actions">
                <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">+ Add Video</button>
                <a href="../../home.php#videos" target="_blank" class="btn btn-primary btn-sm">🌐 View Videos ↗</a>
            </div>
        </header>

        <div class="page-content">

            <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:24px;">
                <div class="stat-card blue">
                    <div class="stat-info"><h4>Total Videos</h4><div class="stat-number"><?= $total ?></div></div>
                    <div class="stat-icon">🎥</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-info"><h4>Active</h4><div class="stat-number"><?= $active ?></div></div>
                    <div class="stat-icon">✅</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-info"><h4>Hidden</h4><div class="stat-number"><?= $total - $active ?></div></div>
                    <div class="stat-icon">🙈</div>
                </div>
            </div>

            <!-- Videos Table -->
            <div class="card">
                <div class="card-header">
                    <h3>🎥 All Videos</h3>
                    <span style="color:var(--text-muted);font-size:0.82rem;">Paste YouTube watch or share URL</span>
                </div>
                <?php if ($videos->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Video</th>
                            <th>Title</th>
                            <th>YouTube URL</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($v = $videos->fetch_assoc()):
                            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $v['youtube_url'], $m);
                            $ytId = $m[1] ?? '';
                        ?>
                        <tr>
                            <td>
                                <?php if ($ytId): ?>
                                <img src="https://img.youtube.com/vi/<?= htmlspecialchars($ytId) ?>/mqdefault.jpg" class="yt-thumb" alt="thumb">
                                <?php else: ?>
                                <div class="yt-placeholder">▶</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($v['title'] ?: '—') ?></td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <a href="<?= htmlspecialchars($v['youtube_url']) ?>" target="_blank" style="color:var(--primary);font-size:0.82rem;">
                                    <?= htmlspecialchars($v['youtube_url']) ?>
                                </a>
                            </td>
                            <td><?= (int)$v['sort_order'] ?></td>
                            <td>
                                <span class="status-pill <?= $v['is_active'] ? 'pill-active' : 'pill-inactive' ?>" id="pill-<?= $v['id'] ?>">
                                    <?= $v['is_active'] ? '✓ Active' : '✗ Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-sm btn-outline" onclick="openEdit(<?= $v['id'] ?>, '<?= htmlspecialchars(addslashes($v['title'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($v['youtube_url'])) ?>', <?= (int)$v['sort_order'] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-outline" onclick="toggleVideo(<?= $v['id'] ?>)" id="toggleBtn-<?= $v['id'] ?>">
                                        <?= $v['is_active'] ? 'Hide' : 'Show' ?>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteVideo(<?= $v['id'] ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
                    <div style="font-size:3rem;margin-bottom:16px;">🎥</div>
                    <p>No videos added yet.</p>
                    <button class="btn btn-primary" onclick="openModal('addModal')" style="margin-top:16px;">+ Add First Video</button>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3>Add Video</h3>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label>Video Title <span style="color:var(--text-muted);font-size:0.8rem;">(optional)</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Morning Yoga Class Tour">
                </div>
                <div class="form-group">
                    <label>YouTube URL <span style="color:var(--primary)">*</span></label>
                    <input type="url" name="youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>
                    <small style="color:var(--text-muted);">Paste the full YouTube link (watch URL, share URL, or embed URL)</small>
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    <small style="color:var(--text-muted);">Lower number = shows first</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Video</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3>Edit Video</h3>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="video_id" id="editVideoId">
            <div class="modal-body">
                <div class="form-group">
                    <label>Video Title</label>
                    <input type="text" name="title" id="editTitle" class="form-control">
                </div>
                <div class="form-group">
                    <label>YouTube URL <span style="color:var(--primary)">*</span></label>
                    <input type="url" name="youtube_url" id="editUrl" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" id="editOrder" class="form-control" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE FORM -->
<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="video_id" id="deleteVideoId">
</form>
<!-- TOGGLE FORM -->
<form id="toggleForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="toggle">
    <input type="hidden" name="video_id" id="toggleVideoId">
</form>

<script src="../../assets/js/main.js"></script>
<script>
function openEdit(id, title, url, order) {
    document.getElementById('editVideoId').value = id;
    document.getElementById('editTitle').value   = title;
    document.getElementById('editUrl').value     = url;
    document.getElementById('editOrder').value   = order;
    openModal('editModal');
}

function deleteVideo(id) {
    if (!confirm('Delete this video?')) return;
    document.getElementById('deleteVideoId').value = id;
    document.getElementById('deleteForm').submit();
}

function toggleVideo(id) {
    var fd = new FormData();
    fd.append('action', 'toggle');
    fd.append('video_id', id);
    fetch('', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                var pill = document.getElementById('pill-' + id);
                var btn  = document.getElementById('toggleBtn-' + id);
                if (res.active) {
                    pill.className = 'status-pill pill-active';
                    pill.textContent = '✓ Active';
                    btn.textContent  = 'Hide';
                } else {
                    pill.className = 'status-pill pill-inactive';
                    pill.textContent = '✗ Hidden';
                    btn.textContent  = 'Show';
                }
            }
        });
}
</script>
</body>
</html>
