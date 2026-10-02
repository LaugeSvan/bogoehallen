<?php
/**
 * Edit Main Content and Settings
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';
require_once __DIR__ . '/includes/image-handler.php';

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
            set_content('main', 'about_text', sanitize_rich_text($_POST['about_text'] ?? ''), $user_id);
        }

        if (isset($_POST['board_text'])) {
            set_content('board', 'title', $_POST['board_title'] ?? 'Bestyrelsen', $user_id);
            set_content('board', 'text', $_POST['board_text'], $user_id);
        }

        $contact_label_defaults = [
            'name' => 'Navn',
            'email' => 'Email',
            'subject' => 'Emne',
            'message' => 'Besked',
            'submit' => 'Send besked',
        ];
        foreach ($contact_label_defaults as $key => $default_label) {
            $label = trim($_POST['contact_' . $key . '_label'] ?? '');
            if ($label !== '') {
                set_content('contact_form', $key . '_label', $label, $user_id);
            }
        }

        $navigation_defaults = [
            'home' => ['Forside', '/'],
            'board' => ['Bestyrelsen', '/bestyrelsen.php'],
            'about' => ['Om os', '/om-os.php'],
            'contact' => ['Kontakt', '/kontakt.php'],
        ];
        foreach ($navigation_defaults as $key => $defaults) {
            $label = trim($_POST['nav_' . $key . '_label'] ?? $defaults[0]);
            if ($label !== '') {
                set_content('header', 'nav_' . $key . '_label', $label, $user_id);
            }
            $url = safe_navigation_url($_POST['nav_' . $key . '_url'] ?? $defaults[1], $defaults[1]);
            set_content('header', 'nav_' . $key . '_url', $url, $user_id);
        }

        // Update footer content
        if (isset($_POST['footer_address'])) {
            set_content('footer', 'address', $_POST['footer_address'], $user_id);
            set_content('footer', 'cvr', $_POST['footer_cvr'], $user_id);
            $contact_email = filter_var(trim($_POST['footer_contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
            if ($contact_email !== false) {
                set_content('footer', 'contact_email', $contact_email, $user_id);
            } else {
                $error = 'Ugyldig kontakt-email. Den tidligere emailadresse er bevaret.';
            }
            set_content('footer', 'facebook_url', safe_navigation_url($_POST['footer_facebook_url'], ''), $user_id);
            set_content('footer', 'google_maps_query', $_POST['footer_google_maps_query'] ?? '', $user_id);
            set_content('footer', 'bylaws_url', safe_navigation_url($_POST['footer_bylaws_url'] ?? '', '/vedtaegt.php'), $user_id);
            set_content('footer', 'sponsor_url', safe_navigation_url($_POST['footer_sponsor_url'] ?? '', '/bliv-sponsor.php'), $user_id);
            set_content('footer', 'contact_url', safe_navigation_url($_POST['footer_contact_url'] ?? '', '/kontakt.php'), $user_id);
        }

        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = handle_image_upload($_FILES['site_logo'], UPLOADS_DIR, 600, 200);
            if ($upload['success']) {
                $old_logo = get_content('header', 'logo', '');
                set_content('header', 'logo', $upload['url'], $user_id);
                log_audit($user_id, 'uploaded_logo', 'content', null, ['logo' => $old_logo], ['logo' => $upload['url']]);
            } else {
                $error = $upload['error'];
            }
        }

        // Update opening hours
        if (isset($_POST['hours'])) {
            $hours = [];
            foreach ($_POST['hours'] as $day => $time) {
                $hours[$day] = $time;
            }
            save_opening_hours($hours, $user_id);
        }

        if ($error === '') {
            $success = 'Indhold opdateret succesfuldt!';
        }
    }
}

// Get current content
$about_title = get_content('main', 'about_title', 'Velkommen til Bogø Hallen');
$about_text = get_content('main', 'about_text', 'Bogø Hallen er Danmarks moderne idrætscenter.');
$board_title = get_content('board', 'title', 'Bestyrelsen');
$board_text = get_content('board', 'text', 'Bestyrelsen varetager Bogø Hallens daglige drift og udvikling. Kontakt os gerne via kontaktsiden.');
$footer_address = get_content('footer', 'address', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod');
$footer_google_maps_query = get_content('footer', 'google_maps_query', $footer_address);
$footer_bylaws_url = get_content('footer', 'bylaws_url', '/vedtaegt.php');
$footer_sponsor_url = get_content('footer', 'sponsor_url', '/bliv-sponsor.php');
$footer_contact_url = get_content('footer', 'contact_url', '/kontakt.php');
$footer_cvr = get_content('footer', 'cvr', 'CVR: 12345678');
$footer_contact_email = get_content('footer', 'contact_email', 'kontakt@bogohallen.dk');
$footer_facebook_url = get_content('footer', 'facebook_url', 'https://facebook.com/bogohallen');
$site_logo = normalize_upload_url(get_content('header', 'logo', ''));
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

    .wysiwyg-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 8px;
    }

    .wysiwyg-toolbar button {
        padding: 7px 10px;
    }

    .wysiwyg-editor {
        min-height: 200px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
        font-size: 13px;
        line-height: 1.6;
    }

    .wysiwyg-editor:focus {
        outline: 2px solid #3498db;
        outline-offset: 1px;
    }
</style>

<form method="POST" enctype="multipart/form-data">
    <!-- About Section -->
    <div class="form-section">
        <h3>Om Os</h3>

        <div class="form-group">
            <label for="about_title">Titel</label>
            <input type="text" id="about_title" name="about_title" value="<?php echo safe_html($about_title); ?>" required>
        </div>

        <div class="form-group">
            <label for="about_text">Beskrivelse</label>
            <div class="wysiwyg-toolbar" id="about_text_toolbar" hidden>
                <button type="button" data-command="bold" aria-label="Fed" title="Fed"><strong>B</strong></button>
                <button type="button" data-command="italic" aria-label="Kursiv" title="Kursiv"><em>I</em></button>
                <button type="button" data-command="insertUnorderedList">Punktopstilling</button>
                <button type="button" data-command="insertOrderedList">Nummereret liste</button>
            </div>
            <div id="about_text_editor" class="wysiwyg-editor" contenteditable="true" role="textbox" aria-label="Beskrivelse" aria-multiline="true" hidden><?php echo sanitize_rich_text($about_text); ?></div>
            <textarea id="about_text" name="about_text"><?php echo safe_html($about_text); ?></textarea>
        </div>
    </div>

    <div class="form-section">
        <h3>Navigation</h3>
        <?php
        $navigation_defaults = [
            'home' => ['Forside', '/'],
            'board' => ['Bestyrelsen', '/bestyrelsen.php'],
            'about' => ['Om os', '/om-os.php'],
            'contact' => ['Kontakt', '/kontakt.php'],
        ];
        foreach ($navigation_defaults as $key => $defaults):
            $label = get_content('header', 'nav_' . $key . '_label', $defaults[0]);
            $url = get_content('header', 'nav_' . $key . '_url', $defaults[1]);
        ?>
            <div class="form-group-row">
                <div class="form-group">
                    <label for="nav_<?php echo $key; ?>_label"><?php echo safe_html($defaults[0]); ?> - tekst</label>
                    <input type="text" id="nav_<?php echo $key; ?>_label" name="nav_<?php echo $key; ?>_label" value="<?php echo safe_html($label); ?>" required>
                </div>
                <div class="form-group">
                    <label for="nav_<?php echo $key; ?>_url"><?php echo safe_html($defaults[0]); ?> - link</label>
                    <input type="text" id="nav_<?php echo $key; ?>_url" name="nav_<?php echo $key; ?>_url" value="<?php echo safe_html($url); ?>" required>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-section">
        <h3>Bestyrelsen</h3>
        <div class="form-group">
            <label for="board_title">Titel</label>
            <input type="text" id="board_title" name="board_title" value="<?php echo safe_html($board_title); ?>" required>
        </div>
        <div class="form-group">
            <label for="board_text">Tekst</label>
            <textarea id="board_text" name="board_text" required><?php echo safe_html($board_text); ?></textarea>
        </div>
    </div>

    <div class="form-section">
        <h3>Logo</h3>
        <?php if ($site_logo): ?>
            <p><img src="<?php echo safe_html($site_logo); ?>" alt="Nuværende logo" style="max-width: 240px; max-height: 100px; object-fit: contain;"></p>
        <?php endif; ?>
        <div class="form-group">
            <label for="site_logo">Upload logo (JPG, PNG, SVG)</label>
            <input type="file" id="site_logo" name="site_logo" accept=".jpg,.jpeg,.png,.svg">
        </div>
    </div>

    <div class="form-section">
        <h3>Kontaktformular</h3>
        <?php
        $contact_label_defaults = [
            'name' => 'Navn',
            'email' => 'Email',
            'subject' => 'Emne',
            'message' => 'Besked',
            'submit' => 'Send besked',
        ];
        foreach ($contact_label_defaults as $key => $default_label):
            $label = get_content('contact_form', $key . '_label', $default_label);
        ?>
            <div class="form-group">
                <label for="contact_<?php echo $key; ?>_label"><?php echo safe_html($default_label); ?></label>
                <input type="text" id="contact_<?php echo $key; ?>_label" name="contact_<?php echo $key; ?>_label" value="<?php echo safe_html($label); ?>" required>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Footer Section -->
    <div class="form-section">
        <h3>Sideninformationer</h3>

        <div class="form-group">
            <label for="footer_address">Adresse</label>
            <input type="text" id="footer_address" name="footer_address" value="<?php echo safe_html($footer_address); ?>" required>
        </div>

        <div class="form-group">
            <label for="footer_google_maps_query">Google Maps placering</label>
            <input type="text" id="footer_google_maps_query" name="footer_google_maps_query" value="<?php echo safe_html($footer_google_maps_query); ?>" placeholder="Adresse eller koordinater">
        </div>

        <div class="form-group-row">
            <div class="form-group">
                <label for="footer_contact_url">Link til kontakt</label>
                <input type="text" id="footer_contact_url" name="footer_contact_url" value="<?php echo safe_html($footer_contact_url); ?>">
            </div>
            <div class="form-group">
                <label for="footer_bylaws_url">Link til vedtægt</label>
                <input type="text" id="footer_bylaws_url" name="footer_bylaws_url" value="<?php echo safe_html($footer_bylaws_url); ?>">
            </div>
            <div class="form-group">
                <label for="footer_sponsor_url">Link til bliv sponsor</label>
                <input type="text" id="footer_sponsor_url" name="footer_sponsor_url" value="<?php echo safe_html($footer_sponsor_url); ?>">
            </div>
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
const richText = document.getElementById('about_text_editor');
const richTextInput = document.getElementById('about_text');
const richTextToolbar = document.getElementById('about_text_toolbar');
const contentForm = richTextInput.form;

richText.hidden = false;
richTextToolbar.hidden = false;
richTextInput.hidden = true;

richTextToolbar.addEventListener('click', (event) => {
    const button = event.target.closest('[data-command]');
    if (!button) return;
    richText.focus();
    document.execCommand(button.dataset.command, false);
});
richTextToolbar.addEventListener('mousedown', (event) => {
    if (event.target.closest('[data-command]')) event.preventDefault();
});

contentForm.addEventListener('submit', () => {
    richTextInput.value = richText.innerHTML;
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
