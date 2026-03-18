<?php
$wbPath  = '../../';
$wbPage  = 'facts';
$wbTitle = 'Athlete Facts';
$wbDesc  = 'Science-backed fitness and athlete facts to fuel your motivation, knowledge, and performance.';
include $wbPath . 'includes/website-header.php';

// Ensure table + seed
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

$cat    = $_GET['cat'] ?? '';
$where  = $cat ? "WHERE is_active=1 AND category='" . $conn->real_escape_string($cat) . "'" : "WHERE is_active=1";
$facts  = $conn->query("SELECT * FROM athlete_facts $where ORDER BY sort_order ASC, id ASC");
$cats   = $conn->query("SELECT DISTINCT category FROM athlete_facts WHERE is_active=1 ORDER BY category ASC");
?>

<div class="page-hero">
    <div class="page-hero-inner">
        <div class="page-hero-tag">Did You Know?</div>
        <h1 class="page-hero-title">Athlete <span>Facts</span></h1>
        <p class="page-hero-sub">Science-backed facts about fitness, the human body, and athletic performance — to fuel your knowledge and motivation.</p>
        <div class="page-breadcrumb">
            <a href="<?= $wbPath ?>home.php">Home</a> › <span>Athlete Facts</span>
        </div>
    </div>
</div>

<section class="section section-alt">
    <div class="section-inner">

        <!-- Category Filter -->
        <div class="filter-tabs">
            <a href="facts.php" class="filter-tab <?= !$cat ? 'active' : '' ?>">All</a>
            <?php while ($c = $cats->fetch_assoc()): ?>
            <a href="?cat=<?= urlencode($c['category']) ?>" class="filter-tab <?= $cat === $c['category'] ? 'active' : '' ?>"><?= htmlspecialchars($c['category']) ?></a>
            <?php endwhile; ?>
        </div>

        <div class="facts-grid">
            <?php if ($facts->num_rows > 0):
                $i = 0;
                while ($f = $facts->fetch_assoc()): ?>
            <div class="fact-card reveal" style="transition-delay:<?= ($i % 3) * 0.1 ?>s">
                <div class="fact-category"><?= htmlspecialchars($f['category']) ?></div>
                <div class="fact-icon"><?= htmlspecialchars($f['icon']) ?></div>
                <div class="fact-title"><?= htmlspecialchars($f['title']) ?></div>
                <p class="fact-text"><?= htmlspecialchars($f['fact_text']) ?></p>
            </div>
            <?php $i++; endwhile; else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--white-40)">No facts found.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA to join -->
<section class="section">
    <div class="section-inner">
        <div style="text-align:center;max-width:600px;margin:0 auto;display:flex;flex-direction:column;align-items:center;gap:20px;">
            <div class="section-tag">Apply the Science</div>
            <h2 class="section-title">Put the <span>Facts</span> to Work</h2>
            <p style="color:var(--white-70);line-height:1.75">Knowledge is only valuable when applied. Join FitZone and work with trainers who apply these principles to your program every single day.</p>
            <a href="<?= $wbPath ?>pages/website/plans.php" class="btn-primary-ws">View Membership Plans →</a>
        </div>
    </div>
</section>

<?php include $wbPath . 'includes/website-footer.php'; ?>
