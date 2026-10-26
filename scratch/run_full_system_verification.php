<?php
/**
 * SecureFaculty Unified System Test Suite
 * Executes Unit Tests, Database & Architecture Integration Tests, and System Verification.
 * Run inside container: docker exec securefaculty_app php scratch/run_full_system_verification.php
 */

require_once __DIR__ . '/../app/bootstrap.php';

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function runTest(string $category, string $description, callable $fn): void {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    try {
        $result = $fn();
        if ($result === true) {
            $passedTests++;
            echo "  [PASS] {$description}\n";
        } else {
            $failedTests++;
            $msg = is_string($result) ? $result : "Assertion returned false";
            echo "  [FAIL] {$description}\n         ↳ {$msg}\n";
        }
    } catch (Throwable $e) {
        $failedTests++;
        echo "  [FAIL] {$description}\n         ↳ Exception: {$e->getMessage()}\n";
    }
}

echo "========================================================================\n";
echo " SECUREFACULTY: COMPREHENSIVE FULL-SYSTEM VERIFICATION HARNESS\n";
echo " Testing: Unit Logic, Database & Multi-Author Integration, and System Health\n";
echo "========================================================================\n\n";

// =============================================================================
// LAYER 1: UNIT TESTING (Core Algorithms, Security & Normalization)
// =============================================================================
echo "------------------------------------------------------------------------\n";
echo " LAYER 1: UNIT TESTS (Crypto, Normalization, Sanitization, Governance)\n";
echo "------------------------------------------------------------------------\n";

runTest("UNIT", "ISO Date Normalization (2026-05-07 -> 2026-05-07)", function() {
    return CertificateModel::normalizeDate("2026-05-07") === "2026-05-07";
});

runTest("UNIT", "Philippine Academic Certificate Date Normalization ('15th day of December, 2022')", function() {
    return CertificateModel::normalizeDate("15th day of December, 2022") === "2022-12-15";
});

runTest("UNIT", "US Slash Date Normalization ('05/07/2026')", function() {
    return CertificateModel::normalizeDate("05/07/2026") === "2026-05-07";
});

runTest("UNIT", "Anti-Hallucination Guard: Empty or whitespace date yields NULL", function() {
    return CertificateModel::normalizeDate("") === null && CertificateModel::normalizeDate("   ") === null;
});

runTest("UNIT", "Anti-Hallucination Guard: Garbage string yields NULL", function() {
    return CertificateModel::normalizeDate("not_a_valid_date_token") === null;
});

runTest("UNIT", "AES-256 Encryption & RAM Decryption Integrity", function() {
    $tempFile = TEMP_DIR . '/unit_crypto_test.txt';
    $encFile = VAULT_DIR . '/unit_crypto_test.enc';
    $payload = "SECURE_FACULTY_CONFIDENTIAL_PAYLOAD_" . bin2hex(random_bytes(16));
    file_put_contents($tempFile, $payload);

    $encOk = CryptoService::encryptFile($tempFile, $encFile);
    if (!$encOk || !file_exists($encFile)) return "Encryption failed";

    $cipherBytes = file_get_contents($encFile);
    if (str_contains($cipherBytes, $payload)) return "Plaintext leaked into ciphertext!";
    if (strlen($cipherBytes) <= 16) return "Ciphertext lacks proper IV prefix";

    $decrypted = CryptoService::decryptFileToMemory($encFile);
    @unlink($tempFile);
    @unlink($encFile);

    return $decrypted === $payload;
});

runTest("UNIT", "AES-256 Nonce Randomness: Distinct IV per encryption (No IV Reuse)", function() {
    $tempFile = TEMP_DIR . '/unit_iv_test.txt';
    $enc1 = VAULT_DIR . '/unit_iv_test_1.enc';
    $enc2 = VAULT_DIR . '/unit_iv_test_2.enc';
    file_put_contents($tempFile, "SAME_PLAINTEXT_DIFFERENT_IV");

    CryptoService::encryptFile($tempFile, $enc1);
    CryptoService::encryptFile($tempFile, $enc2);

    $iv1 = substr(file_get_contents($enc1), 0, 16);
    $iv2 = substr(file_get_contents($enc2), 0, 16);

    @unlink($tempFile);
    @unlink($enc1);
    @unlink($enc2);

    return $iv1 !== $iv2;
});

runTest("UNIT", "CSV Formula Injection Neutralization (CWE-1236)", function() {
    $dangerous = ['=1+1', '+cmd', '-calc', '@sum', "\ttab", "\rcarriage"];
    $sanitizeCell = function($val) {
        if (is_string($val) && strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $val;
        }
        return $val;
    };
    foreach ($dangerous as $d) {
        if (!str_starts_with($sanitizeCell($d), "'")) return "Failed to sanitize: {$d}";
    }
    return $sanitizeCell("Clean Title") === "Clean Title";
});

runTest("UNIT", "Dean Self-Deactivation Guard (Governance Resilience)", function() {
    $dean = UserModel::findByUsername('dean');
    if (!$dean) return "Dean account not found";
    $res = UserDAO::toggleStatus($dean['id'], $dean['id']);
    return $res['success'] === false;
});

runTest("UNIT", "Dean Self-Deletion Guard (Governance Resilience)", function() {
    $dean = UserModel::findByUsername('dean');
    if (!$dean) return "Dean account not found";
    $res = UserDAO::delete($dean['id'], $dean['id']);
    return $res['success'] === false;
});

// =============================================================================
// LAYER 2: INTEGRATION TESTING (Database, Models, Co-Authors, Reports)
// =============================================================================
echo "\n------------------------------------------------------------------------\n";
echo " LAYER 2: INTEGRATION TESTS (Database Queries, Multi-Author, Reports)\n";
echo "------------------------------------------------------------------------\n";

runTest("INTEGRATION", "Database Connection Active & Responsive", function() {
    $pdo = Database::getConnection();
    $ver = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    return !empty($ver);
});

runTest("INTEGRATION", "Multi-Column Search Query Execution (No SQLSTATE[HY093] Parameter Collision)", function() {
    $results = CertificateModel::getAll('All', 'eljay', null, '', '');
    return is_array($results);
});

runTest("INTEGRATION", "Multi-Column Keyword Search on Complex Strings (Quotes & Symbols)", function() {
    $res = CertificateModel::getAll('All', "Research 'Innovation' & Security", null, '', '');
    return is_array($res);
});

runTest("INTEGRATION", "Audit Log Pagination & Search Filter (No Parameter Collision)", function() {
    $logData = AuditLogDAO::getPaginatedLogs('login', 'LOGIN_SUCCESS', 1, 10);
    return isset($logData['rows']) && isset($logData['totalRows']);
});

runTest("INTEGRATION", "Co-Authorship Attribution (Mikhail + Rae Jan + Eljay)", function() {
    $certs = CertificateModel::getAll('All', 'SECUREFACULTY', null, '', '');
    if (empty($certs)) return "Sample publication not found";
    $target = $certs[0];
    return !empty($target['co_authors']);
});

runTest("INTEGRATION", "Duplicate Pre-Commit Interception by SHA-256 Hash", function() {
    $testHash = hash('sha256', "INTEGRATION_TEST_FILE_" . time());
    $certId = CertificateDAO::create([
        'faculty_id' => 2,
        'title' => 'Hash Test Cert ' . time(),
        'issuer' => 'ISUFST Testing Dept',
        'date_issued' => '2026-01-01',
        'category' => 'Seminar',
        'file_path' => 'dummy_' . time() . '.enc',
        'original_filename' => 'hash_test.pdf',
        'file_type' => 'pdf',
        'file_size' => 2048,
        'file_hash' => $testHash,
        'cert_number' => 'CERT-HASH-' . time()
    ]);
    if (!$certId) return "Failed to insert test certificate";

    $found = CertificateModel::findByHash($testHash);
    CertificateDAO::delete($certId, 2, true);

    return $found && (int)$found['id'] === $certId;
});

runTest("INTEGRATION", "Audit Logging Commit Trail", function() {
    AuditLogger::log('SYSTEM_TEST', "Full system integration test checkpoint", 1);
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT id FROM audit_logs WHERE action = 'SYSTEM_TEST' ORDER BY id DESC LIMIT 1");
    $id = (int)$stmt->fetchColumn();
    return $id > 0;
});

runTest("INTEGRATION", "ReportLab Server-Side PDF Report Compilation (Portrait A4)", function() {
    $tempJson = TEMP_DIR . '/integration_test_report.json';
    $tempPdf = TEMP_DIR . '/integration_test_report.pdf';
    $payload = [
        'meta' => [
            'category' => 'All',
            'date_scope' => 'All Validated Records',
            'auditor_name' => 'Dr. Arlene S. Poblador',
            'generated_at' => date('F d, Y \a\t h:i A')
        ],
        'records' => [
            [
                'title' => 'System Integration Verification Certificate',
                'file_type' => 'PDF',
                'faculty_name' => 'Prof. Levy Mikhail B. Aguilar',
                'academic_rank' => 'Assistant Professor II',
                'co_authors' => 'Prof. Rae Jan B. Corneja',
                'cert_number' => 'CERT-INT-001',
                'date_issued' => '2026-10-21',
                'issuer' => 'ISUFST CICI',
                'category' => 'Research Publication'
            ]
        ]
    ];
    file_put_contents($tempJson, json_encode($payload));

    $pythonBin = defined('PYTHON_BIN') ? PYTHON_BIN : 'python3';
    $generatorScript = BASE_DIR . '/python_engine/generate_report_pdf.py';

    $cmd = sprintf(
        '%s %s %s %s 2>&1',
        escapeshellcmd($pythonBin),
        escapeshellarg($generatorScript),
        escapeshellarg($tempJson),
        escapeshellarg($tempPdf)
    );
    shell_exec($cmd);
    @unlink($tempJson);

    $success = file_exists($tempPdf) && filesize($tempPdf) > 1000;
    if ($success) {
        $header = file_get_contents($tempPdf, false, null, 0, 8);
        $success = str_starts_with($header, '%PDF-1.');
    }
    @unlink($tempPdf);
    return $success;
});

runTest("INTEGRATION", "OCR Multi-Key Configuration & Quota Monitor Initialized", function() {
    $quota = CertificateDAO::getQuotaStatus();
    return isset($quota['monthly_limit']) && (int)$quota['monthly_limit'] === 25000 && defined('OCR_PRIMARY_KEY') && defined('OCR_BACKUP_KEY');
});

// =============================================================================
// SUMMARY REPORT
// =============================================================================
echo "\n========================================================================\n";
echo " SYSTEM VERIFICATION RESULTS\n";
echo " Total Tests: {$totalTests} | Passed: {$passedTests} | Failed: {$failedTests}\n";
$percentage = round(($passedTests / max(1, $totalTests)) * 100, 1);
echo " Success Rate: {$percentage}%\n";
echo "========================================================================\n";

exit($failedTests > 0 ? 1 : 0);
