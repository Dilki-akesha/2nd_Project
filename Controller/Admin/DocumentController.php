<?php
/**
 * Admin-only document access control.
 *
 * Uploaded Farmer and Courier Partner verification documents, and complaint
 * evidence files, are stored under assets/documents/ inside the web root. They
 * must never be linked directly, so every view links through this controller.
 *
 * Core PHP only. No external service.
 */
require_once __DIR__ . '/../../config/app.php';
checkAdminAuth();

$documentId = (int)($_GET['id'] ?? 0);
$file = null;
$downloadName = null;

if ($documentId > 0) {
    $document = db_fetch_one(
        'SELECT stored_file_path, original_file_name FROM verification_documents WHERE document_id = ?',
        'i',
        [$documentId]
    );
    if ($document) {
        $file = realpath(__DIR__ . '/../../' . ltrim((string)$document['stored_file_path'], '/'));
        $downloadName = (string)($document['original_file_name'] ?: 'document');
    }
} else {
    /*
     * Complaint evidence is stored by relative path rather than by id.
     * The complaint id is resolved first so only genuine evidence rows can be
     * requested, and the path must live under assets/documents/complaints/.
     */
    $complaintId = (int)($_GET['complaint'] ?? 0);
    if ($complaintId > 0) {
        $complaint = db_fetch_one(
            'SELECT evidence_path FROM complaints WHERE complaint_id = ?',
            'i',
            [$complaintId]
        );
        if ($complaint && !empty($complaint['evidence_path'])) {
            $file = realpath(__DIR__ . '/../../' . ltrim((string)$complaint['evidence_path'], '/'));
            $downloadName = basename((string)$complaint['evidence_path']);
        }
    }
}

// Block any path that escapes the uploads directory.
$root = realpath(__DIR__ . '/../../assets/documents');
if (
    !$file
    || !$root
    || !str_starts_with($file, $root . DIRECTORY_SEPARATOR)
    || !is_file($file)
) {
    http_response_code(404);
    exit('Document not found.');
}

$mime = function_exists('mime_content_type') ? mime_content_type($file) : 'application/octet-stream';
header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($file));
header(
    'Content-Disposition: attachment; filename="'
    . preg_replace('/[^a-zA-Z0-9._-]/', '_', $downloadName ?: 'document')
    . '"'
);
readfile($file);
exit;
