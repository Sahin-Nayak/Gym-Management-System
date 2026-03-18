<?php
$wbPath  = '../../';
$wbPage  = 'contact';
$wbTitle = 'Contact Us';
$wbDesc  = 'Get in touch with FitZone Gym. Visit us, call, email, or send a message — we\'re here to help.';
include $wbPath . 'includes/website-header.php';
?>

<div class="page-hero">
    <div class="page-hero-inner">
        <div class="page-hero-tag">Get In Touch</div>
        <h1 class="page-hero-title">Contact <span>FitZone</span></h1>
        <p class="page-hero-sub">Have a question, want to visit, or ready to join? We'd love to hear from you.</p>
        <div class="page-breadcrumb">
            <a href="<?= $wbPath ?>home.php">Home</a> › <span>Contact</span>
        </div>
    </div>
</div>

<section class="section section-alt">
    <div class="section-inner">
        <div class="contact-grid">
            <!-- Info -->
            <div class="contact-info reveal">
                <div class="about-eyebrow">Reach Out</div>
                <h2 class="contact-info-title">We're Here to <span>Help</span></h2>
                <p style="color:var(--white-70);font-size:0.95rem;line-height:1.75;margin-bottom:8px;">
                    Whether you're a curious visitor, a potential member, or an existing member with a question — our team is here for you.
                </p>
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

                <div style="margin-top:8px;">
                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--white-40);margin-bottom:12px;">Follow Us</div>
                    <div class="social-row">
                        <?php
                        $socials = [['social_instagram','📸','Instagram'],['social_facebook','👍','Facebook'],['social_twitter','🐦','Twitter'],['social_youtube','▶️','YouTube']];
                        foreach ($socials as [$key,$icon,$label]): $url = wc($key,'#'); ?>
                        <a href="<?= htmlspecialchars($url) ?>" class="social-pill" <?= $url !== '#' ? 'target="_blank"' : '' ?>>
                            <?= $icon ?> <?= $label ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Form -->
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
                        <textarea class="ws-form-control" name="message" placeholder="Tell us about your fitness goals or ask us anything…" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary-ws" style="width:100%;justify-content:center;" id="submitBtn">
                        Send Message →
                    </button>
                    <div class="contact-success" id="contactSuccess"></div>
                    <div class="contact-error"   id="contactError"></div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Quick CTA -->
<section class="section">
    <div class="section-inner">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
            <?php
            $ctas = [
                ['🎯','Book a Free Tour','Come see the gym in person. Our team will show you around and answer all your questions.','Book Tour'],
                ['💳','Start Your Free Trial','Try FitZone for 3 days completely free. No credit card required, no commitment.','Get Free Trial'],
                ['📞','Call Us Now','Prefer to talk? Our friendly team is available from 8AM to 8PM every day.',wc('contact_phone','+91 98765 43210')],
            ];
            foreach ($ctas as $i => $cta): ?>
            <div class="service-card reveal" style="transition-delay:<?= $i*0.1 ?>s;text-align:center;align-items:center;">
                <div class="service-icon-wrap" style="margin:0 auto"><?= $cta[0] ?></div>
                <div class="service-title"><?= $cta[1] ?></div>
                <p class="service-desc"><?= $cta[2] ?></p>
                <a href="<?= $i === 2 ? 'tel:'.wc('contact_phone') : ($i === 1 ? $wbPath.'pages/website/plans.php' : '#contact') ?>"
                   class="btn-primary-ws" style="margin-top:auto;align-self:center"><?= $cta[3] ?></a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
function handleContactForm(e) {
    e.preventDefault();
    var btn     = document.getElementById('submitBtn');
    var success = document.getElementById('contactSuccess');
    var error   = document.getElementById('contactError');
    btn.textContent = 'Sending…'; btn.disabled = true;
    success.style.display = 'none'; error.style.display = 'none';
    fetch('<?= $wbPath ?>handle_enquiry.php', { method:'POST', body: new FormData(e.target) })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                success.textContent   = '✅ ' + res.message;
                success.style.display = 'block';
                btn.textContent       = 'Message Sent ✓';
                e.target.reset();
            } else {
                error.textContent   = '⚠️ ' + res.message;
                error.style.display = 'block';
                btn.textContent     = 'Send Message →'; btn.disabled = false;
            }
        })
        .catch(function() {
            error.textContent = '⚠️ Network error. Please try again.';
            error.style.display = 'block';
            btn.textContent = 'Send Message →'; btn.disabled = false;
        });
}
</script>

<?php include $wbPath . 'includes/website-footer.php'; ?>
