<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');

$lastmod = date('Y-m-d');

$urls = [
    ['loc' => site_url(), 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => site_url('services'), 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => site_url('reviews'), 'priority' => '0.8', 'changefreq' => 'weekly'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
  <url>
    <loc><?= e($url['loc']) ?></loc>
    <lastmod><?= e($lastmod) ?></lastmod>
    <changefreq><?= e($url['changefreq']) ?></changefreq>
    <priority><?= e($url['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
