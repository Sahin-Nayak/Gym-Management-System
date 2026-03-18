<?php
$wbPath = '../../';
require_once $wbPath . 'includes/config.php';

$id   = (int)($_GET['id'] ?? 0);
$post = null;
if ($id) {
    $r = $conn->query("SELECT * FROM blogs WHERE id=$id AND is_published=1");
    if ($r && $r->num_rows) {
        $post = $r->fetch_assoc();
        // Increment views
        $conn->query("UPDATE blogs SET views=views+1 WHERE id=$id");
    }
}

if (!$post) {
    header('Location: blog.php'); exit;
}

$wbPage  = 'blog';
$wbTitle = $post['title'];
$wbDesc  = $post['excerpt'] ?: 'Read this article on the FitZone Blog.';
include $wbPath . 'includes/website-header.php';

// Related posts
$related = $conn->query("SELECT id,title,cover_emoji,category,published_at FROM blogs WHERE is_published=1 AND id<>$id AND category='" . $conn->real_escape_string($post['category']) . "' ORDER BY published_at DESC LIMIT 3");

// Simple markdown-style renderer
function renderContent($text) {
    if (empty($text)) return '<p>Content coming soon.</p>';
    
    // Decode any already-escaped HTML entities first
    $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // If it contains HTML tags — render as HTML
    if (preg_match('/<(p|h[1-6]|ul|ol|li|strong|em|br|div|span|blockquote|table|tr|td|th|hr)\b/i', $decoded)) {
        $allowed = [
            'p', 'h2', 'h3', 'h4', 'h5', 'h6',
            'strong', 'em', 'b', 'i', 'u',
            'ul', 'ol', 'li',
            'br', 'hr',
            'a', 'span', 'div', 'blockquote',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
        ];
        return strip_tags($decoded, '<' . implode('><', $allowed) . '>');
    }
    
    // Plain text / markdown fallback
    $text = htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m',  '<h2>$1</h2>', $text);
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.+?)\*/',     '<em>$1</em>',         $text);
    $text = nl2br($text);
    return $text;
}
?>

<!-- POST HEADER -->
<div class="post-header">
    <div class="post-header-inner">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
            <span class="blog-category"><?= htmlspecialchars($post['category']) ?></span>
            <span style="font-size:0.78rem;color:var(--white-40)"><?= date('F d, Y', strtotime($post['published_at'] ?: $post['created_at'])) ?></span>
            <span class="views-badge">👁 <?= number_format($post['views']) ?> views</span>
        </div>
        <h1 style="font-family:var(--font-head);font-size:clamp(2.2rem,5vw,4rem);letter-spacing:2px;text-transform:uppercase;color:var(--white);line-height:1.05;margin-bottom:16px;">
            <?= htmlspecialchars($post['title']) ?>
        </h1>
        <p style="font-size:1rem;color:var(--white-70);line-height:1.7;max-width:640px;margin-bottom:20px;">
            <?= htmlspecialchars($post['excerpt'] ?: '') ?>
        </p>
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:38px;height:38px;border-radius:50%;background:var(--red);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;color:#fff;flex-shrink:0;">
                <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
            </div>
            <div>
                <div style="font-weight:700;font-size:0.88rem;color:var(--white)"><?= htmlspecialchars($post['author_name']) ?></div>
                <div style="font-size:0.75rem;color:var(--white-40)">FitZone Expert</div>
            </div>
        </div>
        <div style="margin-top:20px;">
            <a href="blog.php" style="font-size:0.8rem;color:var(--red);font-weight:600">← Back to Blog</a>
        </div>
    </div>
</div>

<!-- LARGE COVER EMOJI -->
<div style="background:var(--black-2);border-bottom:1px solid var(--white-06);">
    <div style="max-width:800px;margin:0 auto;padding:0 32px;">
        <div style="height:300px;background:var(--black-3);border-radius:0 0 20px 20px;display:flex;align-items:center;justify-content:center;font-size:9rem;position:relative;overflow:hidden;">
            <div style="position:absolute;inset:0;background:radial-gradient(circle at 50% 50%,rgba(230,57,70,0.12),transparent 60%);"></div>
            <?= htmlspecialchars($post['cover_emoji']) ?>
        </div>
    </div>
</div>

<!-- POST CONTENT -->
<div class="post-content-wrap">
    <?php if (!empty($post['tags'])): ?>
    <div class="ws-tag-list" style="margin-bottom:32px;">
        <?php foreach (explode(',', $post['tags']) as $tag): ?>
        <a href="blog.php?q=<?= urlencode(trim($tag)) ?>" class="ws-tag">#<?= htmlspecialchars(trim($tag)) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <article class="post-content">
        <?= renderContent($post['content'] ?: $post['excerpt'] ?: 'Content coming soon.') ?>
    </article>

    <!-- Share / Nav -->
    <div style="margin-top:48px;padding-top:32px;border-top:1px solid var(--white-06);display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <a href="blog.php" class="btn-outline-ws">← All Articles</a>
        <div style="display:flex;gap:10px;">
            <a href="<?= $wbPath ?>pages/website/plans.php" class="btn-primary-ws">Join FitZone →</a>
        </div>
    </div>
</div>

<!-- RELATED POSTS -->
<?php if ($related->num_rows > 0): ?>
<section style="background:var(--black-2);border-top:1px solid var(--white-06);padding:70px 0;">
    <div class="section-inner">
        <div class="section-header" style="margin-bottom:40px;">
            <div class="section-tag">Keep Reading</div>
            <h2 class="section-title">Related <span>Articles</span></h2>
        </div>
        <div class="blog-grid">
            <?php while ($rp = $related->fetch_assoc()): ?>
            <a href="blog-post.php?id=<?= $rp['id'] ?>" class="blog-card reveal" style="text-decoration:none;">
                <div class="blog-cover"><?= htmlspecialchars($rp['cover_emoji']) ?></div>
                <div class="blog-body">
                    <div class="blog-meta">
                        <span class="blog-category"><?= htmlspecialchars($rp['category']) ?></span>
                        <span class="blog-date"><?= date('M d, Y', strtotime($rp['published_at'] ?: 'now')) ?></span>
                    </div>
                    <div class="blog-title"><?= htmlspecialchars($rp['title']) ?></div>
                    <div class="blog-read-more" style="margin-top:auto">Read →</div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include $wbPath . 'includes/website-footer.php'; ?>
