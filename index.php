<?php
/**
 * Bogø Hallen - Homepage
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php'; 

$page_title = 'Forside';

// Get database content
$db = get_db_connection();

$about_title = get_content('main', 'about_title', 'Velkommen til Bogø Hallen');
$about_text = get_content('main', 'about_text', 'Bogø Hallen er Danmarks moderne idrætscenter med faciliteter til alle typer sport og aktiviteter.');

// Get gallery images
$gallery_result = $db->query('SELECT id, image_path, caption FROM gallery_images ORDER BY sort_order ASC LIMIT 4');
$gallery = $gallery_result ? $gallery_result->fetch_all(MYSQLI_ASSOC) : [];

// Get sponsors
$sponsors_result = $db->query('SELECT id, name, logo, link FROM sponsors ORDER BY sort_order ASC');
$sponsors = $sponsors_result ? $sponsors_result->fetch_all(MYSQLI_ASSOC) : [];

// Get opening hours
$hours_result = $db->query("SELECT `key`, value FROM content WHERE section = 'opening_hours' ORDER BY FIELD(`key`, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')");
$opening_hours = [];
if ($hours_result) {
    while ($row = $hours_result->fetch_assoc()) {
        $opening_hours[$row['key']] = $row['value'];
    }
}

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- Gallery Section -->
<section class="section">
    <div class="gallery">
        <?php foreach ($gallery as $item): ?>
            <div class="gallery-item">
                <img src="<?php echo safe_html($item['image_path']); ?>" alt="<?php echo safe_html($item['caption']); ?>">
                <div class="gallery-item-caption"><?php echo safe_html($item['caption'] ?: ''); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- About and Opening Hours Section -->
<section class="section">
    <h2 class="section-title"><?php echo safe_html($about_title); ?></h2>

    <div class="two-column">
        <div class="two-column-content">
            <?php echo nl2br(safe_html($about_text)); ?>
        </div>

        <div>
            <div class="opening-hours">
                <h4>Åbningstider</h4>
                <ul>
                    <?php
                    $days_labels = [
                        'monday' => 'Mandag',
                        'tuesday' => 'Tirsdag',
                        'wednesday' => 'Onsdag',
                        'thursday' => 'Torsdag',
                        'friday' => 'Fredag',
                        'saturday' => 'Lørdag',
                        'sunday' => 'Søndag'
                    ];

                    foreach ($days_labels as $day_key => $day_name):
                        $time = $opening_hours[$day_key] ?? 'Lukket';
                    ?>
                        <li>
                            <span class="day-name"><?php echo $day_name; ?></span>
                            <span class="day-time"><?php echo safe_html($time); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Sponsors Section -->
<?php if (count($sponsors) > 0): ?>
    <section class="section">
        <h2 class="section-title">Vores Sponsorer</h2>

        <div class="sponsors">
            <?php foreach ($sponsors as $sponsor): ?>
                <div class="sponsor-item">
                    <?php if ($sponsor['link']): ?>
                        <a href="<?php echo safe_html($sponsor['link']); ?>" target="_blank" title="<?php echo safe_html($sponsor['name']); ?>">
                            <img src="<?php echo safe_html($sponsor['logo']); ?>" alt="<?php echo safe_html($sponsor['name']); ?>">
                        </a>
                    <?php else: ?>
                        <img src="<?php echo safe_html($sponsor['logo']); ?>" alt="<?php echo safe_html($sponsor['name']); ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
