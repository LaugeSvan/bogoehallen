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
$bylaws_content = sanitize_rich_text(get_content('bylaws', 'content', get_bylaws_default_content()));
$bylaws_sections = [];
$bylaws_content = preg_replace_callback(
    '/<h([23])>(.*?)<\/h\1>/is',
    function ($matches) use (&$bylaws_sections) {
        $title = trim(strip_tags($matches[2]));
        if ($title === '') {
            return $matches[0];
        }

        $id = 'vedtaegt-afsnit-' . (count($bylaws_sections) + 1);
        $bylaws_sections[] = ['id' => $id, 'title' => $title];

        return '<h' . $matches[1] . ' id="' . $id . '">' . $matches[2] . '</h' . $matches[1] . '>';
    },
    $bylaws_content
);

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <h1 class="section-title"><?php echo safe_html($bylaws_title); ?></h1>

    <div class="bylaws-layout">
        <?php if (count($bylaws_sections) >= 3): ?>
            <nav class="page-toc" aria-label="Indhold på siden">
                <h2>På denne side</h2>
                <ol>
                    <?php foreach ($bylaws_sections as $section): ?>
                        <li><a href="#<?php echo safe_html($section['id']); ?>"><?php echo safe_html($section['title']); ?></a></li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

        <div class="two-column-content page-reading-column bylaws-content">
            <?php echo $bylaws_content; ?>
        </div>
    </div>

    <div class="bylaws-contact">
        <p>Har du spørgsmål til vedtægterne? <a href="/kontakt.php">Kontakt os</a></p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
