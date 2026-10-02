<?php
/**
 * Admin Panel Header Template
 */
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? safe_html($page_title) . ' - ' : ''; ?>Admin - Bogø Hallen</title>
    <style>
        :root {
            --bg: #071a2a;
            --panel: #0d2237;
            --panel-strong: #11314d;
            --surface: #122d46;
            --surface-soft: #163a5b;
            --line: rgba(255, 255, 255, 0.08);
            --text: #e9edf2;
            --muted: #a8b5c3;
            --blue: #3C85BA;
            --green: #2B7B35;
            --navy: #002748;
            --danger: #d85b5b;
            --warning: #d1a952;
            --success: #2d8a4f;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(180deg, #071a2a 0%, #0b2038 100%);
            color: var(--text);
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
            background: var(--bg);
        }

        .sidebar {
            width: 250px;
            background: rgba(8, 20, 33, 0.96);
            color: var(--text);
            padding: 24px 18px;
            border-right: 1px solid var(--line);
            box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.03);
        }

        .sidebar h2 {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 24px;
            padding: 0 12px 12px;
            border-bottom: 1px solid var(--line);
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li {
            margin-bottom: 8px;
        }

        .sidebar a {
            display: block;
            color: var(--muted);
            text-decoration: none;
            padding: 11px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 600;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(60, 133, 186, 0.14);
            color: var(--text);
            border: 1px solid rgba(60, 133, 186, 0.28);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .header {
            background: rgba(15, 31, 48, 0.96);
            border-bottom: 1px solid var(--line);
            padding: 22px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title h1 {
            font-size: 24px;
            color: var(--text);
            margin-bottom: 4px;
        }

        .header-title p {
            font-size: 13px;
            color: var(--muted);
        }

        .user-menu {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .user-info {
            text-align: right;
            font-size: 13px;
        }

        .user-info strong {
            display: block;
            color: var(--text);
            font-weight: 700;
        }

        .user-info small {
            color: var(--muted);
        }

        .logout-btn {
            padding: 9px 16px;
            background: rgba(216, 91, 91, 0.12);
            color: #f7dada;
            border: 1px solid rgba(216, 91, 91, 0.38);
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: rgba(216, 91, 91, 0.2);
        }

        .content {
            flex: 1;
            padding: 28px 30px 40px;
            overflow-y: auto;
            background: linear-gradient(180deg, rgba(13, 34, 55, 0.95), rgba(8, 20, 33, 1));
        }

        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(45, 138, 79, 0.12);
            color: #c9f2d7;
            border-color: rgba(45, 138, 79, 0.35);
        }

        .alert-danger {
            background: rgba(216, 91, 91, 0.1);
            color: #ffd9d9;
            border-color: rgba(216, 91, 91, 0.35);
        }

        .alert-warning {
            background: rgba(209, 169, 82, 0.12);
            color: #f6e2ae;
            border-color: rgba(209, 169, 82, 0.32);
        }

        .alert-info {
            background: rgba(60, 133, 186, 0.12);
            color: #d9ebfb;
            border-color: rgba(60, 133, 186, 0.35);
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--line);
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .user-menu {
                width: 100%;
                justify-content: space-between;
            }

            .content {
                padding: 22px 18px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="/admin/dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="/admin/edit_content.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'edit_content.php') ? 'active' : ''; ?>">Indhold</a></li>
                <li><a href="/admin/edit_gallery.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'edit_gallery.php') ? 'active' : ''; ?>">Galleri</a></li>
                <li><a href="/admin/edit_sponsors.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'edit_sponsors.php') ? 'active' : ''; ?>">Sponsorer</a></li>
                <li><a href="/admin/audit_log.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'audit_log.php') ? 'active' : ''; ?>">Ændringslog</a></li>
                <li><a href="/admin/change_password.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'change_password.php') ? 'active' : ''; ?>">Skift Adgangskode</a></li>
                <li><a href="/admin/manage_users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'manage_users.php') ? 'active' : ''; ?>">Bruger Administration</a></li>
            </ul>
        </aside>

        <div class="main-content">
            <header class="header">
                <div class="header-title">
                    <h1><?php echo isset($page_title) ? safe_html($page_title) : 'Admin Panel'; ?></h1>
                    <p><?php echo isset($page_subtitle) ? safe_html($page_subtitle) : ''; ?></p>
                </div>
                <div class="user-menu">
                    <div class="user-info">
                        <strong><?php echo safe_html($_SESSION['username'] ?? 'Bruger'); ?></strong>
                        <small><?php echo $_SESSION['role'] === 'super_admin' ? 'Super Administrator' : 'Redaktør'; ?></small>
                    </div>
                    <a href="/admin/logout.php" class="logout-btn">Log Ud</a>
                </div>
            </header>

            <main class="content">
