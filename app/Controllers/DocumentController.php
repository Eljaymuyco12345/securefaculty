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
            if (!headers_sent()) {
                http_response_code($result['code']);
                header('Content-Type: text/html; charset=UTF-8');
            }
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Error | SecureFaculty</title></head><body style="font-family:sans-serif;padding:2rem;background:#081225;color:#e2e8f0;"><div style="max-width:32rem;margin:auto;background:#111c36;padding:2rem;border-radius:0.75rem;border:1px solid #1e293b;"><h2 style="color:#f43f5e;">Access Error</h2><p>' . htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') . '</p><p><a href="vault.php" style="color:#38bdf8;">Return to Vault</a></p></div></body></html>';
            exit;
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
