<?php
/**
 * SecureFaculty Master Micro-Unit Test Runner
 * Discovers and executes all micro-unit test suites across Core, Models, DAO, Services, and Controllers.
 * Run inside container: docker exec securefaculty_app php tests/run_all_unit_tests.php
 */

require_once __DIR__ . '/TestCase.php';

$testFiles = [
    'Core' => [
        __DIR__ . '/Unit/Core/DatabaseTest.php',
    ],
    'Models' => [
        __DIR__ . '/Unit/Models/CertificateModelTest.php',
        __DIR__ . '/Unit/Models/UserModelTest.php',
    ],
    'Data Access Objects (DAO)' => [
        __DIR__ . '/Unit/DAO/UserDAOTest.php',
        __DIR__ . '/Unit/DAO/CertificateDAOTest.php',
        __DIR__ . '/Unit/DAO/AuditLogDAOTest.php',
    ],
    'Services' => [
        __DIR__ . '/Unit/Services/CryptoServiceTest.php',
        __DIR__ . '/Unit/Services/AuthServiceTest.php',
        __DIR__ . '/Unit/Services/AuditLoggerTest.php',
        __DIR__ . '/Unit/Services/CertificateServiceTest.php',
        __DIR__ . '/Unit/Services/UserServiceTest.php',
    ],
    'Controllers' => [
        __DIR__ . '/Unit/Controllers/BaseControllerTest.php',
        __DIR__ . '/Unit/Controllers/AuthControllerTest.php',
        __DIR__ . '/Unit/Controllers/DashboardControllerTest.php',
        __DIR__ . '/Unit/Controllers/VaultControllerTest.php',
        __DIR__ . '/Unit/Controllers/DocumentControllerTest.php',
        __DIR__ . '/Unit/Controllers/ExportControllerTest.php',
        __DIR__ . '/Unit/Controllers/UploadControllerTest.php',
        __DIR__ . '/Unit/Controllers/UserControllerTest.php',
        __DIR__ . '/Unit/Controllers/AuditControllerTest.php',
    ]
];

echo "========================================================================\n";
echo " SECUREFACULTY: EXHAUSTIVE MODULE-BY-MODULE MICRO-UNIT TEST SUITE\n";
echo " Executing granular micro-tests for every single file in the architecture\n";
echo "========================================================================\n\n";

$overallTests = 0;
$overallAssertions = 0;
$overallPassed = 0;
$overallFailed = 0;
$failures = [];
$suiteStart = microtime(true);

foreach ($testFiles as $moduleGroup => $files) {
    echo "------------------------------------------------------------------------\n";
    echo " MODULE LAYER: " . strtoupper($moduleGroup) . "\n";
    echo "------------------------------------------------------------------------\n";

    foreach ($files as $filePath) {
        $fileName = basename($filePath);
        require_once $filePath;

        $className = pathinfo($filePath, PATHINFO_FILENAME);
        if (!class_exists($className)) {
            echo "  [WARN] Test class {$className} not found in {$fileName}\n";
            continue;
        }

        $ref = new ReflectionClass($className);
        if ($ref->isAbstract()) continue;

        $methods = array_filter(
            $ref->getMethods(ReflectionMethod::IS_PUBLIC),
            fn($m) => str_starts_with($m->getName(), 'test')
        );

        echo "  [SUITE] {$className} (" . count($methods) . " tests):\n";

        // Call beforeClass
        if ($ref->hasMethod('beforeClass')) {
            $className::beforeClass();
        }

        foreach ($methods as $method) {
            $testName = $method->getName();
            $instance = new $className();
            $overallTests++;

            $testStart = microtime(true);
            try {
                $instance->setUp();
                $instance->$testName();
                $instance->tearDown();

                $duration = round((microtime(true) - $testStart) * 1000, 1);
                $assertions = $instance->getAssertionCount();
                $overallAssertions += $assertions;
                $overallPassed++;

                // Format human-friendly description from test name
                $friendly = preg_replace('/(?<!^)([A-Z])/', ' $1', substr($testName, 4));
                echo "    ✓ [PASS] {$friendly} ({$assertions} assertions, {$duration}ms)\n";
            } catch (Throwable $e) {
                try {
                    $instance->tearDown();
                } catch (Throwable $ignore) {}

                $overallFailed++;
                $failures[] = [
                    'class' => $className,
                    'method' => $testName,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
                echo "    ✗ [FAIL] {$testName}\n           ↳ {$e->getMessage()}\n";
            }
        }

        // Call afterClass
        if ($ref->hasMethod('afterClass')) {
            $className::afterClass();
        }
        echo "\n";
    }
}

$suiteElapsed = round((microtime(true) - $suiteStart) * 1000, 2);

echo "========================================================================\n";
echo " MICRO-UNIT TEST EXECUTION SUMMARY\n";
echo " Total Unit Tests:   {$overallTests}\n";
echo " Total Assertions:   {$overallAssertions}\n";
echo " Passed:             {$overallPassed}\n";
echo " Failed:             {$overallFailed}\n";
$rate = round(($overallPassed / max(1, $overallTests)) * 100, 1);
echo " Success Rate:       {$rate}%\n";
echo " Execution Time:     {$suiteElapsed} ms\n";
echo "========================================================================\n";

if ($overallFailed > 0) {
    echo "\nFAILURE DETAILS:\n";
    foreach ($failures as $f) {
        echo " - [{$f['class']}::{$f['method']}] {$f['message']}\n   at {$f['file']}:{$f['line']}\n";
    }
    exit(1);
}

exit(0);
