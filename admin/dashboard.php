<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';

require_admin_login();

$page_title = 'Dashboard';
$page_subtitle = 'Velkomst til admin panelet';

$db = get_db_connection();

// Get statistics
$queries = [
    'users' => 'SELECT COUNT(*) as count FROM users',
    'sponsors' => 'SELECT COUNT(*) as count FROM sponsors',
    'gallery' => 'SELECT COUNT(*) as count FROM gallery_images',
    'recent_changes' => 'SELECT COUNT(*) as count FROM audit_log WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
];

$stats = [];
foreach ($queries as $key => $query) {
    $result = $db->query($query);
    $stats[$key] = $result ? $result->fetch_assoc()['count'] : 0;
}

// Get recent audit log
$recent_log = $db->query("
    SELECT a.*, u.username
    FROM audit_log a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.timestamp DESC
    LIMIT 10
");

?>

<?php
$page_title = 'Dashboard';
$page_subtitle = 'Velkomst til admin panelet';
require_once __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php
        $errors = [
            'insufficient_permissions' => 'Du har ikke tilladelse til at få adgang til denne side.'
        ];
        echo safe_html($errors[$_GET['error']] ?? $_GET['error']);
        ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: rgba(17, 49, 77, 0.9); border: 1px solid rgba(255,255,255,0.08); padding: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border-left: 4px solid #3C85BA;">
        <h3 style="font-size: 12px; color: #a8b7c8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.06em;">Brugere</h3>
        <p style="font-size: 28px; font-weight: bold; color: #edf3fa;"><?php echo $stats['users']; ?></p>
    </div>

    <div style="background: rgba(17, 49, 77, 0.9); border: 1px solid rgba(255,255,255,0.08); padding: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border-left: 4px solid #2B7B35;">
        <h3 style="font-size: 12px; color: #a8b7c8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.06em;">Sponsorer</h3>
        <p style="font-size: 28px; font-weight: bold; color: #edf3fa;"><?php echo $stats['sponsors']; ?></p>
    </div>

    <div style="background: rgba(17, 49, 77, 0.9); border: 1px solid rgba(255,255,255,0.08); padding: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border-left: 4px solid #d85b5b;">
        <h3 style="font-size: 12px; color: #a8b7c8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.06em;">Galleribilledbeder</h3>
        <p style="font-size: 28px; font-weight: bold; color: #edf3fa;"><?php echo $stats['gallery']; ?></p>
    </div>

    <div style="background: rgba(17, 49, 77, 0.9); border: 1px solid rgba(255,255,255,0.08); padding: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border-left: 4px solid #d1a952;">
        <h3 style="font-size: 12px; color: #a8b7c8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.06em;">Ændringer (7 dage)</h3>
        <p style="font-size: 28px; font-weight: bold; color: #edf3fa;"><?php echo $stats['recent_changes']; ?></p>
    </div>
</div>

<div style="background: rgba(17, 49, 77, 0.9); border: 1px solid rgba(255,255,255,0.08); padding: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
    <h2 style="font-size: 18px; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px; color: #edf3fa;">Seneste Ændringer</h2>

    <?php if ($recent_log && $recent_log->num_rows > 0): ?>
        <table style="width: 100%; border-collapse: collapse; font-size: 13px; color: #edf3fa;">
            <thead>
                <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <th style="padding: 10px; text-align: left; font-weight: 600; color: #a8b7c8;">Handling</th>
                    <th style="padding: 10px; text-align: left; font-weight: 600; color: #a8b7c8;">Tabel</th>
                    <th style="padding: 10px; text-align: left; font-weight: 600; color: #a8b7c8;">Bruger</th>
                    <th style="padding: 10px; text-align: left; font-weight: 600; color: #a8b7c8;">Tidspunkt</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recent_log->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                        <td style="padding: 10px;"><span style="background: rgba(255,255,255,0.06); padding: 3px 8px; border-radius: 3px; font-size: 12px; color: #edf3fa; "><?php echo safe_html($row['action']); ?></span></td>
                        <td style="padding: 10px; color: #edf3fa;"><?php echo safe_html($row['table_affected']); ?></td>
                        <td style="padding: 10px; color: #edf3fa;"><?php echo safe_html($row['username'] ?? 'Systemadministrator'); ?></td>
                        <td style="padding: 10px; color: #a8b7c8;"><?php echo date('d/m/Y H:i', strtotime($row['timestamp'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="/admin/audit_log.php" style="display: inline-block; margin-top: 15px; color: #7bb5df; text-decoration: none; font-weight: 600; font-size: 13px;">Se hele ændringsloggen →</a>
    <?php else: ?>
        <p style="color: #a8b7c8; text-align: center; padding: 20px;">Ingen ændringer endnu.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
