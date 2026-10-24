<?php
/**
 * Frontend Header Template
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php'; 

init_session();

$meta = get_site_meta_data();
$site_title = get_content('header', 'site_title', $meta['site_name']);
$logo_alt = get_content('header', 'logo_alt', $site_title);
$site_logo = normalize_upload_url(get_content('header', 'logo', ''));
$current_page = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$canonical_url = safe_navigation_url(get_content('header', 'canonical_url', $meta['canonical_url']), $meta['canonical_url']);
$site_description = get_content('header', 'site_description', $meta['site_description']);
$navigation = [
    ['label' => get_content('header', 'nav_home_label', 'Forside'), 'url' => safe_navigation_url(get_content('header', 'nav_home_url', '/'), '/'), 'page' => 'index.php'],
    ['label' => get_content('header', 'nav_board_label', 'Bestyrelsen'), 'url' => safe_navigation_url(get_content('header', 'nav_board_url', '/bestyrelsen.php'), '/bestyrelsen.php'), 'page' => 'bestyrelsen.php'],
    ['label' => get_content('header', 'nav_about_label', 'Om os'), 'url' => safe_navigation_url(get_content('header', 'nav_about_url', '/om-os.php'), '/om-os.php'), 'page' => 'om-os.php'],
    ['label' => get_content('header', 'nav_contact_label', 'Kontakt'), 'url' => safe_navigation_url(get_content('header', 'nav_contact_url', '/kontakt.php'), '/kontakt.php'), 'page' => 'kontakt.php'],
    ['label' => get_content('header', 'nav_booking_label', 'Booking'), 'url' => safe_navigation_url(get_content('header', 'nav_booking_url', 'https://bogoe-ic.halbooking.dk'), 'https://bogoe-ic.halbooking.dk'), 'page' => ''],
];

$page_title_text = isset($page_title) ? $page_title : $site_title;
$description_text = isset($page_description) ? $page_description : $site_description;
$og_image = $meta['og_image'] ?: '/images/og-default.jpg';
$canonical_page_url = $canonical_url;
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo safe_html($description_text); ?>">
    <meta name="theme-color" content="<?php echo safe_html($meta['theme_color']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo safe_html($page_title_text . ' - ' . $site_title); ?>">
    <meta property="og:description" content="<?php echo safe_html($description_text); ?>">
    <meta property="og:url" content="<?php echo safe_html($canonical_page_url); ?>">
    <meta property="og:image" content="<?php echo safe_html($og_image); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo safe_html($page_title_text . ' - ' . $site_title); ?>">
    <meta name="twitter:description" content="<?php echo safe_html($description_text); ?>">
    <meta name="twitter:image" content="<?php echo safe_html($og_image); ?>">
    <link rel="canonical" href="<?php echo safe_html($canonical_page_url); ?>">
    <title><?php echo isset($page_title) ? safe_html($page_title) . ' - ' : ''; ?><?php echo safe_html($site_title); ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="cookie-banner" id="cookie-banner" aria-live="polite" hidden>
        <div class="cookie-content">
            <p><?php echo safe_html($meta['cookie_text']); ?></p>
            <div class="cookie-actions">
                <button type="button" class="cookie-button cookie-button-secondary" data-cookie-choice="decline">Afvis</button>
                <button type="button" class="cookie-button cookie-button-primary" data-cookie-choice="accept">Accepter</button>
            </div>
        </div>
    </div>
    <header class="site-header">
        <div class="header-content">
            <div class="logo">
                <?php if ($site_logo): ?>
                    <a href="/" aria-label="<?php echo safe_html($site_title); ?> - Forside">
                        <img src="<?php echo safe_html($site_logo); ?>" alt="<?php echo safe_html($logo_alt); ?>">
                    </a>
                <?php else: ?>
                    <h1><?php echo safe_html($site_title); ?></h1>
                <?php endif; ?>
            </div>
            <button class="nav-toggle" type="button" aria-controls="site-navigation" aria-expanded="false" aria-label="Åbn menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="main-nav" id="site-navigation">
                <ul>
                    <?php foreach ($navigation as $item): ?>
                        <li><a href="<?php echo safe_html($item['url']); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) === $item['page'] ? 'active' : ''; ?>"><?php echo safe_html($item['label']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="site-main">
