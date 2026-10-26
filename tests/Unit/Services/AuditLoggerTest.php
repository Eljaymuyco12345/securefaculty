<?php
/**
 * AuditLogger Micro-Unit Test
 * Tests AuditLogger service, IP resolution logic, and delegation to DAO
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Services/AuditLogger.php';

class AuditLoggerTest extends TestCase {
    public function testGetClientIpDirect(): void {
        $_SERVER['REMOTE_ADDR'] = '203.177.100.5';
        $ip = AuditLogger::getClientIp();
        $this->assertEquals('203.177.100.5', $ip);
    }

    public function testGetClientIpBehindTrustedProxy(): void {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '120.28.1.1, 10.0.0.1';
        $ip = AuditLogger::getClientIp();
        $this->assertEquals('120.28.1.1', $ip, "Must extract the first client IP from X-Forwarded-For when coming from trusted proxy");
    }

    public function testAuditLoggerDelegation(): void {
        $unique = 'LOGGER_TEST_' . bin2hex(random_bytes(4));
        AuditLogger::log($unique, "Test logger payload", 1);

        $recent = AuditLogger::getRecentLogs(1, $unique);
        $this->assertCount(1, $recent);
        $this->assertEquals($unique, $recent[0]['action']);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE action = ?");
        $stmt->execute([$unique]);
    }
}
