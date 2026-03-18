<?php
// Shared website footer
// Expects $wbPath to be set by calling page
if (!isset($wbPath)) $wbPath = '';
?>
<!-- CTA BANNER -->
<div class="cta-banner">
    <div class="cta-banner-inner">
        <h2 class="cta-banner-title">Ready to Transform?</h2>
        <p class="cta-banner-sub">Join thousands of members who chose FitZone. Start your free trial today.</p>
        <a href="<?= $wbPath ?>login.php" class="btn-cta-white">Get Started Today →</a>
    </div>
</div>

<footer class="footer">
    <div class="footer-main">
        <div class="footer-brand">
            <div class="footer-brand-logo">
                <div class="logo-icon">💪</div>
                <h2>FITZONE</h2>
            </div>
            <p class="footer-tagline"><?= htmlspecialchars(wc('footer_tagline', 'Your transformation starts here. Join the FitZone family.')) ?></p>
            <div class="social-row">
                <a href="<?= htmlspecialchars(wc('social_instagram','#')) ?>" class="social-pill" target="_blank">📸</a>
                <a href="<?= htmlspecialchars(wc('social_facebook','#')) ?>"  class="social-pill" target="_blank">👍</a>
                <a href="<?= htmlspecialchars(wc('social_twitter','#')) ?>"   class="social-pill" target="_blank">🐦</a>
                <a href="<?= htmlspecialchars(wc('social_youtube','#')) ?>"   class="social-pill" target="_blank">▶️</a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Explore</h4>
            <ul class="footer-links">
                <li><a href="<?= $wbPath ?>pages/website/about.php">About Us</a></li>
                <li><a href="<?= $wbPath ?>pages/website/services.php">Services</a></li>
                <li><a href="<?= $wbPath ?>pages/website/plans.php">Membership Plans</a></li>
                <li><a href="<?= $wbPath ?>pages/website/trainers.php">Our Trainers</a></li>
                <li><a href="<?= $wbPath ?>pages/website/classes.php">Classes</a></li>
                <li><a href="<?= $wbPath ?>pages/website/facts.php">Athlete Facts</a></li>
                <li><a href="<?= $wbPath ?>pages/website/blog.php">Blog</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul class="footer-links">
                <li><a href="<?= $wbPath ?>pages/website/contact.php">Contact Us</a></li>
                <li><a href="<?= $wbPath ?>login.php">Member Login</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Contact</h4>
            <ul class="footer-links">
                <li><a href="tel:<?= htmlspecialchars(wc('contact_phone')) ?>"><?= htmlspecialchars(wc('contact_phone', '+91 98765 43210')) ?></a></li>
                <li><a href="mailto:<?= htmlspecialchars(wc('contact_email')) ?>"><?= htmlspecialchars(wc('contact_email', 'info@fitzonegym.com')) ?></a></li>
                <li><a href="<?= $wbPath ?>pages/website/contact.php"><?= htmlspecialchars(wc('contact_hours_wd', 'Mon–Fri 5AM–11PM')) ?></a></li>
                <li><a href="<?= $wbPath ?>pages/website/contact.php"><?= htmlspecialchars(wc('contact_hours_we', 'Sat–Sun 6AM–10PM')) ?></a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom" style="max-width:1280px;margin:0 auto;padding:24px 32px;border-top:1px solid var(--white-06);display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div style="font-size:0.82rem;color:var(--white-40);"><?= htmlspecialchars(wc('footer_copyright', '© 2025 FitZone Gym. All rights reserved.')) ?></div>
        <div style="display:flex;gap:20px;">
            <a href="#" style="font-size:0.82rem;color:var(--white-40);transition:color 0.2s" onmouseover="this.style.color='var(--white)'" onmouseout="this.style.color='var(--white-40)'">Privacy Policy</a>
            <a href="#" style="font-size:0.82rem;color:var(--white-40);transition:color 0.2s" onmouseover="this.style.color='var(--white)'" onmouseout="this.style.color='var(--white-40)'">Terms</a>
            <a href="<?= $wbPath ?>login.php" style="font-size:0.82rem;color:var(--white-40);transition:color 0.2s" onmouseover="this.style.color='var(--white)'" onmouseout="this.style.color='var(--white-40)'">Admin Login</a>
        </div>
    </div>
</footer>

<script>
function toggleMobileNav() {
    document.getElementById('mobileNav').classList.toggle('show');
}
window.addEventListener('scroll', function() {
    var nav = document.getElementById('navbar');
    if (window.scrollY > 60) nav.classList.add('scrolled');
    else nav.classList.remove('scrolled');
});
// Scroll reveal
var revealObs = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) {
        if (e.isIntersecting) { e.target.classList.add('visible'); revealObs.unobserve(e.target); }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
document.querySelectorAll('.reveal').forEach(function(el) { revealObs.observe(el); });
</script>
</body>
</html>
