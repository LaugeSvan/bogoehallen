<?php
/**
 * Kontakt (Contact) Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php'; 

$page_title = 'Kontakt';
$contact_email = get_content('footer', 'contact_email', 'kontakt@bogoehallen.dk');

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <h2 class="section-title">Kontakt os</h2>

    <p class="section-subtitle">
        Har du spørgsmål eller vil gerne vide mere? Send os en mail, så vender vi tilbage til dig.
    </p>

    <div class="two-column">
        <div class="contact-info-card">
            <h3>Skriv til os</h3>
            <p class="contact-email-intro">Brug dit eget mailprogram til at sende os din besked.</p>
            <a class="button primary" href="mailto:<?php echo safe_html($contact_email); ?>">Send os en mail</a>
        </div>

        <div class="contact-info-card">
            <h3>Kontaktoplysninger</h3>

            <div class="contact-info-item">
                <h4>Adresse</h4>
                <p><?php echo safe_html(get_content('footer', 'address', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod')); ?></p>
            </div>

            <div class="contact-info-item">
                <h4>Email</h4>
                <p><a href="mailto:<?php echo safe_html($contact_email); ?>"><?php echo safe_html($contact_email); ?></a></p>
            </div>

            <div class="contact-info-item">
                <h4>CVR</h4>
                <p><?php echo safe_html(get_content('footer', 'cvr', 'CVR: 12345678')); ?></p>
            </div>

            <?php $facebook_url = safe_navigation_url(get_content('footer', 'facebook_url', ''), ''); if ($facebook_url): ?>
                <div class="contact-info-item">
                    <h4>Social Media</h4>
                    <p><a href="<?php echo safe_html($facebook_url); ?>" target="_blank" rel="noopener noreferrer">Facebook →</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
