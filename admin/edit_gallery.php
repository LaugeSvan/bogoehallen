<?php
/**
 * Gallery Manager
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';
require_once __DIR__ . '/includes/image-handler.php';

require_admin_login();

$page_title = 'Rediger Galleri';
$page_subtitle = 'Administrer billeder i galleriet';

$db = get_db_connection();
$success = '';
$error = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF-validering mislykkedes';
    } else {
        $caption = sanitize_input($_POST['caption'] ?? '');

        $upload = handle_image_upload($_FILES['image'], UPLOADS_DIR, 800, 600);

        if ($upload['success']) {
            $stmt = $db->prepare("
                INSERT INTO gallery_images (image_path, caption, sort_order)
                VALUES (?, ?, (SELECT MAX(sort_order) + 1 FROM gallery_images))
            ");

            if ($stmt) {
                $stmt->bind_param('ss', $upload['url'], $caption);
                if ($stmt->execute()) {
                    log_audit(get_current_user_id(), 'uploaded_image', 'gallery_images', $db->insert_id, null, ['image' => $upload['url'], 'caption' => $caption]);
                    $success = 'Billede uploadet succesfuldt!';
                } else {
                    $error = 'Database-fejl: ' . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $error = $upload['error'];
        }
    }
}

// Handle image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_image'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF-validering mislykkedes';
    } else {
        $id = (int)($_POST['image_id'] ?? 0);
        $caption = sanitize_input($_POST['caption'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $old_stmt = $db->prepare('SELECT caption, sort_order FROM gallery_images WHERE id = ?');
        if ($old_stmt) {
            $old_stmt->bind_param('i', $id);
            $old_stmt->execute();
            $old_result = $old_stmt->get_result();
            $old_image = $old_result->fetch_assoc();
            $old_stmt->close();

            $update_stmt = $db->prepare('UPDATE gallery_images SET caption = ?, sort_order = ? WHERE id = ?');
            if ($old_image && $update_stmt) {
                $update_stmt->bind_param('sii', $caption, $sort_order, $id);
                if ($update_stmt->execute()) {
                    log_audit(get_current_user_id(), 'updated_image', 'gallery_images', $id, $old_image, ['caption' => $caption, 'sort_order' => $sort_order]);
                    $success = 'Billedet blev opdateret.';
                } else {
                    $error = 'Billedet kunne ikke opdateres.';
                }
                $update_stmt->close();
            } else {
                $error = 'Billedet blev ikke fundet.';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF-validering mislykkedes';
    } else {
        $id = (int)($_POST['image_id'] ?? 0);
        $select_stmt = $db->prepare('SELECT image_path FROM gallery_images WHERE id = ?');

        if ($select_stmt) {
            $select_stmt->bind_param('i', $id);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            $row = $result->fetch_assoc();
            $select_stmt->close();

            if ($row) {
                $delete_stmt = $db->prepare('DELETE FROM gallery_images WHERE id = ?');
                if ($delete_stmt) {
                    $delete_stmt->bind_param('i', $id);
                    if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
                        $image_url = normalize_upload_url($row['image_path']);
                        if (strpos($image_url, UPLOADS_PUBLIC_PATH . '/') === 0) {
                            $filename = basename(parse_url($image_url, PHP_URL_PATH));
                            delete_image(UPLOADS_DIR . '/' . $filename);
                        }
                        log_audit(get_current_user_id(), 'deleted_image', 'gallery_images', $id, ['image' => $row['image_path']], null);
                        header('Location: /admin/edit_gallery.php?success=deleted');
                        exit;
                    }
                    $delete_stmt->close();
                }
            }

            $error = 'Billedet kunne ikke slettes.';
        }
    }
}

// Get gallery images
$result = $db->query("SELECT id, image_path, caption, sort_order FROM gallery_images ORDER BY sort_order ASC");
$images = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo safe_html($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo safe_html($error); ?></div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
    <div class="alert alert-success">Billede slettet succesfuldt!</div>
<?php endif; ?>

<style>
    .upload-section {
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
    input[type="file"],
    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }

    .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
        width: 100%;
    }

    .file-input-label {
        display: block;
        padding: 10px;
        background: #3498db;
        color: white;
        cursor: pointer;
        border-radius: 4px;
        text-align: center;
        font-weight: 600;
    }

    input[type="file"] {
        position: absolute;
        left: -9999px;
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

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .gallery-item {
        background: white;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .gallery-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }

    .gallery-item-info {
        padding: 10px;
    }

    .gallery-item-caption {
        font-size: 13px;
        color: #333;
        margin-bottom: 8px;
        word-break: break-word;
    }

    .gallery-edit-form input {
        width: 100%;
        padding: 7px;
        margin-bottom: 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }

    .gallery-item-actions {
        display: flex;
        gap: 5px;
    }

    .delete-btn {
        flex: 1;
        background: #e74c3c;
        color: white;
        padding: 5px;
        text-align: center;
        border-radius: 3px;
        text-decoration: none;
        font-size: 12px;
        cursor: pointer;
        border: none;
    }

    .delete-btn:hover {
        background: #c0392b;
    }
</style>

<!-- Upload Section -->
<div class="upload-section">
    <h3 style="margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Upload billede</h3>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="image">Vælg billede (JPG, PNG, SVG)</label>
            <div class="file-input-wrapper">
                <label for="image" class="file-input-label">Vælg fil...</label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.svg" required>
            </div>
        </div>

        <div class="form-group">
            <label for="caption">Billedtekst</label>
            <input type="text" id="caption" name="caption" placeholder="F.eks. 'Støttemedlem'">
        </div>

        <?php echo csrf_input(); ?>

        <button type="submit" style="width: 100%; padding: 12px;">Upload billede</button>
    </form>
</div>

<!-- Gallery Section -->
<div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
    <h3 style="margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Galleri (<?php echo count($images); ?> billeder)</h3>

    <?php if (count($images) > 0): ?>
        <div class="gallery-grid">
            <?php foreach ($images as $image): ?>
                <?php $image_url = normalize_upload_url($image['image_path'] ?? ''); ?>
                <div class="gallery-item">
                    <img src="<?php echo safe_html($image_url); ?>" alt="<?php echo safe_html($image['caption']); ?>">
                    <div class="gallery-item-info">
                        <form method="POST" class="gallery-edit-form">
                            <input type="hidden" name="update_image" value="1">
                            <input type="hidden" name="image_id" value="<?php echo (int)$image['id']; ?>">
                            <label for="caption_<?php echo (int)$image['id']; ?>">Billedtekst</label>
                            <input type="text" id="caption_<?php echo (int)$image['id']; ?>" name="caption" value="<?php echo safe_html($image['caption']); ?>" maxlength="100">
                            <label for="sort_<?php echo (int)$image['id']; ?>">Rækkefølge</label>
                            <input type="number" id="sort_<?php echo (int)$image['id']; ?>" name="sort_order" value="<?php echo (int)$image['sort_order']; ?>">
                            <?php echo csrf_input(); ?>
                            <button type="submit">Gem</button>
                        </form>
                        <div class="gallery-item-actions">
                            <form method="POST" onsubmit="return confirm('Slet dette billede?');">
                                <input type="hidden" name="delete_image" value="1">
                                <input type="hidden" name="image_id" value="<?php echo (int)$image['id']; ?>">
                                <?php echo csrf_input(); ?>
                                <button type="submit" class="delete-btn">Slet</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: #7f8c8d; padding: 20px;">Ingen billeder i galleriet endnu.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
