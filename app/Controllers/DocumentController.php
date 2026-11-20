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
        AuditLogger::log($action, "Just-in-Time Decryption executed for certificate #{$id}: '{$cert['title']}'", (int)($cert['faculty_id'] ?? $id));

        $ext = strtolower(ltrim($cert['file_type'] ?? '', '.'));

        // Policy: ALL documents viewed or downloaded must be delivered as PDF to prevent tampering & ensure inline preview
        if ($ext !== 'pdf') {
            $pythonBin = $this->getPythonBinary();
            $tempIn = TEMP_DIR . DIRECTORY_SEPARATOR . 'jit_in_' . bin2hex(random_bytes(8)) . '.' . ($ext ?: 'tmp');
            $tempOut = TEMP_DIR . DIRECTORY_SEPARATOR . 'jit_out_' . bin2hex(random_bytes(8)) . '.pdf';

            try {
                file_put_contents($tempIn, $decryptedBytes);
                $cmd = sprintf(
                    '%s %s --convert-to-pdf %s --out %s 2>&1',
                    escapeshellcmd($pythonBin),
                    escapeshellarg(PYTHON_ENGINE_PATH),
                    escapeshellarg($tempIn),
                    escapeshellarg($tempOut)
                );
                shell_exec($cmd);

                if (file_exists($tempOut) && filesize($tempOut) > 0) {
                    $decryptedBytes = file_get_contents($tempOut);
                    $ext = 'pdf';
                }
            } finally {
                if (file_exists($tempIn)) @unlink($tempIn);
                if (file_exists($tempOut)) @unlink($tempOut);
            }
        }

        $mimeType = 'application/pdf';
        $disposition = $isDownload ? 'attachment' : 'inline';

        // Safe filename sanitization enforced with .pdf extension
        $rawFilename = basename(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $cert['original_filename'] ?? 'document'));
        $safeBase = pathinfo($rawFilename, PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[\x00-\x1F\x7F"\';\\\]/', '_', $safeBase) . '.pdf';
        $safeFilename = trim($safeFilename, " .");
        if ($safeFilename === '' || $safeFilename === '.pdf') {
            $safeFilename = "certificate_{$id}.pdf";
        }
        $asciiFilename = preg_replace('/[^\x20-\x7E]/', '_', $safeFilename);
        if ($asciiFilename === '' || $asciiFilename === '.pdf') {
            $asciiFilename = "certificate_{$id}.pdf";
        }

        // Clean any active output buffers to guarantee binary stream integrity
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . strlen($decryptedBytes));
        header("Content-Disposition: {$disposition}; filename=\"{$asciiFilename}\"; filename*=UTF-8''" . rawurlencode($safeFilename));
        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');

        echo $decryptedBytes;
        exit;
    }
}
