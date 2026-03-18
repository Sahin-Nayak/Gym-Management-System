<?php
$wbPath  = '../../';
$wbPage  = 'classes';
$wbTitle = 'Classes & Schedule';
$wbDesc  = 'Browse FitZone\'s full class schedule — yoga, HIIT, boxing, spinning, and more for every fitness level.';
include $wbPath . 'includes/website-header.php';

// Get active classes from database
$classes = $conn->query("SELECT * FROM gym_classes WHERE is_active=1 ORDER BY 
    FIELD(schedule_day, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday'), 
    start_time ASC");

// Define days in lowercase (to match database) but with proper display names
$dayDisplay = [
    'monday' => 'Monday',
    'tuesday' => 'Tuesday',
    'wednesday' => 'Wednesday',
    'thursday' => 'Thursday',
    'friday' => 'Friday',
    'saturday' => 'Saturday',
    'sunday' => 'Sunday'
];

$byDay = [];
while ($cls = $classes->fetch_assoc()) {
    $day = strtolower($cls['schedule_day'] ?? 'other'); // Ensure lowercase
    if (!isset($byDay[$day])) {
        $byDay[$day] = [];
    }
    $byDay[$day][] = $cls;
}

$classIcons = ['🏋️','🏃','🧘','🥊','💃','🚴','🤸','🏊','⚡','🔥','💪','🎯'];
?>

<div class="page-hero">
    <div class="page-hero-inner">
        <div class="page-hero-tag">Schedule</div>
        <h1 class="page-hero-title">Classes & <span>Schedule</span></h1>
        <p class="page-hero-sub">From high-intensity cardio to relaxing yoga — there's a class for every body, every day of the week.</p>
        <div class="page-breadcrumb">
            <a href="<?= $wbPath ?>home.php">Home</a> › <span>Classes</span>
        </div>
    </div>
</div>

<section class="section section-alt">
    <div class="section-inner">
        <?php if (empty($byDay)): ?>
        <div style="text-align:center;padding:80px 0;color:var(--white-40)">
            <div style="font-size:4rem;margin-bottom:16px">📅</div>
            <p>Class schedule coming soon.</p>
        </div>
        <?php else: ?>

        <!-- Day filter tabs -->
        <div class="filter-tabs">
            <a href="#all" class="filter-tab active" onclick="showAll(event)">All Days</a>
            <?php foreach ($dayDisplay as $dayKey => $dayLabel):
                if (!isset($byDay[$dayKey])) continue; ?>
            <a href="#<?= $dayKey ?>" class="filter-tab" onclick="filterDay(event,'<?= $dayKey ?>')"><?= $dayLabel ?></a>
            <?php endforeach; ?>
        </div>

        <!-- Schedule by day -->
        <?php
        $ci = 0;
        foreach ($dayDisplay as $dayKey => $dayLabel):
            if (!isset($byDay[$dayKey])) continue; ?>
        <div class="day-group" data-day="<?= $dayKey ?>" style="margin-bottom:40px;">
            <h3 style="font-family:var(--font-head);font-size:2rem;letter-spacing:2px;color:var(--white);margin-bottom:20px;text-transform:uppercase;display:flex;align-items:center;gap:16px;">
                <?= $dayLabel ?>
                <span style="font-size:0.85rem;font-family:var(--font-body);font-weight:600;color:var(--red);letter-spacing:1px;"><?= count($byDay[$dayKey]) ?> classes</span>
            </h3>
            <div class="classes-grid">
                <?php foreach ($byDay[$dayKey] as $cls):
                    $dur = (!empty($cls['start_time']) && !empty($cls['end_time']))
                        ? (strtotime($cls['end_time']) - strtotime($cls['start_time'])) / 60 : null;
                    
                    // Get trainer name if trainer_id exists
                    $trainerName = '';
                    if (!empty($cls['trainer_id'])) {
                        $trainerResult = $conn->query("SELECT first_name, last_name FROM trainers WHERE id = " . $cls['trainer_id']);
                        if ($trainerResult && $trainerRow = $trainerResult->fetch_assoc()) {
                            $trainerName = $trainerRow['first_name'] . ' ' . $trainerRow['last_name'];
                        }
                    }
                ?>
                <div class="class-card reveal">
                    <div class="class-icon-wrap"><?= $classIcons[$ci % count($classIcons)] ?></div>
                    <div class="class-info">
                        <div class="class-name"><?= htmlspecialchars($cls['class_name']) ?></div>
                        <div class="class-meta">
                            <?php if (!empty($cls['start_time'])): ?>
                                <?= date('g:i A', strtotime($cls['start_time'])) ?>
                            <?php endif; ?>
                            <?php if ($dur !== null): ?> · <?= (int)$dur ?> min<?php endif; ?>
                            <?php if (!empty($cls['max_capacity'])): ?> · <?= $cls['max_capacity'] ?> spots<?php endif; ?>
                        </div>
                        <?php if (!empty($trainerName)): ?>
                        <div class="class-trainer" style="font-size:0.8rem;color:var(--red);margin-top:4px;">
                            👨‍🏫 with <?= htmlspecialchars($trainerName) ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($cls['description'])): ?>
                        <p class="class-desc"><?= htmlspecialchars(substr($cls['description'],0,100)) ?><?= strlen($cls['description']) > 100 ? '…' : '' ?></p>
                        <?php endif; ?>
                        <div style="margin-top:10px;">
                            <a href="<?= $wbPath ?>login.php" class="btn-primary-ws" style="padding:8px 16px;font-size:0.78rem;">Book Class →</a>
                        </div>
                    </div>
                </div>
                <?php $ci++; endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script>
function showAll(e) {
    e.preventDefault();
    document.querySelectorAll('.day-group').forEach(function(g){ 
        g.style.display = ''; 
    });
    document.querySelectorAll('.filter-tab').forEach(function(t){ 
        t.classList.remove('active'); 
    });
    e.target.classList.add('active');
}

function filterDay(e, day) {
    e.preventDefault();
    document.querySelectorAll('.day-group').forEach(function(g){
        g.style.display = g.dataset.day === day ? '' : 'none';
    });
    document.querySelectorAll('.filter-tab').forEach(function(t){ 
        t.classList.remove('active'); 
    });
    e.target.classList.add('active');
}

// Check URL hash on page load to show specific day
window.addEventListener('load', function() {
    if (window.location.hash && window.location.hash !== '#all') {
        var day = window.location.hash.substring(1);
        var tab = document.querySelector('a[href="#' + day + '"]');
        if (tab) {
            filterDay(new Event('click'), day);
        }
    }
});
</script>

<?php include $wbPath . 'includes/website-footer.php'; ?>