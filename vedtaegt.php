<?php
/**
 * Vedtægt (Constitution) Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/content-defaults.php';
require_once __DIR__ . '/includes/security.php';

$page_title = 'Vedtægt';
$bylaws_title = get_content('bylaws', 'title', 'Vedtægter');
$bylaws_content = get_content('bylaws', 'content', get_bylaws_default_content());

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <h2 class="section-title"><?php echo safe_html($bylaws_title); ?></h2>

    <div class="two-column-content" style="max-width: 800px;">
        <?php echo sanitize_rich_text($bylaws_content); ?>
        <p>Har du spørgsmål til vedtægterne? <a href="/kontakt.php">Kontakt os</a></p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
