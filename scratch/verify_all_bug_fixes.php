<?php
/**
 * Exhaustive Verification Suite for Security, Normalization, and Architecture Bug Fixes
 */

require_once __DIR__ . '/../app/bootstrap.php';

echo "=== STARTING VERIFICATION OF BUG FIXES ===\n\n";

$allPassed = true;
function assertTest($cond, $desc) {
    global $allPassed;
    if ($cond) {
        echo "[PASS] $desc\n";
    } else {
        echo "[FAIL] $desc\n";
        $allPassed = false;
    }
}

// ----------------------------------------------------
// TEST 1: DATE NORMALIZATION IN PHP
// ----------------------------------------------------
echo "--- 1. Testing PHP Date Normalization ---\n";
$cases = [
    '2023-10-24' => '2023-10-24',
    '24/10/2023' => '2023-10-24',
    '10/24/2023' => '2023-10-24',
    '24-10-2023' => '2023-10-24',
    '10-24-2023' => '2023-10-24',
    'October 24, 2023' => '2023-10-24',
    '24th day of October, 2023' => '2023-10-24',
    '15 December 2022' => '2022-12-15',
    'not a date' => null,
    '' => null,
    null => null
];

foreach ($cases as $in => $expected) {
    $out = CertificateModel::normalizeDate($in);
    assertTest($out === $expected, "PHP normalizeDate('" . ($in ?? 'NULL') . "') => " . ($out ?? 'NULL') . " (Expected: " . ($expected ?? 'NULL') . ")");
}

// ----------------------------------------------------
// TEST 2: DATE NORMALIZATION IN PYTHON
// ----------------------------------------------------
echo "\n--- 2. Testing Python Date Normalization ---\n";
$pythonCmd = 'python -c "
import sys, json
sys.path.insert(0, \'python_engine\')
from conversion_gate import normalize_date_iso

tests = [
    (\'2023-10-24\', \'2023-10-24\'),
    (\'24/10/2023\', \'2023-10-24\'),
    (\'10/24/2023\', \'2023-10-24\'),
    (\'24-10-2023\', \'2023-10-24\'),
    (\'10-24-2023\', \'2023-10-24\'),
    (\'October 24, 2023\', \'2023-10-24\'),
    (\'24th day of October, 2023\', \'2023-10-24\'),
    (\'invalid\', \'\')
]
res = []
for inp, exp in tests:
    actual = normalize_date_iso(inp)
    res.append((inp, actual, exp, actual == exp))
print(json.dumps(res))
"';
$pyOut = shell_exec($pythonCmd);
$pyResults = json_decode($pyOut, true);
if (is_array($pyResults)) {
    foreach ($pyResults as $r) {
        assertTest($r[3], "Python normalize_date_iso('{$r[0]}') => '{$r[1]}' (Expected: '{$r[2]}')");
    }
} else {
    assertTest(false, "Failed to execute Python normalization test: $pyOut");
}

// ----------------------------------------------------
// TEST 3: IDOR PREVENTION IN EXPORT & GETBYIDS
// ----------------------------------------------------
echo "\n--- 3. Testing IDOR Prevention in CertificateModel::getByIds ---\n";
// Let's inspect database records
$pdo = Database::getConnection();
$allCerts = $pdo->query("SELECT id, faculty_id, title FROM certificates LIMIT 5")->fetchAll();

if (!empty($allCerts)) {
    $firstCert = $allCerts[0];
    $certId = (int)$firstCert['id'];
    $ownerFacultyId = (int)$firstCert['faculty_id'];
    $otherFacultyId = ($ownerFacultyId === 2) ? 3 : 2;

    // Check Dean access (null facultyId)
    $deanFetched = CertificateModel::getByIds([$certId], null);
    assertTest(count($deanFetched) === 1, "Dean can access certificate #$certId across college");

    // Check Owner access
    $ownerFetched = CertificateModel::getByIds([$certId], $ownerFacultyId);
    assertTest(count($ownerFetched) === 1, "Owner faculty #$ownerFacultyId can access their own certificate #$certId");

    // Check Unauthorized Colleague access (simulate IDOR attack)
    $unauthFetched = CertificateModel::getByIds([$certId], $otherFacultyId);
    // Unless the other faculty happens to be a co-author:
    $isCo = CertificateDAO::isAuthorOrCoAuthor($certId, $otherFacultyId);
    if ($isCo) {
        assertTest(count($unauthFetched) === 1, "Co-author faculty #$otherFacultyId can legitimately access co-authored certificate #$certId");
    } else {
        assertTest(count($unauthFetched) === 0, "IDOR BLOCKED: Non-author faculty #$otherFacultyId CANNOT access certificate #$certId belonging to faculty #$ownerFacultyId");
    }
} else {
    echo "[INFO] No certificates found in database to test IDOR directly.\n";
}

// ----------------------------------------------------
// TEST 4: DUPLICATE DETECTION DAO & MODEL
// ----------------------------------------------------
echo "\n--- 4. Testing CertificateModel::findDuplicateByTitleAndDate ---\n";
if (!empty($allCerts)) {
    $targetCert = $pdo->query("SELECT faculty_id, title, date_issued FROM certificates WHERE date_issued IS NOT NULL LIMIT 1")->fetch();
    if ($targetCert) {
        $found = CertificateModel::findDuplicateByTitleAndDate((int)$targetCert['faculty_id'], $targetCert['title'], $targetCert['date_issued']);
        assertTest($found !== null, "findDuplicateByTitleAndDate correctly found existing record '{$targetCert['title']}'");

        $notFound = CertificateModel::findDuplicateByTitleAndDate((int)$targetCert['faculty_id'], 'Completely Non-Existent Title 9999999', $targetCert['date_issued']);
        assertTest($notFound === null, "findDuplicateByTitleAndDate returns null for distinct title");
    }
}

// ----------------------------------------------------
// TEST 5: FULL CONTROLLER RENDERS WITH ZERO NOTICES
// ----------------------------------------------------
echo "\n--- 5. Testing Full Controller Renders with All Changes ---\n";
ob_start();
require __DIR__ . '/test_all_renders.php';
$renderLogs = ob_get_clean();
assertTest(str_contains($renderLogs, 'ALL RENDERS PASSED WITH ZERO NOTICES/WARNINGS/ERRORS!'), "Full Dean & Faculty View Rendering produces 0 errors/notices/warnings");

echo "\n============================================\n";
if ($allPassed) {
    echo "ALL BUG FIX AUDIT TESTS PASSED SUCCESSFULLY! (100% HEALTHY)\n";
} else {
    echo "SOME AUDIT TESTS FAILED. CHECK LOGS ABOVE.\n";
}
echo "============================================\n";
