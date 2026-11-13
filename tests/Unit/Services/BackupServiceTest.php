<?php
/**
 * BackupService Micro-Unit Test
 * Validates Plan B Disaster Recovery & Snapshot Engine, Housekeeping, Pruning, and Audit Logging
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Services/BackupService.php';
require_once __DIR__ . '/../../../app/Services/AuditLogger.php';
require_once __DIR__ . '/../../../app/Services/AuthService.php';

class BackupServiceTest extends TestCase {
    private string $testBackupDir;

    public function setUp(): void {
        parent::setUp();
        $this->testBackupDir = STORAGE_DIR . DIRECTORY_SEPARATOR . 'test_backups_' . bin2hex(random_bytes(4));
        if (!is_dir($this->testBackupDir)) {
            mkdir($this->testBackupDir, 0755, true);
        }
    }

    public function tearDown(): void {
        if (is_dir($this->testBackupDir)) {
            $files = glob($this->testBackupDir . DIRECTORY_SEPARATOR . '*') ?: [];
            foreach ($files as $f) {
                if (is_file($f)) {
                    @unlink($f);
                }
            }
            @rmdir($this->testBackupDir);
        }
        parent::tearDown();
    }

    public function testCreateBackupGeneratesValidSnapshot(): void {
        $result = BackupService::createBackup();

        $this->assertTrue($result['success'], "BackupService::createBackup() should return success = true");
        $this->assertTrue(!empty($result['filename']), "Snapshot filename must not be empty");
        $this->assertMatchesRegularExpression(
            '/^securefaculty_planb_snapshot_\d{8}_\d{6}\.(sqlite|sql)$/',
            $result['filename'],
            "Snapshot filename must follow the standard naming convention"
        );

        $this->assertTrue(file_exists($result['filepath']), "Generated backup file must exist on disk");
        $this->assertGreaterThan(0, filesize($result['filepath']), "Generated backup file must not be empty (0 bytes)");
        $this->assertGreaterThan(0.0, $result['size_kb'], "Reported size_kb must be greater than zero");

        // Verify that DATABASE_BACKUP_CREATED was recorded in audit_logs
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT details FROM audit_logs WHERE action = 'DATABASE_BACKUP_CREATED' ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $logDetails = $stmt->fetchColumn();

        $this->assertTrue(!empty($logDetails), "Audit log must contain DATABASE_BACKUP_CREATED record");
        $this->assertStringContains($result['filename'], (string)$logDetails, "Audit log details must reference the generated backup filename");
    }

    public function testPruneOldBackupsKeepsOnlyLimit(): void {
        $createdFiles = [];
        // Create 15 dummy backup files with staggered timestamps
        for ($i = 1; $i <= 15; $i++) {
            $pad = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            $filePath = $this->testBackupDir . DIRECTORY_SEPARATOR . "securefaculty_planb_snapshot_20261112_1000{$pad}.sqlite";
            file_put_contents($filePath, "DUMMY_SNAPSHOT_DATA_{$i}");
            // Set explicit modification time to ensure deterministic ordering
            touch($filePath, 1700000000 + ($i * 100));
            $createdFiles[] = $filePath;
        }

        $allBefore = glob($this->testBackupDir . DIRECTORY_SEPARATOR . 'securefaculty_planb_snapshot_*.*') ?: [];
        $this->assertCount(15, $allBefore, "Should have created exactly 15 test backup files");

        // Prune down to 10 most recent
        BackupService::pruneOldBackups($this->testBackupDir, 10);

        $remainingFiles = glob($this->testBackupDir . DIRECTORY_SEPARATOR . 'securefaculty_planb_snapshot_*.*') ?: [];
        $this->assertCount(10, $remainingFiles, "Pruning must retain exactly 10 backups");

        // Verify the 5 oldest files were deleted
        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(file_exists($createdFiles[$i]), "Oldest file {$createdFiles[$i]} should have been pruned");
        }

        // Verify the 10 newest files still exist
        for ($i = 5; $i < 15; $i++) {
            $this->assertTrue(file_exists($createdFiles[$i]), "Recent file {$createdFiles[$i]} should be preserved");
        }
    }

    public function testPruneOldBackupsSafeOnEmptyOrMissingDirectory(): void {
        // Must execute cleanly without fatal TypeError in PHP 8
        BackupService::pruneOldBackups($this->testBackupDir . DIRECTORY_SEPARATOR . 'non_existent_subdir', 10);
        $this->assertTrue(true, "Pruning non-existent directory handled safely without throwing fatal error");
    }

    public function testAuditLoggingOnBackupFailure(): void {
        $pdo = Database::getConnection();
        AuditLogger::log('BACKUP_FAILED', 'Plan B database backup failed: Simulated test failure');

        $stmt = $pdo->prepare("SELECT details FROM audit_logs WHERE action = 'BACKUP_FAILED' ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $details = $stmt->fetchColumn();

        $this->assertTrue(!empty($details), "Audit log must contain BACKUP_FAILED record");
        $this->assertStringContains('Simulated test failure', (string)$details, "Audit log details must capture the failure reason");
    }
}
