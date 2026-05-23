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
                <h1><?php echo safe_html($site_title); ?></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">Forside</a></li>
                    <li><a href="/om-os.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'om-os.php' ? 'active' : ''; ?>">Om os</a></li>
                    <li><a href="/kontakt.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'kontakt.php' ? 'active' : ''; ?>">Kontakt</a></li>
                    <li><a href="/bliv-sponsor.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'bliv-sponsor.php' ? 'active' : ''; ?>">Bliv sponsor</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="site-main">
