<?php
$wbPath  = '../../';
$wbPage  = 'plans';
$wbTitle = 'Membership Plans';
$wbDesc  = 'Flexible gym membership plans for every goal and budget. No contracts, no hidden fees.';
include $wbPath . 'includes/website-header.php';

$plans = $conn->query("SELECT * FROM membership_plans WHERE is_active=1 ORDER BY price ASC");
?>

<div class="page-hero">
    <div class="page-hero-inner">
        <div class="page-hero-tag">Pricing</div>
        <h1 class="page-hero-title">Membership <span>Plans</span></h1>
        <p class="page-hero-sub">Flexible options designed for every fitness goal and budget. No long-term commitments. Cancel anytime.</p>
        <div class="page-breadcrumb">
            <a href="<?= $wbPath ?>home.php">Home</a> › <span>Membership Plans</span>
        </div>
    </div>
</div>

<!-- PLANS -->
<section class="section section-alt">
    <div class="section-inner">
        <div class="plans-grid">
            <?php
            $pi = 0;
            while ($plan = $plans->fetch_assoc()):
                $featured = ($pi === 1); $pi++;
            ?>
            <div class="plan-card <?= $featured ? 'featured' : '' ?> reveal" style="transition-delay:<?= $pi*0.1 ?>s">
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
                <a href="<?= $wbPath ?>login.php" class="plan-cta <?= $featured ? 'plan-cta-filled' : 'plan-cta-outline' ?>">
                    <?= $featured ? 'Get Started →' : 'Choose Plan' ?>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- FEATURES COMPARISON -->
<section class="section">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">All Plans Include</div>
            <h2 class="section-title">What's <span>Included</span></h2>
        </div>
        <div class="services-grid">
            <?php
            $features = [
                ['🏋️','Full Gym Access','Unlimited access to all equipment zones — strength, cardio, functional fitness, and more.'],
                ['🚿','Locker Rooms & Showers','Premium locker rooms with hot showers, towel service, and secure storage.'],
                ['📱','FitZone App Access','Track workouts, book classes, and monitor your progress from our mobile app.'],
                ['🧑‍🏫','Expert Staff','Certified trainers on the floor all day to guide, correct form, and answer questions.'],
                ['📅','Class Bookings','Reserve spots in group classes — yoga, HIIT, spinning, boxing, and more.'],
                ['💬','Community Support','Access to our private members community, challenges, and accountability groups.'],
            ];
            foreach ($features as $i => $f): ?>
            <div class="service-card reveal" style="transition-delay:<?= $i*0.08 ?>s">
                <div class="service-icon-wrap"><?= $f[0] ?></div>
                <div class="service-title"><?= $f[1] ?></div>
                <p class="service-desc"><?= $f[2] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section section-alt">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-tag">Questions</div>
            <h2 class="section-title">Frequently Asked <span>Questions</span></h2>
        </div>
        <div style="max-width:760px;margin:0 auto;display:flex;flex-direction:column;gap:14px;">
            <?php
            $faqs = [
                ['Is there a joining fee?','No joining fee, ever. You only pay the membership price listed above. No hidden charges.'],
                ['Can I cancel anytime?','Yes. All our plans are month-to-month (or multi-month). You can cancel before your renewal date with no penalty.'],
                ['Is there a free trial?','Yes! New members get a 3-day free trial pass. Visit the gym and speak to our team to redeem yours.'],
                ['Can I freeze my membership?','Yes, you can freeze your membership for up to 2 months per year in case of travel, injury, or other reasons.'],
                ['Do I get a personal trainer?','Our trainers are on the floor to help all members. Dedicated 1-on-1 personal training sessions are available as an add-on.'],
                ['What are the gym hours?','We\'re open Monday–Friday 5AM to 11PM, and Saturday–Sunday 6AM to 10PM. The gym is open on most holidays.'],
            ];
            foreach ($faqs as $faq): ?>
            <details style="background:var(--black-3);border:1px solid var(--white-06);border-radius:var(--radius-sm);padding:20px 24px;cursor:pointer;transition:border-color 0.2s" class="reveal">
                <summary style="font-weight:700;font-size:0.95rem;color:var(--white);list-style:none;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <?= htmlspecialchars($faq[0]) ?>
                    <span style="color:var(--red);font-size:1.2rem;flex-shrink:0;">+</span>
                </summary>
                <p style="margin-top:14px;font-size:0.9rem;color:var(--white-70);line-height:1.75;"><?= htmlspecialchars($faq[1]) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include $wbPath . 'includes/website-footer.php'; ?>
