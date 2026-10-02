<?php
/**
 * Bestyrelsen (Board) Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

$page_title = 'Bestyrelsen';
$board_title = get_content('board', 'title', 'Bestyrelsen');
$board_text = get_content(
    'board',
    'text',
    'Bestyrelsen varetager Bogø Hallens daglige drift og udvikling. Kontakt os gerne via kontaktsiden.'
);
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <h2 class="section-title"><?php echo safe_html($board_title); ?></h2>
    <div class="two-column-content" style="max-width: 800px;">
        <?php echo nl2br(safe_html($board_text)); ?>
    </div>
    <p><a href="/kontakt.php">Kontakt bestyrelsen</a></p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>