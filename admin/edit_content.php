<?php
/**
 * Edit Main Content and Settings
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';

require_admin_login();

$page_title = 'Rediger Indhold';
$page_subtitle = 'Administrer tekstblokke, åbningstider og sidearrangementer';

$db = get_db_connection();
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'CSRF-validering mislykkedes';
    } else {
        $user_id = get_current_user_id();

        // Update about section
        if (isset($_POST['about_title'])) {
            set_content('main', 'about_title', $_POST['about_title'], $user_id);
            set_content('main', 'about_text', $_POST['about_text'], $user_id);
        }

        // Update footer content
        if (isset($_POST['footer_address'])) {
            set_content('footer', 'address', $_POST['footer_address'], $user_id);
            set_content('footer', 'cvr', $_POST['footer_cvr'], $user_id);
            set_content('footer', 'contact_email', $_POST['footer_contact_email'], $user_id);
            set_content('footer', 'facebook_url', $_POST['footer_facebook_url'], $user_id);
        }

        // Update opening hours
        if (isset($_POST['hours'])) {
            $hours = [];
            foreach ($_POST['hours'] as $day => $time) {
                $hours[$day] = $time;
            }
            save_opening_hours($hours, $user_id);
        }

        $success = 'Indhold opdateret succesfuldt!';
    }
}

// Get current content
$about_title = get_content('main', 'about_title', 'Velkommen til Bogø Hallen');
$about_text = get_content('main', 'about_text', 'Bogø Hallen er Danmarks moderne idrætscenter.');
$footer_address = get_content('footer', 'address', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod');
$footer_cvr = get_content('footer', 'cvr', 'CVR: 12345678');
$footer_contact_email = get_content('footer', 'contact_email', 'kontakt@bogohallen.dk');
$footer_facebook_url = get_content('footer', 'facebook_url', 'https://facebook.com/bogohallen');
$opening_hours = get_opening_hours();

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo safe_html($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo safe_html($error); ?></div>
<?php endif; ?>

<style>
    .form-section {
        background: white;
        padding: 20px;
        border-radius: 4px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .form-section h3 {
        font-size: 16px;
        margin-bottom: 15px;
        border-bottom: 2px solid #ecf0f1;
        padding-bottom: 10px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 14px;
        color: #2c3e50;
    }

    input[type="text"],
    input[type="email"],
    input[type="url"],
    textarea,
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
        font-family: inherit;
    }

    textarea {
        min-height: 200px;
        resize: vertical;
    }

    .hours-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .hour-input {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .hour-input label {
        flex: 1;
        margin: 0;
    }

    .hour-input input {
        flex: 1;
    }

    button {
        background: #3498db;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: background 0.3s;
    }

    button:hover {
        background: #2980b9;
    }

    .form-group-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
</style>

<form method="POST">
    <!-- About Section -->
    <div class="form-section">
        <h3>Om Os</h3>

        <div class="form-group">
            <label for="about_title">Titel</label>
            <input type="text" id="about_title" name="about_title" value="<?php echo safe_html($about_title); ?>" required>
        </div>

        <div class="form-group">
            <label for="about_text">Beskrivelse</label>
            <textarea id="about_text" name="about_text" required><?php echo safe_html($about_text); ?></textarea>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="form-section">
        <h3>Sideninformationer</h3>

        <div class="form-group">
            <label for="footer_address">Adresse</label>
            <input type="text" id="footer_address" name="footer_address" value="<?php echo safe_html($footer_address); ?>" required>
        </div>

        <div class="form-group-row">
            <div class="form-group">
                <label for="footer_cvr">CVR</label>
                <input type="text" id="footer_cvr" name="footer_cvr" value="<?php echo safe_html($footer_cvr); ?>">
            </div>

            <div class="form-group">
                <label for="footer_contact_email">Kontakt Email</label>
                <input type="email" id="footer_contact_email" name="footer_contact_email" value="<?php echo safe_html($footer_contact_email); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="footer_facebook_url">Facebook URL</label>
            <input type="url" id="footer_facebook_url" name="footer_facebook_url" placeholder="https://facebook.com/..." value="<?php echo safe_html($footer_facebook_url); ?>">
        </div>
    </div>

    <!-- Opening Hours -->
    <div class="form-section">
        <h3>Åbningstider</h3>

        <div class="hours-grid">
            <?php
            $days = ['monday' => 'Mandag', 'tuesday' => 'Tirsdag', 'wednesday' => 'Onsdag', 'thursday' => 'Torsdag', 'friday' => 'Fredag', 'saturday' => 'Lørdag', 'sunday' => 'Søndag'];
            foreach ($days as $day_key => $day_name):
            ?>
                <div class="hour-input">
                    <label><?php echo $day_name; ?></label>
                    <input type="text" name="hours[<?php echo $day_key; ?>]" placeholder="HH:MM - HH:MM" value="<?php echo safe_html($opening_hours[$day_key] ?? ''); ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php echo csrf_input(); ?>

    <button type="submit" style="width: 100%; padding: 15px; font-size: 16px;">Gem Indhold</button>
</form>

<script>
// Simple WYSIWYG placeholder (TinyMCE can be added via CDN if needed)
// For now, basic textarea editing
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
