<?php
/**
 * CertificateService
 * Orchestrates business logic, cryptographic vault operations, and audit trails
 * Service Layer
 */

require_once __DIR__ . '/../DAO/CertificateDAO.php';
require_once __DIR__ . '/../Services/CryptoService.php';
require_once __DIR__ . '/../Services/AuditLogger.php';

class CertificateService {
    /**
     * Store and encrypt a staged certificate into the AES-256 Vault
     */
    public static function commitCertificate(array $input, int $requestingUserId, bool $isDean): array {
        $tempFilename = basename($input['temp_filename'] ?? '');
        $tempFilePath = TEMP_DIR . DIRECTORY_SEPARATOR . $tempFilename;

        if (empty($tempFilename)) {
            return ['success' => false, 'code' => 400, 'message' => 'Missing staged filename'];
        }

        if (!file_exists($tempFilePath) && str_starts_with($tempFilename, 'sample_demo')) {
            @file_put_contents($tempFilePath, "%PDF-1.4\n% Demo Certificate for SecureFaculty Smart Pipeline\n" . date('Y-m-d H:i:s'));
        }

        if (!file_exists($tempFilePath)) {
            return ['success' => false, 'code' => 404, 'message' => 'Temporary staged file not found or expired'];
        }

        $fileHash = hash_file('sha256', $tempFilePath);
        $assignedFacultyId = ($isDean && !empty($input['assigned_faculty_id']))
            ? (int)$input['assigned_faculty_id']
            : $requestingUserId;

        // Validate assigned faculty exists
        if (!UserDAO::find($assignedFacultyId)) {
            return ['success' => false, 'code' => 400, 'message' => 'Specified faculty member does not exist in registry.'];
        }

        $vaultFilename = bin2hex(random_bytes(16)) . '.enc';
        $vaultFilePath = VAULT_DIR . DIRECTORY_SEPARATOR . $vaultFilename;

        if (!CryptoService::encryptFile($tempFilePath, $vaultFilePath)) {
            return ['success' => false, 'code' => 500, 'message' => 'Cryptographic Failure: AES-256 encryption operation failed'];
        }

        // Clean up temporary plaintext staged file (Zero plaintext persistence)
        @unlink($tempFilePath);

        // Validate co-authors to prevent foreign key integrity violations
        $coAuthorIds = [];
        if (!empty($input['co_authors']) && is_array($input['co_authors'])) {
            $candidateIds = array_filter(array_map('intval', $input['co_authors']), fn($cid) => $cid > 0 && $cid !== $assignedFacultyId);
            if (!empty($candidateIds)) {
                $pdo = Database::getConnection();
                $inHolders = implode(',', array_fill(0, count($candidateIds), '?'));
                $vStmt = $pdo->prepare("SELECT id FROM users WHERE id IN ($inHolders)");
                $vStmt->execute($candidateIds);
                $coAuthorIds = $vStmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }

        $normalizedDate = CertificateModel::normalizeDate($input['date_issued'] ?? null);

        // String clamping to prevent column truncation
        $title = mb_substr(trim($input['title'] ?? 'Untitled Document'), 0, 255);
        if ($title === '') {
            $title = 'Untitled Document';
        }
        $issuer = mb_substr(trim($input['issuer'] ?? 'Not Specified'), 0, 255);
        $category = mb_substr(trim($input['category'] ?? 'Seminar'), 0, 100);
        $certNumber = !empty($input['cert_number']) ? mb_substr(trim($input['cert_number']), 0, 100) : null;
        $originalFilename = mb_substr(basename($input['original_name'] ?? 'certificate.pdf'), 0, 255);

        $certData = [
            'faculty_id' => $assignedFacultyId,
            'title' => $title,
            'issuer' => $issuer,
            'date_issued' => $normalizedDate,
            'category' => $category,
            'file_path' => $vaultFilename,
            'original_filename' => $originalFilename,
            'file_type' => strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION)),
            'file_size' => filesize($vaultFilePath),
            'ocr_raw_text' => $input['raw_text'] ?? '',
            'ocr_confidence' => floatval($input['confidence'] ?? 0.95),
            'status' => 'approved',
            'file_hash' => $fileHash,
            'cert_number' => $certNumber,
            'processing_status' => 'completed',
            'batch_id' => $input['batch_id'] ?? null
        ];

        try {
            $certId = CertificateDAO::create($certData, $coAuthorIds);
        } catch (Throwable $e) {
            AuditLogger::log('COMMIT_ERROR', "Failed to commit certificate: " . $e->getMessage(), $assignedFacultyId);
            return ['success' => false, 'code' => 500, 'message' => 'Database commit failure: ' . $e->getMessage()];
        }

        AuditLogger::log('COMMIT_VAULT_RECORD', "Approved and AES-256 encrypted certificate #{$certId}: '{$certData['title']}'", $certId);

        return [
            'success' => true,
            'certificate_id' => $certId,
            'file_hash' => $fileHash
        ];
    }

    /**
     * Authorize and decrypt document bytes in volatile RAM
     */
    public static function retrieveDocument(int $certId, int $requestingUserId, bool $isDean): array {
        $cert = CertificateDAO::find($certId);
        if (!$cert) {
            return ['success' => false, 'code' => 404, 'message' => 'Record not found.'];
        }

        $isAuthorized = false;
        if ($isDean || $cert['faculty_id'] == $requestingUserId || CertificateDAO::isAuthorOrCoAuthor($certId, $requestingUserId)) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            AuditLogger::log('UNAUTHORIZED_ACCESS_ATTEMPT', "Denied Just-in-Time decryption for certificate #{$certId}", $certId);
            return ['success' => false, 'code' => 403, 'message' => 'Access Denied: You do not possess cryptographic clearance for this faculty record.'];
        }

        $encryptedPath = VAULT_DIR . DIRECTORY_SEPARATOR . $cert['file_path'];
        $decryptedBytes = CryptoService::decryptFileToMemory($encryptedPath);

        if ($decryptedBytes === null) {
            return ['success' => false, 'code' => 500, 'message' => 'Decryption Failure: Corrupted cryptographic block or invalid master key.'];
        }

        return [
            'success' => true,
            'cert' => $cert,
            'bytes' => $decryptedBytes
        ];
    }

    /**
     * Purge stale staged uncommitted files older than given threshold (default 1 hour)
     * Enforces Zero-Plaintext persistence lifecycle
     */
    public static function purgeStaleTempFiles(int $maxAgeSeconds = 3600): int {
        $tempDir = TEMP_DIR;
        if (!is_dir($tempDir)) return 0;
        $now = time();
        $purged = 0;
        $files = glob($tempDir . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if (basename($file) === '.gitkeep' || basename($file) === '.htaccess' || is_dir($file)) continue;
            if ($now - filemtime($file) > $maxAgeSeconds) {
                if (@unlink($file)) {
                    $purged++;
                }
            }
        }
        return $purged;
    }
}
