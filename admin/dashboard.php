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
    <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 4px solid #3498db;">
        <h3 style="font-size: 12px; color: #7f8c8d; margin-bottom: 10px; text-transform: uppercase;">Brugere</h3>
        <p style="font-size: 28px; font-weight: bold; color: #2c3e50;"><?php echo $stats['users']; ?></p>
    </div>

    <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 4px solid #2ecc71;">
        <h3 style="font-size: 12px; color: #7f8c8d; margin-bottom: 10px; text-transform: uppercase;">Sponsorer</h3>
        <p style="font-size: 28px; font-weight: bold; color: #2c3e50;"><?php echo $stats['sponsors']; ?></p>
    </div>

    <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 4px solid #e74c3c;">
        <h3 style="font-size: 12px; color: #7f8c8d; margin-bottom: 10px; text-transform: uppercase;">Galleribilledbeder</h3>
        <p style="font-size: 28px; font-weight: bold; color: #2c3e50;"><?php echo $stats['gallery']; ?></p>
    </div>

    <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 4px solid #f39c12;">
        <h3 style="font-size: 12px; color: #7f8c8d; margin-bottom: 10px; text-transform: uppercase;">Ændringer (7 dage)</h3>
        <p style="font-size: 28px; font-weight: bold; color: #2c3e50;"><?php echo $stats['recent_changes']; ?></p>
    </div>
</div>

<div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
    <h2 style="font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Seneste Ændringer</h2>

    <?php if ($recent_log && $recent_log->num_rows > 0): ?>
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: #f9f9f9; border-bottom: 2px solid #ecf0f1;">
                    <th style="padding: 10px; text-align: left; font-weight: 600;">Handling</th>
                    <th style="padding: 10px; text-align: left; font-weight: 600;">Tabel</th>
                    <th style="padding: 10px; text-align: left; font-weight: 600;">Bruger</th>
                    <th style="padding: 10px; text-align: left; font-weight: 600;">Tidspunkt</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recent_log->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 10px;"><span style="background: #ecf0f1; padding: 3px 8px; border-radius: 3px; font-size: 12px;"><?php echo safe_html($row['action']); ?></span></td>
                        <td style="padding: 10px;"><?php echo safe_html($row['table_affected']); ?></td>
                        <td style="padding: 10px;"><?php echo safe_html($row['username'] ?? 'Systemadministrator'); ?></td>
                        <td style="padding: 10px; color: #7f8c8d;"><?php echo date('d/m/Y H:i', strtotime($row['timestamp'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="/admin/audit_log.php" style="display: inline-block; margin-top: 15px; color: #3498db; text-decoration: none; font-weight: 600; font-size: 13px;">Se hele ændringsloggen →</a>
    <?php else: ?>
        <p style="color: #7f8c8d; text-align: center; padding: 20px;">Ingen ændringer endnu.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
