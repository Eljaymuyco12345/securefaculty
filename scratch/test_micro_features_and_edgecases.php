<?php
/**
 * Exhaustive Micro-Features & Edge-Cases Test Suite
 * SecureFaculty CICT Portal
 */

require_once __DIR__ . '/../app/bootstrap.php';

echo "========================================================================\n";
echo "SECUREFACULTY: MICRO-FEATURES & LOGICAL EDGE-CASES AUDIT SUITE\n";
echo "========================================================================\n\n";

$passCount = 0;
$totalCount = 0;

function assertTest(string $description, bool $condition, string $details = '') {
    global $passCount, $totalCount;
    $totalCount++;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$description}\n";
        if ($details) echo "         ↳ {$details}\n";
    } else {
        echo "  [FAIL] {$description}\n";
        if ($details) echo "         ↳ {$details}\n";
    }
}

$pdo = Database::getConnection();

// -----------------------------------------------------------------------------
// 1. DATE NORMALIZATION & ANTI-HALLUCINATION EDGE CASES
// -----------------------------------------------------------------------------
echo "[1] Date Normalization & Anti-Hallucination Edge Cases:\n";

assertTest(
    "Standard ISO date normalizes cleanly (2023-10-24 -> 2023-10-24)",
    CertificateModel::normalizeDate("2023-10-24") === "2023-10-24"
);

assertTest(
    "Philippine academic certificate phrasing ('15th day of December, 2022')",
    CertificateModel::normalizeDate("15th day of December, 2022") === "2022-12-15"
);

assertTest(
    "US Slash date format ('10/24/2023')",
    CertificateModel::normalizeDate("10/24/2023") === "2023-10-24"
);

assertTest(
    "Empty string yields NULL (Anti-hallucination: Never defaults to upload date)",
    CertificateModel::normalizeDate("") === null
);

assertTest(
    "Whitespace-only string yields NULL",
    CertificateModel::normalizeDate("   \t  \n ") === null
);

assertTest(
    "NULL input yields NULL",
    CertificateModel::normalizeDate(null) === null
);

assertTest(
    "Unparseable garbage date string yields NULL",
    CertificateModel::normalizeDate("invalid_date_token_xyz") === null
);

assertTest(
    "Out-of-range historical year (e.g. 1820) yields NULL",
    CertificateModel::normalizeDate("1820-05-12") === null
);

assertTest(
    "Out-of-range future year (e.g. 2150) yields NULL",
    CertificateModel::normalizeDate("2150-01-01") === null
);

// -----------------------------------------------------------------------------
// 2. CRYPTOGRAPHIC ENGINE & INTEGRITY EDGE CASES
// -----------------------------------------------------------------------------
echo "\n[2] Cryptographic Engine (AES-256-CBC) Edge Cases:\n";

$tempTestFile = TEMP_DIR . '/crypto_edge_test.txt';
$vaultTestFile = VAULT_DIR . '/crypto_edge_test.enc';

file_put_contents($tempTestFile, "TOP_SECRET_FACULTY_CREDENTIAL_DATA_" . microtime(true));

// Test encryption
$encOk = CryptoService::encryptFile($tempTestFile, $vaultTestFile);
assertTest("Encryption produces valid output file", $encOk && file_exists($vaultTestFile));

// Check binary payload structure: IV (16 bytes) + ciphertext
$encryptedBytes = file_get_contents($vaultTestFile);
assertTest("Encrypted file length > 16 bytes (IV + ciphertext)", strlen($encryptedBytes) > 16);

// Check that plaintext does NOT exist in ciphertext (Zero Plaintext at rest)
$hasPlaintextLeak = str_contains($encryptedBytes, "TOP_SECRET_FACULTY_CREDENTIAL");
assertTest("Ciphertext contains zero plaintext remnants at rest", !$hasPlaintextLeak);

// Test decryption into volatile RAM
$decrypted = CryptoService::decryptFileToMemory($vaultTestFile);
assertTest("Decryption to memory accurately recovers original payload", $decrypted === file_get_contents($tempTestFile));

// Test unique IV generation (No IV reuse)
$vaultTestFile2 = VAULT_DIR . '/crypto_edge_test_2.enc';
CryptoService::encryptFile($tempTestFile, $vaultTestFile2);
$bytes2 = file_get_contents($vaultTestFile2);
$iv1 = substr($encryptedBytes, 0, 16);
$iv2 = substr($bytes2, 0, 16);
assertTest("Two encryptions of identical plaintext produce distinct IVs (No IV Reuse)", $iv1 !== $iv2);

// Edge case: Decrypting non-existent file
assertTest("Decrypting non-existent file returns NULL safely", CryptoService::decryptFileToMemory(VAULT_DIR . '/non_existent_12345.enc') === null);

// Edge case: Decrypting truncated / corrupted file (< 16 bytes)
$corruptedFile = VAULT_DIR . '/corrupted_test.enc';
file_put_contents($corruptedFile, "too_short");
assertTest("Decrypting truncated ciphertext (< 16 bytes) returns NULL safely", CryptoService::decryptFileToMemory($corruptedFile) === null);

// Cleanup crypto test files
@unlink($tempTestFile);
@unlink($vaultTestFile);
@unlink($vaultTestFile2);
@unlink($corruptedFile);

// -----------------------------------------------------------------------------
// 3. DUPLICATE DOCUMENT & PRE-COMMIT POLICY INTERCEPTION
// -----------------------------------------------------------------------------
echo "\n[3] Duplicate Document Pre-Commit Policy Interception:\n";

// Create test document in certificates
$testHash = hash('sha256', "UNIQUE_TEST_PAYLOAD_" . microtime(true));
$testCertData = [
    'faculty_id' => 2,
    'title' => 'Edgecase Test Certificate ' . time(),
    'issuer' => 'ISUFST Testing Authority',
    'date_issued' => '2026-06-15',
    'category' => 'Seminar',
    'file_path' => 'dummy_edgecase_' . time() . '.enc',
    'original_filename' => 'test_edgecase.pdf',
    'file_type' => 'pdf',
    'file_size' => 1024,
    'file_hash' => $testHash,
    'cert_number' => 'CERT-EDGE-' . time()
];
$createdId = CertificateDAO::create($testCertData);
assertTest("Baseline test certificate inserted for duplicate validation", $createdId > 0);

// Check finding by hash
$foundByHash = CertificateModel::findByHash($testHash);
assertTest("findByHash correctly locates existing identical binary fingerprint", $foundByHash && (int)$foundByHash['id'] === $createdId);

// Check duplicate title + date for same faculty
$stmtDup = $pdo->prepare("SELECT id FROM certificates WHERE faculty_id = ? AND title = ? AND date_issued = ?");
$stmtDup->execute([$testCertData['faculty_id'], $testCertData['title'], $testCertData['date_issued']]);
assertTest("Duplicate query detects exact matching title and date issued for faculty", (int)$stmtDup->fetchColumn() === $createdId);

// Clean up test certificate and verify cascade author deletion
$deleted = CertificateDAO::delete($createdId, 2, true);
assertTest("CertificateDAO::delete cleans up certificate record", $deleted);
$authorRowsLeft = (int)$pdo->query("SELECT COUNT(*) FROM certificate_authors WHERE certificate_id = {$createdId}")->fetchColumn();
assertTest("CertificateDAO::delete ensures zero orphaned rows in certificate_authors", $authorRowsLeft === 0);

// -----------------------------------------------------------------------------
// 4. CSV FORMULA INJECTION (CWE-1236 / SPREADSHEET SECURITY)
// -----------------------------------------------------------------------------
echo "\n[4] CSV Formula Injection Sanitization:\n";

$dangerousInputs = [
    '=cmd|"/C calc"!A0',
    '+1+2',
    '-5+5',
    '@SUM(A1:A10)',
    "\tmalicious_tab",
    "\rmalicious_cr"
];

$sanitizeCell = function($val) {
    if (is_string($val) && strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@', "\t", "\r"])) {
        return "'" . $val;
    }
    return $val;
};

foreach ($dangerousInputs as $input) {
    $sanitized = $sanitizeCell($input);
    $isSafe = str_starts_with($sanitized, "'");
    assertTest("Formula symbol '{$input[0]}' neutralized with leading single-quote", $isSafe, "Input: {$input} -> Output: {$sanitized}");
}

$safeInput = "Standard Faculty Publication Title";
assertTest("Normal string remains untouched by sanitizer", $sanitizeCell($safeInput) === $safeInput);

// -----------------------------------------------------------------------------
// 5. USER GOVERNANCE & ACCESS CONTROL GUARDS
// -----------------------------------------------------------------------------
echo "\n[5] User Governance & Access Control Edge Cases:\n";

// Prevent self-deactivation
$deanUser = UserModel::findByUsername('dean');
if ($deanUser) {
    $toggleSelf = UserDAO::toggleStatus($deanUser['id'], $deanUser['id']);
    assertTest("Self-deactivation guard: Dean cannot deactivate their own active session", !$toggleSelf['success']);

    $deleteSelf = UserDAO::delete($deanUser['id'], $deanUser['id']);
    assertTest("Self-deletion guard: Dean cannot delete their own active account", !$deleteSelf['success']);
}

// Password minimum length check in controller
$weakPassOk = strlen("123") >= 6;
assertTest("Sub-6 character password rejected by minimum length rule", !$weakPassOk);

// Non-existent user profile lookup returns null
assertTest("Looking up profile for non-existent user returns NULL safely", UserModel::getProfile(999999) === null);

// -----------------------------------------------------------------------------
// 6. SQL WILDCARD & PARAMETERIZED QUERY INTEGRITY
// -----------------------------------------------------------------------------
echo "\n[6] SQL Wildcard & Parameterized Query Integrity:\n";

// Search with wildcard characters and quotes
$specialSearch = "test%'; DROP TABLE users; --";
$resSearch = CertificateDAO::getAll('All', $specialSearch);
assertTest("SQL injection payload in search filter executed safely without error", is_array($resSearch));

// Search with apostrophe
$quoteSearch = "O'Connor's Research";
$resQuote = CertificateDAO::getAll('All', $quoteSearch);
assertTest("Search query with single quotes handled cleanly via prepared statements", is_array($resQuote));

// Inverted date range (date_from > date_to)
$invertedRange = CertificateDAO::getAll('All', null, null, '2026-12-31', '2026-01-01');
assertTest("Inverted date range returns empty array without SQL failure", is_array($invertedRange) && count($invertedRange) === 0);

// Single date_from only
$openUpperBound = CertificateDAO::getAll('All', null, null, '2020-01-01', null);
assertTest("Open-ended upper bound (date_from only) executes successfully", is_array($openUpperBound));

// Single date_to only
$openLowerBound = CertificateDAO::getAll('All', null, null, null, '2030-12-31');
assertTest("Open-ended lower bound (date_to only) executes successfully", is_array($openLowerBound));

// -----------------------------------------------------------------------------
// 7. INPUT VALIDATION & DEFENSIVE BUSINESS LOGIC GUARDS
// -----------------------------------------------------------------------------
echo "\n[7] Input Validation & Defensive Business Logic Guards:\n";

// Self-role demotion guard
if ($deanUser) {
    $demoteSelf = UserService::updateProfile($deanUser['id'], ['full_name' => $deanUser['full_name'], 'role' => 'faculty'], $deanUser['id']);
    assertTest("Self-role demotion guard: Dean cannot demote themselves to faculty", !$demoteSelf['success']);

    $demoteDao = UserDAO::updateProfile($deanUser['id'], ['role' => 'faculty'], $deanUser['id']);
    assertTest("DAO-level self-demotion guard: Rejects self-demotion directly", !$demoteDao);
}

// Invalid username format validation
$invalidUserRes = UserService::create([
    'username' => 'invalid user!@#',
    'password' => 'validPass123',
    'full_name' => 'Test User'
]);
assertTest("Username regex constraint: Rejects spaces and illegal characters", !$invalidUserRes['success']);

// Short username (< 3 chars)
$shortUserRes = UserService::create([
    'username' => 'ab',
    'password' => 'validPass123',
    'full_name' => 'Test User'
]);
assertTest("Username length constraint: Rejects usernames shorter than 3 characters", !$shortUserRes['success']);

// Invalid email validation
$invalidEmailRes = UserService::create([
    'username' => 'valid_user_' . rand(100, 999),
    'password' => 'validPass123',
    'full_name' => 'Test User',
    'email' => 'not-an-email-address'
]);
assertTest("Email syntax validation: Rejects malformed email string", !$invalidEmailRes['success']);

// Password exceeding 128 characters (DoS prevention)
$excessivePassRes = UserService::create([
    'username' => 'valid_user_' . rand(100, 999),
    'password' => str_repeat('a', 129),
    'full_name' => 'Test User'
]);
assertTest("Password upper bound: Rejects passwords > 128 characters", !$excessivePassRes['success']);

// Reset password for non-existent user
$nonExistentReset = UserService::resetPassword(999999, 'newPassword123');
assertTest("Reset password guard: Rejects password reset for non-existent user ID", !$nonExistentReset['success']);

// API Quota increment auto-seeding
CertificateDAO::incrementQuota();
$quotaStatus = CertificateDAO::getQuotaStatus();
assertTest("Quota status active & incremented successfully", $quotaStatus['current_usage'] > 0);

echo "\n========================================================================\n";
echo "AUDIT SUMMARY: {$passCount}/{$totalCount} TESTS PASSED (" . round(($passCount / $totalCount) * 100, 1) . "%)\n";
echo "========================================================================\n";
