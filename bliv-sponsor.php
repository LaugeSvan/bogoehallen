<?php
/**
 * Bliv Sponsor (Become Sponsor) Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/content-defaults.php';
require_once __DIR__ . '/includes/security.php'; 

$sponsor_defaults = [
    'title' => 'Bliv Sponsor',
    'intro' => 'Vil din virksomhed være med til at støtte Bogø Hallen og lokal sport?',
    'content' => get_sponsor_page_default_content(),
    'contact_title' => 'Kontakt os',
    'contact_intro' => 'Interesseret i at blive sponsor? Vi glæder os til at høre fra dig!',
    'contact_note' => 'Vi kontakter dig inden for 2 arbejdsdage.',
];
$sponsor_page = [];
foreach ($sponsor_defaults as $key => $default) {
    $sponsor_page[$key] = get_content('sponsor_page', $key, $default);
}

$page_title = $sponsor_page['title'];

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <h1 class="section-title"><?php echo safe_html($sponsor_page['title']); ?></h1>

    <p class="section-subtitle"><?php echo safe_html($sponsor_page['intro']); ?></p>

    <div class="two-column">
        <div class="two-column-content sponsor-page-content">
            <?php echo sanitize_rich_text($sponsor_page['content']); ?>
            <p class="sponsor-page-cta">Har du spørgsmål eller vil du høre mere? <a href="/kontakt.php">Kontakt os</a>.</p>
        </div>

        <div>
            <div class="sponsor-contact-card">
                <h2><?php echo safe_html($sponsor_page['contact_title']); ?></h2>

                <p class="sponsor-contact-intro"><?php echo safe_html($sponsor_page['contact_intro']); ?></p>

                <div class="sponsor-contact-item">
                    <h4>Email</h4>
                    <p>
                        <a href="mailto:<?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogoehallen.dk')); ?>">
                            <?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogoehallen.dk')); ?>
                        </a>
                    </p>
                </div>

                <div class="sponsor-contact-item">
                    <h4>Adresse</h4>
                    <p><?php echo safe_html(get_content('footer', 'address', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod')); ?></p>
                </div>

                <div class="sponsor-contact-item">
                    <h4>CVR</h4>
                    <p><?php echo safe_html(get_content('footer', 'cvr', 'CVR: 12345678')); ?></p>
                </div>

                <p class="sponsor-contact-note"><?php echo safe_html($sponsor_page['contact_note']); ?></p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
