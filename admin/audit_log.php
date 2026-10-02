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
        background: rgba(17, 49, 77, 0.9);
        border: 1px solid rgba(255,255,255,0.08);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .audit-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        color: #edf3fa;
    }

    .audit-table thead {
        background: rgba(255,255,255,0.02);
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .audit-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #a8b7c8;
    }

    .audit-table td {
        padding: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        color: #edf3fa;
    }

    .audit-table tr:hover {
        background: rgba(255,255,255,0.02);
    }

    .action-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .action-updated { background: rgba(45, 138, 79, 0.2); color: #d6fbe0; }
    .action-added { background: rgba(60, 133, 186, 0.2); color: #d9ebfb; }
    .action-deleted { background: rgba(216, 91, 91, 0.18); color: #ffdfe1; }
    .action-uploaded { background: rgba(209, 169, 82, 0.18); color: #f6e2ae; }

    .timestamp {
        color: #a8b7c8;
        white-space: nowrap;
    }

    .value-preview {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #a8b7c8;
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
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        text-decoration: none;
        color: #7bb5df;
        font-size: 13px;
        background: rgba(255,255,255,0.02);
    }

    .pagination a:hover {
        background: rgba(255,255,255,0.04);
    }

    .pagination span.current {
        background: #3C85BA;
        color: white;
        border-color: #3C85BA;
    }

    .pagination span.disabled {
        color: #6d7f91;
        cursor: not-allowed;
    }

    .stats {
        margin-bottom: 20px;
        padding: 15px;
        background: rgba(255,255,255,0.02);
        border-radius: 8px;
        font-size: 13px;
        color: #a8b7c8;
        border: 1px solid rgba(255,255,255,0.08);
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
