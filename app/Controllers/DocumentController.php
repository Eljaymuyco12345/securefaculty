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
        $requestedFormat = strtolower(trim((string)($_GET['format'] ?? '')));

        $delivery = $this->resolveDeliveryPayload($cert, $decryptedBytes, $isDownload, $requestedFormat);
        if (!$delivery['success']) {
            if (!headers_sent()) {
                http_response_code($delivery['code']);
                header('Content-Type: text/html; charset=UTF-8');
            }
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Error | SecureFaculty</title></head><body style="font-family:sans-serif;padding:2rem;background:#081225;color:#e2e8f0;"><div style="max-width:32rem;margin:auto;background:#111c36;padding:2rem;border-radius:0.75rem;border:1px solid #1e293b;"><h2 style="color:#f43f5e;">Unsupported Format</h2><p>' . htmlspecialchars($delivery['message'], ENT_QUOTES, 'UTF-8') . '</p><p><a href="vault.php" style="color:#38bdf8;">Return to Vault</a></p></div></body></html>';
            exit;
        }

        $action = $isDownload ? 'DOWNLOAD_DOCUMENT' : 'VIEW_DOCUMENT';
        AuditLogger::log($action, "Just-in-Time Decryption ({$delivery['format_label']}) executed for certificate #{$id}: '{$cert['title']}'", (int)($cert['faculty_id'] ?? $id));

        // Clean any active output buffers to guarantee binary stream integrity
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $delivery['mime_type']);
        header('Content-Length: ' . strlen($delivery['bytes']));
        header("Content-Disposition: {$delivery['disposition']}; filename=\"{$delivery['ascii_filename']}\"; filename*=UTF-8''" . rawurlencode($delivery['filename']));
        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');

        echo $delivery['bytes'];
        exit;
    }

    /**
     * Resolves the output bytes, MIME type, disposition, and sanitized filename
     * for PDF preview/download or original DOCX download.
     */
    public function resolveDeliveryPayload(array $cert, string $decryptedBytes, bool $isDownload, string $requestedFormat = ''): array {
        $id = (int)($cert['id'] ?? 0);
        $ext = strtolower(ltrim((string)($cert['file_type'] ?? ''), '.'));
        $requestedFormat = strtolower(trim($requestedFormat));

        if ($requestedFormat === 'docx' && $ext !== 'docx') {
            return [
                'success' => false,
                'code'    => 400,
                'message' => 'DOCX download is only available for records originally uploaded as Word (.docx) documents.'
            ];
        }

        $serveOriginalDocx = ($requestedFormat === 'docx' && $ext === 'docx');
        $bytes = $decryptedBytes;

        if (!$serveOriginalDocx && $ext !== 'pdf') {
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
                    $bytes = file_get_contents($tempOut);
                }
            } finally {
                if (file_exists($tempIn)) @unlink($tempIn);
                if (file_exists($tempOut)) @unlink($tempOut);
            }
        }

        $targetExt = $serveOriginalDocx ? 'docx' : 'pdf';
        $mimeType = $serveOriginalDocx
            ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            : 'application/pdf';
        $disposition = ($isDownload || $serveOriginalDocx) ? 'attachment' : 'inline';

        $rawFilename = basename(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $cert['original_filename'] ?? 'document'));
        $safeBase = pathinfo($rawFilename, PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[\x00-\x1F\x7F"\';\\\]/', '_', $safeBase) . '.' . $targetExt;
        $safeFilename = trim($safeFilename, " .");
        if ($safeFilename === '' || $safeFilename === '.' . $targetExt) {
            $safeFilename = "certificate_{$id}.{$targetExt}";
        }
        $asciiFilename = preg_replace('/[^\x20-\x7E]/', '_', $safeFilename);
        if ($asciiFilename === '' || $asciiFilename === '.' . $targetExt) {
            $asciiFilename = "certificate_{$id}.{$targetExt}";
        }

        return [
            'success'        => true,
            'bytes'          => $bytes,
            'mime_type'      => $mimeType,
            'filename'       => $safeFilename,
            'ascii_filename' => $asciiFilename,
            'disposition'    => $disposition,
            'format_label'   => $serveOriginalDocx ? 'DOCX' : 'PDF',
        ];
    }
}
