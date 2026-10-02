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
            $role = in_array($role, ['editor', 'super_admin'], true) ? $role : 'editor';

            $check = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            if (!$check) {
                $error = 'Database-fejl ved brugerkontrol.';
            } else {
                $check->bind_param('s', $username);
                $check->execute();
                $existing = $check->get_result();

                if ($existing->num_rows > 0) {
                    $error = 'Brugernavn findes allerede.';
                } else {
                    $hash = hash_password($password);
                    $insert = $db->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                    if (!$insert) {
                        $error = 'Database-fejl ved oprettelse af bruger.';
                    } else {
                        $insert->bind_param('sss', $username, $hash, $role);
                        $insert->execute();
                        log_audit(get_current_user_id(), 'created_user', 'users', $db->insert_id);
                        $success = 'Bruger oprettet!';
                        $insert->close();
                    }
                }

                $check->close();
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validering mislykkedes.';
    } else {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $current_id = get_current_user_id();

        if ($user_id === $current_id) {
            $error = 'Du kan ikke slette din egen bruger.';
        } else {
            $delete_stmt = $db->prepare('DELETE FROM users WHERE id = ?');
            if ($delete_stmt) {
                $delete_stmt->bind_param('i', $user_id);
                if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
                    log_audit($current_id, 'deleted_user', 'users', $user_id);
                    header('Location: /admin/manage_users.php?success=deleted');
                    exit;
                }
                $delete_stmt->close();
            }

            $error = 'Brugeren kunne ikke slettes.';
        }
    }
}

if (($_GET['success'] ?? '') === 'deleted') {
    $success = 'Bruger slettet!';
}

$users = $db->query('SELECT id, username, role, created_at FROM users ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><?php echo safe_html($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo safe_html($success); ?></div><?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
    <div style="background: rgba(17, 49, 77, 0.9); border: 1px solid rgba(255,255,255,0.08); padding: 25px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h2 style="margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px; color: #edf3fa;">Opret Ny Bruger</h2>
        <form method="POST">
            <input type="hidden" name="create_user" value="1">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #a8b7c8;">Brugernavn *</label>
                <input type="text" name="username" required style="width: 100%; padding: 10px; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; background: rgba(5,15,27,0.45); color: #edf3fa;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #a8b7c8;">Adgangskode *</label>
                <input type="password" name="password" required minlength="8" style="width: 100%; padding: 10px; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; background: rgba(5,15,27,0.45); color: #edf3fa;">
                <small style="color: #a8b7c8;">Mindst 8 tegn</small>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #a8b7c8;">Rolle *</label>
                <select name="role" required style="width: 100%; padding: 10px; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; background: rgba(5,15,27,0.45); color: #edf3fa;">
                    <option value="editor">Redaktør</option>
                    <option value="super_admin">Super Administrator</option>
                </select>
            </div>
            <?php echo csrf_input(); ?>
            <button type="submit" style="background: linear-gradient(135deg, #2B7B35 0%, #002748 100%); color: white; padding: 10px 30px; border: none; border-radius: 8px; cursor: pointer;">Opret Bruger</button>
        </form>
    </div>
    <div style="background: rgba(17, 49, 77, 0.9); border: 1px solid rgba(255,255,255,0.08); padding: 25px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h2 style="margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px; color: #edf3fa;">Eksisterende Brugere</h2>
        <?php if (count($users) > 0): ?>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; color: #edf3fa;">
                <thead>
                    <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.08);">
                        <th style="padding: 10px; text-align: left; color: #a8b7c8;">Brugernavn</th>
                        <th style="padding: 10px; text-align: left; color: #a8b7c8;">Rolle</th>
                        <th style="padding: 10px; text-align: left; color: #a8b7c8;">Oprettet</th>
                        <th style="padding: 10px; text-align: right; color: #a8b7c8;">Handlinger</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                            <td style="padding: 10px; color: #edf3fa;"><?php echo safe_html($user['username']); ?></td>
                            <td style="padding: 10px;">
                                <span style="background: <?php echo $user['role'] === 'super_admin' ? '#3C85BA' : '#6b7d8d'; ?>; color: white; padding: 3px 8px; border-radius: 999px; font-size: 11px;">
                                    <?php echo $user['role'] === 'super_admin' ? 'Super Admin' : 'Redaktør'; ?>
                                </span>
                            </td>
                            <td style="padding: 10px; color: #a8b7c8;"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td style="padding: 10px; text-align: right;">
                                <?php if ($user['id'] != get_current_user_id()): ?>
                                    <form method="POST" onsubmit="return confirm('Er du sikker?');">
                                        <input type="hidden" name="delete_user" value="1">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <?php echo csrf_input(); ?>
                                        <button type="submit" style="color: #ffb5b5; background: none; border: 0; padding: 0; font-size: 12px; cursor: pointer;">Slet</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #8495a8; font-size: 12px;">(Dig selv)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #a8b7c8; text-align: center; padding: 20px;">Ingen brugere fundet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>