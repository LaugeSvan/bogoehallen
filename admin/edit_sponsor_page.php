<?php
/**
 * Edit sponsor page content
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';
require_once __DIR__ . '/../includes/content-defaults.php';

require_admin_login();

$page_title = 'Rediger Bliv sponsor';
$page_subtitle = 'Administrer tekst og kontaktinformation på sponsorsiden';
$success = '';
$error = '';

$defaults = [
    'title' => 'Bliv Sponsor',
    'intro' => 'Vil din virksomhed være med til at støtte Bogø Hallen og lokal sport?',
    'content' => get_sponsor_page_default_content(),
    'contact_title' => 'Kontakt os',
    'contact_intro' => 'Interesseret i at blive sponsor? Vi glæder os til at høre fra dig!',
    'contact_note' => 'Vi kontakter dig inden for 2 arbejdsdage.',
];
$sponsor_content = [];
foreach ($defaults as $key => $default) {
    $sponsor_content[$key] = get_content('sponsor_page', $key, $default);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'CSRF-validering mislykkedes';
    } else {
        $text_fields = ['title', 'intro', 'contact_title', 'contact_intro', 'contact_note'];
        foreach ($text_fields as $key) {
            if (!is_string($_POST[$key] ?? null)) {
                $error = 'Ugyldigt indhold.';
                break;
            }
        }

        if (!is_string($_POST['content'] ?? null)) {
            $error = 'Ugyldigt indhold.';
        }

        if ($error === '' && trim($_POST['title']) === '') {
            $error = 'Titlen må ikke være tom.';
        }

        if ($error === '') {
            $user_id = get_current_user_id();
            foreach ($text_fields as $key) {
                $sponsor_content[$key] = trim($_POST[$key]);
                if (!set_content('sponsor_page', $key, $sponsor_content[$key], $user_id)) {
                    $error = 'En eller flere værdier kunne ikke gemmes. Kontrollér databaseforbindelsen; enkelte ændringer kan allerede være gemt.';
                    break;
                }
            }

            if ($error === '') {
                $sponsor_content['content'] = sanitize_rich_text($_POST['content']);
                if (set_content('sponsor_page', 'content', $sponsor_content['content'], $user_id)) {
                    $success = 'Sponsorsiden blev opdateret.';
                } else {
                    $error = 'En eller flere værdier kunne ikke gemmes. Kontrollér databaseforbindelsen; enkelte ændringer kan allerede være gemt.';
                }
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
            <label for="title">Titel</label>
            <input type="text" id="title" name="title" value="<?php echo safe_html($sponsor_content['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="intro">Introduktion</label>
            <textarea id="intro" name="intro" rows="3"><?php echo safe_html($sponsor_content['intro']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="content">Fordele og sponsorniveauer</label>
            <div class="wysiwyg-toolbar" id="content_toolbar" hidden>
                <button type="button" data-command="bold" aria-label="Fed" title="Fed"><strong>B</strong></button>
                <button type="button" data-command="italic" aria-label="Kursiv" title="Kursiv"><em>I</em></button>
                <button type="button" data-command="formatBlock" data-value="h3">Overskrift</button>
                <button type="button" data-command="insertUnorderedList">Punktopstilling</button>
                <button type="button" data-command="insertOrderedList">Nummereret liste</button>
            </div>
            <div id="content_editor" class="wysiwyg-editor" contenteditable="true" role="textbox" aria-label="Fordele og sponsorniveauer" aria-multiline="true" hidden><?php echo sanitize_rich_text($sponsor_content['content']); ?></div>
            <textarea id="content" name="content"><?php echo safe_html($sponsor_content['content']); ?></textarea>
        </div>
    </div>

    <div class="form-section">
        <h2>Kontaktfelt</h2>
        <div class="form-group">
            <label for="contact_title">Overskrift</label>
            <input type="text" id="contact_title" name="contact_title" value="<?php echo safe_html($sponsor_content['contact_title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="contact_intro">Introduktion</label>
            <textarea id="contact_intro" name="contact_intro" rows="3"><?php echo safe_html($sponsor_content['contact_intro']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="contact_note">Afsluttende besked</label>
            <input type="text" id="contact_note" name="contact_note" value="<?php echo safe_html($sponsor_content['contact_note']); ?>">
        </div>
        <p>Email, adresse og CVR hentes fra indstillingerne under <a href="/admin/edit_content.php">Kontakt og sideinformationer</a>.</p>
    </div>

    <?php echo csrf_input(); ?>
    <button type="submit" class="save-button">Gem sponsorside</button>
</form>

<script>
const editor = document.getElementById('content_editor');
const input = document.getElementById('content');
const toolbar = document.getElementById('content_toolbar');

editor.hidden = false;
toolbar.hidden = false;
input.hidden = true;

toolbar.addEventListener('click', (event) => {
    const button = event.target.closest('[data-command]');
    if (!button) return;
    editor.focus();
    document.execCommand(button.dataset.command, false, button.dataset.value || null);
});
toolbar.addEventListener('mousedown', (event) => {
    if (event.target.closest('[data-command]')) event.preventDefault();
});
input.form.addEventListener('submit', () => {
    input.value = editor.innerHTML;
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
