<?php
/**
 * Image Upload and Processing Handler
 * Handles image uploads, validation, resizing using GD library
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/security.php';

/**
 * Upload and process image
 */
function handle_image_upload($file, $destination_dir = null, $resize_width = null, $resize_height = null) {
    if (!$destination_dir) {
        $destination_dir = UPLOADS_DIR;
    }

    // Create directory if it doesn't exist
    if (!is_dir($destination_dir)) {
        @mkdir($destination_dir, 0755, true);
    }

    // Validate upload
    $validation = validate_file_upload($file);
    if (!$validation['success']) {
        return ['success' => false, 'error' => $validation['error']];
    }

    // Generate unique filename
    $filename = generate_upload_filename($file['name']);
    $filepath = $destination_dir . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'Failed to save file'];
    }

    // Set permissions
    chmod($filepath, 0644);

    // Resize if dimensions provided
    if ($resize_width && $resize_height) {
        $result = resize_image($filepath, $resize_width, $resize_height);
        if (!$result['success']) {
            unlink($filepath);
            return $result;
        }
    }

    return [
        'success' => true,
        'filename' => $filename,
        'path' => $filepath,
        'url' => str_replace(__DIR__ . '/../../', '/', $filepath)
    ];
}

/**
 * Resize image using GD library
 */
function resize_image($filepath, $max_width, $max_height) {
    if (!extension_loaded('gd')) {
        return ['success' => false, 'error' => 'GD library is not installed'];
    }

    if (!file_exists($filepath)) {
        return ['success' => false, 'error' => 'File not found'];
    }

    $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

    // Load image based on type
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $source = @imagecreatefromjpeg($filepath);
            break;
        case 'png':
            $source = @imagecreatefrompng($filepath);
            break;
        case 'gif':
            $source = @imagecreatefromgif($filepath);
            break;
        default:
            return ['success' => false, 'error' => 'Unsupported image format'];
    }

    if (!$source) {
        return ['success' => false, 'error' => 'Failed to load image'];
    }

    $width = imagesx($source);
    $height = imagesy($source);

    // Calculate new dimensions
    $ratio = $width / $height;
    $new_width = $max_width;
    $new_height = round($max_width / $ratio);

    if ($new_height > $max_height) {
        $new_height = $max_height;
        $new_width = round($max_height * $ratio);
    }

    // Create resized image
    $resized = imagecreatetruecolor($new_width, $new_height);

    // Preserve transparency for PNG
    if ($ext === 'png') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $new_width, $new_height, $transparent);
    }

    // Copy and resize
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Save resized image
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($resized, $filepath, 90);
            break;
        case 'png':
            imagepng($resized, $filepath, 8);
            break;
        case 'gif':
            imagegif($resized, $filepath);
            break;
    }

    imagedestroy($source);
    imagedestroy($resized);

    return ['success' => true, 'size' => ['width' => $new_width, 'height' => $new_height]];
}

/**
 * Delete image file
 */
function delete_image($filepath) {
    if (file_exists($filepath)) {
        return @unlink($filepath);
    }
    return true;
}

/**
 * Get image dimensions
 */
function get_image_dimensions($filepath) {
    if (!file_exists($filepath)) {
        return null;
    }

    $size = @getimagesize($filepath);
    if (!$size) {
        return null;
    }

    return ['width' => $size[0], 'height' => $size[1]];
}
