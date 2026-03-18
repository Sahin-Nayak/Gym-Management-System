<?php
/**
 * Shared public website header
 * Variables expected from calling page:
 *   $wbPath    — relative path back to root e.g. '../../' or ''
 *   $wbPage    — active nav key e.g. 'about', 'blog', 'plans'
 *   $wbTitle   — page <title> text
 *   $wbDesc    — optional meta description
 */
if (!isset($wbPath))  $wbPath  = '';
if (!isset($wbPage))  $wbPage  = '';
if (!isset($wbTitle)) $wbTitle = 'FitZone Gym';
if (!isset($wbDesc))  $wbDesc  = 'FitZone Gym — State-of-the-art fitness facility with expert trainers, 80+ weekly classes, and flexible memberships.';

require_once $wbPath . 'includes/config.php';

// Website CMS helper (cached)
function wc($key, $default = '') {
    global $conn;
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        // Create table if missing
        $conn->query("CREATE TABLE IF NOT EXISTS website_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            section_key VARCHAR(80) NOT NULL UNIQUE,
            label VARCHAR(120) NOT NULL,
            value TEXT,
            type ENUM('text','textarea','url','email','phone','toggle') DEFAULT 'text',
            section_group VARCHAR(60) DEFAULT 'general',
            sort_order INT DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $res = $conn->query("SELECT section_key, value FROM website_content");
        if ($res) while ($row = $res->fetch_assoc()) $cache[$row['section_key']] = $row['value'];
    }
    return $cache[$key] ?? $default;
}

$_showAnn = (wc('announcement_show', '1') === '1');
$_annText = wc('announcement_text', '');

$navLinks = [
    'home'       => ['Home',         $wbPath . 'home.php'],
    'about'      => ['About',        $wbPath . 'pages/website/about.php'],
    'services'   => ['Services',     $wbPath . 'pages/website/services.php'],
    'plans'      => ['Plans',        $wbPath . 'pages/website/plans.php'],
    'trainers'   => ['Trainers',     $wbPath . 'pages/website/trainers.php'],
    'classes'    => ['Classes',      $wbPath . 'pages/website/classes.php'],
    'blog'       => ['Blog',         $wbPath . 'pages/website/blog.php'],
    'facts'      => ['Athlete Facts',$wbPath . 'pages/website/facts.php'],
    'contact'    => ['Contact',      $wbPath . 'pages/website/contact.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($wbDesc) ?>">
    <title><?= htmlspecialchars($wbTitle) ?> — FitZone Gym</title>
    <link rel="stylesheet" href="<?= $wbPath ?>assets/css/website.css">
</head>
<body>

<?php if ($_showAnn && $_annText): ?>
<div class="announcement-bar">
    <?= htmlspecialchars($_annText) ?>
    <a href="<?= $wbPath ?>pages/website/plans.php">View Plans →</a>
</div>
<?php endif; ?>

<nav class="navbar" id="navbar">
    <div class="navbar-inner">
        <a href="<?= $wbPath ?>home.php" class="navbar-brand">
            <div class="navbar-logo">💪</div>
            <h1>FITZONE</h1>
        </a>

        <ul class="navbar-links">
            <?php foreach ($navLinks as $key => [$label, $href]):
                if ($key === 'home') continue; // skip home in desktop nav
            ?>
            <li>
                <a href="<?= $href ?>" <?= $wbPage === $key ? 'class="nav-active"' : '' ?>>
                    <?= $label ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="navbar-actions">
            <a href="<?= $wbPath ?>login.php" class="btn-nav-login">Member Login</a>
            <a href="<?= $wbPath ?>pages/website/plans.php" class="btn-nav-cta">Join Now</a>
            <button class="nav-hamburger" onclick="toggleMobileNav()" aria-label="Menu">☰</button>
        </div>
    </div>
</nav>

<!-- Mobile nav -->
<div class="mobile-nav" id="mobileNav">
    <div class="mobile-nav-header">
        <a href="<?= $wbPath ?>home.php" class="navbar-brand">
            <div class="navbar-logo">💪</div>
            <h1>FITZONE</h1>
        </a>
        <button class="close-nav" onclick="toggleMobileNav()">✕</button>
    </div>
    <ul class="mobile-nav-links">
        <?php foreach ($navLinks as $key => [$label, $href]): ?>
        <li>
            <a href="<?= $href ?>" onclick="toggleMobileNav()" <?= $wbPage === $key ? 'style="color:var(--red);border-left-color:var(--red)"' : '' ?>>
                <?= $label ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <a href="<?= $wbPath ?>login.php" class="btn-primary-ws" style="display:block;text-align:center;margin-top:auto;">Member Login →</a>
</div>
