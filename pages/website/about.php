<?php
$wbPath  = '../../';
$wbPage  = 'about';
$wbTitle = 'About Us';
$wbDesc  = 'Learn about FitZone Gym — our story, values, and the team dedicated to transforming lives through fitness.';
include $wbPath . 'includes/website-header.php';

$totalMembers   = (int)$conn->query("SELECT COUNT(*) as c FROM members")->fetch_assoc()['c'];
$activeTrainers = (int)$conn->query("SELECT COUNT(*) as c FROM trainers WHERE status='active'")->fetch_assoc()['c'];
$totalClasses   = (int)$conn->query("SELECT COUNT(*) as c FROM gym_classes WHERE is_active=1")->fetch_assoc()['c'];
?>

<!-- PAGE HERO -->
<div class="page-hero">
    <div class="page-hero-inner">
        <div class="page-hero-tag">Our Story</div>
        <h1 class="page-hero-title">About <span>FitZone</span></h1>
        <p class="page-hero-sub">More than a gym — a community built on passion, science, and the relentless pursuit of your best self.</p>
        <div class="page-breadcrumb">
            <a href="<?= $wbPath ?>home.php">Home</a> › <span>About Us</span>
        </div>
    </div>
</div>

<!-- STORY -->
<section class="section section-alt">
    <div class="section-inner">
        <div class="about-grid">
            <div class="about-content reveal">
                <div class="about-eyebrow">Our Beginning</div>
                <h2 class="about-title">Built on <span>Passion</span><br>Driven by Results</h2>
                <p class="about-text"><?= nl2br(htmlspecialchars(wc('about_text'))) ?></p>
                <div class="about-highlights">
                    <?php foreach (['about_h1','about_h2','about_h3','about_h4'] as $hk):
                        $hv = wc($hk); if (!$hv) continue;
                        preg_match('/^(\S+)\s+(.+)$/u', $hv, $m);
                        $icon = $m[1] ?? '✓'; $text = $m[2] ?? $hv;
                    ?>
                    <div class="about-highlight">
                        <div class="about-highlight-icon"><?= $icon ?></div>
                        <span><?= htmlspecialchars($text) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
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

<!-- STATS -->
<div class="stats-ticker">
    <div class="stats-ticker-inner">
        <?php $items = [[$totalMembers.'+','Members'],[$activeTrainers.'+','Trainers'],[$totalClasses.'+','Classes'],[wc('stats_years','10+'),'Years'],[wc('stats_classes','80+'),'Weekly Classes'],['5AM–11PM','Open Daily']];
        $all = array_merge($items,$items);
        foreach ($all as $it): ?>
        <div class="stats-ticker-item">
            <div class="stats-ticker-num"><?= htmlspecialchars($it[0]) ?></div>
            <div class="stats-ticker-label"><?= htmlspecialchars($it[1]) ?></div>
        </div>
        <div class="stats-ticker-dot"></div>
        <?php endforeach; ?>
    </div>
</div>

<!-- VALUES -->
<section class="section">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">What We Stand For</div>
            <h2 class="section-title">Our Core <span>Values</span></h2>
        </div>
        <div class="services-grid">
            <?php
            $values = [
                ['🎯','Results-Driven','Every program, every class, every piece of equipment is chosen with one goal: your real results.'],
                ['🤝','Community First','We believe fitness is better together. Our members support, motivate, and challenge each other daily.'],
                ['🔬','Science-Backed','Our trainers follow evidence-based methods. No fads, no gimmicks — just what the science proves works.'],
                ['♾️','Inclusive & Welcoming','Whether you\'re 18 or 80, beginner or competitor, FitZone is your home. Everyone belongs here.'],
                ['🏆','Excellence Always','We hold ourselves to the highest standards — in equipment quality, trainer certification, and member experience.'],
                ['💚','Holistic Wellness','We train the body, mind, and spirit. Nutrition coaching, recovery, and mental wellness are part of our DNA.'],
            ];
            foreach ($values as $i => $v): ?>
            <div class="service-card reveal" style="transition-delay:<?= $i*0.08 ?>s">
                <div class="service-icon-wrap"><?= $v[0] ?></div>
                <div class="service-title"><?= $v[1] ?></div>
                <p class="service-desc"><?= $v[2] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include $wbPath . 'includes/website-footer.php'; ?>
