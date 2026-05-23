<?php
/**
 * Change Password Page
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/includes/admin-functions.php';
require_admin_login();

$page_title = 'Skift Adgangskode';
$page_subtitle = 'Opdater din adgangskode';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validering mislykkedes.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($current) || empty($new) || empty($confirm)) {
            $error = 'Alle felter er påkrævet.';
        } elseif ($new !== $confirm) {
            $error = 'De nye adgangskoder stemmer ikke overens.';
        } elseif (strlen($new) < 8) {
            $error = 'Adgangskoden skal være mindst 8 tegn.';
        } else {
            $db = get_db_connection();
            $user_id = get_current_user_id();
            $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            if (!verify_password($current, $user['password'])) {
                $error = 'Nuværende adgangskode er forkert.';
            } else {
                $new_hash = hash_password($new);
                $db->query("UPDATE users SET password = '$new_hash' WHERE id = $user_id");
                log_audit($user_id, 'changed_password', 'users', $user_id);
                destroy_admin_session();
                header('Refresh: 2; url=/admin/login.php?password_changed=1');
                $success = 'Adgangskode opdateret! Du bliver logget ud...';
            }
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><?php echo safe_html($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo safe_html($success); ?></div><?php endif; ?>

<?php if (!$success): ?>
<div style="background: white; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); max-width: 500px;">
    <h2 style="margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Skift Adgangskode</h2>
    <form method="POST">
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Nuværende Adgangskode *</label>
            <input type="password" name="current_password" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Ny Adgangskode *</label>
            <input type="password" name="new_password" required minlength="8" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <small style="color: #7f8c8d;">Mindst 8 tegn</small>
        </div>
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;">Bekræft Ny Adgangskode *</label>
            <input type="password" name="confirm_password" required minlength="8" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <?php echo csrf_input(); ?>
        <button type="submit" style="background: #3498db; color: white; padding: 10px 30px; border: none; border-radius: 4px; cursor: pointer;">Opdater</button>
    </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 