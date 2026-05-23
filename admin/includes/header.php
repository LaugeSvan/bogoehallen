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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 30px;
            border-bottom: 2px solid #34495e;
            padding-bottom: 10px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li {
            margin-bottom: 10px;
        }

        .sidebar a {
            display: block;
            color: #ecf0f1;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 4px;
            transition: background 0.3s;
            font-size: 14px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #34495e;
            color: #3498db;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: white;
            border-bottom: 1px solid #ddd;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .header-title h1 {
            font-size: 24px;
            color: #2c3e50;
        }

        .header-title p {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 4px;
        }

        .user-menu {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-info {
            text-align: right;
            font-size: 13px;
        }

        .user-info strong {
            display: block;
            color: #2c3e50;
        }

        .user-info small {
            color: #7f8c8d;
        }

        .logout-btn {
            padding: 8px 16px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                display: flex;
                align-items: center;
            }

            .header {
                flex-direction: column;
                text-align: center;
            }

            .user-menu {
                margin-top: 15px;
                flex-direction: column;
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
