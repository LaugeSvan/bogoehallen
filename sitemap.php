<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$site_url = rtrim(get_site_meta_value('site_url', SITE_URL), '/');
$pages = [
    '/',
    '/bestyrelsen.php',
    '/om-os.php',
    '/kontakt.php',
    '/vedtaegt.php',
    '/bliv-sponsor.php',
];

header('Content-Type: application/xml; charset=utf-8');

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($pages as $page) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($site_url . $page, ENT_QUOTES, 'UTF-8') . "</loc>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>" . ($page === '/' ? '1.0' : '0.8') . "</priority>\n";
    $xml .= "  </url>\n";
}
$xml .= "</urlset>\n";

echo $xml;
