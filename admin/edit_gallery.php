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
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $db->prepare('SELECT image_path FROM gallery_images WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $filepath = __DIR__ . '/..' . $row['image_path'];

            delete_image($filepath);

            $stmt = $db->prepare('DELETE FROM gallery_images WHERE id = ?');
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                log_audit(get_current_user_id(), 'deleted_image', 'gallery_images', $id, ['image' => $row['image_path']], null);
                header('Location: /admin/edit_gallery.php?success=deleted');
                exit;
            }
        }
        $stmt->close();
    }
}

// Get gallery images
$result = $db->query("SELECT id, image_path, caption FROM gallery_images ORDER BY sort_order ASC");
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
            <label for="image">Vælg billede (JPG, PNG)</label>
            <div class="file-input-wrapper">
                <label for="image" class="file-input-label">Vælg fil...</label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png" required>
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
                <div class="gallery-item">
                    <img src="<?php echo safe_html($image['image_path']); ?>" alt="<?php echo safe_html($image['caption']); ?>">
                    <div class="gallery-item-info">
                        <div class="gallery-item-caption"><?php echo safe_html($image['caption'] ?: 'Uden tekst'); ?></div>
                        <div class="gallery-item-actions">
                            <a href="/admin/edit_gallery.php?delete=<?php echo $image['id']; ?>" class="delete-btn" onclick="return confirm('Slet dette billede?');">Slet</a>
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
