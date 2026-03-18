<?php
require_once 'includes/auth.php';

// Already logged in → dashboard
if (isLoggedIn()) {
    if (isAdmin())         redirect('pages/admin/dashboard.php');
    elseif (isTrainer())   redirect('pages/trainer/dashboard.php');
    else                   redirect('pages/member/dashboard.php');
}

$error   = '';
$success = '';

// ── Forgot-password step tracker (stored in session so user can't skip ahead) ──
// $_SESSION['fp_step']    = 1 | 2
// $_SESSION['fp_user_id'] = int   (set after step 1 verified)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {

    /* ── LOGIN ── */
    if ($_POST['action'] === 'login') {
        $result = loginUser($_POST['username'] ?? '', $_POST['password'] ?? '');
        if ($result['success']) {
            if ($result['role'] === 'admin')        redirect('pages/admin/dashboard.php');
            elseif ($result['role'] === 'trainer')  redirect('pages/trainer/dashboard.php');
            else                                    redirect('pages/member/dashboard.php');
        } else {
            $error = $result['message'];
        }
    }

    /* ── FORGOT STEP 1: verify email + username ── */
    if ($_POST['action'] === 'fp_step1') {
        $result = verifyIdentity(
            trim($_POST['fp_email']    ?? ''),
            trim($_POST['fp_username'] ?? '')
        );
        if ($result['success']) {
            $_SESSION['fp_user_id'] = $result['user_id'];
            $_SESSION['fp_step']    = 2;
        } else {
            $error = $result['message'];
            $_SESSION['fp_step'] = 1;
        }
    }

    /* ── FORGOT STEP 2: set new password ── */
    if ($_POST['action'] === 'fp_step2') {
        $userId  = $_SESSION['fp_user_id'] ?? 0;
        $newPass = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$userId || ($_SESSION['fp_step'] ?? 0) !== 2) {
            $error = 'Session expired. Please start again.';
            unset($_SESSION['fp_step'], $_SESSION['fp_user_id']);
        } elseif (strlen($newPass) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($newPass !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $result = resetPasswordDirect($userId, $newPass);
            if ($result['success']) {
                unset($_SESSION['fp_step'], $_SESSION['fp_user_id']);
                $success = 'Password reset successfully! You can now sign in.';
            } else {
                $error = $result['message'];
            }
        }
    }

    /* ── CANCEL / RESTART forgot flow ── */
    if ($_POST['action'] === 'fp_cancel') {
        unset($_SESSION['fp_step'], $_SESSION['fp_user_id']);
    }
}

// Determine which forgot-password step to show
$fpStep = $_SESSION['fp_step'] ?? 1;
// If a successful reset just happened, reset step back to 1
if ($success) $fpStep = 1;

// Active tab: if we're mid-reset or had a fp error, keep forgot tab open
$activeTab = (($fpStep === 2 || $error) && empty($success) && ($_POST['action'] ?? '') !== 'login')
    ? 'forgot' : 'login';
// If login had an error, keep login tab
if (!empty($error) && in_array($_POST['action'] ?? '', ['login'])) $activeTab = 'login';
?>
<!DOCTYPE html>
<?php $__theme = $_COOKIE["fitzone_theme"] ?? "dark"; ?>
<html lang="en" data-theme="<?= htmlspecialchars($__theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — FitZone Gym</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── Step indicator ── */
        .fp-steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 28px;
        }
        .fp-step-dot {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.78rem; font-weight: 700;
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
            background: var(--bg-card);
            transition: all 0.25s;
            position: relative; z-index: 1;
        }
        .fp-step-dot.active {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
        }
        .fp-step-dot.done {
            border-color: #28a745;
            background: #28a745;
            color: #fff;
        }
        .fp-step-line {
            flex: 1; height: 2px;
            background: var(--border-color);
            max-width: 60px;
        }
        .fp-step-line.done { background: #28a745; }

        .fp-step-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 0.72rem;
            color: var(--text-secondary);
            padding: 0 4px;
        }
        .fp-step-label span { text-align: center; flex: 1; }

        /* password strength bar */
        .pw-strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 6px;
            transition: all 0.3s;
            background: var(--border-color);
        }
        .pw-strength-bar.weak   { background: #dc3545; width: 33%; }
        .pw-strength-bar.medium { background: #ffc107; width: 66%; }
        .pw-strength-bar.strong { background: #28a745; width: 100%; }
        .pw-strength-text {
            font-size: 0.72rem;
            margin-top: 4px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body class="login-page">

    <!-- Theme toggle -->
    <div class="login-theme-toggle">
        <span><?= (($__theme) === 'dark') ? '🌙' : '☀️' ?></span>
        <button class="theme-toggle" onclick="handleLoginTheme()" title="Toggle theme" aria-label="Toggle theme"></button>
    </div>

    <!-- Back to website -->
    <a href="home.php" style="position:fixed;top:22px;left:28px;display:flex;align-items:center;gap:8px;font-size:0.82rem;font-weight:600;color:var(--text-secondary);text-decoration:none;z-index:10;transition:color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-secondary)'">
        ← Back to Website
    </a>

    <div class="login-container">
        <div class="login-brand">
            <div class="login-logo">💪</div>
            <h1>FITZONE</h1>
            <p>Gym Management System</p>
        </div>

        <div class="login-card">
            <div class="login-tabs">
                <button class="login-tab <?= $activeTab === 'login'  ? 'active' : '' ?>" onclick="switchLoginTab('login')">Sign In</button>
                <button class="login-tab <?= $activeTab === 'forgot' ? 'active' : '' ?>" onclick="switchLoginTab('forgot')">Forgot Password</button>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- ══════════════ SIGN IN FORM ══════════════ -->
            <div id="login-form" class="login-form" style="<?= $activeTab !== 'login' ? 'display:none' : '' ?>">
                <form method="POST" autocomplete="on">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label>Username or Email</label>
                        <input type="text" name="username" class="form-control" placeholder="Enter username or email" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In →</button>
                </form>
                <div class="demo-creds mt-2">
                    <strong>Demo Credentials</strong>
                    Admin: admin / admin123<br>
                    Trainer: trainer_rahul / admin123
                </div>
            </div>

            <!-- ══════════════ FORGOT PASSWORD ══════════════ -->
            <div id="forgot-form" class="login-form" style="<?= $activeTab !== 'forgot' ? 'display:none' : '' ?>">

                <?php if ($fpStep === 1): ?>
                <!-- ── Step 1: Verify identity ── -->
                <div class="fp-steps">
                    <div class="fp-step-dot active">1</div>
                    <div class="fp-step-line"></div>
                    <div class="fp-step-dot">2</div>
                    <div class="fp-step-line"></div>
                    <div class="fp-step-dot">3</div>
                </div>
                <div class="fp-step-label">
                    <span style="color:var(--primary)">Verify Identity</span>
                    <span>New Password</span>
                    <span>Done</span>
                </div>
                <p style="font-size:0.88rem;color:var(--text-secondary);margin-bottom:20px;">
                    Enter the email address and username linked to your account.
                </p>
                <form method="POST" autocomplete="off">
                    <input type="hidden" name="action" value="fp_step1">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="fp_email" class="form-control"
                               placeholder="your@email.com"
                               value="<?= htmlspecialchars($_POST['fp_email'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="fp_username" class="form-control"
                               placeholder="Your username"
                               value="<?= htmlspecialchars($_POST['fp_username'] ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Verify &amp; Continue →</button>
                </form>

                <?php elseif ($fpStep === 2): ?>
                <!-- ── Step 2: Set new password ── -->
                <div class="fp-steps">
                    <div class="fp-step-dot done">✓</div>
                    <div class="fp-step-line done"></div>
                    <div class="fp-step-dot active">2</div>
                    <div class="fp-step-line"></div>
                    <div class="fp-step-dot">3</div>
                </div>
                <div class="fp-step-label">
                    <span style="color:#28a745">Verified ✓</span>
                    <span style="color:var(--primary)">New Password</span>
                    <span>Done</span>
                </div>
                <p style="font-size:0.88rem;color:var(--text-secondary);margin-bottom:20px;">
                    Identity confirmed. Choose a strong new password.
                </p>
                <form method="POST" autocomplete="off">
                    <input type="hidden" name="action" value="fp_step2">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" id="newPasswordInput"
                               class="form-control" placeholder="Min. 6 characters"
                               oninput="checkPwStrength(this.value)" required autofocus>
                        <div class="pw-strength-bar" id="pwStrengthBar"></div>
                        <div class="pw-strength-text" id="pwStrengthText"></div>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirmPasswordInput"
                               class="form-control" placeholder="Re-enter new password"
                               oninput="checkPwMatch()" required>
                        <div class="pw-strength-text" id="pwMatchText"></div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" id="resetSubmitBtn">Reset Password →</button>
                </form>
                <form method="POST" style="margin-top:12px;">
                    <input type="hidden" name="action" value="fp_cancel">
                    <button type="submit" class="btn btn-block"
                            style="background:transparent;border:1px solid var(--border-color);color:var(--text-secondary);font-size:0.82rem;">
                        ← Start Over
                    </button>
                </form>
                <?php endif; ?>

            </div><!-- /forgot-form -->
        </div><!-- /login-card -->
    </div><!-- /login-container -->

    <script src="assets/js/main.js"></script>
    <script>
    function handleLoginTheme() {
        toggleTheme();
        var theme = document.documentElement.getAttribute('data-theme') || 'dark';
        var icon  = document.querySelector('.login-theme-toggle span');
        if (icon) icon.textContent = theme === 'dark' ? '🌙' : '☀️';
    }

    function switchLoginTab(tab) {
        document.getElementById('login-form').style.display  = tab === 'login'  ? '' : 'none';
        document.getElementById('forgot-form').style.display = tab === 'forgot' ? '' : 'none';
        document.querySelectorAll('.login-tab').forEach(function(b) {
            b.classList.toggle('active', b.textContent.trim().toLowerCase().startsWith(tab === 'login' ? 'sign' : 'forgot'));
        });
    }

    function checkPwStrength(val) {
        var bar  = document.getElementById('pwStrengthBar');
        var text = document.getElementById('pwStrengthText');
        if (!bar) return;
        bar.className = 'pw-strength-bar';
        if (val.length === 0) { text.textContent = ''; return; }
        var strong = val.length >= 8 && /[A-Z]/.test(val) && /[0-9]/.test(val) && /[^a-zA-Z0-9]/.test(val);
        var medium = val.length >= 6 && (/[A-Z]/.test(val) || /[0-9]/.test(val));
        if (strong)       { bar.classList.add('strong'); text.textContent = 'Strong password'; text.style.color = '#28a745'; }
        else if (medium)  { bar.classList.add('medium'); text.textContent = 'Medium — add symbols or uppercase'; text.style.color = '#ffc107'; }
        else              { bar.classList.add('weak');   text.textContent = 'Weak — too short or simple'; text.style.color = '#dc3545'; }
        checkPwMatch();
    }

    function checkPwMatch() {
        var p1   = document.getElementById('newPasswordInput');
        var p2   = document.getElementById('confirmPasswordInput');
        var text = document.getElementById('pwMatchText');
        var btn  = document.getElementById('resetSubmitBtn');
        if (!p1 || !p2 || !p2.value) { if (text) text.textContent = ''; return; }
        var match = p1.value === p2.value;
        text.textContent  = match ? '✓ Passwords match' : '✗ Passwords do not match';
        text.style.color  = match ? '#28a745' : '#dc3545';
        if (btn) btn.disabled = !match;
    }
    </script>
</body>
</html>
