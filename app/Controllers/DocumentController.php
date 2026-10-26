<?php
/**
 * DocumentController
 * Just-in-Time Cryptographic Decryption & Secure File Streaming
 * Controller Layer
 */

require_once __DIR__ . '/BaseController.php';

class DocumentController extends BaseController {
    public function view(): void {
        AuthService::requireLogin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $currentUser = AuthService::user();
        $isDean = AuthService::isDean();

        $result = CertificateService::retrieveDocument($id, $currentUser['id'], $isDean);
        if (!$result['success']) {
            http_response_code($result['code']);
            die($result['message']);
        }

        $cert = $result['cert'];
        $decryptedBytes = $result['bytes'];

        $isDownload = isset($_GET['download']) && $_GET['download'] == '1';
        $action = $isDownload ? 'DOWNLOAD_DOCUMENT' : 'VIEW_DOCUMENT';
        AuditLogger::log($action, "Just-in-Time Decryption executed for certificate #{$id}: '{$cert['title']}'", $id);

        // Map MIME type
        $ext = strtolower($cert['file_type']);
        $mimeMap = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $mimeType = $mimeMap[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . strlen($decryptedBytes));
        $disposition = $isDownload ? 'attachment' : 'inline';
        $safeFilename = str_replace(["\r", "\n", '"', ';', '/'], '_', basename($cert['original_filename']));
        header("Content-Disposition: {$disposition}; filename=\"{$safeFilename}\"; filename*=UTF-8''" . rawurlencode($safeFilename));
        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');

        echo $decryptedBytes;
        exit;
    }
}
