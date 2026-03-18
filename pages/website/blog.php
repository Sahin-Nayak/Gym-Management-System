<?php
$wbPath  = '../../';
$wbPage  = 'blog';
$wbTitle = 'Blog';
$wbDesc  = 'Expert fitness articles on training, nutrition, motivation, and the gym lifestyle from the FitZone team.';
include $wbPath . 'includes/website-header.php';

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

$cat    = $_GET['cat'] ?? '';
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 9;
$offset = ($page - 1) * $limit;

$where = "WHERE is_published=1";
if ($cat)    $where .= " AND category='" . $conn->real_escape_string($cat) . "'";
if ($search) $where .= " AND (title LIKE '%" . $conn->real_escape_string($search) . "%' OR excerpt LIKE '%" . $conn->real_escape_string($search) . "%')";

$total  = (int)$conn->query("SELECT COUNT(*) as c FROM blogs $where")->fetch_assoc()['c'];
$pages  = (int)ceil($total / $limit);
$posts  = $conn->query("SELECT * FROM blogs $where ORDER BY published_at DESC LIMIT $limit OFFSET $offset");
$cats   = $conn->query("SELECT DISTINCT category FROM blogs WHERE is_published=1 ORDER BY category ASC");
?>

<div class="page-hero">
    <div class="page-hero-inner">
        <div class="page-hero-tag">Knowledge Hub</div>
        <h1 class="page-hero-title">FitZone <span>Blog</span></h1>
        <p class="page-hero-sub">Expert articles on training, nutrition, motivation, and everything fitness — from our certified team.</p>
        <div class="page-breadcrumb">
            <a href="<?= $wbPath ?>home.php">Home</a> › <span>Blog</span>
        </div>
    </div>
</div>

<section class="section section-alt">
    <div class="section-inner">

        <!-- Filters -->
        <div style="display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:32px;">
            <div class="filter-tabs" style="margin-bottom:0">
                <a href="blog.php" class="filter-tab <?= !$cat ? 'active' : '' ?>">All</a>
                <?php while ($c = $cats->fetch_assoc()): ?>
                <a href="?cat=<?= urlencode($c['category']) ?>" class="filter-tab <?= $cat === $c['category'] ? 'active' : '' ?>"><?= htmlspecialchars($c['category']) ?></a>
                <?php endwhile; ?>
            </div>
            <form method="GET" action="">
                <?php if ($cat): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($cat) ?>"><?php endif; ?>
                <div class="search-bar">
                    <input type="text" name="q" placeholder="Search articles…" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">🔍</button>
                </div>
            </form>
        </div>

        <?php if ($search || $cat): ?>
        <p style="font-size:0.85rem;color:var(--white-40);margin-bottom:24px;">
            <?= $total ?> article<?= $total !== 1 ? 's' : '' ?> found
            <?= $search ? ' for "<strong style="color:var(--white)">' . htmlspecialchars($search) . '</strong>"' : '' ?>
            <?= $cat ? ' in <strong style="color:var(--red)">' . htmlspecialchars($cat) . '</strong>' : '' ?>
            — <a href="blog.php" style="color:var(--red)">Clear filters</a>
        </p>
        <?php endif; ?>

        <div class="blog-grid">
            <?php if ($posts->num_rows > 0):
                while ($post = $posts->fetch_assoc()): ?>
            <a href="blog-post.php?id=<?= $post['id'] ?>" class="blog-card reveal" style="text-decoration:none;"
               onclick="trackView(<?= $post['id'] ?>)">
                <div class="blog-cover"><?= htmlspecialchars($post['cover_emoji']) ?></div>
                <div class="blog-body">
                    <div class="blog-meta">
                        <span class="blog-category"><?= htmlspecialchars($post['category']) ?></span>
                        <span class="blog-date"><?= date('M d, Y', strtotime($post['published_at'] ?: $post['created_at'])) ?></span>
                    </div>
                    <div class="blog-title"><?= htmlspecialchars($post['title']) ?></div>
                    <p class="blog-excerpt"><?= htmlspecialchars($post['excerpt'] ?: '') ?></p>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;">
                        <div class="blog-read-more">Read Article →</div>
                        <div class="views-badge">👁 <?= number_format($post['views']) ?></div>
                    </div>
                </div>
            </a>
            <?php endwhile; else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:80px;color:var(--white-40)">
                <div style="font-size:3rem;margin-bottom:16px">📭</div>
                No articles found.
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?><?= $cat ? '&cat='.urlencode($cat) : '' ?><?= $search ? '&q='.urlencode($search) : '' ?>" class="page-btn">‹</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $pages; $p++): ?>
            <a href="?page=<?= $p ?><?= $cat ? '&cat='.urlencode($cat) : '' ?><?= $search ? '&q='.urlencode($search) : '' ?>"
               class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($page < $pages): ?>
            <a href="?page=<?= $page+1 ?><?= $cat ? '&cat='.urlencode($cat) : '' ?><?= $search ? '&q='.urlencode($search) : '' ?>" class="page-btn">›</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include $wbPath . 'includes/website-footer.php'; ?>
