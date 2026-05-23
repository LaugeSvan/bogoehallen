<?php
/**
 * Audit Log Viewer
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';

require_admin_login();

$page_title = 'Ændringslog';
$page_subtitle = 'Se historik over alle ændringer foretaget i admin panelet';

$db = get_db_connection();

// Pagination
$per_page = 50;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// Get total count
$count_result = $db->query('SELECT COUNT(*) as total FROM audit_log');
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Get audit logs with pagination
$query = "
    SELECT a.*, u.username
    FROM audit_log a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.timestamp DESC
    LIMIT ? OFFSET ?
";

$stmt = $db->prepare($query);
if ($stmt) {
    $stmt->bind_param('ii', $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $logs = [];
}

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<style>
    .audit-section {
        background: white;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .audit-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .audit-table thead {
        background: #f9f9f9;
        border-bottom: 2px solid #ecf0f1;
    }

    .audit-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
    }

    .audit-table td {
        padding: 12px;
        border-bottom: 1px solid #ecf0f1;
    }

    .audit-table tr:hover {
        background: #f9f9f9;
    }

    .action-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .action-updated { background: #d4edda; color: #155724; }
    .action-added { background: #cfe2ff; color: #084298; }
    .action-deleted { background: #f8d7da; color: #842029; }
    .action-uploaded { background: #fff3cd; color: #664d03; }

    .timestamp {
        color: #7f8c8d;
        white-space: nowrap;
    }

    .value-preview {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #7f8c8d;
        font-family: monospace;
        font-size: 12px;
    }

    .pagination {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 5px;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #3498db;
        font-size: 13px;
    }

    .pagination a:hover {
        background: #f9f9f9;
    }

    .pagination span.current {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }

    .pagination span.disabled {
        color: #ccc;
        cursor: not-allowed;
    }

    .stats {
        margin-bottom: 20px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 4px;
        font-size: 13px;
        color: #7f8c8d;
    }
</style>

<div class="audit-section">
    <div class="stats">
        <strong>Samlet antal ændringer:</strong> <?php echo $total; ?> |
        <strong>Side:</strong> <?php echo $page; ?> af <?php echo $total_pages; ?>
    </div>

    <?php if (count($logs) > 0): ?>
        <table class="audit-table">
            <thead>
                <tr>
                    <th>Handling</th>
                    <th>Tabel</th>
                    <th>Bruger</th>
                    <th>ID</th>
                    <th>Tidspunkt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <span class="action-badge action-<?php echo strtolower($log['action']); ?>">
                                <?php
                                $action_labels = [
                                    'updated_content' => 'Opdateret',
                                    'updated_sponsor' => 'Opdateret',
                                    'added_sponsor' => 'Tilføjet',
                                    'deleted_sponsor' => 'Slettet',
                                    'uploaded_image' => 'Uploadet',
                                    'deleted_image' => 'Slettet'
                                ];
                                echo $action_labels[$log['action']] ?? $log['action'];
                                ?>
                            </span>
                        </td>
                        <td><?php echo safe_html(ucfirst($log['table_affected'])); ?></td>
                        <td><?php echo safe_html($log['username'] ?? 'System'); ?></td>
                        <td><?php echo $log['record_id'] ?? '-'; ?></td>
                        <td class="timestamp"><?php echo date('d/m/Y H:i:s', strtotime($log['timestamp'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="/admin/audit_log.php?page=1">← Første</a>
                    <a href="/admin/audit_log.php?page=<?php echo $page - 1; ?>">← Forrige</a>
                <?php else: ?>
                    <span class="disabled">← Første</span>
                    <span class="disabled">← Forrige</span>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="/admin/audit_log.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="/admin/audit_log.php?page=<?php echo $page + 1; ?>">Næste →</a>
                    <a href="/admin/audit_log.php?page=<?php echo $total_pages; ?>">Sidste →</a>
                <?php else: ?>
                    <span class="disabled">Næste →</span>
                    <span class="disabled">Sidste →</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p style="text-align: center; color: #7f8c8d; padding: 20px;">Ingen ændringer endnu.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
