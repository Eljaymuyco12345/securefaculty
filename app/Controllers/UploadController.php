<?php
/**
 * UploadController
 * Manages Document Ingestion Gateway, Conversion Gate, and Cryptographic Vault Commits
 * Controller Layer
 */

require_once __DIR__ . '/BaseController.php';

class UploadController extends BaseController {
    /**
     * Render the Smart Pipeline / Ingestion Gateway view
     */
    public function index(): void {
        AuthService::requireLogin();
        $currentUser = AuthService::user();
        $isDean = AuthService::isDean();

        $facultyList = UserModel::getFacultyOnly();

        $this->renderView('upload/index.php', [
            'currentUser' => $currentUser,
            'isDean' => $isDean,
            'facultyList' => $facultyList,
            'activeNav' => 'pipeline',
            'pageTitle' => 'Smart Pipeline - H-I-T-L Validation Loop'
        ]);
    }

    /**
     * Handle multipart upload API endpoint
     */
    public function handleUploadApi(): void {
        if (!headers_sent()) header('Content-Type: application/json');
        AuthService::initSession();
        if (!AuthService::check()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized session'], 401);
        }

        // Housekeeping: purge stale uncommitted staged files older than 1 hour
        CertificateService::purgeStaleTempFiles(3600);

        $pythonBin = defined('PYTHON_BIN') ? PYTHON_BIN : 'python';

        // Check for multiple files upload (e.g. multi-page photo compilation)
        $isMultiple = isset($_FILES['files']) && is_array($_FILES['files']['name']) && count($_FILES['files']['name']) > 1;

        if ($isMultiple) {
            $tempPaths = [];
            $count = count($_FILES['files']['name']);
            $firstOriginalName = basename($_FILES['files']['name'][0]);

            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $name = basename($_FILES['files']['name'][$i]);
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;

                // Server-side MIME validation (OWASP A04)
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = finfo_file($finfo, $_FILES['files']['tmp_name'][$i]);
                finfo_close($finfo);
                if (!in_array($detectedMime, ['image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png'])) continue;

                $tempName = bin2hex(random_bytes(10)) . "_page_{$i}.{$ext}";
                $dest = TEMP_DIR . DIRECTORY_SEPARATOR . $tempName;
                if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $dest)) {
                    $tempPaths[] = $dest;
                }
            }

            if (empty($tempPaths)) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Failed to stage uploaded image pages'], 400);
            }

            // Call Python conversion gate with --images
            $escapedPaths = array_map('escapeshellarg', $tempPaths);
            $cmd = sprintf(
                '%s %s --images %s --apikey %s 2>&1',
                escapeshellcmd($pythonBin),
                escapeshellarg(PYTHON_ENGINE_PATH),
                implode(' ', $escapedPaths),
                escapeshellarg(OCR_API_KEY)
            );

            $output = shell_exec($cmd);
            $result = json_decode($output, true);

            if ($result && isset($result['status']) && $result['status'] === 'success') {
                $compiledFilename = $result['compiled_pdf'];
                $compiledPath = TEMP_DIR . DIRECTORY_SEPARATOR . $compiledFilename;
                $cleanBaseName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($firstOriginalName, PATHINFO_FILENAME));
                $displayDocName = $cleanBaseName . "_Compiled_({$count}_pages).pdf";

                if (!empty($result['quota_consumed']) && $result['quota_consumed'] > 0) {
                    CertificateModel::incrementQuota();
                }

                AuditLogger::log('OCR_EXTRACT', "Conversion Gate compiled {$count} image pages into multi-page PDF: {$displayDocName}");

                $this->jsonResponse([
                    'status' => 'success',
                    'temp_filename' => $compiledFilename,
                    'original_name' => $displayDocName,
                    'file_type' => 'pdf',
                    'file_size' => file_exists($compiledPath) ? filesize($compiledPath) : 1024,
                    'metadata' => $result['metadata'],
                    'raw_text' => $result['raw_text'] ?? '',
                    'page_count' => $result['page_count'] ?? $count,
                    'ocr_used' => $result['ocr_used'] ?? false
                ]);
            } else {
                $errMsg = $result['message'] ?? 'Conversion Gate failed to compile multi-page image upload';
                $this->jsonResponse(['status' => 'error', 'message' => $errMsg], 400);
            }
        }

        // Single file upload fallback
        if (!isset($_FILES['file'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'No file received in upload stream'], 400);
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Upload error code: ' . $file['error']], 400);
        }

        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'zip'];

        if (!in_array($extension, $allowed)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unsupported format. Allowed: JPG, PNG, PDF, DOCX, ZIP'], 400);
        }

        // Server-side MIME inspection (OWASP A04/A08)
        $allowedMimes = [
            'jpg'  => ['image/jpeg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/pjpeg'],
            'png'  => ['image/png', 'image/x-png'],
            'pdf'  => ['application/pdf', 'application/x-pdf'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream', 'application/msword'],
            'zip'  => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed', 'application/octet-stream']
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $expectedMimes = $allowedMimes[$extension] ?? [];
        if (!in_array($detectedMime, $expectedMimes)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => "Cryptographic validation failed: File signature ({$detectedMime}) does not match declared .{$extension} extension."
            ], 400);
        }

        // Generate secure temp filename
        $tempId = bin2hex(random_bytes(16));
        $tempFilename = $tempId . '.' . $extension;
        $tempFilePath = TEMP_DIR . DIRECTORY_SEPARATOR . $tempFilename;

        if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Failed to stage file in Conversion Gate'], 500);
        }

        // Branch: ZIP Archive Batch Upload
        if ($extension === 'zip') {
            $cmd = sprintf(
                '%s %s --zip %s --outdir %s --apikey %s 2>&1',
                escapeshellcmd($pythonBin),
                escapeshellarg(PYTHON_ENGINE_PATH),
                escapeshellarg($tempFilePath),
                escapeshellarg(TEMP_DIR),
                escapeshellarg(OCR_API_KEY)
            );

            $output = shell_exec($cmd);
            $result = json_decode($output, true);
            @unlink($tempFilePath);

            if (!$result || !isset($result['status']) || $result['status'] !== 'success') {
                $errMsg = $result['message'] ?? 'ZIP extraction or batch parsing failed';
                $this->jsonResponse(['status' => 'error', 'message' => $errMsg], 400);
            }

            CertificateModel::createBatch([
                'batch_uuid' => $result['batch_uuid'],
                'user_id' => AuthService::user()['id'],
                'total_files' => $result['total_files'],
                'processed_files' => $result['total_files'],
                'success_files' => $result['success_files'],
                'failed_files' => $result['failed_files'],
                'log_summary' => "Unpacked {$result['total_files']} files from {$originalName}"
            ]);

            AuditLogger::log('BATCH_UPLOAD', "Batch ZIP unpacked and processed: {$originalName} ({$result['success_files']} valid files)");

            $this->jsonResponse([
                'status' => 'success',
                'is_batch' => true,
                'batch_uuid' => $result['batch_uuid'],
                'original_name' => $originalName,
                'total_files' => $result['total_files'],
                'success_files' => $result['success_files'],
                'failed_files' => $result['failed_files'],
                'items' => $result['items']
            ]);
        }

        // Single File Processing
        $fileHash = hash_file('sha256', $tempFilePath);
        $existingRecord = CertificateModel::findByHash($fileHash);

        $cmd = sprintf(
            '%s %s --file %s --apikey %s 2>&1',
            escapeshellcmd($pythonBin),
            escapeshellarg(PYTHON_ENGINE_PATH),
            escapeshellarg($tempFilePath),
            escapeshellarg(OCR_API_KEY)
        );

        $output = shell_exec($cmd);
        $result = json_decode($output, true);

        if (!$result || !isset($result['status']) || $result['status'] !== 'success') {
            $rawMessage = $result['message'] ?? ($output ? substr($output, 0, 300) : 'Python engine execution failed');
            $this->jsonResponse([
                'status' => 'partial_success',
                'temp_filename' => $tempFilename,
                'original_name' => $originalName,
                'file_type' => $extension,
                'file_size' => filesize($tempFilePath),
                'file_hash' => $fileHash,
                'is_duplicate' => ($existingRecord !== null),
                'duplicate_info' => $existingRecord ? "Exact copy exists: '{$existingRecord['title']}' for {$existingRecord['faculty_name']}" : null,
                'metadata' => [
                    'title' => pathinfo($originalName, PATHINFO_FILENAME),
                    'issuer' => 'CICI Department / External Agency',
                    'date_issued' => '', // Strict: never default to upload date
                    'category' => 'Seminar',
                    'confidence' => 0.50
                ],
                'raw_text' => 'Fallback parsed from filename: ' . $originalName,
                'note' => 'Conversion Gate fallback used: ' . $rawMessage
            ]);
        }

        if (!empty($result['quota_consumed']) && $result['quota_consumed'] > 0) {
            CertificateModel::incrementQuota();
        }

        AuditLogger::log('OCR_EXTRACT', "Conversion Gate & OCR processed asset: {$originalName} ({$extension})");

        $duplicateWarning = null;
        if ($existingRecord !== null) {
            $duplicateWarning = [
                'type' => 'exact_file',
                'message' => "Exact identical file exists in vault: '{$existingRecord['title']}' for {$existingRecord['faculty_name']} (ID #{$existingRecord['id']})"
            ];
        } elseif (!empty($result['metadata']['title']) && !empty($result['metadata']['date_issued'])) {
            $normDate = CertificateModel::normalizeDate($result['metadata']['date_issued']);
            if ($normDate) {
                $sameRec = CertificateModel::findDuplicateByTitleAndDate(AuthService::user()['id'], trim($result['metadata']['title']), $normDate);
                if ($sameRec) {
                    $duplicateWarning = [
                        'type' => 'metadata_match',
                        'message' => "A certificate record with matching Title ('{$sameRec['title']}') and Issued Date already exists in the vault (ID #{$sameRec['id']})."
                    ];
                }
            }
        }

        $this->jsonResponse([
            'status' => 'success',
            'temp_filename' => $tempFilename,
            'original_name' => $originalName,
            'file_type' => $extension,
            'file_size' => filesize($tempFilePath),
            'file_hash' => $fileHash,
            'is_duplicate' => ($existingRecord !== null),
            'duplicate_info' => $existingRecord ? "Exact copy exists in vault: '{$existingRecord['title']}' for {$existingRecord['faculty_name']}" : null,
            'duplicate_warning' => $duplicateWarning,
            'metadata' => $result['metadata'],
            'raw_text' => $result['raw_text'] ?? '',
            'ocr_used' => $result['ocr_used'] ?? false
        ]);
    }

    /**
     * Handle commit and encryption API endpoint
     */
    public function handleCommitApi(): void {
        if (!headers_sent()) header('Content-Type: application/json');
        AuthService::initSession();
        if (!AuthService::check()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized session'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        if (!$input) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid JSON payload'], 400);
        }

        // CSRF Verification (OWASP A01)
        $csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!AuthService::verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'CSRF Security Violation: Invalid or expired token.'], 403);
        }

        $tempFilename = basename($input['temp_filename'] ?? '');
        $tempFilePath = TEMP_DIR . DIRECTORY_SEPARATOR . $tempFilename;

        if (empty($tempFilename)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Missing staged filename'], 400);
        }

        if (!file_exists($tempFilePath) && str_starts_with($tempFilename, 'sample_demo')) {
            @file_put_contents($tempFilePath, "%PDF-1.4\n% Demo Certificate for SecureFaculty Smart Pipeline\n" . date('Y-m-d H:i:s'));
        }

        if (!file_exists($tempFilePath)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Temporary staged file not found or expired'], 404);
        }

        $fileHash = hash_file('sha256', $tempFilePath);
        $currentUser = AuthService::user();

        $assignedFacultyId = (AuthService::isDean() && (!empty($input['assigned_faculty_id']) || !empty($input['faculty_id'])))
            ? (int)($input['assigned_faculty_id'] ?? $input['faculty_id'])
            : $currentUser['id'];

        // Validate assigned faculty exists to prevent foreign key integrity failure
        if (!UserModel::find($assignedFacultyId)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Specified faculty member does not exist in registry.'], 400);
        }

        // Normalize authentic certificate date
        $normalizedDate = !empty($input['date_issued']) ? CertificateModel::normalizeDate($input['date_issued']) : null;

        // Safe string clamping to prevent database column truncation crashes (VARCHAR boundaries)
        $title = mb_substr(trim($input['title'] ?? 'Untitled Document'), 0, 255);
        if ($title === '') {
            $title = 'Untitled Document';
        }
        $issuer = mb_substr(trim($input['issuer'] ?? 'Not Specified'), 0, 255);
        $category = mb_substr(trim($input['category'] ?? 'Seminar'), 0, 100);
        $certNumber = !empty($input['cert_number']) ? mb_substr(trim($input['cert_number']), 0, 100) : null;
        $originalFilename = mb_substr(basename($input['original_name'] ?? 'certificate.pdf'), 0, 255);

        // Strict Duplicate Pre-Commit Interception (OWASP A04 / Integrity Assurance)
        $forceDuplicate = !empty($input['force_duplicate']);
        if (!$forceDuplicate) {
            $existing = CertificateModel::findByHash($fileHash);
            if ($existing) {
                $this->jsonResponse([
                    'status' => 'duplicate_detected',
                    'message' => "Policy Intercept: An identical file already exists in the institutional vault: '{$existing['title']}' (Vault ID #{$existing['id']}). If you intend to archive this as an alternate version, check the override checkbox in the form."
                ], 409);
            }

            if (!empty($input['title']) && $normalizedDate !== null) {
                $sameRecord = CertificateModel::findDuplicateByTitleAndDate($assignedFacultyId, $title, $normalizedDate);
                if ($sameRecord) {
                    $this->jsonResponse([
                        'status' => 'duplicate_detected',
                        'message' => "Policy Intercept: A certificate record with matching Title ('{$sameRecord['title']}') and Issued Date already exists in the vault (ID #{$sameRecord['id']}). Check the override checkbox if this is an intentional revision."
                    ], 409);
                }
            }
        }

        $vaultFilename = bin2hex(random_bytes(16)) . '.enc';
        $vaultFilePath = VAULT_DIR . DIRECTORY_SEPARATOR . $vaultFilename;

        if (!CryptoService::encryptFile($tempFilePath, $vaultFilePath)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Cryptographic Failure: AES-256 encryption operation failed'], 500);
        }

        // Clean up temporary plaintext staged file and intermediate derivatives (Zero Plaintext Persistence)
        @unlink($tempFilePath);
        $baseTempName = pathinfo($tempFilePath, PATHINFO_FILENAME);
        foreach (['_opt.jpg', '_page1.png', '_opt.png', '_compiled.pdf'] as $deriv) {
            $derivPath = TEMP_DIR . DIRECTORY_SEPARATOR . $baseTempName . $deriv;
            if (file_exists($derivPath)) {
                @unlink($derivPath);
            }
        }

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
            $certId = CertificateModel::create($certData, $coAuthorIds);
        } catch (Throwable $e) {
            AuditLogger::log('COMMIT_ERROR', "Failed to commit certificate: " . $e->getMessage(), $assignedFacultyId);
            $this->jsonResponse(['status' => 'error', 'message' => 'Database commit failure: ' . $e->getMessage()], 500);
        }

        AuditLogger::log('COMMIT_VAULT_RECORD', "Approved and AES-256 encrypted certificate #{$certId}: '{$certData['title']}'", $certId);

        $this->jsonResponse([
            'status' => 'success',
            'message' => 'Record encrypted and safely archived in vault',
            'certificate_id' => $certId,
            'file_hash' => $fileHash
        ]);
    }
}
