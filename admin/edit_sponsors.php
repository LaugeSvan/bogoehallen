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
        $link = safe_navigation_url($_POST['link'] ?? '', '');

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
        $link = safe_navigation_url($_POST['link'] ?? '', '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $old_stmt = $db->prepare('SELECT name, logo, link, sort_order FROM sponsors WHERE id = ?');
        if ($old_stmt) {
            $old_stmt->bind_param('i', $sponsor_id);
            $old_stmt->execute();
            $old_result = $old_stmt->get_result();
            $old_sponsor = $old_result->fetch_assoc();
            $old_stmt->close();

            $logo = $old_sponsor['logo'] ?? '';
            $new_upload = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $new_upload = handle_image_upload($_FILES['logo'], UPLOADS_DIR, 200, 200);
                if (!$new_upload['success']) {
                    $error = $new_upload['error'];
                } else {
                    $logo = $new_upload['url'];
                }
            }

            if ($old_sponsor && $name !== '' && $error === '') {
                $stmt = $db->prepare('UPDATE sponsors SET name = ?, logo = ?, link = ?, sort_order = ? WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('sssii', $name, $logo, $link, $sort_order, $sponsor_id);
                    if ($stmt->execute()) {
                        log_audit(get_current_user_id(), 'updated_sponsor', 'sponsors', $sponsor_id, $old_sponsor, ['name' => $name, 'logo' => $logo, 'link' => $link, 'sort_order' => $sort_order]);
                        $success = 'Sponsor opdateret succesfuldt!';
                        if ($new_upload && strpos($old_sponsor['logo'], UPLOADS_PUBLIC_PATH . '/') === 0) {
                            delete_image(UPLOADS_DIR . '/' . basename(parse_url($old_sponsor['logo'], PHP_URL_PATH)));
                        }
                    } else {
                        $error = 'Sponsor kunne ikke opdateres.';
                    }
                    $stmt->close();
                }
            } elseif ($old_sponsor && $name === '') {
                $error = 'Sponsornavn er påkrævet.';
            } elseif (!$old_sponsor) {
                $error = 'Sponsoren blev ikke fundet.';
            }
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
                if (strpos($row['logo'], UPLOADS_PUBLIC_PATH . '/') === 0) {
                    delete_image(UPLOADS_DIR . '/' . basename(parse_url($row['logo'], PHP_URL_PATH)));
                }

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

    label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 14px;
        color: #a8b7c8;
    }

    input[type="text"],
    input[type="url"],
    input[type="file"] {
        width: 100%;
        padding: 10px;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        font-size: 13px;
        background: rgba(5,15,27,0.45);
        color: #edf3fa;
    }

    .form-group-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    button {
        background: linear-gradient(135deg, #3C85BA 0%, #002748 100%);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
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
        background: rgba(12, 28, 43, 0.9);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 12px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .sponsor-logo {
        width: 100%;
        height: 100px;
        object-fit: contain;
        background: rgba(5,15,27,0.45);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
    }

    .sponsor-name {
        font-weight: 600;
        color: #edf3fa;
    }

    .sponsor-link {
        font-size: 12px;
        color: #7bb5df;
        word-break: break-all;
    }

    .sponsor-edit-form input {
        width: 100%;
        padding: 7px;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 6px;
        background: rgba(5,15,27,0.45);
        color: #edf3fa;
    }

    .sponsor-edit-form label {
        margin-top: 8px;
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
        border-radius: 6px;
        cursor: pointer;
        color: white;
        text-align: center;
    }

    .btn-edit {
        background: #3C85BA;
    }

    .btn-delete {
        background: rgba(216, 91, 91, 0.9);
    }

    .btn-delete:hover {
        background: rgba(200, 70, 70, 1);
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
                <label for="sponsor_logo">Logo (JPG, PNG, SVG)</label>
                <input type="file" id="sponsor_logo" name="logo" accept=".jpg,.jpeg,.png,.svg" required>
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
                    <form method="POST" enctype="multipart/form-data" class="sponsor-edit-form">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="sponsor_id" value="<?php echo (int)$sponsor['id']; ?>">
                        <label for="sponsor_name_<?php echo (int)$sponsor['id']; ?>">Navn</label>
                        <input type="text" id="sponsor_name_<?php echo (int)$sponsor['id']; ?>" name="name" value="<?php echo safe_html($sponsor['name']); ?>" required>
                        <label for="sponsor_link_<?php echo (int)$sponsor['id']; ?>">Link</label>
                        <input type="url" id="sponsor_link_<?php echo (int)$sponsor['id']; ?>" name="link" value="<?php echo safe_html($sponsor['link']); ?>">
                        <label for="sponsor_order_<?php echo (int)$sponsor['id']; ?>">Rækkefølge</label>
                        <input type="number" id="sponsor_order_<?php echo (int)$sponsor['id']; ?>" name="sort_order" value="<?php echo (int)$sponsor['sort_order']; ?>">
                        <label for="sponsor_logo_<?php echo (int)$sponsor['id']; ?>">Skift logo</label>
                        <input type="file" id="sponsor_logo_<?php echo (int)$sponsor['id']; ?>" name="logo" accept=".jpg,.jpeg,.png,.svg">
                        <?php echo csrf_input(); ?>
                        <button type="submit" class="btn-small btn-edit">Gem ændringer</button>
                    </form>
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
