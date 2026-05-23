<?php
/**
 * Shared Functions for Frontend and Admin
 */
require_once __DIR__ . '/../config.php';

function get_content($section, $key, $default = '') {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT value FROM content WHERE section = ? AND `key` = ? LIMIT 1');
    if (!$stmt) return $default;
    $stmt->bind_param('ss', $section, $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->num_rows ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row['value'] ?? $default;
}

function get_opening_hours() {
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT `key`, value FROM content WHERE section = 'opening_hours' ORDER BY FIELD(`key`, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')");
    if (!$stmt) return [];
    $stmt->execute();
    $result = $stmt->get_result();
    $hours = [];
    while ($row = $result->fetch_assoc()) {
        $hours[$row['key']] = $row['value'];
    }
    $stmt->close();
    return $hours;
}

function get_gallery_images() {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT id, image_path, caption FROM gallery_images ORDER BY sort_order ASC');
    if (!$stmt) return [];
    $stmt->execute();
    $result = $stmt->get_result();
    $images = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $images;
}

function get_sponsors() {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT id, name, logo, link FROM sponsors ORDER BY sort_order ASC');
    if (!$stmt) return [];
    $stmt->execute();
    $result = $stmt->get_result();
    $sponsors = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $sponsors;
}