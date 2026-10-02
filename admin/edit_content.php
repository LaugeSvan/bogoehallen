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

        // Update footer content
        if (isset($_POST['footer_address'])) {
            set_content('footer', 'address', $_POST['footer_address'], $user_id);
            set_content('footer', 'cvr', $_POST['footer_cvr'], $user_id);
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
$footer_cvr = get_content('footer', 'cvr', 'CVR: 12345678');
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
        background: rgba(17, 49, 77, 0.9);
        border: 1px solid rgba(255,255,255,0.08);
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .form-section h3 {
        font-size: 16px;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        padding-bottom: 10px;
        color: #edf3fa;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 14px;
        color: #a8b7c8;
    }

    input[type="text"],
    input[type="email"],
    input[type="url"],
    textarea,
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        background: rgba(5,15,27,0.45);
        color: #edf3fa;
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
        background: linear-gradient(135deg, #3C85BA 0%, #002748 100%);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    button:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 20px rgba(60,133,186,0.2);
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
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        background: rgba(5,15,27,0.45);
        color: #edf3fa;
        font-size: 13px;
        line-height: 1.6;
    }

    .wysiwyg-editor:focus {
        outline: 2px solid #3C85BA;
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

    <!-- Footer Section -->
    <div class="form-section">
        <h3>Sideinformationer</h3>

        <div class="form-group">
            <label for="footer_address">Adresse</label>
            <input type="text" id="footer_address" name="footer_address" value="<?php echo safe_html($footer_address); ?>" required>
        </div>

        <div class="form-group">
            <label for="footer_cvr">CVR</label>
            <input type="text" id="footer_cvr" name="footer_cvr" value="<?php echo safe_html($footer_cvr); ?>">
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
