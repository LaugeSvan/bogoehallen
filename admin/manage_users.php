<?php
/**
 * User Management Page
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/includes/admin-functions.php';
require_admin_login();
require_permission('super_admin');

$page_title = 'Bruger Administration';
$page_subtitle = 'Administrer systembrugere';
$error = $success = '';
$db = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validering mislykkedes.';
    } else {
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'editor';
        
        if (empty($username) || empty($password)) {
            $error = 'Brugernavn og adgangskode er påkrævet.';
        } elseif (strlen($password) < 8) {
            $error = 'Adgangskoden skal være mindst 8 tegn.';
        } else {
            $check = $db->query("SELECT id FROM users WHERE username = '$username'");
            if ($check->num_rows > 0) {
                $error = 'Brugernavn findes allerede.';
            } else {
                $hash = hash_password($password);
                $db->query("INSERT INTO users (username, password, role) VALUES ('$username', '$hash', '$role')");
                log_audit(get_current_user_id(), 'created_user', 'users', $db->insert_id);
                $success = 'Bruger oprettet!';
            }
        }
    }
}

if (isset($_GET['delete']) && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $current_id = get_current_user_id();
    if ($user_id === $current_id) {
        $error = 'Du kan ikke slette din egen bruger.';
    } else {
        $db->query("DELETE FROM users WHERE id = $user_id");
        log_audit($current_id, 'deleted_user', 'users', $user_id);
        $success = 'Bruger slettet!';
    }
}

$users = $db->query('SELECT id, username, role, created_at FROM users ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><?php echo safe_html($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo safe_html($success); ?></div><?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
    <div style="background: white; padding: 25px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom: 20px; font-size: 18px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Opret Ny Bruger</h2>
        <form method="POST">
            <input type="hidden" name="create_user" value="1">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Brugernavn *</label>
                <input type="text" name="username" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Adgangskode *</label>
                <input type="password" name="password" required minlength="8" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="color: #7f8c8d;">Mindst 8 tegn</small>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Rolle *</label>
                <select name="role" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="editor">Redaktør</option>
                    <option value="super_admin">Super Administrator</option>
                </select>
            </div>
            <?php echo csrf_input(); ?>
            <button type="submit" style="background: #2ecc71; color: white; padding: 10px 30px; border: none; border-radius: 4px; cursor: pointer;">Opret Bruger</button>
        </form>
    </div>
    <div style="background: white; padding: 25px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom: 20px; font-size: 18px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Eksisterende Brugere</h2>
        <?php if (count($users) > 0): ?>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f9f9f9; border-bottom: 2px solid #ecf0f1;">
                        <th style="padding: 10px; text-align: left;">Brugernavn</th>
                        <th style="padding: 10px; text-align: left;">Rolle</th>
                        <th style="padding: 10px; text-align: left;">Oprettet</th>
                        <th style="padding: 10px; text-align: right;">Handlinger</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid #ecf0f1;">
                            <td style="padding: 10px;"><?php echo safe_html($user['username']); ?></td>
                            <td style="padding: 10px;">
                                <span style="background: <?php echo $user['role'] === 'super_admin' ? '#3498db' : '#95a5a6'; ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">
                                    <?php echo $user['role'] === 'super_admin' ? 'Super Admin' : 'Redaktør'; ?>
                                </span>
                            </td>
                            <td style="padding: 10px; color: #7f8c8d;"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td style="padding: 10px; text-align: right;">
                                <?php if ($user['id'] != get_current_user_id()): ?>
                                    <a href="?delete=1&user_id=<?php echo $user['id']; ?>" 
                                       onclick="return confirm('Er du sikker?');"
                                       style="color: #e74c3c; text-decoration: none; font-size: 12px;">Slet</a>
                                <?php else: ?>
                                    <span style="color: #ccc; font-size: 12px;">(Dig selv)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #7f8c8d; text-align: center; padding: 20px;">Ingen brugere fundet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>