<?php
define('CURRENT_PAGE', 'website-content');
require_once '../../includes/auth.php';
requireAdmin();

// Auto-create and seed table (mirrors home.php logic)
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
    // Trigger home.php seed by including it would cause HTML output — use direct inserts instead
    $conn->query("INSERT IGNORE INTO website_content (section_key, label, value, type, section_group, sort_order) VALUES
        ('announcement_text','Announcement Bar Text','🔥 Limited time: Get 3 months free with annual membership! Join today.','text','general',1),
        ('announcement_show','Show Announcement Bar','1','toggle','general',2),
        ('hero_title','Hero Title','TRANSFORM\nYOUR BODY\nTRANSFORM\nYOUR LIFE','textarea','hero',1),
        ('hero_subtitle','Hero Subtitle','State-of-the-art facilities, world-class trainers, and a community that pushes you to be your best every single day.','textarea','hero',2),
        ('hero_cta_primary','Hero Primary Button','Start Today — Free Trial','text','hero',3),
        ('hero_cta_secondary','Hero Secondary Button','View Membership Plans','text','hero',4),
        ('hero_badge','Hero Badge Text','🏆 Rated #1 Gym in the City','text','hero',5),
        ('stats_members','Stats: Total Members','1,200+','text','stats',1),
        ('stats_trainers','Stats: Expert Trainers','25+','text','stats',2),
        ('stats_classes','Stats: Weekly Classes','80+','text','stats',3),
        ('stats_years','Stats: Years Experience','10+','text','stats',4),
        ('about_title','About: Title','More Than a Gym — A Lifestyle','text','about',1),
        ('about_text','About: Description','At FitZone, we believe fitness is not just about the body — it\\'s about the mind, confidence, and community.','textarea','about',2),
        ('about_h1','About: Highlight 1','🏋️ State-of-the-art equipment updated every year','text','about',3),
        ('about_h2','About: Highlight 2','👥 Expert certified trainers with 5+ years experience','text','about',4),
        ('about_h3','About: Highlight 3','📅 Flexible memberships with no hidden fees','text','about',5),
        ('about_h4','About: Highlight 4','🔥 300+ classes per month across all fitness levels','text','about',6),
        ('about_years','About: Years Badge Number','10','text','about',7),
        ('about_years_text','About: Years Badge Label','Years of Excellence','text','about',8),
        ('services_show','Show Services Section','1','toggle','services',1),
        ('plans_show','Show Plans Section','1','toggle','plans',1),
        ('plans_title','Plans Section Title','Choose Your Plan','text','plans',2),
        ('plans_subtitle','Plans Section Subtitle','Flexible options for every fitness goal and budget.','textarea','plans',3),
        ('trainers_show','Show Trainers Section','1','toggle','trainers',1),
        ('classes_show','Show Classes Section','1','toggle','classes',1),
        ('contact_address','Contact: Address','123 Fitness Street, Sports Complex, Mumbai - 400001','textarea','contact',1),
        ('contact_phone','Contact: Phone','+91 98765 43210','phone','contact',2),
        ('contact_email','Contact: Email','info@fitzonegym.com','email','contact',3),
        ('contact_hours_wd','Contact: Weekday Hours','Mon – Fri: 5:00 AM – 11:00 PM','text','contact',4),
        ('contact_hours_we','Contact: Weekend Hours','Sat – Sun: 6:00 AM – 10:00 PM','text','contact',5),
        ('social_instagram','Social: Instagram URL','#','url','social',1),
        ('social_facebook','Social: Facebook URL','#','url','social',2),
        ('social_twitter','Social: Twitter/X URL','#','url','social',3),
        ('social_youtube','Social: YouTube URL','#','url','social',4),
        ('footer_tagline','Footer: Tagline','Your transformation starts here. Join the FitZone family.','textarea','footer',1),
        ('footer_copyright','Footer: Copyright Text','© 2025 FitZone Gym. All rights reserved.','text','footer',2)
    ");
}

$success = '';
$error   = '';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'save_section' && isset($_POST['fields'])) {
        $stmt = $conn->prepare("UPDATE website_content SET value=? WHERE section_key=?");
        foreach ($_POST['fields'] as $key => $val) {
            $key = $conn->real_escape_string($key);
            $stmt->bind_param("ss", $val, $key);
            $stmt->execute();
        }
        $success = 'Changes saved successfully!';
    }

    if ($_POST['action'] === 'toggle' && isset($_POST['key'])) {
        $key     = $conn->real_escape_string($_POST['key']);
        $current = $conn->query("SELECT value FROM website_content WHERE section_key='$key'")->fetch_assoc()['value'] ?? '0';
        $newVal  = ($current === '1') ? '0' : '1';
        $conn->query("UPDATE website_content SET value='$newVal' WHERE section_key='$key'");
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'value' => $newVal]);
        exit;
    }
}

// Ensure new seed rows exist (for existing databases that pre-date these fields)
$conn->query("INSERT IGNORE INTO website_content (section_key, label, value, type, section_group, sort_order) VALUES
    ('about_image',        'About: Section Image URL',                         '', 'url',      'about',    9),
    ('whatsapp_number',    'WhatsApp Number',                                  '', 'phone',    'general',  7),
    ('videos_show',        'Show Videos Section',                              '1','toggle',   'videos',   1),
    ('videos_title',       'Videos: Section Title',                 'Inside FitZone','text',  'videos',   2),
    ('videos_subtitle',    'Videos: Subtitle','See our world-class facilities and training sessions in action.','textarea','videos',3),
    ('gallery_show',       'Show Gallery Section',                             '1','toggle',   'gallery',  1),
    ('gallery_title',      'Gallery: Section Title',                 'Gym Gallery', 'text',   'gallery',  2),
    ('gallery_subtitle',   'Gallery: Subtitle',      'A glimpse into the FitZone experience.','textarea','gallery',3),
    ('location_show',      'Show Location Section',                            '1','toggle',   'location', 1),
    ('location_map_embed', 'Location: Google Maps Embed (paste full iframe code)','','textarea','location',2),
    ('location_title',     'Location: Section Title',                  'Location',  'text',   'location', 3)
");

// Load all content
$allContent = [];
$res = $conn->query("SELECT * FROM website_content ORDER BY section_group, sort_order");
while ($row = $res->fetch_assoc()) {
    $allContent[$row['section_group']][] = $row;
}

$sections = [
    'general'  => ['label' => 'General & WhatsApp',  'icon' => '📢'],
    'hero'     => ['label' => 'Hero Section',         'icon' => '🦸', 'disabled' => true],
    // 'stats'    => ['label' => 'Statistics Bar',       'icon' => '📊'],
    'about'    => ['label' => 'About Section',        'icon' => 'ℹ️'],
    'services' => ['label' => 'Services Section',     'icon' => '⚙️'],
    'plans'    => ['label' => 'Plans Section',        'icon' => '💳'],
    'trainers' => ['label' => 'Trainers Section',     'icon' => '🏋️'],
    'classes'  => ['label' => 'Classes Section',      'icon' => '📅'],
    'videos'   => ['label' => 'Videos Section',       'icon' => '🎥'],
    'gallery'  => ['label' => 'Gallery Section',      'icon' => '🖼️'],
    'location' => ['label' => 'Location / Map',       'icon' => '📍'],
    'contact'  => ['label' => 'Contact Info',         'icon' => '📞'],
    'social'   => ['label' => 'Social Media Links',   'icon' => '🔗'],
    'footer'   => ['label' => 'Footer Content',       'icon' => '📄'],
];

$activeTab = $_GET['tab'] ?? 'general';
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Content — FitZone Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* Extra CMS styles */
        .cms-layout    { display: grid; grid-template-columns: 240px 1fr; gap: 24px; align-items: start; }
        .cms-sidebar   { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 12px; position: sticky; top: calc(var(--header-height) + 24px); }
        .cms-tab-btn   {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 10px 12px; border: none; background: transparent;
            color: var(--text-secondary); border-radius: var(--radius-sm);
            font-family: 'Inter','Outfit',sans-serif; font-weight: 500; font-size: 0.87rem;
            cursor: pointer; text-align: left; transition: all 0.2s; margin-bottom: 2px;
        }
        .cms-tab-btn:hover   { background: var(--primary-alpha); color: var(--text-primary); }
        .cms-tab-btn.active  { background: var(--primary-alpha); color: var(--primary); font-weight: 600; }
        .cms-tab-btn.active::before { display: none; }
        .cms-section-icon    { font-size: 1rem; width: 20px; text-align: center; }
        .cms-main            { display: flex; flex-direction: column; gap: 0; }
        .cms-panel           { display: none; }
        .cms-panel.active    { display: block; }

        .field-group {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 16px 18px;
            margin-bottom: 14px;
            transition: border-color 0.2s;
        }
        .field-group:hover  { border-color: var(--border-hover); }
        .field-group:focus-within { border-color: var(--primary); }

        .field-label {
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-secondary);
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .field-type-badge {
            font-size: 0.62rem;
            padding: 2px 7px;
            border-radius: 100px;
            background: var(--bg-surface2);
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .cms-input, .cms-textarea {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-xs);
            color: var(--text-primary);
            font-family: 'Inter','Outfit',sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s;
            -webkit-appearance: none;
            appearance: none;
        }
        .cms-input:focus, .cms-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-alpha);
        }
        .cms-textarea {
            resize: vertical;
            min-height: 90px;
            line-height: 1.6;
        }

        /* Toggle switch */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .toggle-label-text { font-size: 0.9rem; color: var(--text-secondary); }
        .toggle-pill {
            position: relative;
            width: 48px;
            height: 27px;
            cursor: pointer;
        }
        .toggle-pill input { display: none; }
        .toggle-pill-track {
            position: absolute;
            inset: 0;
            background: var(--bg-surface2);
            border: 1px solid var(--border);
            border-radius: 100px;
            transition: all 0.25s;
        }
        .toggle-pill input:checked + .toggle-pill-track {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(230,57,70,0.35);
        }
        .toggle-pill-thumb {
            position: absolute;
            top: 3px; left: 3px;
            width: 19px; height: 19px;
            border-radius: 50%;
            background: var(--text-muted);
            transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1), background 0.25s;
        }
        .toggle-pill input:checked ~ .toggle-pill-thumb {
            transform: translateX(21px);
            background: #fff;
        }

        .section-save-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 0 0;
            border-top: 1px solid var(--border);
            margin-top: 8px;
        }
        .save-hint { font-size: 0.8rem; color: var(--text-muted); }

        .preview-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: var(--radius-xs);
            transition: all 0.2s;
            text-decoration: none;
        }
        .preview-link:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .cms-layout { grid-template-columns: 1fr; }
            .cms-sidebar { position: static; display: flex; flex-wrap: wrap; gap: 4px; }
            .cms-tab-btn { width: auto; }
        }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-header">
            <button class="hamburger" onclick="toggleSidebar()">☰</button>
            <h2 class="page-title">WEBSITE CONTENT</h2>
            <div class="top-header-actions">
                <a href="../../home.php" target="_blank" class="preview-link">
                    🌐 Preview Website ↗
                </a>
            </div>
        </header>

        <div class="page-content">

            <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Overview cards -->
            <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:24px;">
                <div class="stat-card red">
                    <div class="stat-info">
                        <h4>Website URL</h4>
                        <div style="font-size:0.85rem;margin-top:8px;font-weight:600;">
                            <a href="../../home.php" target="_blank" style="color:var(--primary);text-decoration:none;">home.php ↗</a>
                        </div>
                    </div>
                    <div class="stat-icon">🌐</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-info">
                        <h4>Content Fields</h4>
                        <div class="stat-number"><?= array_sum(array_map('count', $allContent)) ?></div>
                    </div>
                    <div class="stat-icon">📝</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-info">
                        <h4>Sections</h4>
                        <div class="stat-number"><?= count($sections) ?></div>
                    </div>
                    <div class="stat-icon">📑</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-info">
                        <h4>Last Updated</h4>
                        <div style="font-size:0.85rem;margin-top:8px;font-weight:600;">
                            <?php
                            $lu = $conn->query("SELECT MAX(updated_at) as t FROM website_content")->fetch_assoc()['t'];
                            echo $lu ? date('d M, H:i', strtotime($lu)) : 'Never';
                            ?>
                        </div>
                    </div>
                    <div class="stat-icon">🕐</div>
                </div>
            </div>

            <!-- CMS Layout -->
            <div class="cms-layout">

                <!-- Section Sidebar -->
                <div class="cms-sidebar">
                    <?php foreach ($sections as $key => $meta): ?>
                    <?php $isDisabled = !empty($meta['disabled']); ?>
                    <button class="cms-tab-btn <?= $activeTab === $key ? 'active' : '' ?> <?= $isDisabled ? 'disabled' : '' ?>"
                            <?= $isDisabled ? 'disabled title="This section is currently locked"' : "onclick=\"switchTab('$key')\"" ?>>
                        <span class="cms-section-icon"><?= $meta['icon'] ?></span>
                        <?= $meta['label'] ?>
                        <?php if ($isDisabled): ?>
                        <span style="margin-left:auto;font-size:0.65rem;background:rgba(255,255,255,0.08);padding:2px 7px;border-radius:100px;color:var(--text-muted);">LOCKED</span>
                        <?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- Editor Panels -->
                <div class="cms-main">
                    <?php foreach ($sections as $sKey => $sMeta): ?>
                    <div class="cms-panel <?= $activeTab === $sKey ? 'active' : '' ?>" id="panel-<?= $sKey ?>">
                        <div class="card">
                            <div class="card-header">
                                <h3><?= $sMeta['icon'] ?> <?= $sMeta['label'] ?></h3>
                                <a href="../../home.php#<?= $sKey === 'general' ? 'home' : $sKey ?>" target="_blank" class="preview-link">Preview ↗</a>
                            </div>

                            <form method="POST" id="form-<?= $sKey ?>" onsubmit="return saveSection(event, '<?= $sKey ?>')">
                                <input type="hidden" name="action" value="save_section">

                                <?php if (!empty($allContent[$sKey])): ?>
                                    <?php foreach ($allContent[$sKey] as $field): ?>
                                    <div class="field-group">
                                        <div class="field-label">
                                            <?= htmlspecialchars($field['label']) ?>
                                            <span class="field-type-badge"><?= $field['type'] ?></span>
                                        </div>

                                        <?php if ($field['type'] === 'toggle'): ?>
                                        <div class="toggle-row">
                                            <span class="toggle-label-text">
                                                <?= $field['value'] === '1' ? '✅ Visible on website' : '❌ Hidden from website' ?>
                                            </span>
                                            <label class="toggle-pill">
                                                <input type="checkbox"
                                                    <?= $field['value'] === '1' ? 'checked' : '' ?>
                                                    onchange="toggleField('<?= htmlspecialchars($field['section_key']) ?>', this)">
                                                <div class="toggle-pill-track"></div>
                                                <div class="toggle-pill-thumb"></div>
                                            </label>
                                        </div>

                                        <?php elseif ($field['type'] === 'textarea'): ?>
                                        <textarea
                                            class="cms-textarea"
                                            name="fields[<?= htmlspecialchars($field['section_key']) ?>]"
                                            rows="<?= substr_count($field['value'], "\n") + 3 ?>"
                                        ><?= htmlspecialchars($field['value']) ?></textarea>

                                        <?php else: ?>
                                        <input
                                            type="<?= $field['type'] === 'email' ? 'email' : ($field['type'] === 'url' ? 'url' : ($field['type'] === 'phone' ? 'tel' : 'text')) ?>"
                                            class="cms-input"
                                            name="fields[<?= htmlspecialchars($field['section_key']) ?>]"
                                            value="<?= htmlspecialchars($field['value']) ?>">
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="color:var(--text-muted);text-align:center;padding:30px;">No fields in this section.</p>
                                <?php endif; ?>

                                <div class="section-save-bar">
                                    <span class="save-hint">Changes apply instantly on the website after saving.</span>
                                    <button type="submit" class="btn btn-primary" id="save-btn-<?= $sKey ?>">
                                        💾 Save <?= $sMeta['label'] ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /page-content -->
    </div><!-- /main-content -->
</div>

<script src="../../assets/js/main.js"></script>
<script>
// Tab switching
function switchTab(key) {
    document.querySelectorAll('.cms-panel').forEach(function(p) { p.classList.remove('active'); });
    document.querySelectorAll('.cms-tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.getElementById('panel-' + key).classList.add('active');
    document.querySelector('[onclick="switchTab(\'' + key + '\')"]').classList.add('active');
    history.replaceState(null, '', '?tab=' + key);
}

// Save section via AJAX (show success inline)
function saveSection(e, sKey) {
    e.preventDefault();
    var form = document.getElementById('form-' + sKey);
    var btn  = document.getElementById('save-btn-' + sKey);
    var fd   = new FormData(form);
    var orig = btn.innerHTML;

    btn.innerHTML = '⏳ Saving…';
    btn.disabled  = true;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '');
    xhr.onload = function() {
        btn.innerHTML = '✅ Saved!';
        btn.style.background = 'var(--success)';
        setTimeout(function() {
            btn.innerHTML = orig;
            btn.disabled  = false;
            btn.style.background = '';
        }, 2000);
    };
    xhr.onerror = function() {
        btn.innerHTML = '❌ Error';
        btn.disabled  = false;
        setTimeout(function() { btn.innerHTML = orig; }, 2000);
    };
    xhr.send(fd);
    return false;
}

// Toggle field (section visibility) via AJAX
function toggleField(key, checkbox) {
    var label = checkbox.closest('.toggle-row').querySelector('.toggle-label-text');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        try {
            var res = JSON.parse(xhr.responseText);
            if (res.success) {
                label.textContent = res.value === '1' ? '✅ Visible on website' : '❌ Hidden from website';
            }
        } catch(e) {}
    };
    xhr.send('action=toggle&key=' + encodeURIComponent(key));
}
</script>
</body>
</html>
