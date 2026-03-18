<?php
$wbPath  = '../../';
$wbPage  = 'services';
$wbTitle = 'Services';
$wbDesc  = 'Explore FitZone\'s world-class fitness services — strength training, cardio, yoga, boxing, swimming, and nutrition coaching.';
include $wbPath . 'includes/website-header.php';
?>

<div class="page-hero">
    <div class="page-hero-inner">
        <div class="page-hero-tag">What We Offer</div>
        <h1 class="page-hero-title">Our <span>Services</span></h1>
        <p class="page-hero-sub">Everything you need to build the body and life you want — all under one roof.</p>
        <div class="page-breadcrumb">
            <a href="<?= $wbPath ?>home.php">Home</a> › <span>Services</span>
        </div>
    </div>
</div>

<?php
$services = [
    [
        'strength-training',
        'Strength Training',
        'Build muscle, increase power, and reshape your body with our comprehensive strength zone.',
        [
            'Fully-equipped free weights area (up to 50kg dumbbells)',
            'Olympic lifting platforms with bumper plates',
            'Hammer Strength machines for every muscle group',
            'Expert strength coaches on the floor daily',
            'Dedicated powerlifting corner with monolift'
        ],
        'Whether your goal is to build muscle, increase strength, or improve athletic performance, our strength zone has everything you need. Our certified coaches provide guidance, spot you on heavy sets, and design progressive programs tailored to your level.'
    ],
    [
        'cardio-zone',
        'Cardio Zone',
        'Burn fat, build endurance, and supercharge your cardiovascular health with cutting-edge equipment.',
        [
            '50+ commercial-grade treadmills',
            'Assault bikes and rowers for HIIT',
            'Stairmaster and elliptical machines',
            'Dedicated HIIT studio with coaching',
            'Heart rate monitoring system'
        ],
        'Our cardio zone is designed for every level — from gentle walks to elite interval training. With live heart rate displays and coach-led HIIT sessions, you\'ll burn more calories and reach your fitness goals faster than ever.'
    ],
    [
        'yoga',
        'Yoga & Mindfulness',
        'Restore balance, flexibility, and mental clarity with our diverse range of mind-body classes.',
        [
            'Daily Hatha, Vinyasa, and Yin yoga',
            'Guided meditation sessions',
            'Hot yoga studio (38°C)',
            'Aerial yoga rigs',
            'Certified RYT-200 instructors'
        ],
        'Yoga is not just stretching — it\'s a complete system for body and mind. Our yoga studio offers everything from gentle restorative sessions to powerful hot yoga and advanced Vinyasa flows, guided by world-class instructors.'
    ],
    [
        'boxing',
        'Boxing & MMA',
        'Get fight-ready or just shred fat in the most exciting workouts we offer — boxing and martial arts.',
        [
            'Professional boxing ring',
            'Heavy bags, speed bags, and double-end bags',
            'Muay Thai, BJJ, and wrestling classes',
            'Sparring sessions with safety gear',
            'MMA fitness classes for non-fighters'
        ],
        'Boxing and MMA training delivers incredible results — fat loss, coordination, confidence, and stress relief. Our classes are designed for all levels, with separate tracks for fitness-focused members and those training to compete.'
    ],
    [
        'swimming',
        'Swimming Pool',
        'Glide through crystal-clear water in our Olympic-size heated pool — perfect for all skill levels.',
        [
            'Olympic 25m heated pool (27–29°C)',
            'Lap swimming lanes (6 lanes)',
            'Aqua aerobics classes',
            'Certified swim coaching (beginner to competitive)',
            'Private coaching sessions available'
        ],
        'Swimming is one of the best full-body workouts with virtually zero joint impact. Whether you\'re a beginner learning technique or a competitive swimmer looking to improve your times, our pool and coaching team have you covered.'
    ],
    [
        'nutrition',
        'Nutrition Coaching',
        'Transform your diet and fuel your performance with expert, personalized nutrition guidance.',
        [
            '1-on-1 nutrition assessments',
            'Personalized meal plan design',
            'Macro and calorie coaching',
            'Supplement guidance',
            'Body composition tracking (monthly)'
        ],
        'You cannot out-train a bad diet. Our certified nutrition coaches work with you to build sustainable eating habits that support your training goals — whether you\'re cutting, bulking, or optimizing performance.'
    ],
];

// Helper function to check if image exists
function getServiceImage($slug) {
    global $wbPath;
    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    
    // Get the absolute server path to the document root
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    
    // Build the relative web path
    $webPath = $wbPath . 'assets/images/services/' . $slug;
    
    // Build the absolute server path
    $serverBase = '/gym-management-system/';
    
    foreach ($imageExtensions as $ext) {
        $serverPath = $docRoot . $serverBase . 'assets/images/services/' . $slug . '.' . $ext;
        if (file_exists($serverPath)) {
            return $webPath . '.' . $ext;
        }
    }
    return null;
}
?>

<?php foreach ($services as $i => $s): ?>
<section class="section <?= $i % 2 === 1 ? 'section-alt' : '' ?>" id="<?= strtolower(str_replace(' ','_',$s[1])) ?>">
    <div class="section-inner">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:70px;align-items:center;<?= $i % 2 === 1 ? 'direction:rtl' : '' ?>">
            <div style="<?= $i % 2 === 1 ? 'order:2' : '' ?>" class="reveal">
                <div class="about-eyebrow">Service <?= $i+1 ?></div>
                <h2 class="about-title" style="font-size:clamp(2rem,4vw,3.2rem)"><?= htmlspecialchars($s[1]) ?></h2>
                <p class="about-text" style="margin-bottom:24px"><?= htmlspecialchars($s[2]) ?></p>
                <ul style="display:flex;flex-direction:column;gap:10px;margin-bottom:28px;">
                    <?php foreach ($s[3] as $pt): ?>
                    <li style="display:flex;align-items:flex-start;gap:12px;font-size:0.88rem;color:var(--white-70);">
                        <span style="color:var(--red);font-size:1rem;flex-shrink:0;margin-top:2px;">✓</span>
                        <?= htmlspecialchars($pt) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= $wbPath ?>pages/website/plans.php" class="btn-primary-ws">Join & Access This Service →</a>
            </div>
            <div style="<?= $i % 2 === 1 ? 'order:1' : '' ?>" class="reveal reveal-delay-2">
                <div style="background:var(--black-3);border:1px solid var(--white-06);border-radius:20px;aspect-ratio:4/3;position:relative;overflow:hidden;">
                    <?php
                    $imageUrl = getServiceImage($s[0]);
                    if ($imageUrl): 
                    ?>
                        <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($s[1]) ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(230,57,70,0.1),transparent 60%);display:flex;align-items:center;justify-content:center;font-size:4rem;color:var(--white-30);">
                            <?= substr($s[1], 0, 2) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endforeach; ?>

<?php include $wbPath . 'includes/website-footer.php'; ?>