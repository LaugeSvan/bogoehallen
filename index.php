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
$booking_url = safe_navigation_url(get_content('header', 'nav_booking_url', 'https://bogoe-ic.halbooking.dk'), 'https://bogoe-ic.halbooking.dk');
$notice_title = get_content('home_notice', 'title', 'Praktisk information');
$notice_content = get_content('home_notice', 'content', '');

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

<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">Bogø Hallen</p>
        <h1><?php echo safe_html($about_title); ?></h1>
        <p class="hero-text"><?php echo safe_html(strip_tags($about_text)); ?></p>
        <div class="cta-row">
            <a class="button primary" href="<?php echo safe_html($booking_url); ?>">Book hallen</a>
            <a class="button secondary" href="/kontakt.php">Kontakt os</a>
        </div>
    </div>

    <div class="hero-panel">
        <div class="hero-panel-card">
            <h3>Åbningstider</h3>
            <ul>
                <?php
                $hero_days = ['monday' => 'Mandag', 'tuesday' => 'Tirsdag', 'wednesday' => 'Onsdag', 'thursday' => 'Torsdag', 'friday' => 'Fredag', 'saturday' => 'Lørdag', 'sunday' => 'Søndag'];
                foreach ($hero_days as $day_key => $day_name):
                    $time = $opening_hours[$day_key] ?? 'Lukket';
                    echo '<li><span>' . safe_html($day_name) . '</span><strong>' . safe_html($time) . '</strong></li>';
                endforeach;
                ?>
            </ul>
        </div>
    </div>
</section>

<?php if (trim(strip_tags($notice_content)) !== ''): ?>
    <section class="home-notice" aria-labelledby="home-notice-title">
        <h2 id="home-notice-title"><?php echo safe_html($notice_title); ?></h2>
        <div class="two-column-content"><?php echo sanitize_rich_text($notice_content); ?></div>
    </section>
<?php endif; ?>

<!-- Gallery Section -->
<section class="section">
    <div class="gallery-header">
        <h2 class="section-title">Galleri</h2>
    </div>
    <div class="gallery">
        <?php foreach ($gallery as $item): ?>
            <?php $image_url = normalize_upload_url($item['image_path'] ?? ''); ?>
            <div class="gallery-item">
                <a class="gallery-image-link" href="<?php echo safe_html($image_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo safe_html('Vis billede i fuld størrelse' . ($item['caption'] ? ': ' . $item['caption'] : '')); ?>">
                    <img src="<?php echo safe_html($image_url); ?>" alt="<?php echo safe_html($item['caption']); ?>" loading="lazy">
                </a>
                <div class="gallery-item-caption"><?php echo safe_html($item['caption'] ?: ''); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- About Section -->
<section class="section">
    <h2 class="section-title">Om Bogø Hallen</h2>

    <div class="two-column-content page-reading-column">
        <?php echo sanitize_rich_text($about_text); ?>
    </div>
</section>

<!-- Sponsors Section -->
<?php if (count($sponsors) > 0): ?>
    <section class="section">
        <h2 class="section-title">Vores Sponsorer</h2>
        <p class="section-subtitle">Tak til vores sponsorer! Har du lyst til at støtte Bogø Hallen, øens samlingspunkt? <a href="/bliv-sponsor.php">Læs om at blive sponsor</a> eller kontakt os på <a href="mailto:<?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogoehallen.dk')); ?>"><?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogoehallen.dk')); ?></a>.</p>

        <div class="sponsors">
            <?php foreach ($sponsors as $sponsor): ?>
                <div class="sponsor-item">
                    <?php if ($sponsor['link']): ?>
                        <a href="<?php echo safe_html(safe_navigation_url($sponsor['link'], '#')); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo safe_html($sponsor['name']); ?>">
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
