<?php
/**
 * Admin Panel Helper Functions
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/security.php';

/**
 * Check if user is logged in
 */
function is_admin_logged_in() {
    init_session();
    return !empty($_SESSION['user_id']) && !empty($_SESSION['username']) && !empty($_SESSION['role']);
}

/**
 * Redirect to login if not authenticated
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Check if user has permission (role-based)
 */
function has_permission($required_role = 'editor') {
    if (!is_admin_logged_in()) {
        return false;
    }

    if ($_SESSION['role'] === 'super_admin') {
        return true;
    }

    return $_SESSION['role'] === $required_role;
}

/**
 * Require permission or redirect to dashboard
 */
function require_permission($required_role = 'editor') {
    if (!has_permission($required_role)) {
        header('Location: /admin/dashboard.php?error=insufficient_permissions');
        exit;
    }
}

/**
 * Get current user ID
 */
function get_current_user_id() {
    init_session();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function get_current_user_role() {
    init_session();
    return $_SESSION['role'] ?? null;
}

/**
 * Authenticate user with username and password
 */
function authenticate_user($username, $password) {
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
    if (!$stmt) {
        error_log('Database error: ' . $db->error);
        return ['success' => false, 'error' => 'Database error'];
    }

    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $stmt->close();
        return ['success' => false, 'error' => 'Invalid credentials'];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!verify_password($password, $user['password'])) {
        return ['success' => false, 'error' => 'Invalid credentials'];
    }

    return ['success' => true, 'user' => $user];
}

/**
 * Create admin session
 */
function create_admin_session($user) {
    init_session();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_time'] = time();
}

/**
 * Destroy admin session
 */
function destroy_admin_session() {
    init_session();
    session_destroy();
    setcookie(SESSION_NAME, '', time() - 3600, '/');
}

/**
 * Get content value from database
 */
function get_content($section, $key, $default = '') {
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT value FROM content WHERE section = ? AND `key` = ? LIMIT 1');
    if (!$stmt) {
        return $default;
    }

    $stmt->bind_param('ss', $section, $key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return $default;
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['value'] ?? $default;
}

/**
 * Update or insert content
 */
function set_content($section, $key, $value, $user_id = null) {
    $db = get_db_connection();

    $old_value = get_content($section, $key);

    $stmt = $db->prepare('
        INSERT INTO content (section, `key`, value)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE value = VALUES(value)
    ');

    if (!$stmt) {
        error_log('Database error: ' . $db->error);
        return false;
    }

    $stmt->bind_param('sss', $section, $key, $value);
    $result = $stmt->execute();
    $stmt->close();

    if ($result && $user_id) {
        log_audit($user_id, 'updated_content', 'content', null, ['key' => $key, 'value' => $old_value], ['key' => $key, 'value' => $value]);
    }

    return $result;
}

/**
 * Get all opening hours
 */
function get_opening_hours() {
    $db = get_db_connection();

    $stmt = $db->prepare("
        SELECT `key`, value FROM content WHERE section = 'opening_hours'
        ORDER BY FIELD(`key`, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $hours = [];

    while ($row = $result->fetch_assoc()) {
        $hours[$row['key']] = $row['value'];
    }

    $stmt->close();
    return $hours;
}

/**
 * Save opening hours
 */
function save_opening_hours($hours, $user_id = null) {
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    foreach ($days as $day) {
        if (isset($hours[$day])) {
            set_content('opening_hours', $day, $hours[$day], $user_id);
        }
    }

    return true;
}
