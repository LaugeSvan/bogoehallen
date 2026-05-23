<?php
/**
 * Kontakt (Contact) Page
 */

require_once __DIR__ . '/config.php';

$page_title = 'Kontakt';

$db = get_db_connection();
$success = '';
$error = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'CSRF-validering mislykkedes. Prøv igen.';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $subject = sanitize_input($_POST['subject'] ?? '');
        $message = sanitize_input($_POST['message'] ?? '');

        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error = 'Alle felter er påkrævet.';
        } elseif (!validate_email($email)) {
            $error = 'Ugyldig email-adresse.';
        } else {
            // Insert into database
            $stmt = $db->prepare('
                INSERT INTO contact_submissions (name, email, subject, message)
                VALUES (?, ?, ?, ?)
            ');

            if ($stmt) {
                $stmt->bind_param('ssss', $name, $email, $subject, $message);

                if ($stmt->execute()) {
                    // Send email to admin
                    $to = get_content('footer', 'contact_email', 'admin@bogohallen.dk');
                    $email_subject = 'Ny henvendelse: ' . $subject;
                    $email_message = "Navn: $name\nEmail: $email\n\nBesked:\n$message";
                    $headers = "From: $email\r\nReply-To: $email";

                    @mail($to, $email_subject, $email_message, $headers);

                    $success = 'Tak for din henvendelse! Vi vender tilbage til dig snart.';
                    $name = $email = $subject = $message = '';
                } else {
                    $error = 'Der opstod en fejl. Prøv igen senere.';
                }

                $stmt->close();
            } else {
                $error = 'Database-fejl: ' . $db->error;
            }
        }
    }
}

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <h2 class="section-title">Kontakt os</h2>

    <p class="section-subtitle">
        Har du spørgsmål eller vil gerne vide mere? Kontakt os via formularen nedenfor eller direkte.
    </p>

    <div class="two-column">
        <div>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo safe_html($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo safe_html($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="contact-form">
                <div class="form-group">
                    <label for="name">Navn *</label>
                    <input type="text" id="name" name="name" value="<?php echo safe_html($name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo safe_html($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="subject">Emne *</label>
                    <input type="text" id="subject" name="subject" value="<?php echo safe_html($subject ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="message">Besked *</label>
                    <textarea id="message" name="message" required><?php echo safe_html($message ?? ''); ?></textarea>
                </div>

                <?php echo csrf_input(); ?>

                <button type="submit">Send besked</button>
            </form>
        </div>

        <div>
            <div style="background: #f9f9f9; padding: 30px; border-radius: 4px;">
                <h3 style="margin-bottom: 20px; font-size: 18px;">Kontaktoplysninger</h3>

                <div style="margin-bottom: 20px;">
                    <h4 style="color: var(--text-color); margin-bottom: 5px; font-size: 14px; text-transform: uppercase; font-weight: 600;">Adresse</h4>
                    <p><?php echo safe_html(get_content('footer', 'address', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod')); ?></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <h4 style="color: var(--text-color); margin-bottom: 5px; font-size: 14px; text-transform: uppercase; font-weight: 600;">Email</h4>
                    <p><a href="mailto:<?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogohallen.dk')); ?>"><?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogohallen.dk')); ?></a></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <h4 style="color: var(--text-color); margin-bottom: 5px; font-size: 14px; text-transform: uppercase; font-weight: 600;">CVR</h4>
                    <p><?php echo safe_html(get_content('footer', 'cvr', 'CVR: 12345678')); ?></p>
                </div>

                <?php $facebook_url = get_content('footer', 'facebook_url', ''); if ($facebook_url): ?>
                    <div>
                        <h4 style="color: var(--text-color); margin-bottom: 5px; font-size: 14px; text-transform: uppercase; font-weight: 600;">Social Media</h4>
                        <p><a href="<?php echo safe_html($facebook_url); ?>" target="_blank">Facebook →</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
