<?php
/**
 * Edit Main Content and Settings
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';
require_once __DIR__ . '/includes/image-handler.php';
require_once __DIR__ . '/../includes/content-defaults.php';

require_admin_login();

$page_title = 'Rediger Indhold';
$page_subtitle = 'Administrer tekstblokke, åbningstider og sidearrangementer';

$db = get_db_connection();
$success = '';
$error = '';

$content_setting_sections = [
    'Contact and footer' => [
        ['footer', 'address', 'Adresse', 'text', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod'],
        ['footer', 'contact_email', 'Kontakt-email', 'email', 'kontakt@bogoehallen.dk'],
        ['footer', 'cvr', 'CVR', 'text', 'CVR: 12345678'],
        ['footer', 'facebook_url', 'Facebook-link', 'url', ''],
        ['footer', 'google_maps_query', 'Google Maps placering/søgning', 'text', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod'],
    ],
];
$content_settings = [];
foreach ($content_setting_sections as $fields) {
    foreach ($fields as [$section, $key, $label, $type, $default]) {
        $content_settings[$section][$key] = get_content($section, $key, $default);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'CSRF-validering mislykkedes';
    } else {
        if (isset($_POST['content_settings']) && !is_array($_POST['content_settings'])) {
            $error = 'Ugyldige indstillinger.';
        } elseif (isset($_POST['content_settings'])) {
            foreach ($content_setting_sections as $fields) {
                foreach ($fields as [$section, $key, $label, $type]) {
                    $section_values = $_POST['content_settings'][$section] ?? [];
                    if (!is_array($section_values)) {
                        $error = 'Ugyldig værdi for feltet "' . $label . '".';
                        break 2;
                    }
                    $value = $section_values[$key] ?? '';
                    if (!is_string($value)) {
                        $error = 'Ugyldig værdi for feltet "' . $label . '".';
                        break 2;
                    }
                    if (
                        $type === 'url'
                        && $value !== ''
                        && (
                            safe_navigation_url($value, '') === ''
                            || !preg_match('#^https?://#i', $value)
                        )
                    ) {
                        $error = 'Indtast en gyldig Facebook-linkadresse, der starter med http:// eller https://.';
                        break 2;
                    }
                    if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $error = 'Ugyldig email-adresse.';
                        break 2;
                    }
                }
            }
        }

        if ($error === '') {
            $user_id = get_current_user_id();

            foreach ($content_setting_sections as $fields) {
                foreach ($fields as [$section, $key]) {
                    if (
                        isset($_POST['content_settings'][$section])
                        && is_array($_POST['content_settings'][$section])
                        && array_key_exists($key, $_POST['content_settings'][$section])
                    ) {
                        set_content($section, $key, trim((string) $_POST['content_settings'][$section][$key]), $user_id);
                    }
                }
            }

            // Update about section
            if (isset($_POST['about_title'])) {
                set_content('main', 'about_title', $_POST['about_title'], $user_id);
                set_content('main', 'about_text', sanitize_rich_text($_POST['about_text'] ?? ''), $user_id);
            }

            if (isset($_POST['about_page_content'])) {
                set_content('about_page', 'content', sanitize_rich_text($_POST['about_page_content']), $user_id);
            }

            if (isset($_POST['home_notice_title'])) {
                set_content('home_notice', 'title', trim($_POST['home_notice_title']), $user_id);
                set_content('home_notice', 'content', sanitize_rich_text($_POST['home_notice_content'] ?? ''), $user_id);
            }

            if (isset($_POST['board_text'])) {
                set_content('board', 'title', $_POST['board_title'] ?? 'Bestyrelsen', $user_id);
                set_content('board', 'text', $_POST['board_text'], $user_id);
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
        }

        if ($error === '') {
            $success = 'Indhold opdateret succesfuldt!';
        }
    }
}

// Get current content
$about_title = get_content('main', 'about_title', 'Velkommen til Bogø Hallen');
$about_text = get_content('main', 'about_text', 'Bogø Hallen er Danmarks moderne idrætscenter.');
$about_page_content = get_content('about_page', 'content', get_about_page_default_content());
$home_notice_title = get_content('home_notice', 'title', 'Praktisk information');
$home_notice_content = get_content('home_notice', 'content', '');
$board_title = get_content('board', 'title', 'Bestyrelsen');
$board_text = get_content('board', 'text', 'Bestyrelsen varetager Bogø Hallens daglige drift og udvikling. Kontakt os gerne via kontaktsiden.');
$site_logo = normalize_upload_url(get_content('header', 'logo', ''));
$opening_hours = get_opening_hours();
if ($error !== '' && isset($_POST['content_settings']) && is_array($_POST['content_settings'])) {
    foreach ($content_setting_sections as $fields) {
        foreach ($fields as [$section, $key]) {
            $value = $_POST['content_settings'][$section][$key] ?? null;
            if (is_string($value)) {
                $content_settings[$section][$key] = $value;
            }
        }
    }
}

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
        <h3>Om os-sidens indhold</h3>
        <div class="form-group">
            <label for="about_page_content">Tekst, liste og fakta</label>
            <div class="wysiwyg-toolbar" id="about_page_content_toolbar" hidden>
                <button type="button" data-command="bold" aria-label="Fed" title="Fed"><strong>B</strong></button>
                <button type="button" data-command="italic" aria-label="Kursiv" title="Kursiv"><em>I</em></button>
                <button type="button" data-command="insertUnorderedList">Punktopstilling</button>
                <button type="button" data-command="insertOrderedList">Nummereret liste</button>
            </div>
            <div id="about_page_content_editor" class="wysiwyg-editor" contenteditable="true" role="textbox" aria-label="Om os-sidens indhold" aria-multiline="true" hidden><?php echo sanitize_rich_text($about_page_content); ?></div>
            <textarea id="about_page_content" name="about_page_content"><?php echo safe_html($about_page_content); ?></textarea>
        </div>
    </div>

    <div class="form-section">
        <h3>Besked på forsiden</h3>
        <div class="form-group">
            <label for="home_notice_title">Overskrift</label>
            <input type="text" id="home_notice_title" name="home_notice_title" value="<?php echo safe_html($home_notice_title); ?>">
        </div>
        <div class="form-group">
            <label for="home_notice_content">Besked (lad feltet være tomt for at skjule boksen)</label>
            <div class="wysiwyg-toolbar" id="home_notice_content_toolbar" hidden>
                <button type="button" data-command="bold" aria-label="Fed" title="Fed"><strong>B</strong></button>
                <button type="button" data-command="italic" aria-label="Kursiv" title="Kursiv"><em>I</em></button>
                <button type="button" data-command="insertUnorderedList">Punktopstilling</button>
                <button type="button" data-command="insertOrderedList">Nummereret liste</button>
            </div>
            <div id="home_notice_content_editor" class="wysiwyg-editor" contenteditable="true" role="textbox" aria-label="Besked på forsiden" aria-multiline="true" hidden><?php echo sanitize_rich_text($home_notice_content); ?></div>
            <textarea id="home_notice_content" name="home_notice_content"><?php echo safe_html($home_notice_content); ?></textarea>
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

    <?php
    $section_titles = [
        'Contact and footer' => 'Kontakt og sideinformationer',
    ];
    foreach ($content_setting_sections as $section_title => $fields):
    ?>
        <div class="form-section">
            <h3><?php echo safe_html($section_titles[$section_title]); ?></h3>
            <?php foreach ($fields as [$section, $key, $label, $type]): ?>
                <?php $input_id = 'setting_' . $section . '_' . $key; ?>
                <div class="form-group">
                    <label for="<?php echo safe_html($input_id); ?>"><?php echo safe_html($label); ?></label>
                    <input
                        type="<?php echo in_array($type, ['email', 'url'], true) ? $type : 'text'; ?>"
                        id="<?php echo safe_html($input_id); ?>"
                        name="content_settings[<?php echo safe_html($section); ?>][<?php echo safe_html($key); ?>]"
                        value="<?php echo safe_html($content_settings[$section][$key]); ?>">
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

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
['about_text', 'about_page_content', 'home_notice_content'].forEach((inputId) => {
    const editor = document.getElementById(`${inputId}_editor`);
    const input = document.getElementById(inputId);
    const toolbar = document.getElementById(`${inputId}_toolbar`);

    editor.hidden = false;
    toolbar.hidden = false;
    input.hidden = true;

    toolbar.addEventListener('click', (event) => {
        const button = event.target.closest('[data-command]');
        if (!button) return;
        editor.focus();
        document.execCommand(button.dataset.command, false);
    });
    toolbar.addEventListener('mousedown', (event) => {
        if (event.target.closest('[data-command]')) event.preventDefault();
    });

    input.form.addEventListener('submit', () => {
        input.value = editor.innerHTML;
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
