<?php
/**
 * Om Os (About) Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

$page_title = 'Om os';
$about_page_content = get_content('about_page', 'content', get_about_page_default_content());

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <h2 class="section-title">Om Bogø Hallen</h2>

    <div class="two-column-content" style="max-width: 800px;">
        <?php echo sanitize_rich_text($about_page_content); ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
