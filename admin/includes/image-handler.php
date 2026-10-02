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
        return ['success' => false, 'error' => 'Filen kunne ikke gemmes.'];
    }

    $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    if ($extension === 'svg' && !sanitize_svg_upload($filepath)) {
        unlink($filepath);
        return ['success' => false, 'error' => 'SVG-filen indeholder ugyldigt eller usikkert indhold'];
    }

    // Set permissions
    chmod($filepath, 0644);

    // Resize if dimensions provided
    if ($resize_width && $resize_height && $extension !== 'svg') {
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
        'url' => rtrim(UPLOADS_PUBLIC_PATH, '/') . '/' . $filename
    ];
}

/**
 * Remove executable and externally-referenced content from uploaded SVG files.
 */
function sanitize_svg_upload($filepath) {
    if (!class_exists('DOMDocument')) {
        return false;
    }

    $source = @file_get_contents($filepath);
    if ($source === false || preg_match('/<!DOCTYPE|<!ENTITY/i', $source)) {
        return false;
    }

    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadXML($source, LIBXML_NONET | LIBXML_NOBLANKS);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded || !$document->documentElement || strtolower($document->documentElement->localName) !== 'svg') {
        return false;
    }

    $allowed_elements = [
        'svg', 'g', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline', 'polygon',
        'text', 'tspan', 'title', 'desc', 'defs', 'lineargradient', 'radialgradient', 'stop',
        'clippath', 'mask'
    ];
    $allowed_attributes = [
        'id', 'xmlns', 'xmlns:xlink', 'viewbox', 'width', 'height', 'preserveaspectratio',
        'version', 'fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-linecap',
        'stroke-linejoin', 'stroke-opacity', 'opacity', 'transform', 'd', 'cx', 'cy', 'r', 'rx',
        'ry', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'points', 'offset', 'stop-color', 'stop-opacity',
        'gradientunits', 'gradienttransform'
    ];

    $sanitize_node = function ($node) use (&$sanitize_node, $allowed_elements, $allowed_attributes) {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                if (!in_array(strtolower($child->localName), $allowed_elements, true)) {
                    $node->removeChild($child);
                    continue;
                }
                $sanitize_node($child);
            } elseif ($child->nodeType !== XML_TEXT_NODE) {
                $node->removeChild($child);
            }
        }

        foreach (iterator_to_array($node->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = trim($attribute->nodeValue);
            if (!in_array($name, $allowed_attributes, true)
                || preg_match('/(?:javascript:|data:|url\s*\(\s*(?!#))/i', $value)) {
                $node->removeAttributeNode($attribute);
            }
        }
    };

    $sanitize_node($document->documentElement);
    return @file_put_contents($filepath, $document->saveXML($document->documentElement)) !== false;
}

/**
 * Resize image using GD library
 */
function resize_image($filepath, $max_width, $max_height) {
    if (!extension_loaded('gd')) {
        return ['success' => false, 'error' => 'Billedbehandling er ikke tilgængelig på serveren.'];
    }

    if (!file_exists($filepath)) {
        return ['success' => false, 'error' => 'Filen blev ikke fundet.'];
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
            return ['success' => false, 'error' => 'Billedformatet understøttes ikke.'];
    }

    if (!$source) {
        return ['success' => false, 'error' => 'Billedet kunne ikke åbnes.'];
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
