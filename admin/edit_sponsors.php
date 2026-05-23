<?php
/**
 * Sponsor Manager
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';
require_once __DIR__ . '/includes/image-handler.php';

require_admin_login();

$page_title = 'Rediger Sponsorer';
$page_subtitle = 'Administrer sponsorlogoer og information';

$db = get_db_connection();
$success = '';
$error = '';

// Handle new sponsor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF-validering mislykkedes';
    } elseif ($_POST['action'] === 'add' && isset($_FILES['logo'])) {
        $name = sanitize_input($_POST['name'] ?? '');
        $link = sanitize_input($_POST['link'] ?? '');

        if (empty($name)) {
            $error = 'Sponsornavn er påkrævet';
        } else {
            $upload = handle_image_upload($_FILES['logo'], UPLOADS_DIR, 200, 200);

            if ($upload['success']) {
                $stmt = $db->prepare("
                    INSERT INTO sponsors (name, logo, link, sort_order)
                    VALUES (?, ?, ?, (SELECT MAX(sort_order) + 1 FROM sponsors))
                ");

                if ($stmt) {
                    $stmt->bind_param('sss', $name, $upload['url'], $link);
                    if ($stmt->execute()) {
                        log_audit(get_current_user_id(), 'added_sponsor', 'sponsors', $db->insert_id, null, ['name' => $name, 'logo' => $upload['url']]);
                        $success = 'Sponsor tilføjet succesfuldt!';
                    } else {
                        $error = 'Database-fejl: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                $error = $upload['error'];
            }
        }
    } elseif ($_POST['action'] === 'edit' && isset($_POST['sponsor_id'])) {
        $sponsor_id = (int)$_POST['sponsor_id'];
        $name = sanitize_input($_POST['name'] ?? '');
        $link = sanitize_input($_POST['link'] ?? '');

        $stmt = $db->prepare('UPDATE sponsors SET name = ?, link = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('ssi', $name, $link, $sponsor_id);
            if ($stmt->execute()) {
                log_audit(get_current_user_id(), 'updated_sponsor', 'sponsors', $sponsor_id, ['name' => 'old'], ['name' => $name]);
                $success = 'Sponsor opdateret succesfuldt!';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['sponsor_id'])) {
        $sponsor_id = (int)$_POST['sponsor_id'];

        $stmt = $db->prepare('SELECT logo FROM sponsors WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $sponsor_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                delete_image(__DIR__ . '/..' . $row['logo']);

                $stmt = $db->prepare('DELETE FROM sponsors WHERE id = ?');
                $stmt->bind_param('i', $sponsor_id);
                if ($stmt->execute()) {
                    log_audit(get_current_user_id(), 'deleted_sponsor', 'sponsors', $sponsor_id, ['logo' => $row['logo']], null);
                    $success = 'Sponsor slettet succesfuldt!';
                }
            }
            $stmt->close();
        }
    }
}

// Get sponsors
$result = $db->query('SELECT id, name, logo, link, sort_order FROM sponsors ORDER BY sort_order ASC');
$sponsors = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo safe_html($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo safe_html($error); ?></div>
<?php endif; ?>

<style>
    .sponsor-section {
        background: white;
        padding: 20px;
        border-radius: 4px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 14px;
    }

    input[type="text"],
    input[type="url"],
    input[type="file"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }

    .form-group-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    button {
        background: #3498db;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
    }

    button:hover {
        background: #2980b9;
    }

    .sponsors-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .sponsor-card {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .sponsor-logo {
        width: 100%;
        height: 100px;
        object-fit: contain;
        background: white;
        border: 1px solid #ecf0f1;
        border-radius: 3px;
    }

    .sponsor-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .sponsor-link {
        font-size: 12px;
        color: #3498db;
        word-break: break-all;
    }

    .sponsor-actions {
        display: flex;
        gap: 5px;
    }

    .btn-small {
        flex: 1;
        padding: 6px 8px;
        font-size: 12px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        color: white;
        text-align: center;
    }

    .btn-edit {
        background: #3498db;
    }

    .btn-delete {
        background: #e74c3c;
    }

    .btn-delete:hover {
        background: #c0392b;
    }
</style>

<!-- Add Sponsor Form -->
<div class="sponsor-section">
    <h3 style="margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Tilføj ny sponsor</h3>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group-row">
            <div class="form-group">
                <label for="sponsor_name">Navn</label>
                <input type="text" id="sponsor_name" name="name" required>
            </div>

            <div class="form-group">
                <label for="sponsor_link">Link</label>
                <input type="url" id="sponsor_link" name="link" placeholder="https://example.com">
            </div>
        </div>

        <div class="form-group">
            <label for="sponsor_logo">Logo (JPG, PNG)</label>
            <input type="file" id="sponsor_logo" name="logo" accept=".jpg,.jpeg,.png" required>
        </div>

        <input type="hidden" name="action" value="add">
        <?php echo csrf_input(); ?>

        <button type="submit" style="width: 100%; padding: 12px;">Tilføj sponsor</button>
    </form>
</div>

<!-- Sponsors List -->
<div class="sponsor-section">
    <h3 style="margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Sponsorer (<?php echo count($sponsors); ?>)</h3>

    <?php if (count($sponsors) > 0): ?>
        <div class="sponsors-list">
            <?php foreach ($sponsors as $sponsor): ?>
                <div class="sponsor-card">
                    <img src="<?php echo safe_html($sponsor['logo']); ?>" alt="<?php echo safe_html($sponsor['name']); ?>" class="sponsor-logo">
                    <div class="sponsor-name"><?php echo safe_html($sponsor['name']); ?></div>
                    <?php if ($sponsor['link']): ?>
                        <div class="sponsor-link"><a href="<?php echo safe_html($sponsor['link']); ?>" target="_blank"><?php echo safe_html($sponsor['link']); ?></a></div>
                    <?php endif; ?>
                    <div class="sponsor-actions">
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="sponsor_id" value="<?php echo $sponsor['id']; ?>">
                            <?php echo csrf_input(); ?>
                            <button type="submit" class="btn-small btn-delete" onclick="return confirm('Slet denne sponsor?');">Slet</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: #7f8c8d; padding: 20px;">Ingen sponsorer endnu.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
