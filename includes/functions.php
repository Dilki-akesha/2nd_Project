<?php
/**
 * Harvestly Global Helper Functions
 * Security, session management, output sanitization, and file uploads.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookie attributes
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

/**
 * Escape output string to prevent XSS attacks
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Verify Admin Authentication & Role
 */
function checkAdminAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: index.php?page=login&error=Unauthorized+access.+Please+login+as+Admin.");
        exit();
    }
}

/**
 * Base URL helper for absolute asset/link references
 */
function baseUrl($path = '') {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($scriptDir === '/' || $scriptDir === '\\') {
        $scriptDir = '';
    }
    return rtrim($scriptDir, '/') . '/' . ltrim($path, '/');
}

/**
 * Secure File Upload Handler
 * Validates file size, allowed MIME types, renames storage file, blocks executables.
 */
function handleFileUpload($fileArray, $targetDirRelative = 'assets/documents/') {
    if (!isset($fileArray['error']) || is_array($fileArray['error'])) {
        throw new Exception('Invalid upload parameter format.');
    }

    switch ($fileArray['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new Exception('No file was uploaded.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception('Exceeded file size limit.');
        default:
            throw new Exception('Unknown upload error.');
    }

    // Max 5MB file size limit
    if ($fileArray['size'] > 5 * 1024 * 1024) {
        throw new Exception('Exceeded maximum allowed file size (5MB).');
    }

    // Allowed extensions and MIME types
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
    ];

    $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
    
    // Check extension
    if (!array_key_exists($ext, $allowedMimes)) {
        throw new Exception('Invalid file extension. Only JPG, PNG, and PDF files are permitted.');
    }

    // Check MIME type using finfo
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileArray['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowedMimes)) {
            throw new Exception('Invalid file format. Security check failed.');
        }
    }

    // Prepare target directory
    $targetDirAbsolute = __DIR__ . '/../' . ltrim($targetDirRelative, '/');
    if (!is_dir($targetDirAbsolute)) {
        mkdir($targetDirAbsolute, 0755, true);
    }

    // Secure unique filename creation
    $newFileName = sprintf('%s_%s.%s', 
        pathinfo($fileArray['name'], PATHINFO_FILENAME), 
        bin2hex(random_bytes(8)), 
        $ext
    );

    $destination = $targetDirAbsolute . '/' . $newFileName;

    if (!move_uploaded_file($fileArray['tmp_name'], $destination)) {
        throw new Exception('Failed to save uploaded file.');
    }

    return ltrim($targetDirRelative, '/') . '/' . $newFileName;
}
