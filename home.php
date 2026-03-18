<?php
require_once 'includes/config.php';

// Auto-create + seed website_content table
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

$count = $conn->query("SELECT COUNT(*) as c FROM website_content")->fetch_assoc()['c'];
if ($count == 0) {
    $defaults = [
        ['announcement_text',  'Announcement Bar Text',   '🔥 Limited time: Get 3 months free with annual membership! Join today.',  'text',    'general', 1],
        ['announcement_show',  'Show Announcement Bar',   '1',                                                                        'toggle',  'general', 2],
        ['hero_title',         'Hero Title',              "TRANSFORM\nYOUR BODY.\nTRANSFORM\nYOUR LIFE.",                            'textarea','hero',    1],
        ['hero_subtitle',      'Hero Subtitle',           'State-of-the-art facilities, world-class trainers, and a community that pushes you to be your best every single day.', 'textarea','hero',2],
        ['hero_cta_primary',   'Hero Primary Button',     'Start Free Trial',                                                         'text',    'hero',    3],
        ['hero_cta_secondary', 'Hero Secondary Button',   'View Membership Plans',                                                    'text',    'hero',    4],
        ['hero_badge',         'Hero Badge Text',         '🏆 Rated #1 Gym in the City',                                             'text',    'hero',    5],
        ['stats_members',      'Stats: Total Members',    '1,200+',                                                                   'text',    'stats',   1],
        ['stats_trainers',     'Stats: Expert Trainers',  '25+',                                                                      'text',    'stats',   2],
        ['stats_classes',      'Stats: Weekly Classes',   '80+',                                                                      'text',    'stats',   3],
        ['stats_years',        'Stats: Years Experience', '10+',                                                                      'text',    'stats',   4],
        ['about_title',        'About: Title',            'More Than a Gym — A Lifestyle',                                            'text',    'about',   1],
        ['about_text',         'About: Description',      "At FitZone, we believe fitness is not just about the body — it's about the mind, confidence, and community. Our world-class facility is equipped with cutting-edge technology and staffed by passionate trainers dedicated to your transformation.\n\nWhether you're a beginner taking your first step or an elite athlete pushing your limits, we have the programs, equipment, and support to take you further.", 'textarea','about',2],
        ['about_h1',           'About: Highlight 1',     '🏋️ State-of-the-art equipment updated every year',                       'text',    'about',   3],
        ['about_h2',           'About: Highlight 2',     '👥 Expert certified trainers with 5+ years experience',                   'text',    'about',   4],
        ['about_h3',           'About: Highlight 3',     '📅 Flexible memberships with no hidden fees',                             'text',    'about',   5],
        ['about_h4',           'About: Highlight 4',     '🔥 300+ classes per month across all fitness levels',                     'text',    'about',   6],
        ['about_years',        'About: Years Badge',      '10',                                                                       'text',    'about',   7],
        ['about_years_text',   'About: Years Label',      'Years of Excellence',                                                      'text',    'about',   8],
        ['services_show',      'Show Services Section',   '1',                                                                        'toggle',  'services',1],
        ['plans_show',         'Show Plans Section',      '1',                                                                        'toggle',  'plans',   1],
        ['plans_title',        'Plans Section Title',     'Choose Your Plan',                                                         'text',    'plans',   2],
        ['plans_subtitle',     'Plans Section Subtitle',  'Flexible options for every fitness goal and budget. No long-term commitments.', 'textarea','plans',3],
        ['trainers_show',      'Show Trainers Section',   '1',                                                                        'toggle',  'trainers',1],
        ['classes_show',       'Show Classes Section',    '1',                                                                        'toggle',  'classes', 1],
        ['contact_address',    'Contact: Address',        '123 Fitness Street, Sports Complex, Mumbai - 400001',                     'textarea','contact', 1],
        ['contact_phone',      'Contact: Phone',          '+91 98765 43210',                                                          'phone',   'contact', 2],
        ['contact_email',      'Contact: Email',          'info@fitzonegym.com',                                                      'email',   'contact', 3],
        ['contact_hours_wd',   'Contact: Weekday Hours',  'Mon – Fri: 5:00 AM – 11:00 PM',                                           'text',    'contact', 4],
        ['contact_hours_we',   'Contact: Weekend Hours',  'Sat – Sun: 6:00 AM – 10:00 PM',                                           'text',    'contact', 5],
        ['social_instagram',   'Social: Instagram URL',   '#',                                                                        'url',     'social',  1],
        ['social_facebook',    'Social: Facebook URL',    '#',                                                                        'url',     'social',  2],
        ['social_twitter',     'Social: Twitter/X URL',   '#',                                                                        'url',     'social',  3],
        ['social_youtube',     'Social: YouTube URL',     '#',                                                                        'url',     'social',  4],
        ['footer_tagline',     'Footer: Tagline',         'Your transformation starts here. Join the FitZone family and become the best version of yourself.', 'textarea','footer',1],
        ['footer_copyright',   'Footer: Copyright',       '© 2025 FitZone Gym. All rights reserved.',                                'text',    'footer',  2],
        // About image
        ['about_image',        'About: Section Image URL', '',                                                                          'url',     'about',   9],
        // WhatsApp
        ['whatsapp_number',    'WhatsApp Number',          '',                                                                          'phone',   'general', 7],
        // Videos section
        ['videos_show',        'Show Videos Section',      '1',                                                                         'toggle',  'videos',  1],
        ['videos_title',       'Videos: Section Title',    'Inside FitZone',                                                            'text',    'videos',  2],
        ['videos_subtitle',    'Videos: Subtitle',         'See our world-class facilities and training sessions in action.',           'textarea','videos',  3],
        // Gallery section
        ['gallery_show',       'Show Gallery Section',     '1',                                                                         'toggle',  'gallery', 1],
        ['gallery_title',      'Gallery: Section Title',   'Gym Gallery',                                                               'text',    'gallery', 2],
        ['gallery_subtitle',   'Gallery: Subtitle',        'A glimpse into the FitZone experience.',                                    'textarea','gallery', 3],
        // Location section
        ['location_show',      'Show Location Section',    '1',                                                                         'toggle',  'location',1],
        ['location_map_embed', 'Location: Google Maps Embed (paste full iframe code)', '',                                              'textarea','location',2],
        ['location_title',     'Location: Section Title',  'Find Us',                                                                   'text',    'location',3],
    ];
    $stmt = $conn->prepare("INSERT IGNORE INTO website_content (section_key, label, value, type, section_group, sort_order) VALUES (?,?,?,?,?,?)");
    foreach ($defaults as $d) {
        $stmt->bind_param("sssssi", $d[0], $d[1], $d[2], $d[3], $d[4], $d[5]);
        $stmt->execute();
    }
}

// Helper: get content value (cached)
function wc($key, $default = '') {
    global $conn;
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $res = $conn->query("SELECT section_key, value FROM website_content");
        while ($row = $res->fetch_assoc()) $cache[$row['section_key']] = $row['value'];
    }
    return $cache[$key] ?? $default;
}

// Fetch live DB data
$plans    = $conn->query("SELECT * FROM membership_plans WHERE is_active=1 ORDER BY price ASC");
$trainers = $conn->query("SELECT t.*, u.username FROM trainers t JOIN users u ON t.user_id=u.id WHERE t.status='active' ORDER BY t.id ASC LIMIT 8");
$classes  = $conn->query("SELECT * FROM gym_classes WHERE is_active=1 ORDER BY id ASC LIMIT 9");
$totalMembers   = (int)$conn->query("SELECT COUNT(*) as c FROM members")->fetch_assoc()['c'];
$activeTrainers = (int)$conn->query("SELECT COUNT(*) as c FROM trainers WHERE status='active'")->fetch_assoc()['c'];
$totalClasses   = (int)$conn->query("SELECT COUNT(*) as c FROM gym_classes WHERE is_active=1")->fetch_assoc()['c'];

$services = [
    ['💪', 'Strength Training',   'Build muscle and power with free weights, machines, and expert strength coaches.'],
    ['🏃', 'Cardio Zone',         'Treadmills, bikes, rowers and HIIT equipment for every cardio goal.'],
    ['🧘', 'Yoga & Mindfulness',  'Restore balance and flexibility with daily yoga and stretch classes.'],
    ['🥊', 'Boxing & MMA',        'Learn self-defense while shredding fat in high-energy martial arts sessions.'],
    ['🏊', 'Swimming Pool',        'Olympic-size heated pool with swim coaching for all levels.'],
    ['🍎', 'Nutrition Coaching',   'Personalized diet plans and ongoing nutrition support for your goals.'],
];

$testimonials = [
    ['Rohit S.',  '★★★★★', 'Lost 18kg in 4 months! The trainers are incredibly motivating and the facilities are world-class. Best decision I ever made.', 'Member since 2022'],
    ['Priya M.',  '★★★★★', 'The yoga and strength classes completely transformed my posture and confidence. I recommend FitZone to everyone I know.', 'Member since 2021'],
    ['Arjun K.',  '★★★★★', 'Trained at 5 different gyms. FitZone is hands down the best — equipment, trainers, atmosphere. Nothing comes close.', 'Member since 2023'],
];

// Auto-create athlete_facts table + seed
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
$fcnt = $conn->query("SELECT COUNT(*) as c FROM athlete_facts")->fetch_assoc()['c'];
if ($fcnt == 0) {
    $fdefs = [
        ['🫀','Heart Beats 100,000x Daily','Your heart beats approximately 100,000 times per day, pumping about 2,000 gallons of blood.','Cardio',1],
        ['💪','Muscle Burns 3x More Calories','Muscle tissue burns 3× more calories than fat tissue, even at rest. More muscle = faster metabolism.','Strength',2],
        ['🧠','Exercise Grows Your Brain','30 mins of cardio increases BDNF by up to 200%, dramatically improving memory and focus.','Science',3],
        ['😴','Sleep Builds Muscle','80% of muscle repair happens during deep sleep. 7–9 hours nightly is non-negotiable for gains.','Recovery',4],
        ['🔥','Afterburn Lasts 38 Hours','High-intensity training elevates your metabolism for up to 38 hours post-workout.','Cardio',5],
        ['💧','2% Dehydration = 25% Less Power','Even slight dehydration dramatically cuts athletic performance. Hydrate before you feel thirsty.','Nutrition',6],
    ];
    $fstmt = $conn->prepare("INSERT INTO athlete_facts (icon,title,fact_text,category,sort_order) VALUES (?,?,?,?,?)");
    foreach ($fdefs as $fd) { $fstmt->bind_param("ssssi",$fd[0],$fd[1],$fd[2],$fd[3],$fd[4]); $fstmt->execute(); }
}
$facts6 = $conn->query("SELECT * FROM athlete_facts WHERE is_active=1 ORDER BY sort_order ASC LIMIT 6");

// Auto-create blogs table + seed
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
$bcnt = $conn->query("SELECT COUNT(*) as c FROM blogs")->fetch_assoc()['c'];
if ($bcnt == 0) {
    $bsamples = [
        ['5 Proven Strategies to Break Through a Fitness Plateau','5-proven-strategies-fitness-plateau','Every gym-goer hits a wall eventually. Here\'s how the pros push past it with proven science-backed methods.','🏋️','Training','plateau,gains,training tips'],
        ['The Ultimate Guide to Gym Nutrition Timing','ultimate-guide-gym-nutrition-timing','When you eat is almost as important as what you eat. This guide breaks down pre, intra, and post-workout nutrition.','🥗','Nutrition','nutrition,protein,meal timing'],
        ['How to Stay Motivated When You Don\'t Feel Like Training','stay-motivated-dont-feel-like-training','Motivation is unreliable. Build systems instead. Here\'s how top athletes stay consistent year-round.','🔥','Motivation','motivation,mindset,consistency'],
    ];
    $bstmt = $conn->prepare("INSERT INTO blogs (title,slug,excerpt,cover_emoji,category,tags,is_published,published_at) VALUES (?,?,?,?,?,?,1,NOW())");
    foreach ($bsamples as $bs) { $bstmt->bind_param("ssssss",$bs[0],$bs[1],$bs[2],$bs[3],$bs[4],$bs[5]); $bstmt->execute(); }
}
$latestBlogs = $conn->query("SELECT * FROM blogs WHERE is_published=1 ORDER BY published_at DESC LIMIT 3");

// Auto-create gym_videos table + fetch
$conn->query("CREATE TABLE IF NOT EXISTS gym_videos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200),
    youtube_url VARCHAR(300) NOT NULL,
    sort_order  INT DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$gymVideos = $conn->query("SELECT * FROM gym_videos WHERE is_active=1 ORDER BY sort_order ASC, id ASC");

// Auto-create gym_gallery table + fetch
$conn->query("CREATE TABLE IF NOT EXISTS gym_gallery (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    image_path  VARCHAR(255) NOT NULL,
    caption     VARCHAR(200),
    sort_order  INT DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$gymGallery = $conn->query("SELECT * FROM gym_gallery WHERE is_active=1 ORDER BY sort_order ASC, id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitZone Gym — State-of-the-art fitness facility with expert trainers, 80+ weekly classes, and flexible memberships.">
    <title>FitZone Gym — Transform Your Body, Transform Your Life</title>
    <link rel="stylesheet" href="assets/css/website.css">
</head>
<body>

<!-- ANNOUNCEMENT BAR -->
<?php if (wc('announcement_show', '1') === '1' && wc('announcement_text')): ?>
<div class="announcement-bar">
    <?= htmlspecialchars(wc('announcement_text')) ?>
    <a href="#plans">View Plans →</a>
</div>
<?php endif; ?>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
    <div class="navbar-inner">
        <a href="home.php" class="navbar-brand">
            <div class="navbar-logo">💪</div>
            <h1>FITZONE</h1>
        </a>
        <ul class="navbar-links">
            <li><a href="#about">About</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#plans">Plans</a></li>
            <li><a href="#trainers">Trainers</a></li>
            <li><a href="#bmi">BMI</a></li>
            <li><a href="#classes">Classes</a></li>
            <li><a href="pages/website/facts.php">Facts</a></li>
            <li><a href="pages/website/blog.php">Blog</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <div class="navbar-actions">
            <a href="login.php" class="btn-nav-login">Member Login</a>
            <a href="#plans" class="btn-nav-cta">Join Now</a>
            <button class="nav-hamburger" onclick="toggleMobileNav()" aria-label="Menu">☰</button>
        </div>
    </div>
</nav>

<!-- MOBILE NAV -->
<div class="mobile-nav" id="mobileNav">
    <div class="mobile-nav-header">
        <a href="home.php" class="navbar-brand">
            <div class="navbar-logo">💪</div>
            <h1>FITZONE</h1>
        </a>
        <button class="close-nav" onclick="toggleMobileNav()">✕</button>
    </div>
    <ul class="mobile-nav-links">
        <li><a href="#about"    onclick="toggleMobileNav()">About</a></li>
        <li><a href="#services" onclick="toggleMobileNav()">Services</a></li>
        <li><a href="#plans"    onclick="toggleMobileNav()">Plans</a></li>
        <li><a href="#trainers" onclick="toggleMobileNav()">Trainers</a></li>
        <li><a href="#classes"  onclick="toggleMobileNav()">Classes</a></li>
        <li><a href="#contact"  onclick="toggleMobileNav()">Contact</a></li>
    </ul>
    <a href="login.php" class="btn-primary-ws" style="display:block;text-align:center;margin-top:auto;">Member Login →</a>
</div>

<!-- HERO -->
<section class="hero" id="home">
    <div class="hero-noise"></div>
    <div class="hero-inner">

        <!-- Left: Text -->
        <div class="hero-content">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                <?= htmlspecialchars(wc('hero_badge', '🏆 Rated #1 Gym in the City')) ?>
            </div>


            <h1 class="hero-title">
                <?php
                $lines = explode("\n", wc('hero_title', "TRANSFORM\nYOUR BODY.\nTRANSFORM\nYOUR LIFE."));
                foreach ($lines as $i => $line) {
                    // Even lines: white solid; odd lines: red accent
                    if ($i % 2 === 1) echo '<span>' . htmlspecialchars($line) . '</span>';
                    else echo htmlspecialchars($line);
                    if ($i < count($lines) - 1) echo '<br>';
                }
                ?>
            </h1>

            <div class="hero-divider"></div>
            <p class="hero-subtitle"><?= nl2br(htmlspecialchars(wc('hero_sub_headline', wc('hero_subtitle', '')))) ?></p>

            <div class="hero-actions">
                <a href="#plans" class="btn-hero-primary">
                    <?= htmlspecialchars(wc('hero_cta_primary', 'Start Free Trial')) ?> →
                </a>
                <a href="#about" class="btn-hero-secondary">
                    <?= htmlspecialchars(wc('hero_cta_secondary', 'Learn More')) ?>
                </a>
            </div>

            <div class="hero-trust">
                <div class="hero-avatars">
                    <span>R</span><span>P</span><span>A</span><span>M</span>
                </div>
                <div class="hero-trust-text">
                    <strong><?= htmlspecialchars(wc('stats_members', $totalMembers . '+')) ?></strong> members already transforming
                </div>
            </div>
        </div>

        <!-- Right: Metric Cards -->
        <div class="hero-visual">
            <div class="hero-metric-card large">
                <div class="metric-icon">🏋️</div>
                <div>
                    <div class="metric-label">Active Members</div>
                    <div class="metric-value red"><?= $totalMembers ?>+</div>
                    <div class="metric-desc">Transforming their bodies right now</div>
                </div>
            </div>

            <div class="hero-metric-card">
                <div class="metric-icon">📆</div>
                <div class="metric-label">Weekly Classes</div>
                <div class="metric-value white"><?= $totalClasses ?>+</div>
                <div class="metric-sub">all levels</div>
            </div>

            <div class="hero-metric-card">
                <div class="metric-icon">⭐</div>
                <div class="metric-label">Rating</div>
                <div class="metric-value gold">4.9</div>
                <div class="metric-sub">500+ reviews</div>
            </div>

            <div class="hero-metric-card" style="grid-column:1/-1;">
                <div class="metric-icon">👨‍🏫</div>
                <div>
                    <div class="metric-label">Expert Trainers</div>
                    <div class="metric-value white" style="font-size:2rem;"><?= $activeTrainers ?>+ certified professionals</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS TICKER -->
<div class="stats-ticker">
    <div class="stats-ticker-inner">
        <?php
        $tickerItems = [
            [wc('stats_members', $totalMembers.'+'), 'Happy Members'],
            [wc('stats_trainers', $activeTrainers.'+'), 'Expert Trainers'],
            [wc('stats_classes', $totalClasses.'+'), 'Weekly Classes'],
            [wc('stats_years', '10+'), 'Years Experience'],
            ['5AM–11PM', 'Open Daily'],
            ['No Contract', 'Flexible Plans'],
        ];
        // Duplicate for seamless scroll
        $all = array_merge($tickerItems, $tickerItems);
        foreach ($all as $item): ?>
        <div class="stats-ticker-item">
            <div class="stats-ticker-num"><?= htmlspecialchars($item[0]) ?></div>
            <div class="stats-ticker-label"><?= htmlspecialchars($item[1]) ?></div>
        </div>
        <div class="stats-ticker-dot"></div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ABOUT -->
<section class="section section-alt" id="about">
    <div class="section-inner">
        <div class="about-grid">
            <div class="about-content reveal">
                <div class="about-eyebrow">About FitZone</div>
                <h2 class="about-title">
                    <?php
                    $aTitle = wc('about_title', 'More Than Just a Gym');
                    $parts  = preg_split('/\s[—–-]\s/', $aTitle, 2);
                    if (count($parts) === 2)
                        echo htmlspecialchars($parts[0]) . ' —<br><span>' . htmlspecialchars($parts[1]) . '</span>';
                    else echo htmlspecialchars($aTitle);
                    ?>
                </h2>
                <?php if (wc('about_subtitle')): ?>
                <p class="about-text" style="font-weight:600;color:var(--white-70);margin-bottom:8px;">
                    <?= nl2br(htmlspecialchars(wc('about_subtitle'))) ?>
                </p>
                <?php endif; ?>
                <p class="about-text"><?= nl2br(htmlspecialchars(wc('about_body', wc('about_text', '')))) ?></p>
                <div class="about-highlights">
                    <?php
                    $highlightKeys = [
                        ['about_feature_1', 'about_h1'],
                        ['about_feature_2', 'about_h2'],
                        ['about_feature_3', 'about_h3'],
                        ['about_feature_4', 'about_h4'],
                    ];
                    foreach ($highlightKeys as [$newKey, $oldKey]):
                        $hv = wc($newKey) ?: wc($oldKey);
                        if (!$hv) continue;
                        preg_match('/^([\x{1F000}-\x{1FFFF}]|[\x{2600}-\x{27BF}]|\S{1,4})\s+(.+)$/u', $hv, $m);
                        $icon = $m[1] ?? '✓'; $text = $m[2] ?? $hv;
                    ?>
                    <div class="about-highlight">
                        <div class="about-highlight-icon"><?= $icon ?></div>
                        <span><?= htmlspecialchars($text) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <a href="#plans" class="btn-primary-ws">Start Your Journey →</a>
            </div>

            <div class="about-visual reveal reveal-delay-2">
                <div class="about-image-box">
                    <?php $aboutImg = wc('about_image',''); ?>
                    <?php if ($aboutImg): ?>
                    <img src="<?= htmlspecialchars($aboutImg) ?>" alt="FitZone Gym" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                    <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:5rem;gap:16px;background:linear-gradient(135deg,var(--black-3),var(--black-4));">
                        💪
                        <span style="font-family:var(--font-body);font-size:0.85rem;color:var(--white-40);font-weight:500;">Upload image from admin</span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="about-stats-grid">
                    <div class="about-stat-box">
                        <div class="about-stat-num"><?= htmlspecialchars(wc('about_years', '10')) ?></div>
                        <div class="about-stat-label"><?= htmlspecialchars(wc('about_years_text', 'Years')) ?></div>
                    </div>
                    <div class="about-stat-box">
                        <div class="about-stat-num"><?= $totalMembers ?>+</div>
                        <div class="about-stat-label">Members</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SERVICES -->
<?php if (wc('services_show', '1') === '1'): ?>
<section class="section" id="services">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">What We Offer</div>
            <h2 class="section-title">
                <?php
                $st = wc('services_title', 'World-Class Services');
                $sw = explode(' ', $st); $sl = array_pop($sw);
                echo htmlspecialchars(implode(' ', $sw)) . ' <span>' . htmlspecialchars($sl) . '</span>';
                ?>
            </h2>
            <p class="section-subtitle"><?= htmlspecialchars(wc('services_subtitle', 'Everything you need to crush your goals — under one roof.')) ?></p>
        </div>
        <div class="services-grid">
            <?php foreach ($services as $i => $s): ?>
            <div class="service-card reveal" style="transition-delay:<?= $i * 0.08 ?>s">
                <div class="service-icon-wrap"><?= $s[0] ?></div>
                <div class="service-title"><?= htmlspecialchars($s[1]) ?></div>
                <p class="service-desc"><?= htmlspecialchars($s[2]) ?></p>
                <div class="service-arrow">Learn more →</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- PLANS -->
<?php if (wc('plans_show', '1') === '1'): ?>
<section class="section section-alt" id="plans">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Pricing</div>
            <h2 class="section-title">
                <?php
                $pt = wc('plans_title', 'Choose Your Plan');
                $words = explode(' ', $pt);
                $last  = array_pop($words);
                echo htmlspecialchars(implode(' ', $words)) . ' <span>' . htmlspecialchars($last) . '</span>';
                ?>
            </h2>
            <p class="section-subtitle"><?= htmlspecialchars(wc('plans_subtitle', 'Flexible options for every goal.')) ?></p>
        </div>

        <div class="plans-grid">
            <?php
            $pi = 0;
            $featuredPlanId = (int) wc('plans_featured_id', 0);
            while ($plan = $plans->fetch_assoc()):
                $featured = $featuredPlanId
                    ? ((int)$plan['id'] === $featuredPlanId)
                    : ($pi === 1);
                $pi++;
            ?>
            <div class="plan-card <?= $featured ? 'featured' : '' ?> reveal" style="transition-delay:<?= $pi * 0.1 ?>s">
                <?php if ($featured): ?><div class="plan-popular">⭐ Most Popular</div><?php endif; ?>
                <div class="plan-name"><?= htmlspecialchars($plan['plan_name']) ?></div>
                <div class="plan-duration"><?= $plan['duration_months'] ?> Month<?= $plan['duration_months'] > 1 ? 's' : '' ?></div>
                <div class="plan-price">
                    <span class="plan-currency">₹</span>
                    <span class="plan-amount"><?= number_format($plan['price']) ?></span>
                    <span class="plan-period">/ <?= $plan['duration_months'] > 1 ? $plan['duration_months'].' mo' : 'mo' ?></span>
                </div>
                <hr class="plan-divider">
                <p class="plan-desc"><?= htmlspecialchars($plan['description'] ?: 'Full access to all gym facilities and equipment.') ?></p>
                <a href="login.php" class="plan-cta <?= $featured ? 'plan-cta-filled' : 'plan-cta-outline' ?>">
                    <?= $featured ? 'Get Started →' : 'Choose Plan' ?>
                </a>
            </div>
            <?php endwhile; ?>
            <?php if ($pi === 0): ?>
            <div class="plan-card" style="grid-column:1/-1;text-align:center;padding:60px">
                <p style="color:var(--white-40)">No membership plans available yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- TRAINERS -->
<?php if (wc('trainers_show', '1') === '1' && $trainers->num_rows > 0): ?>
<section class="section" id="trainers">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Meet the Team</div>
            <h2 class="section-title">
                <?php
                $tt = wc('trainers_title', 'Our Expert Trainers');
                $tw = explode(' ', $tt); $tl = array_pop($tw);
                echo htmlspecialchars(implode(' ', $tw)) . ' <span>' . htmlspecialchars($tl) . '</span>';
                ?>
            </h2>
            <p class="section-subtitle"><?= htmlspecialchars(wc('trainers_subtitle', 'Certified professionals dedicated to your personal best.')) ?></p>
        </div>
        <div class="trainers-grid">
            <?php $ti = 0; while ($tr = $trainers->fetch_assoc()):
                $initials = strtoupper(substr($tr['first_name'], 0, 1) . substr($tr['last_name'] ?? '', 0, 1));
            ?>
            <div class="trainer-card reveal">
                <div class="trainer-avatar-wrap">
                    <!-- Number badge -->
                    <div class="trainer-card-num"><?= str_pad($ti + 1, 2, '0', STR_PAD_LEFT) ?></div>

                    <?php if (!empty($tr['photo']) && file_exists('uploads/trainers/' . $tr['photo'])): ?>
                        <img src="uploads/trainers/<?= htmlspecialchars($tr['photo']) ?>"
                             alt="<?= htmlspecialchars($tr['first_name']) ?>">
                    <?php else: ?>
                        <div class="trainer-avatar-initials"><?= $initials ?></div>
                    <?php endif; ?>

                    <!-- Gradient overlay -->
                    <div class="trainer-overlay"></div>

                    <!-- Info pinned to bottom INSIDE avatar-wrap -->
                    <div class="trainer-info">
                        <div class="trainer-name">
                            <?= htmlspecialchars($tr['first_name'] . ' ' . ($tr['last_name'] ?? '')) ?>
                        </div>
                        <div class="trainer-specialty">
                            <?= htmlspecialchars($tr['specialization'] ?? 'Personal Trainer') ?>
                        </div>
                        <div class="trainer-exp">
                            <?= !empty($tr['experience_years'])
                                ? (int)$tr['experience_years'] . ' years experience'
                                : 'Certified Trainer' ?>
                        </div>
                    </div>

                    <!-- CTA strip INSIDE avatar-wrap -->
                    <div class="trainer-cta"></div>
                </div>
            </div>
            <?php $ti++; endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CLASSES -->
<?php if (wc('classes_show', '1') === '1' && $classes->num_rows > 0): ?>
<section class="section section-alt" id="classes">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Schedule</div>
            <h2 class="section-title">
                <?php
                $ct = wc('classes_title', 'Featured Classes');
                $cw = explode(' ', $ct); $cl = array_pop($cw);
                echo htmlspecialchars(implode(' ', $cw)) . ' <span>' . htmlspecialchars($cl) . '</span>';
                ?>
            </h2>
        <p class="section-subtitle"><?= htmlspecialchars(wc('classes_subtitle', "From high-intensity cardio to relaxing yoga — there's a class for every body.")) ?></p>
        </div>
        <div class="classes-grid">
            <?php
            $classIcons = ['🏋️','🏃','🧘','🥊','💃','🚴','🤸','🏊','⚡'];
            $ci = 0;
            while ($cls = $classes->fetch_assoc()):
            ?>
            <div class="class-card reveal" style="transition-delay:<?= ($ci % 3) * 0.08 ?>s">
                <div class="class-icon-wrap"><?= $classIcons[$ci % count($classIcons)] ?></div>
                <div class="class-info">
                    <div class="class-name"><?= htmlspecialchars($cls['class_name']) ?></div>
                    <div class="class-meta">
                        <?= htmlspecialchars($cls['schedule_day'] ?? '') ?>
                        <?php if (!empty($cls['start_time'])): ?> · <?= date('g:i A', strtotime($cls['start_time'])) ?><?php endif; ?>
                        <?php
                        if (!empty($cls['start_time']) && !empty($cls['end_time'])):
                            $dur = (strtotime($cls['end_time']) - strtotime($cls['start_time'])) / 60;
                        ?> · <?= (int)$dur ?> min<?php endif; ?>
                    </div>
                    <?php if (!empty($cls['description'])): ?>
                    <p class="class-desc"><?= htmlspecialchars(substr($cls['description'], 0, 80)) . (strlen($cls['description']) > 80 ? '…' : '') ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php $ci++; endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ATHLETE FACTS -->
<section class="section" id="facts">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Did You Know?</div>
            <h2 class="section-title">Athlete <span>Facts</span></h2>
            <p class="section-subtitle">Science-backed fitness facts to fuel your motivation and knowledge.</p>
        </div>
        <div class="facts-grid">
            <?php if ($facts6 && $facts6->num_rows > 0):
                while ($f = $facts6->fetch_assoc()):
            ?>
            <div class="fact-card reveal">
                <div class="fact-category"><?= htmlspecialchars($f['category']) ?></div>
                <div class="fact-icon"><?= htmlspecialchars($f['icon']) ?></div>
                <div class="fact-title"><?= htmlspecialchars($f['title']) ?></div>
                <p class="fact-text"><?= htmlspecialchars($f['fact_text']) ?></p>
            </div>
            <?php endwhile; else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--white-40);">No facts yet — add some from the admin panel.</div>
            <?php endif; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="pages/website/facts.php" class="btn-outline-ws">Explore All Facts →</a>
        </div>
    </div>
</section>

<!-- BMI CALCULATOR -->
<section class="section section-alt" id="bmi">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Free Tool</div>
            <h2 class="section-title">BMI <span>Calculator</span></h2>
            <p class="section-subtitle">Find your Body Mass Index and get a personalised FitZone plan recommendation.</p>
        </div>

        <div class="bmi-layout">

            <!-- LEFT: Inputs -->
            <div class="bmi-form-panel">

                <!-- Unit Toggle -->
                <div class="bmi-unit-toggle">
                    <button class="bmi-unit-btn active" id="bmiUnitMetric" onclick="bmiSetUnit('metric')">Metric (cm / kg)</button>
                    <button class="bmi-unit-btn" id="bmiUnitImperial" onclick="bmiSetUnit('imperial')">Imperial (ft / lbs)</button>
                </div>

                <!-- Gender -->
                <div class="bmi-field-group">
                    <label class="bmi-label">Gender</label>
                    <div class="bmi-gender-row">
                        <button class="bmi-gender-btn active" id="bmiGenderM" onclick="bmiSetGender('male')">
                            <span class="bmi-gender-icon">♂</span> Male
                        </button>
                        <button class="bmi-gender-btn" id="bmiGenderF" onclick="bmiSetGender('female')">
                            <span class="bmi-gender-icon">♀</span> Female
                        </button>
                    </div>
                </div>

                <!-- Age -->
                <div class="bmi-field-group">
                    <label class="bmi-label">Age <span class="bmi-label-val" id="bmiAgeDisplay">25</span></label>
                    <div class="bmi-slider-wrap">
                        <input type="range" class="bmi-slider" id="bmiAge" min="10" max="80" value="25"
                               oninput="document.getElementById('bmiAgeDisplay').textContent=this.value; bmiCalc()">
                        <div class="bmi-slider-track-fill" id="bmiAgeTrack"></div>
                    </div>
                    <div class="bmi-slider-labels"><span>10</span><span>80</span></div>
                </div>

                <!-- Height -->
                <div class="bmi-field-group" id="bmiHeightMetricGroup">
                    <label class="bmi-label">Height (cm) <span class="bmi-label-val" id="bmiHeightDisplay">170</span></label>
                    <div class="bmi-slider-wrap">
                        <input type="range" class="bmi-slider" id="bmiHeightCm" min="120" max="220" value="170"
                               oninput="document.getElementById('bmiHeightDisplay').textContent=this.value+'cm'; bmiCalc()">
                    </div>
                    <div class="bmi-slider-labels"><span>120cm</span><span>220cm</span></div>
                </div>

                <div class="bmi-field-group" id="bmiHeightImperialGroup" style="display:none;">
                    <label class="bmi-label">Height</label>
                    <div class="bmi-input-row">
                        <div class="bmi-num-input-wrap">
                            <input type="number" class="bmi-num-input" id="bmiHeightFt" min="3" max="8" value="5" placeholder="ft" oninput="bmiCalc()">
                            <span class="bmi-num-unit">ft</span>
                        </div>
                        <div class="bmi-num-input-wrap">
                            <input type="number" class="bmi-num-input" id="bmiHeightIn" min="0" max="11" value="7" placeholder="in" oninput="bmiCalc()">
                            <span class="bmi-num-unit">in</span>
                        </div>
                    </div>
                </div>

                <!-- Weight -->
                <div class="bmi-field-group" id="bmiWeightMetricGroup">
                    <label class="bmi-label">Weight (kg) <span class="bmi-label-val" id="bmiWeightDisplay">70</span></label>
                    <div class="bmi-slider-wrap">
                        <input type="range" class="bmi-slider" id="bmiWeightKg" min="30" max="200" value="70"
                               oninput="document.getElementById('bmiWeightDisplay').textContent=this.value+'kg'; bmiCalc()">
                    </div>
                    <div class="bmi-slider-labels"><span>30kg</span><span>200kg</span></div>
                </div>

                <div class="bmi-field-group" id="bmiWeightImperialGroup" style="display:none;">
                    <label class="bmi-label">Weight</label>
                    <div class="bmi-input-row">
                        <div class="bmi-num-input-wrap" style="flex:1">
                            <input type="number" class="bmi-num-input" id="bmiWeightLbs" min="60" max="440" value="154" placeholder="lbs" oninput="bmiCalc()">
                            <span class="bmi-num-unit">lbs</span>
                        </div>
                    </div>
                </div>

                <button class="bmi-calc-btn" onclick="bmiCalc()">Calculate My BMI →</button>
            </div>

            <!-- RIGHT: Result -->
            <div class="bmi-result-panel" id="bmiResultPanel">

                <!-- Gauge -->
                <div class="bmi-gauge-wrap">
                    <svg class="bmi-gauge-svg" viewBox="0 0 220 130" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Arc segments -->
                        <path d="M 20 110 A 90 90 0 0 1 57 37" stroke="#3B82F6" stroke-width="14" stroke-linecap="round" fill="none"/>
                        <path d="M 57 37 A 90 90 0 0 1 110 20" stroke="#22c55e" stroke-width="14" stroke-linecap="round" fill="none"/>
                        <path d="M 110 20 A 90 90 0 0 1 163 37" stroke="#f97316" stroke-width="14" stroke-linecap="round" fill="none"/>
                        <path d="M 163 37 A 90 90 0 0 1 200 110" stroke="#ef4444" stroke-width="14" stroke-linecap="round" fill="none"/>
                        <!-- Needle -->
                        <g id="bmiNeedle" style="transform-origin:110px 110px; transform:rotate(-90deg); transition:transform 0.8s cubic-bezier(.4,0,.2,1)">
                            <line x1="110" y1="110" x2="110" y2="32" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                            <circle cx="110" cy="110" r="6" fill="white"/>
                            <circle cx="110" cy="110" r="3" fill="#fd2023"/>
                        </g>
                        <!-- BMI number -->
                        <text id="bmiGaugeNum" x="110" y="95" text-anchor="middle"
                              font-family="'Bebas Neue',sans-serif" font-size="28" fill="white" letter-spacing="2">--</text>
                        <text id="bmiGaugeLabel" x="110" y="115" text-anchor="middle"
                              font-family="'Outfit',sans-serif" font-size="10" fill="rgba(255,255,255,0.5)" letter-spacing="1">ENTER YOUR DATA</text>
                    </svg>

                    <!-- Scale labels -->
                    <div class="bmi-scale-labels">
                        <span style="color:#3B82F6">Under</span>
                        <span style="color:#22c55e">Normal</span>
                        <span style="color:#f97316">Over</span>
                        <span style="color:#ef4444">Obese</span>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="bmi-stats-row" id="bmiStatsRow">
                    <div class="bmi-stat">
                        <div class="bmi-stat-label">BMI</div>
                        <div class="bmi-stat-val" id="bmiStatBMI">—</div>
                    </div>
                    <div class="bmi-stat">
                        <div class="bmi-stat-label">Category</div>
                        <div class="bmi-stat-val" id="bmiStatCat">—</div>
                    </div>
                    <div class="bmi-stat">
                        <div class="bmi-stat-label">Ideal Weight</div>
                        <div class="bmi-stat-val" id="bmiStatIdeal">—</div>
                    </div>
                    <div class="bmi-stat">
                        <div class="bmi-stat-label">Body Fat Est.</div>
                        <div class="bmi-stat-val" id="bmiStatFat">—</div>
                    </div>
                </div>

                <!-- Recommendation card -->
                <div class="bmi-rec-card" id="bmiRecCard" style="display:none;">
                    <div class="bmi-rec-header">
                        <div class="bmi-rec-icon" id="bmiRecIcon">💪</div>
                        <div>
                            <div class="bmi-rec-title" id="bmiRecTitle">Your Recommended Plan</div>
                            <div class="bmi-rec-subtitle" id="bmiRecSub">Based on your BMI and goals</div>
                        </div>
                    </div>
                    <div class="bmi-rec-tips" id="bmiRecTips"></div>
                    <a href="#plans" class="bmi-rec-cta" id="bmiRecCTA">View Matching Plans →</a>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- BLOG -->
<section class="section section-alt" id="blog">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">From the Blog</div>
            <h2 class="section-title">Latest <span>Articles</span></h2>
            <p class="section-subtitle">Expert insights on training, nutrition, and the fitness lifestyle.</p>
        </div>
        <div class="blog-grid">
            <?php if ($latestBlogs && $latestBlogs->num_rows > 0):
                while ($post = $latestBlogs->fetch_assoc()):
            ?>
            <a href="pages/website/blog-post.php?id=<?= $post['id'] ?>" class="blog-card reveal" style="text-decoration:none;">
                <div class="blog-cover"><?= htmlspecialchars($post['cover_emoji']) ?></div>
                <div class="blog-body">
                    <div class="blog-meta">
                        <span class="blog-category"><?= htmlspecialchars($post['category']) ?></span>
                        <span class="blog-date"><?= date('M d, Y', strtotime($post['published_at'] ?: $post['created_at'])) ?></span>
                    </div>
                    <div class="blog-title"><?= htmlspecialchars($post['title']) ?></div>
                    <p class="blog-excerpt"><?= htmlspecialchars($post['excerpt'] ?: '') ?></p>
                    <div class="blog-read-more">Read Article →</div>
                </div>
            </a>
            <?php endwhile; else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--white-40);">No blog posts yet — create some from the admin panel.</div>
            <?php endif; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="pages/website/blog.php" class="btn-outline-ws">View All Articles →</a>
        </div>
    </div>
</section>

<!-- GYM VIDEOS -->
<?php if (wc('videos_show','1') === '1'): ?>
<section class="section" id="videos">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Watch Us</div>
            <h2 class="section-title"><?php
                $vt = wc('videos_title','Inside FitZone');
                $vw = explode(' ', $vt);
                $vl = array_pop($vw);
                echo (count($vw) ? htmlspecialchars(implode(' ', $vw)) . ' ' : '') . '<span>' . htmlspecialchars($vl) . '</span>';
            ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars(wc('videos_subtitle','See our world-class facilities and training sessions in action.')) ?></p>
        </div>

        <?php if ($gymVideos && $gymVideos->num_rows > 0):
            $videoItems = [];
            $gymVideos->data_seek(0);
            while ($v = $gymVideos->fetch_assoc()) {
                preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $v['youtube_url'], $ytm);
                $v['_ytId'] = $ytm[1] ?? '';
                $videoItems[] = $v;
            }
            $vTotal = count($videoItems);
            $featuredVideo = $videoItems[0];
        ?>

        <div class="videos-layout">

            <!-- Featured Player -->
            <div class="videos-featured">
                <div class="videos-player-wrap" id="videosPlayerWrap">
                    <?php if ($featuredVideo['_ytId']): ?>
                    <iframe id="videosMainFrame"
                            src="https://www.youtube.com/embed/<?= htmlspecialchars($featuredVideo['_ytId']) ?>?rel=0"
                            frameborder="0" allowfullscreen loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                    </iframe>
                    <?php else: ?>
                    <div class="video-placeholder">▶ Video unavailable</div>
                    <?php endif; ?>

                    <!-- Overlay shown before play on mobile -->
                    <div class="videos-player-badge">
                        <span class="videos-live-dot"></span> FitZone Studio
                    </div>
                </div>

                <!-- Active video title -->
                <div class="videos-featured-meta" id="videosFeaturedMeta">
                    <div class="videos-featured-num">
                        <span id="videosActiveNum">1</span> / <?= $vTotal ?>
                    </div>
                    <div class="videos-featured-title" id="videosFeaturedTitle">
                        <?= htmlspecialchars($featuredVideo['title'] ?? 'FitZone Video') ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar Playlist -->
            <div class="videos-playlist">
                <div class="videos-playlist-header">
                    <span class="videos-playlist-icon">▶</span>
                    Playlist <span class="videos-playlist-count"><?= $vTotal ?></span>
                </div>
                <div class="videos-playlist-items" id="videosPlaylist">
                    <?php foreach ($videoItems as $vi => $v): ?>
                    <div class="videos-playlist-item <?= $vi === 0 ? 'active' : '' ?>"
                         data-ytid="<?= htmlspecialchars($v['_ytId']) ?>"
                         data-title="<?= htmlspecialchars($v['title'] ?? '') ?>"
                         data-index="<?= $vi ?>"
                         onclick="videosSelectItem(this)">
                        <div class="videos-playlist-thumb">
                            <?php if ($v['_ytId']): ?>
                            <img src="https://img.youtube.com/vi/<?= htmlspecialchars($v['_ytId']) ?>/mqdefault.jpg"
                                 alt="<?= htmlspecialchars($v['title'] ?? '') ?>" loading="lazy">
                            <div class="videos-playlist-play">▶</div>
                            <?php else: ?>
                            <div class="videos-playlist-no-thumb">🎥</div>
                            <?php endif; ?>
                        </div>
                        <div class="videos-playlist-info">
                            <div class="videos-playlist-num"><?= str_pad($vi + 1, 2, '0', STR_PAD_LEFT) ?></div>
                            <div class="videos-playlist-name">
                                <?= htmlspecialchars($v['title'] ?: 'FitZone Video ' . ($vi + 1)) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /.videos-layout -->

        <?php else: ?>
        <div class="section-empty-state">
            <div class="empty-state-icon">🎥</div>
            <div class="empty-state-title">No Videos Yet</div>
            <p class="empty-state-text">Add gym tour and training videos from the admin panel to showcase your facilities.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>


<!-- GYM GALLERY -->
<?php if (wc('gallery_show','1') === '1'): ?>
<section class="section section-alt" id="gallery">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Gallery</div>
            <h2 class="section-title"><?php
                $gt = wc('gallery_title','Gym Gallery');
                $gw = explode(' ', $gt);
                $gl = array_pop($gw);
                echo (count($gw) ? htmlspecialchars(implode(' ', $gw)) . ' ' : '') . '<span>' . htmlspecialchars($gl) . '</span>';
            ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars(wc('gallery_subtitle','A glimpse into the FitZone experience.')) ?></p>
        </div>

        <?php if ($gymGallery && $gymGallery->num_rows > 0):
            $galleryItems = [];
            $gymGallery->data_seek(0);
            while ($g = $gymGallery->fetch_assoc()) $galleryItems[] = $g;
            $total = count($galleryItems);
        ?>

        <div class="gallery-slider-wrap" id="gallerySlider">

            <!-- Main Stage -->
            <div class="gallery-stage">
                <?php foreach ($galleryItems as $idx => $g): ?>
                <div class="gallery-slide <?= $idx === 0 ? 'active' : ($idx === 1 ? 'next' : '') ?>"
                     data-index="<?= $idx ?>"
                     onclick="openLightbox('uploads/gallery/<?= htmlspecialchars($g['image_path']) ?>','<?= htmlspecialchars(addslashes($g['caption'] ?? '')) ?>')">
                    <img src="uploads/gallery/<?= htmlspecialchars($g['image_path']) ?>"
                         alt="<?= htmlspecialchars($g['caption'] ?? 'FitZone Gallery') ?>" loading="lazy">
                    <?php if (!empty($g['caption'])): ?>
                    <div class="gallery-slide-caption"><?= htmlspecialchars($g['caption']) ?></div>
                    <?php endif; ?>
                    <div class="gallery-slide-overlay">
                        <div class="gallery-slide-zoom">🔍 View Full</div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Counter -->
                <div class="gallery-counter">
                    <span id="galleryCurrentNum">1</span>
                    <span class="gallery-counter-sep">/</span>
                    <span><?= $total ?></span>
                </div>

                <!-- Prev / Next Arrows -->
                <button class="gallery-arrow gallery-arrow-prev" onclick="galleryMove(-1)" aria-label="Previous">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="gallery-arrow gallery-arrow-next" onclick="galleryMove(1)" aria-label="Next">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>

            <!-- Thumbnail Rail -->
            <div class="gallery-thumbs-wrap">
                <div class="gallery-thumbs" id="galleryThumbs">
                    <?php foreach ($galleryItems as $idx => $g): ?>
                    <div class="gallery-thumb <?= $idx === 0 ? 'active' : '' ?>"
                         data-index="<?= $idx ?>"
                         onclick="galleryGoTo(<?= $idx ?>)">
                        <img src="uploads/gallery/<?= htmlspecialchars($g['image_path']) ?>"
                             alt="" loading="lazy">
                        <div class="gallery-thumb-overlay"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- Scroll fade edges -->
                <div class="gallery-thumbs-fade-left"></div>
                <div class="gallery-thumbs-fade-right"></div>
            </div>

            <!-- Dot Indicators (max 10 shown) -->
            <?php if ($total > 1): ?>
            <div class="gallery-dots" id="galleryDots">
                <?php for ($d = 0; $d < min($total, 10); $d++): ?>
                <button class="gallery-dot <?= $d === 0 ? 'active' : '' ?>"
                        onclick="galleryGoTo(<?= $d ?>)" aria-label="Slide <?= $d+1 ?>"></button>
                <?php endfor; ?>
                <?php if ($total > 10): ?>
                <span class="gallery-dots-more">+<?= $total - 10 ?> more</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div class="section-empty-state">
            <div class="empty-state-icon">🖼️</div>
            <div class="empty-state-title">Gallery Coming Soon</div>
            <p class="empty-state-text">Upload photos of your gym equipment, classes, and facilities from the admin panel.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox -->
<div class="ws-lightbox" id="wsLightbox" onclick="closeLightbox()">
    <div class="ws-lightbox-inner" onclick="event.stopPropagation()">
        <button class="ws-lightbox-close" onclick="closeLightbox()">✕</button>
        <img id="wsLightboxImg" src="" alt="">
        <div class="ws-lightbox-caption" id="wsLightboxCaption"></div>
    </div>
</div>
<?php endif; ?>

<!-- LOCATION -->
<?php if (wc('location_show','1') === '1'): $mapEmbed = wc('location_map_embed',''); ?>
<section class="section" id="location">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Find Us</div>
            <h2 class="section-title">Our <span><?= htmlspecialchars(wc('location_title','Location')) ?></span></h2>
        </div>
        <?php if (trim($mapEmbed)): ?>
        <div class="location-map-wrap">
            <?= $mapEmbed /* Admin-controlled Google Maps iframe code */ ?>
        </div>
        <?php else: ?>
        <div class="section-empty-state">
            <div class="empty-state-icon">📍</div>
            <div class="empty-state-title">Add Your Location</div>
            <p class="empty-state-text">Go to Admin → Website Content → Location / Map, paste your Google Maps embed code to show your gym location here.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- TESTIMONIALS -->
<section class="section" id="testimonials">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Success Stories</div>
            <h2 class="section-title">Real <span>Results</span></h2>
            <p class="section-subtitle">Thousands have transformed their lives at FitZone. Here's what they say.</p>
        </div>
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $i => $t): ?>
            <div class="testimonial-card reveal" style="transition-delay:<?= $i * 0.12 ?>s">
                <div class="testimonial-stars"><?= $t[1] ?></div>
                <p class="testimonial-text"><?= htmlspecialchars($t[2]) ?></p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar"><?= substr($t[0], 0, 1) ?></div>
                    <div>
                        <div class="testimonial-name"><?= htmlspecialchars($t[0]) ?></div>
                        <div class="testimonial-since"><?= htmlspecialchars($t[3]) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA BANNER -->
<div class="cta-banner">
    <div class="cta-banner-inner">
        <h2 class="cta-banner-title">Ready to Transform?</h2>
        <p class="cta-banner-sub">Join <?= htmlspecialchars(wc('stats_members', '1,200+')) ?> members who chose FitZone. Start your free trial today — no commitment required.</p>
        <a href="login.php" class="btn-cta-white">Get Started Today →</a>
    </div>
</div>

<!-- CONTACT -->
<section class="section section-alt" id="contact">
    <div class="section-inner">
        <div class="contact-grid">
            <!-- Left: Info -->
            <div class="contact-info reveal">
                <div class="about-eyebrow">Get In Touch</div>
                <h2 class="contact-info-title">Visit <span>FitZone</span> Today</h2>
                <div class="contact-items">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <div class="contact-item-label">Visit Us</div>
                            <div class="contact-item-value"><?= nl2br(htmlspecialchars(wc('contact_address','123 Fitness Street, Mumbai'))) ?></div>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <div class="contact-item-label">Call Us</div>
                            <div class="contact-item-value">
                                <a href="tel:<?= htmlspecialchars(wc('contact_phone')) ?>" style="color:var(--white-70)"><?= htmlspecialchars(wc('contact_phone','+91 98765 43210')) ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div>
                            <div class="contact-item-label">Email Us</div>
                            <div class="contact-item-value">
                                <a href="mailto:<?= htmlspecialchars(wc('contact_email')) ?>" style="color:var(--white-70)"><?= htmlspecialchars(wc('contact_email','info@fitzonegym.com')) ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🕐</div>
                        <div>
                            <div class="contact-item-label">Opening Hours</div>
                            <div class="contact-item-value">
                                <?= htmlspecialchars(wc('contact_hours_wd','Mon – Fri: 5:00 AM – 11:00 PM')) ?><br>
                                <?= htmlspecialchars(wc('contact_hours_we','Sat – Sun: 6:00 AM – 10:00 PM')) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--white-40);margin-bottom:12px;">Follow Us</div>
                    <div class="social-row">
                        <?php
                        $socials = [
                            ['social_instagram', '📸', 'Instagram'],
                            ['social_facebook',  '👍', 'Facebook'],
                            ['social_twitter',   '🐦', 'Twitter'],
                            ['social_youtube',   '▶️', 'YouTube'],
                        ];
                        foreach ($socials as [$key, $icon, $label]):
                            $url = wc($key, '#');
                            if ($url === '#') continue;
                        ?>
                        <a href="<?= htmlspecialchars($url) ?>" class="social-pill" target="_blank">
                            <?= $icon ?> <?= $label ?>
                        </a>
                        <?php endforeach; ?>
                        <?php if (wc('social_instagram','#') === '#'): ?>
                        <a href="#" class="social-pill">📸 Instagram</a>
                        <a href="#" class="social-pill">👍 Facebook</a>
                        <a href="#" class="social-pill">🐦 Twitter</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Contact Form -->
            <div class="contact-form-card reveal reveal-delay-2">
                <div class="contact-form-title">Send a Message</div>
                <form id="contactForm" onsubmit="handleContactForm(event)">
                    <div class="form-row">
                        <div class="ws-form-group">
                            <label>Your Name *</label>
                            <input type="text" class="ws-form-control" name="name" placeholder="John Doe" required>
                        </div>
                        <div class="ws-form-group">
                            <label>Phone</label>
                            <input type="tel" class="ws-form-control" name="phone" placeholder="+91 98765 43210">
                        </div>
                    </div>
                    <div class="ws-form-group">
                        <label>Email Address *</label>
                        <input type="email" class="ws-form-control" name="email" placeholder="john@email.com" required>
                    </div>
                    <div class="ws-form-group">
                        <label>Message *</label>
                        <textarea class="ws-form-control" name="message" placeholder="Tell us about your fitness goals, membership enquiry, or anything else…" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary-ws" style="width:100%;justify-content:center;" id="contactSubmitBtn">
                        Send Message →
                    </button>
                    <div class="contact-success" id="contactSuccess"></div>
                    <div class="contact-error"   id="contactError"></div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FLOATING BUTTONS: WhatsApp + Chatbot -->
<div class="ws-float-group">
    <a href="https://wa.me/8850670388" class="ws-float-btn ws-float-whatsapp" 
       target="_blank" rel="noopener" title="Chat on WhatsApp" aria-label="WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor" width="26" height="26">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>
    <button class="ws-float-btn ws-float-chatbot" onclick="toggleChatbot()" title="Free Diet Suggestions" aria-label="Diet Chatbot">
        🤖
    </button>
</div>

<!-- CHATBOT MODAL -->
<div class="ws-chatbot-modal" id="wsChatbotModal">
    <div class="ws-chatbot-header">
        <div class="ws-chatbot-title">
            <span class="ws-chatbot-dot"></span>
            🥗 FitFuel AI — Diet Coach
        </div>
        <button class="ws-chatbot-clear" onclick="clearChatbot()" title="Clear chat">🗑</button>
        <button class="ws-chatbot-close" onclick="toggleChatbot()">✕</button>
    </div>
    <div class="ws-chatbot-body" id="wsChatbotBody">
        <div class="ws-chatbot-welcome">
            <div class="ws-chatbot-welcome-icon">🥗</div>
            <div class="ws-chatbot-welcome-title">FitFuel AI</div>
            <p class="ws-chatbot-welcome-text">Ask me anything about diet, nutrition, meal plans, and supplements!</p>
            <div class="ws-chatbot-chips">
                <button onclick="sendChip(this)">🍱 Give me a meal plan</button>
                <button onclick="sendChip(this)">💪 Bulking diet tips</button>
                <button onclick="sendChip(this)">🥗 Vegetarian gym diet</button>
                <button onclick="sendChip(this)">📊 Calculate my protein</button>
            </div>
        </div>
        <div id="wsChatMessages"></div>
        <div class="ws-chatbot-typing" id="wsChatTyping" style="display:none;">
            <span></span><span></span><span></span>
        </div>
    </div>
    <div class="ws-chatbot-footer">
        <div class="ws-chatbot-input-wrap">
            <input type="text" id="wsChatInput" placeholder="Ask about diet, nutrition, meal plans…"
                   onkeydown="if(event.key==='Enter')sendChatMessage()">
            <button class="ws-chatbot-send" onclick="sendChatMessage()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-main">
        <div class="footer-brand">
            <div class="footer-brand-logo">
                <div class="logo-icon">💪</div>
                <h2>FITZONE</h2>
            </div>
            <p class="footer-tagline"><?= htmlspecialchars(wc('footer_tagline', 'Your transformation starts here.')) ?></p>
            <div class="social-row">
                <a href="<?= htmlspecialchars(wc('social_instagram','#')) ?>" class="social-pill" target="_blank">📸</a>
                <a href="<?= htmlspecialchars(wc('social_facebook','#')) ?>"  class="social-pill" target="_blank">👍</a>
                <a href="<?= htmlspecialchars(wc('social_twitter','#')) ?>"   class="social-pill" target="_blank">🐦</a>
                <a href="<?= htmlspecialchars(wc('social_youtube','#')) ?>"   class="social-pill" target="_blank">▶️</a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul class="footer-links">
                <li><a href="#about">About Us</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#plans">Membership Plans</a></li>
                <li><a href="#trainers">Our Trainers</a></li>
                <li><a href="#classes">Classes</a></li>
                <li><a href="#testimonials">Testimonials</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Services</h4>
            <ul class="footer-links">
                <?php foreach ($services as $s): ?>
                <li><a href="#services"><?= htmlspecialchars($s[1]) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Contact</h4>
            <ul class="footer-links">
                <li><a href="tel:<?= htmlspecialchars(wc('contact_phone')) ?>"><?= htmlspecialchars(wc('contact_phone')) ?></a></li>
                <li><a href="mailto:<?= htmlspecialchars(wc('contact_email')) ?>"><?= htmlspecialchars(wc('contact_email')) ?></a></li>
                <li><a href="#contact"><?= htmlspecialchars(wc('contact_hours_wd')) ?></a></li>
                <li><a href="#contact"><?= htmlspecialchars(wc('contact_hours_we')) ?></a></li>
                <li><a href="login.php">Member Login</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-copy"><?= htmlspecialchars(wc('footer_copyright', '© 2025 FitZone Gym. All rights reserved.')) ?></div>
        <div class="footer-bottom-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="login.php">Admin Login</a>
        </div>
    </div>
</footer>

<script>
// Mobile nav
function toggleMobileNav() {
    document.getElementById('mobileNav').classList.toggle('show');
}

// Navbar scroll
window.addEventListener('scroll', function() {
    var nav = document.getElementById('navbar');
    if (window.scrollY > 60) nav.classList.add('scrolled');
    else nav.classList.remove('scrolled');
});

// Contact form — sends to handle_enquiry.php
function handleContactForm(e) {
    e.preventDefault();
    var btn     = document.getElementById('contactSubmitBtn');
    var success = document.getElementById('contactSuccess');
    var error   = document.getElementById('contactError');
    var form    = e.target;

    btn.textContent = 'Sending…';
    btn.disabled    = true;
    success.style.display = 'none';
    error.style.display   = 'none';

    var data = new FormData(form);

    fetch('handle_enquiry.php', { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                success.textContent    = '✅ ' + res.message;
                success.style.display  = 'block';
                btn.textContent        = 'Message Sent ✓';
                form.reset();
            } else {
                error.textContent   = '⚠️ ' + res.message;
                error.style.display = 'block';
                btn.textContent     = 'Send Message →';
                btn.disabled        = false;
            }
        })
        .catch(function() {
            error.textContent   = '⚠️ Network error. Please try again.';
            error.style.display = 'block';
            btn.textContent     = 'Send Message →';
            btn.disabled        = false;
        });
}

// Scroll reveal (Intersection Observer)
var revealObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.reveal').forEach(function(el) {
    revealObserver.observe(el);
});

// Lightbox
function openLightbox(src, caption) {
    document.getElementById('wsLightboxImg').src = src;
    document.getElementById('wsLightboxCaption').textContent = caption || '';
    document.getElementById('wsLightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('wsLightbox').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeLightbox(); closeChatbot(); } });

// Chatbot modal
function toggleChatbot() {
    var m = document.getElementById('wsChatbotModal');
    if (m) m.classList.toggle('open');
}
function closeChatbot() {
    var m = document.getElementById('wsChatbotModal');
    if (m) m.classList.remove('open');
}

// Active nav link on scroll
var sections = ['home','about','services','plans','trainers','classes','testimonials','contact'];
var navLinks  = document.querySelectorAll('.navbar-links a');

window.addEventListener('scroll', function() {
    var current = '';
    sections.forEach(function(id) {
        var el = document.getElementById(id);
        if (el && window.scrollY >= el.offsetTop - 120) current = id;
    });
    navLinks.forEach(function(link) {
        link.style.color = link.getAttribute('href') === '#' + current ? 'var(--white)' : '';
    });
}, { passive: true });


// ── Gallery Slider ──
(function() {
    var current = 0;
    var slides, thumbs, dots, counter;

    function init() {
        slides  = document.querySelectorAll('.gallery-slide');
        thumbs  = document.querySelectorAll('.gallery-thumb');
        dots    = document.querySelectorAll('.gallery-dot');
        counter = document.getElementById('galleryCurrentNum');
        if (!slides.length) return;

        // Touch/swipe support
        var stage = document.querySelector('.gallery-stage');
        if (stage) {
            var startX = 0;
            stage.addEventListener('touchstart', function(e){ startX = e.touches[0].clientX; }, {passive:true});
            stage.addEventListener('touchend', function(e){
                var dx = e.changedTouches[0].clientX - startX;
                if (Math.abs(dx) > 40) galleryMove(dx < 0 ? 1 : -1);
            }, {passive:true});
        }

        // Autoplay
        setInterval(function(){ galleryMove(1); }, 4500);
    }

    window.galleryGoTo = function(idx) {
        if (!slides || idx === current) return;
        slides[current].classList.remove('active','next','prev');
        if (thumbs[current]) thumbs[current].classList.remove('active');
        if (dots[current])   dots[current].classList.remove('active');

        current = (idx + slides.length) % slides.length;

        slides[current].classList.add('active');
        if (thumbs[current]) {
            thumbs[current].classList.add('active');
            // Scroll thumb into view
            var rail = document.getElementById('galleryThumbs');
            if (rail) {
                var t = thumbs[current];
                rail.scrollTo({ left: t.offsetLeft - rail.clientWidth / 2 + t.clientWidth / 2, behavior: 'smooth' });
            }
        }
        if (dots[current])   dots[current].classList.add('active');
        if (counter)         counter.textContent = current + 1;
    };

    window.galleryMove = function(dir) {
        if (!slides) return;
        window.galleryGoTo(current + dir);
    };

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', init)
        : init();
})();

// ── Videos Playlist ──
function videosSelectItem(el) {
    var ytId = el.dataset.ytid;
    var title = el.dataset.title;
    var idx   = parseInt(el.dataset.index, 10);

    // Update active state
    document.querySelectorAll('.videos-playlist-item').forEach(function(i) {
        i.classList.remove('active');
    });
    el.classList.add('active');

    // Swap iframe src
    var frame = document.getElementById('videosMainFrame');
    if (frame && ytId) {
        frame.src = 'https://www.youtube.com/embed/' + ytId + '?rel=0&autoplay=1';
    }

    // Update meta bar
    var titleEl = document.getElementById('videosFeaturedTitle');
    var numEl   = document.getElementById('videosActiveNum');
    if (titleEl) titleEl.textContent = title || ('FitZone Video ' + (idx + 1));
    if (numEl)   numEl.textContent   = idx + 1;

    // Scroll item into view in sidebar
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}


// ── Chatbot ──
var chatHistory = [];

function toggleChatbot() {
    var m = document.getElementById('wsChatbotModal');
    if (m) m.classList.toggle('open');
}
function closeChatbot() {
    var m = document.getElementById('wsChatbotModal');
    if (m) m.classList.remove('open');
}

function clearChatbot() {
    chatHistory = [];
    document.getElementById('wsChatMessages').innerHTML = '';
    document.getElementById('wsChatTyping').style.display = 'none';
    var welcome = document.querySelector('.ws-chatbot-welcome');
    if (welcome) welcome.style.display = '';
}

function sendChip(btn) {
    document.getElementById('wsChatInput').value = btn.textContent.trim();
    sendChatMessage();
}

function appendMessage(role, text, searched) {
    var welcome = document.querySelector('.ws-chatbot-welcome');
    if (welcome) welcome.style.display = 'none';

    var container = document.getElementById('wsChatMessages');
    var wrap = document.createElement('div');
    wrap.className = 'ws-chat-msg ' + role;

    var bubble = document.createElement('div');
    bubble.className = 'ws-chat-bubble';
    bubble.textContent = text;
    wrap.appendChild(bubble);

    if (role === 'bot' && searched) {
        var badge = document.createElement('div');
        badge.className = 'ws-chat-search-badge';
        badge.textContent = '🔍 Searched online';
        wrap.appendChild(badge);
    }

    container.appendChild(wrap);

    // Scroll to bottom
    var body = document.getElementById('wsChatbotBody');
    body.scrollTop = body.scrollHeight;
}

function sendChatMessage() {
    var input = document.getElementById('wsChatInput');
    var msg   = input.value.trim();
    if (!msg) return;

    input.value = '';
    appendMessage('user', msg, false);
    chatHistory.push({ role: 'user', content: msg });

    // Show typing
    var typing = document.getElementById('wsChatTyping');
    typing.style.display = 'flex';
    var body = document.getElementById('wsChatbotBody');
    body.scrollTop = body.scrollHeight;

    fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg, history: chatHistory })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        typing.style.display = 'none';
        if (data.success && data.message) {
            appendMessage('bot', data.message, data.searched);
            chatHistory.push({ role: 'assistant', content: data.message });
            // Keep history to last 10 exchanges
            if (chatHistory.length > 20) chatHistory = chatHistory.slice(-20);
        } else {
            appendMessage('bot', '⚠️ ' + (data.error || 'Something went wrong. Please try again.'), false);
        }
    })
    .catch(function() {
        typing.style.display = 'none';
        appendMessage('bot', '⚠️ Network error. Please check your connection.', false);
    });
}

// ── BMI Calculator ──
var bmiUnit   = 'metric';
var bmiGender = 'male';

function bmiSetUnit(unit) {
    bmiUnit = unit;
    document.getElementById('bmiUnitMetric').classList.toggle('active', unit === 'metric');
    document.getElementById('bmiUnitImperial').classList.toggle('active', unit === 'imperial');
    document.getElementById('bmiHeightMetricGroup').style.display   = unit === 'metric' ? '' : 'none';
    document.getElementById('bmiWeightMetricGroup').style.display   = unit === 'metric' ? '' : 'none';
    document.getElementById('bmiHeightImperialGroup').style.display = unit === 'imperial' ? '' : 'none';
    document.getElementById('bmiWeightImperialGroup').style.display = unit === 'imperial' ? '' : 'none';
    bmiCalc();
}

function bmiSetGender(g) {
    bmiGender = g;
    document.getElementById('bmiGenderM').classList.toggle('active', g === 'male');
    document.getElementById('bmiGenderF').classList.toggle('active', g === 'female');
    bmiCalc();
}

function bmiCalc() {
    var heightCm, weightKg, age;

    age = parseInt(document.getElementById('bmiAge').value);

    if (bmiUnit === 'metric') {
        heightCm = parseFloat(document.getElementById('bmiHeightCm').value);
        weightKg = parseFloat(document.getElementById('bmiWeightKg').value);
        // Update slider fill colour by setting background gradient
        var hSlider = document.getElementById('bmiHeightCm');
        var wSlider = document.getElementById('bmiWeightKg');
        bmiUpdateSlider(hSlider, 120, 220);
        bmiUpdateSlider(wSlider, 30, 200);
    } else {
        var ft = parseFloat(document.getElementById('bmiHeightFt').value) || 0;
        var inch = parseFloat(document.getElementById('bmiHeightIn').value) || 0;
        heightCm = (ft * 12 + inch) * 2.54;
        weightKg = (parseFloat(document.getElementById('bmiWeightLbs').value) || 0) * 0.453592;
    }

    bmiUpdateSlider(document.getElementById('bmiAge'), 10, 80);

    if (!heightCm || !weightKg || heightCm < 50) return;

    var heightM = heightCm / 100;
    var bmi = weightKg / (heightM * heightM);
    bmi = Math.round(bmi * 10) / 10;

    // Category
    var cat, catColor;
    if (bmi < 18.5)      { cat = 'Underweight'; catColor = '#3B82F6'; }
    else if (bmi < 25)   { cat = 'Normal';       catColor = '#22c55e'; }
    else if (bmi < 30)   { cat = 'Overweight';   catColor = '#f97316'; }
    else                 { cat = 'Obese';         catColor = '#ef4444'; }

    // Ideal weight (Devine formula)
    var idealMin, idealMax;
    if (bmiGender === 'male') {
        var base = 50 + 2.3 * ((heightCm / 2.54) - 60);
        idealMin = Math.round((base - 4) * 10) / 10;
        idealMax = Math.round((base + 4) * 10) / 10;
    } else {
        var base = 45.5 + 2.3 * ((heightCm / 2.54) - 60);
        idealMin = Math.round((base - 4) * 10) / 10;
        idealMax = Math.round((base + 4) * 10) / 10;
    }
    idealMin = Math.max(idealMin, 40);

    // Body fat estimate (Deurenberg formula)
    var fatPct = (1.2 * bmi) + (0.23 * age) - (10.8 * (bmiGender === 'male' ? 1 : 0)) - 5.4;
    fatPct = Math.round(fatPct * 10) / 10;
    fatPct = Math.max(5, Math.min(60, fatPct));

    // Update gauge needle
    // Needle range: -90deg (BMI≤16) to +90deg (BMI≥40)
    var needleAngle = Math.min(90, Math.max(-90, ((bmi - 16) / 24) * 180 - 90));
    document.getElementById('bmiNeedle').style.transform = 'rotate(' + needleAngle + 'deg)';
    document.getElementById('bmiGaugeNum').textContent = bmi;
    var gaugeLabel = document.getElementById('bmiGaugeLabel');
    gaugeLabel.textContent = cat.toUpperCase();
    gaugeLabel.setAttribute('fill', catColor);

    // Stats
    document.getElementById('bmiStatBMI').textContent  = bmi;
    document.getElementById('bmiStatBMI').style.color  = catColor;
    document.getElementById('bmiStatCat').textContent  = cat;
    document.getElementById('bmiStatCat').style.color  = catColor;
    document.getElementById('bmiStatIdeal').textContent = idealMin + '–' + idealMax + ' kg';
    document.getElementById('bmiStatFat').textContent  = fatPct + '%';

    // Recommendation
    var rec = bmiGetRec(cat, bmiGender);
    var card = document.getElementById('bmiRecCard');
    document.getElementById('bmiRecIcon').textContent  = rec.icon;
    document.getElementById('bmiRecTitle').textContent = rec.title;
    document.getElementById('bmiRecSub').textContent   = rec.subtitle;
    var tipsHtml = '';
    rec.tips.forEach(function(tip) {
        tipsHtml += '<div class="bmi-rec-tip">' + tip + '</div>';
    });
    document.getElementById('bmiRecTips').innerHTML = tipsHtml;
    card.style.display = '';
}

function bmiUpdateSlider(slider, min, max) {
    var pct = ((slider.value - min) / (max - min)) * 100;
    slider.style.background = 'linear-gradient(to right, var(--red) 0%, var(--red) ' + pct + '%, var(--black-5) ' + pct + '%, var(--black-5) 100%)';
}

function bmiGetRec(cat, gender) {
    var recs = {
        'Underweight': {
            icon: '🍗',
            title: 'Mass Building Plan',
            subtitle: 'Focus on healthy weight gain with structured nutrition',
            tips: [
                'Increase daily calorie intake by 300–500 kcal above maintenance',
                'Prioritise protein: 1.6–2g per kg of bodyweight daily',
                'Focus on compound lifts: squats, deadlifts, bench press',
                'Our Strength Training & Nutrition Coaching combo is ideal for you'
            ]
        },
        'Normal': {
            icon: '💪',
            title: 'Performance Plan',
            subtitle: 'Maintain and improve — you\'re in the ideal range',
            tips: [
                'Mix of strength training (3×/wk) and cardio (2×/wk)',
                'Maintain protein intake at 1.4–1.8g per kg of bodyweight',
                'Focus on body recomposition: build muscle, reduce fat',
                'Our Standard membership covers all classes you need'
            ]
        },
        'Overweight': {
            icon: '🔥',
            title: 'Fat Loss Plan',
            subtitle: 'A calorie deficit + active training is your path',
            tips: [
                'Create a 300–500 kcal daily deficit through diet + exercise',
                'High-intensity cardio 3–4×/week: HIIT, boxing, cycling classes',
                'Strength train 2–3×/week to preserve muscle mass',
                'Our Cardio Zone + Nutrition Coaching package is perfect for you'
            ]
        },
        'Obese': {
            icon: '🏃',
            title: 'Transformation Plan',
            subtitle: 'Start with low-impact training and build up progressively',
            tips: [
                'Begin with low-impact cardio: swimming, cycling, brisk walking',
                'Work 1-on-1 with a certified trainer for a safe, structured plan',
                'Focus on sustainable dietary changes — not crash diets',
                'Our Personal Training + Annual plan offers the best support'
            ]
        }
    };
    return recs[cat] || recs['Normal'];
}

// Auto-calculate on page load with defaults
document.addEventListener('DOMContentLoaded', function() {
    // Init slider fills
    ['bmiAge','bmiHeightCm','bmiWeightKg'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.dispatchEvent(new Event('input'));
    });
    bmiCalc();
});
</script>
</body>
</html>