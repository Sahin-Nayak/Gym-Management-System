<?php
$wbPath  = '../../';
$wbPage  = 'trainers';
$wbTitle = 'Our Trainers';
$wbDesc  = 'Meet FitZone\'s team of certified personal trainers — passionate professionals dedicated to your transformation.';
include $wbPath . 'includes/website-header.php';

$trainers = $conn->query("SELECT t.*, u.username FROM trainers t JOIN users u ON t.user_id=u.id WHERE t.status='active' ORDER BY t.id ASC");
$totalT   = $conn->query("SELECT COUNT(*) as c FROM trainers WHERE status='active'")->fetch_assoc()['c'];
?>

<div class="page-hero">
    <div class="page-hero-inner">
        <div class="page-hero-tag">The Team</div>
        <h1 class="page-hero-title">Our Expert <span>Trainers</span></h1>
        <p class="page-hero-sub"><?= $totalT ?> certified professionals dedicated to getting you to your best. Your transformation is personal to us.</p>
        <div class="page-breadcrumb">
            <a href="<?= $wbPath ?>home.php">Home</a> › <span>Trainers</span>
        </div>
    </div>
</div>

<!-- TRAINERS GRID -->
<section class="section section-alt">
    <div class="section-inner">
        <?php if ($trainers->num_rows > 0): ?>
        <div class="trainers-grid">
            <?php while ($tr = $trainers->fetch_assoc()):
                $initials = strtoupper(substr($tr['first_name'],0,1) . substr($tr['last_name']??'',0,1));
            ?>
            <div class="trainer-card reveal">
                <div class="trainer-avatar-wrap">
                    <?php if (!empty($tr['photo']) && file_exists($wbPath.'uploads/trainers/'.$tr['photo'])): ?>
                        <img src="<?= $wbPath ?>uploads/trainers/<?= htmlspecialchars($tr['photo']) ?>" alt="<?= htmlspecialchars($tr['first_name']) ?>">
                    <?php else: ?>
                        <div class="trainer-avatar-initials"><?= $initials ?></div>
                    <?php endif; ?>
                    <div class="trainer-overlay"></div>
                </div>
                <div class="trainer-info">
                    <div class="trainer-name"><?= htmlspecialchars($tr['first_name'].' '.$tr['last_name']) ?></div>
                    <div class="trainer-specialty"><?= htmlspecialchars($tr['specialization'] ?? 'Personal Trainer') ?></div>
                    <div class="trainer-exp">
                        <?= !empty($tr['experience_years']) ? (int)$tr['experience_years'].' years experience' : 'Certified Trainer' ?>
                    </div>
                    <?php if (!empty($tr['bio'])): ?>
                    <p style="font-size:0.78rem;color:var(--white-40);margin-top:8px;line-height:1.6"><?= htmlspecialchars(substr($tr['bio'],0,100)).'…' ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:80px 0;color:var(--white-40);">
            <div style="font-size:4rem;margin-bottom:16px">👨‍🏫</div>
            <p>Trainer profiles coming soon.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- WHY TRAIN WITH US -->
<section class="section">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Why Choose Us</div>
            <h2 class="section-title">What Makes Our <span>Trainers</span> Different</h2>
        </div>
        <div class="services-grid">
            <?php
            $reasons = [
                ['🎓','Fully Certified','Every FitZone trainer holds nationally recognized certifications and completes ongoing continuing education.'],
                ['🔬','Science-Based Methods','Our trainers use evidence-based programming — no bro science, no gimmicks. Just what works.'],
                ['❤️','Genuinely Invested','Your trainer remembers your goals, checks your form, and celebrates your wins. They\'re invested in YOU.'],
                ['📊','Progress Tracking','Regular assessments, body composition checks, and program updates ensure you\'re always moving forward.'],
                ['🕐','Flexible Scheduling','Personal training slots available from 5AM to 10PM, 7 days a week. We work around your life.'],
                ['💬','Ongoing Support','Beyond sessions, your trainer is available via our app for questions, motivation, and accountability.'],
            ];
            foreach ($reasons as $i => $r): ?>
            <div class="service-card reveal" style="transition-delay:<?= $i*0.08 ?>s">
                <div class="service-icon-wrap"><?= $r[0] ?></div>
                <div class="service-title"><?= $r[1] ?></div>
                <p class="service-desc"><?= $r[2] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include $wbPath . 'includes/website-footer.php'; ?>
