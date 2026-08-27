<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');

$base = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . BASE_URL;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
echo "  <url><loc>{$base}/index.php</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>\n";
echo '</urlset>';
