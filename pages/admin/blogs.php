<?php
define('CURRENT_PAGE', 'blogs');
require_once '../../includes/auth.php';
requireAdmin();

$conn->query("CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    cover_emoji VARCHAR(10) DEFAULT '📝',
    category VARCHAR(80) DEFAULT 'General',
    tags VARCHAR(255),
    author_name VARCHAR(80) DEFAULT 'FitZone Team',
    is_published TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed sample posts if empty
$cnt = $conn->query("SELECT COUNT(*) as c FROM blogs")->fetch_assoc()['c'];
if ($cnt == 0) {
    $samples = [
        ['5 Proven Strategies to Break Through a Fitness Plateau', '5-proven-strategies-fitness-plateau', 'Every gym-goer hits a wall eventually. Here\'s how the pros push past it.', "## Why Plateaus Happen\n\nA fitness plateau occurs when your body adapts to your current training stimulus. The solution is **progressive overload** — consistently increasing the challenge.\n\n## Strategy 1: Change Your Rep Range\n\nIf you've been doing 3 sets of 10, switch to 5 sets of 5 for strength, or 4 sets of 15 for endurance. Novel stimuli force new adaptations.\n\n## Strategy 2: Add a Deload Week\n\nCounter-intuitive but effective: reduce volume by 40% for one week. Your nervous system recovers, and you come back stronger.\n\n## Strategy 3: Prioritize Sleep\n\n80% of muscle repair happens during deep sleep. 7–9 hours isn't optional — it's the foundation of your gains.\n\n## Strategy 4: Audit Your Nutrition\n\nTrack your protein intake for one week. Most people consuming too little protein while blaming their training.\n\n## Strategy 5: Switch Your Training Split\n\nIf you're on a Push/Pull/Legs, try Upper/Lower. Fresh patterns recruit motor units differently, reigniting progress.", '🏋️', 'Training', 'plateau,gains,training tips', 1],
        ['The Ultimate Guide to Gym Nutrition Timing', 'ultimate-guide-gym-nutrition-timing', 'When you eat is almost as important as what you eat. This guide breaks it all down.', "## Pre-Workout Nutrition\n\nEat a balanced meal 2–3 hours before training: complex carbs + lean protein. 30–60 minutes before, a banana or rice cake works perfectly.\n\n## Intra-Workout\n\nFor sessions under 60 minutes, water is sufficient. Over 60 minutes? Consider 20–30g fast carbs (sports drink, dates) to maintain performance.\n\n## The Anabolic Window (Post-Workout)\n\nConsume 20–40g of high-quality protein within 30 minutes of training. Pair with fast carbs to spike insulin and drive amino acids into muscle cells.\n\n## Evening Nutrition\n\nA casein protein source (cottage cheese, Greek yogurt) before bed provides a slow-release amino acid supply throughout the night.", '🥗', 'Nutrition', 'nutrition,protein,meal timing', 1],
        ['How to Stay Motivated When You Don\'t Feel Like Training', 'stay-motivated-dont-feel-like-training', 'Motivation is unreliable. Build systems instead. Here\'s how top athletes stay consistent.', "## The Motivation Myth\n\nElite athletes don't rely on motivation — they build *discipline*. Motivation is an emotion; it fluctuates. Systems are reliable.\n\n## Trick 1: The 5-Minute Rule\n\nCommit to just 5 minutes. Once you're warmed up, your body releases dopamine and you almost always continue.\n\n## Trick 2: Train With a Partner\n\nSocial accountability is the single most powerful predictor of exercise adherence. Find your training buddy.\n\n## Trick 3: Track Your Progress\n\nData is motivating. Seeing your squat go from 60kg to 100kg over 6 months is incredibly compelling.\n\n## Trick 4: Celebrate Small Wins\n\nDon't wait until you hit your goal weight to celebrate. Every PR, every consistent week matters.", '🔥', 'Motivation', 'motivation,mindset,consistency', 1],
    ];
    $stmt = $conn->prepare("INSERT INTO blogs (title,slug,excerpt,content,cover_emoji,category,tags,is_published,published_at) VALUES (?,?,?,?,?,?,?,1,NOW())");
    foreach ($samples as $s) { $stmt->bind_param("sssssss",$s[0],$s[1],$s[2],$s[3],$s[4],$s[5],$s[6]); $stmt->execute(); }
}

// Handle actions
$msg = ''; $msgType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
    $id        = (int)($_POST['id'] ?? 0);
    $title     = trim($_POST['title'] ?? '');
    $excerpt   = trim($_POST['excerpt'] ?? '');
    $content   = trim($_POST['content'] ?? '');  // ← NO htmlspecialchars on content
    $emoji     = trim($_POST['cover_emoji'] ?? '📝');
    $cat       = trim($_POST['category'] ?? 'General');
    $tags      = trim($_POST['tags'] ?? '');
    $author    = trim($_POST['author_name'] ?? 'FitZone Team');
    $published = isset($_POST['is_published']) ? 1 : 0;

    if (!$title) { $msg = 'Title is required.'; $msgType = 'danger'; }
    else {
        // Generate slug
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
        $slug = trim($slug, '-');
        if ($id) $slug .= '-' . $id;

        // Only escape simple text fields — NOT content/excerpt (they need raw HTML)
        $title  = $conn->real_escape_string($title);
        $emoji  = $conn->real_escape_string($emoji);
        $cat    = $conn->real_escape_string($cat);
        $tags   = $conn->real_escape_string($tags);
        $author = $conn->real_escape_string($author);
        $pubAt  = $published ? date('Y-m-d H:i:s') : null;

        if ($id === 0) {
            $stmt = $conn->prepare("INSERT INTO blogs (title,slug,excerpt,content,cover_emoji,category,tags,author_name,is_published,published_at) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssssis", $title,$slug,$excerpt,$content,$emoji,$cat,$tags,$author,$published,$pubAt);
            $stmt->execute();
            $msg = 'Blog post created!';
            $id  = $stmt->insert_id;
        } else {
            $stmt = $conn->prepare("UPDATE blogs SET title=?,slug=?,excerpt=?,content=?,cover_emoji=?,category=?,tags=?,author_name=?,is_published=?,published_at=? WHERE id=?");
            $stmt->bind_param("ssssssssisi", $title,$slug,$excerpt,$content,$emoji,$cat,$tags,$author,$published,$pubAt,$id);
            $stmt->execute();
            $msg = 'Blog post updated!';
        }
    }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("DELETE FROM blogs WHERE id=$id");
        $msg = 'Post deleted.'; $msgType = 'warning';
        header('Location: blogs.php'); exit;
    } elseif ($action === 'toggle_publish') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("UPDATE blogs SET is_published=1-is_published, published_at=CASE WHEN is_published=0 THEN NOW() ELSE published_at END WHERE id=$id");
        header('Location: blogs.php'); exit;
    }
}

// Load list or edit
$editPost = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $r = $conn->query("SELECT * FROM blogs WHERE id=$eid");
    if ($r && $r->num_rows) $editPost = $r->fetch_assoc();
}

$posts    = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogCats = ['General','Training','Nutrition','Motivation','Recovery','Lifestyle','Science'];
?>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Manager — FitZone Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .post-editor { font-family: 'Courier New', monospace; font-size: 0.88rem; min-height: 280px; }
        .char-count { font-size: 0.72rem; color: var(--text-secondary); margin-top: 4px; }
    </style>
</head>
<body>
<div class="app-layout">
<?php include '../../includes/sidebar.php'; ?>
<div class="main-content">
    <header class="top-header">
        <button class="hamburger" onclick="toggleSidebar()">☰</button>
        <h2 class="page-title">BLOG MANAGER</h2>
        <div class="top-header-actions">
            <?php if (!$editPost && !isset($_GET['new'])): ?>
            <a href="?new=1" class="btn btn-primary btn-sm">+ New Post</a>
            <?php endif; ?>
            <a href="../../pages/website/blog.php" target="_blank" class=" btn btn-primary btn-sm" >🌐 View Blog ↗</a>
        </div>
    </header>
    <div class="page-content">

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?>" style="margin-bottom:20px"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Editor -->
    <?php if (isset($_GET['new']) || $editPost): ?>
    <div class="card" style="margin-bottom:28px;">
        <h3 class="card-title"><?= $editPost ? 'Edit Post: ' . htmlspecialchars($editPost['title']) : 'New Blog Post' ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int)($editPost['id'] ?? 0) ?>">

            <div style="display:grid;grid-template-columns:60px 1fr;gap:16px;margin-bottom:16px;">
                <div class="form-group" style="margin:0">
                    <label>Emoji</label>
                    <input type="text" name="cover_emoji" class="form-control" value="<?= htmlspecialchars($editPost['cover_emoji'] ?? '📝') ?>" maxlength="10" style="text-align:center;font-size:1.5rem;">
                </div>
                <div class="form-group" style="margin:0">
                    <label>Post Title *</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editPost['title'] ?? '') ?>" placeholder="Enter a compelling title…" required>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
                <div class="form-group" style="margin:0">
                    <label>Category</label>
                    <select name="category" class="form-control">
                        <?php foreach ($blogCats as $c): ?>
                        <option value="<?= $c ?>" <?= ($editPost['category'] ?? 'General') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Tags (comma-separated)</label>
                    <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($editPost['tags'] ?? '') ?>" placeholder="fitness, training, tips">
                </div>
                <div class="form-group" style="margin:0">
                    <label>Author Name</label>
                    <input type="text" name="author_name" class="form-control" value="<?= htmlspecialchars($editPost['author_name'] ?? 'FitZone Team') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Excerpt (shown in blog listing)</label>
                <textarea name="excerpt" class="form-control" rows="2" placeholder="Short summary of the post…"><?= htmlspecialchars(html_entity_decode($editPost['excerpt'] ?? '', ENT_QUOTES, 'UTF-8')) ?></textarea>
            </div>
            <div class="form-group">
                <label>Content (supports ## headings, **bold**, *italic*)</label>
                <textarea name="content" class="form-control post-editor" id="postContent" oninput="updateCount(this)"><?= htmlspecialchars(html_entity_decode($editPost['content'] ?? '', ENT_QUOTES, 'UTF-8')) ?></textarea>
                <div class="char-count" id="charCount">0 characters</div>
            </div>
            <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                <label style="display:flex;align-items:center;gap:8px;font-size:0.88rem;cursor:pointer;">
                    <input type="checkbox" name="is_published" <?= ($editPost['is_published'] ?? 0) ? 'checked' : '' ?>> Publish immediately
                </label>
                <button type="submit" class="btn btn-primary">Save Post</button>
                <a href="blogs.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Posts Table -->
    <div class="card">
        <table class="data-table">
            <thead>
                <tr><th>Emoji</th><th>Title</th><th>Category</th><th>Author</th><th>Views</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($p = $posts->fetch_assoc()): ?>
                <tr>
                    <td style="font-size:1.5rem;text-align:center"><?= htmlspecialchars($p['cover_emoji']) ?></td>
                    <td style="font-weight:600;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($p['title']) ?></td>
                    <td><span style="background:var(--primary-alpha);color:var(--primary);padding:3px 10px;border-radius:50px;font-size:0.72rem;font-weight:700"><?= htmlspecialchars($p['category']) ?></span></td>
                    <td style="font-size:0.83rem;color:var(--text-secondary)"><?= htmlspecialchars($p['author_name']) ?></td>
                    <td style="text-align:center"><?= number_format($p['views']) ?></td>
                    <td>
                        <form method="POST" style="margin:0">
                            <input type="hidden" name="action" value="toggle_publish">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="badge <?= $p['is_published'] ? 'badge-success' : 'badge-warning' ?>" style="cursor:pointer;border:none;padding:4px 10px;">
                                <?= $p['is_published'] ? 'Published' : 'Draft' ?>
                            </button>
                        </form>
                    </td>
                    <td style="font-size:0.78rem;color:var(--text-secondary)"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:8px;">
                            <a href="?edit=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="../../pages/website/blog-post.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" target="_blank">View</a>
                            <form method="POST" style="margin:0" onsubmit="return confirm('Delete this post permanently?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
<script>
function updateCount(el) {
    document.getElementById('charCount').textContent = el.value.length + ' characters';
}
// Init count
var pc = document.getElementById('postContent');
if (pc) updateCount(pc);
</script>
</body></html>
