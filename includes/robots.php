<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /install/\n";
echo "Disallow: /api/\n";
echo "Disallow: /database/\n";
echo "Disallow: /settings/\n";
echo "Disallow: /lib/\n";
echo "Disallow: /includes/\n\n";
echo 'Sitemap: ' . site_url('sitemap.xml') . "\n";
