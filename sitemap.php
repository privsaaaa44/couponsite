<?php
// sitemap.php - Dynamic XML Sitemap Generator
require_once __DIR__ . '/connection.php';

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    
    <!-- Static Main Pages -->
    <url>
        <loc><?= base_url() ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= base_url('shops.php') ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= base_url('categories.php') ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= base_url('about.php') ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc><?= base_url('contact.php') ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc><?= base_url('privacy.php') ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>

    <!-- Dynamic Store Pages -->
    <?php
    $stmtBrands = $pdo->query("SELECT slug, created_at FROM brands WHERE status = 'active' ORDER BY id ASC");
    while ($b = $stmtBrands->fetch()):
        $modDate = !empty($b['created_at']) ? date('Y-m-d', strtotime($b['created_at'])) : date('Y-m-d');
    ?>
    <url>
        <loc><?= base_url('store.php?slug=' . urlencode($b['slug'])) ?></loc>
        <lastmod><?= $modDate ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <?php endwhile; ?>

    <!-- Dynamic Category Pages -->
    <?php
    $stmtCats = $pdo->query("SELECT slug, created_at FROM categories ORDER BY id ASC");
    while ($c = $stmtCats->fetch()):
        $modDate = !empty($c['created_at']) ? date('Y-m-d', strtotime($c['created_at'])) : date('Y-m-d');
    ?>
    <url>
        <loc><?= base_url('category.php?slug=' . urlencode($c['slug'])) ?></loc>
        <lastmod><?= $modDate ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endwhile; ?>

</urlset>
