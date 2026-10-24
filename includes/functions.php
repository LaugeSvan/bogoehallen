<?php
/**
 * Shared Functions for Frontend and Admin
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/content-defaults.php';

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

function get_site_meta_value($key, $default = '') {
    $value = get_content('site_meta', $key, $default);
    return trim((string) $value) !== '' ? $value : $default;
}

function get_current_canonical_url() {
    $host = $_SERVER['HTTP_HOST'] ?? 'www.bogoehallen.dk';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
    $path = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($path, PHP_URL_PATH) ?: '/';

    return $scheme . '://' . $host . $path;
}

function get_site_meta_data() {
    $site_name = get_site_meta_value('site_name', 'Bogø Hallen');
    $site_description = get_site_meta_value('site_description', 'Bogø Hallen er et moderne idrætscenter på Bogø med sport, aktiviteter og fællesskab for alle.');
    $site_url = get_site_meta_value('site_url', SITE_URL);
    $canonical_url = get_site_meta_value('canonical_url', $site_url);
    $og_image = normalize_upload_url(get_site_meta_value('og_image', ''));
    $theme_color = get_site_meta_value('theme_color', '#0f1419');
    $cookie_text = get_site_meta_value('cookie_text', 'Vi bruger cookies til at forbedre din oplevelse og sikre, at siden fungerer optimalt.');

    return [
        'site_name' => $site_name,
        'site_description' => $site_description,
        'site_url' => $site_url,
        'canonical_url' => $canonical_url,
        'og_image' => $og_image,
        'theme_color' => $theme_color,
        'cookie_text' => $cookie_text,
    ];
}