<?php
/**
 * Frontend Header Template
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php'; 

init_session();

// Get header content
$site_title = get_content('header', 'site_title', 'Bogø Hallen');
$site_logo = normalize_upload_url(get_content('header', 'logo', ''));
$navigation = [
    ['label' => get_content('header', 'nav_home_label', 'Forside'), 'url' => safe_navigation_url(get_content('header', 'nav_home_url', '/'), '/'), 'page' => 'index.php'],
    ['label' => get_content('header', 'nav_board_label', 'Bestyrelsen'), 'url' => safe_navigation_url(get_content('header', 'nav_board_url', '/bestyrelsen.php'), '/bestyrelsen.php'), 'page' => 'bestyrelsen.php'],
    ['label' => get_content('header', 'nav_about_label', 'Om os'), 'url' => safe_navigation_url(get_content('header', 'nav_about_url', '/om-os.php'), '/om-os.php'), 'page' => 'om-os.php'],
    ['label' => get_content('header', 'nav_contact_label', 'Kontakt'), 'url' => safe_navigation_url(get_content('header', 'nav_contact_url', '/kontakt.php'), '/kontakt.php'), 'page' => 'kontakt.php'],
];
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bogø Hallen - Danmarks moderne idrætscenter">
    <title><?php echo isset($page_title) ? safe_html($page_title) . ' - ' : ''; ?><?php echo safe_html($site_title); ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="header-content">
            <div class="logo">
                <?php if ($site_logo): ?>
                    <a href="/" aria-label="<?php echo safe_html($site_title); ?> - Forside">
                        <img src="<?php echo safe_html($site_logo); ?>" alt="<?php echo safe_html($site_title); ?>">
                    </a>
                <?php else: ?>
                    <h1><?php echo safe_html($site_title); ?></h1>
                <?php endif; ?>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php foreach ($navigation as $item): ?>
                        <li><a href="<?php echo safe_html($item['url']); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) === $item['page'] ? 'active' : ''; ?>"><?php echo safe_html($item['label']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="site-main">
