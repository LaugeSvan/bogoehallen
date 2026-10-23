<?php
/**
 * Edit bylaws page content
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';
require_once __DIR__ . '/../includes/content-defaults.php';

require_admin_login();

$page_title = 'Rediger vedtægter';
$page_subtitle = 'Rediger titel og indhold på vedtægtssiden';
$success = '';
$error = '';

$bylaws_title = get_content('bylaws', 'title', 'Vedtægter');
$bylaws_content = get_content('bylaws', 'content', get_bylaws_default_content());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'CSRF-validering mislykkedes';
    } elseif (!is_string($_POST['bylaws_title'] ?? null) || !is_string($_POST['bylaws_content'] ?? null)) {
        $error = 'Ugyldigt indhold.';
    } else {
        $user_id = get_current_user_id();
        $bylaws_title = trim($_POST['bylaws_title']);
        $bylaws_content = sanitize_rich_text($_POST['bylaws_content']);
        set_content('bylaws', 'title', $bylaws_title, $user_id);
        set_content('bylaws', 'content', $bylaws_content, $user_id);
        $success = 'Vedtægterne blev opdateret.';
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

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 14px;
        color: #a8b7c8;
    }

    input[type="text"],
    textarea {
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
        min-height: 400px;
        resize: vertical;
    }

    .wysiwyg-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 8px;
    }

    .wysiwyg-toolbar button,
    .save-button {
        background: linear-gradient(135deg, #3C85BA 0%, #002748 100%);
        color: white;
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }

    .wysiwyg-editor {
        min-height: 400px;
        padding: 10px;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        background: rgba(5,15,27,0.45);
        color: #edf3fa;
        font-size: 13px;
        line-height: 1.6;
    }
</style>

<form method="POST">
    <div class="form-section">
        <div class="form-group">
            <label for="bylaws_title">Titel</label>
            <input type="text" id="bylaws_title" name="bylaws_title" value="<?php echo safe_html($bylaws_title); ?>" required>
        </div>
        <div class="form-group">
            <label for="bylaws_content">Vedtægter</label>
            <div class="wysiwyg-toolbar" id="bylaws_content_toolbar" hidden>
                <button type="button" data-command="bold" aria-label="Fed" title="Fed"><strong>B</strong></button>
                <button type="button" data-command="italic" aria-label="Kursiv" title="Kursiv"><em>I</em></button>
                <button type="button" data-command="insertUnorderedList">Punktopstilling</button>
                <button type="button" data-command="insertOrderedList">Nummereret liste</button>
            </div>
            <div id="bylaws_content_editor" class="wysiwyg-editor" contenteditable="true" role="textbox" aria-label="Vedtægtsindhold" aria-multiline="true" hidden><?php echo sanitize_rich_text($bylaws_content); ?></div>
            <textarea id="bylaws_content" name="bylaws_content"><?php echo safe_html($bylaws_content); ?></textarea>
        </div>
        <?php echo csrf_input(); ?>
        <button type="submit" class="save-button">Gem vedtægter</button>
    </div>
</form>

<script>
const editor = document.getElementById('bylaws_content_editor');
const input = document.getElementById('bylaws_content');
const toolbar = document.getElementById('bylaws_content_toolbar');

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
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
