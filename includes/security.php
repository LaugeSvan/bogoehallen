<?php
/**
 * Security Utilities
 * CSRF protection, password hashing, input sanitization, audit logging
 */

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token HTML input
 */
function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}

/**
 * Hash password using bcrypt
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password against hash
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize input for database
 */
function sanitize_input($input) {
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize for HTML output
 */
function safe_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape for SQL (use prepared statements instead when possible)
 */
function escape_sql($string, $connection = null) {
    if (!$connection) {
        $connection = get_db_connection();
    }
    return $connection->real_escape_string($string);
}

/**
 * Log audit action
 */
function log_audit($user_id, $action, $table_affected, $record_id, $old_value = null, $new_value = null) {
    $db = get_db_connection();

    $stmt = $db->prepare("
        INSERT INTO audit_log (user_id, action, table_affected, record_id, old_value, new_value)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        error_log('Audit log error: ' . $db->error);
        return false;
    }

    $old_json = $old_value ? json_encode($old_value) : null;
    $new_json = $new_value ? json_encode($new_value) : null;

    $stmt->bind_param(
        'ississ',
        $user_id,
        $action,
        $table_affected,
        $record_id,
        $old_json,
        $new_json
    );

    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Validate email address
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate file upload
 */
function validate_file_upload($file, $allowed_types = ALLOWED_IMAGE_TYPES, $max_size = MAX_UPLOAD_SIZE) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'File upload error'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File size exceeds limit'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }

    return ['success' => true];
}

/**
 * Generate random filename for uploads
 */
function generate_upload_filename($original_filename) {
    $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
    return bin2hex(random_bytes(16)) . '.' . strtolower($ext);
}

/**
 * Get file MIME type safely
 */
function get_mime_type($file_path) {
    if (function_exists('mime_content_type')) {
        return mime_content_type($file_path);
    }

    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $mime_types = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'gif' => 'image/gif',
    ];

    return $mime_types[$ext] ?? 'application/octet-stream';
}

/**
 * Sanitize filename
 */
function sanitize_filename($filename) {
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}
