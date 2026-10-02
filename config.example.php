<?php
/**
 * Copy this file to config.php and enter the database details from Simply.com.
 * config.php is excluded from Git so credentials are never committed.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'database_user');
define('DB_PASSWORD', 'replace-with-database-password');
define('DB_NAME', 'database_name');

define('UPLOADS_DIR', __DIR__ . '/admin/uploads');
define('UPLOADS_PUBLIC_PATH', '/admin/uploads');
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'svg']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('SESSION_NAME', 'bogo_hallen_admin');

$logs_dir = __DIR__ . '/logs';
if (!is_dir($logs_dir)) {
    @mkdir($logs_dir, 0755, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $logs_dir . '/errors.log');
ini_set('display_errors', '0');

function get_db_connection() {
    static $db = null;

    if ($db instanceof mysqli) {
        return $db;
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $connection = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($connection->connect_errno) {
        error_log('Database connection failed: ' . $connection->connect_error);
        throw new RuntimeException('Database connection failed. Check the database settings in config.php.');
    }

    $connection->set_charset('utf8mb4');
    $db = $connection;
    return $db;
}

function init_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}