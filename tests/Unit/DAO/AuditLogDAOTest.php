<?php
/**
 * AuditLogDAO Micro-Unit Test
 * Tests AuditLogDAO immutable logging, pagination algorithms, search filtering, and query integrity.
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/DAO/AuditLogDAO.php';

class AuditLogDAOTest extends TestCase {
    private string $uniqueAction = '';

    public function setUp(): void {
        parent::setUp();
        $this->uniqueAction = 'TEST_AUDIT_' . bin2hex(random_bytes(4));
    }

    public function tearDown(): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE action = ?");
        $stmt->execute([$this->uniqueAction]);
        parent::tearDown();
    }

    public function testInsertAndRetrieveRecentLogs(): void {
        AuditLogDAO::insertLog(
            1,
            'dean',
            'dean',
            $this->uniqueAction,
            2,
            'Unit test audit insertion payload',
            '192.168.1.100',
            'TestBrowser/1.0'
        );

        $recent = AuditLogDAO::getRecentLogs(5, $this->uniqueAction);
        $this->assertCount(1, $recent);

        $log = $recent[0];
        $this->assertEquals($this->uniqueAction, $log['action']);
        $this->assertEquals('dean', $log['username']);
        $this->assertEquals('192.168.1.100', $log['ip_address']);
        $this->assertEquals('Unit test audit insertion payload', $log['details']);
    }

    public function testPaginationAndSearchFilter(): void {
        // Insert 3 test entries
        for ($i = 1; $i <= 3; $i++) {
            AuditLogDAO::insertLog(
                1,
                'dean',
                'dean',
                $this->uniqueAction,
                null,
                "Entry item #{$i} unique_marker_xyz",
                '127.0.0.1',
                'CLI-Unit-Tester'
            );
        }

        // Search with unique marker
        $res = AuditLogDAO::getPaginatedLogs('unique_marker_xyz', $this->uniqueAction, 1, 2);
        $this->assertEquals(3, $res['totalRows'], "Total rows must be 3");
        $this->assertEquals(2, $res['totalPages'], "With perPage=2, 3 items yield 2 pages");
        $this->assertCount(2, $res['rows'], "First page must contain 2 rows");

        // Fetch page 2
        $resPage2 = AuditLogDAO::getPaginatedLogs('unique_marker_xyz', $this->uniqueAction, 2, 2);
        $this->assertCount(1, $resPage2['rows'], "Second page must contain 1 row");
    }

    public function testGetExportLogs(): void {
        AuditLogDAO::insertLog(
            2,
            'mikhail',
            'faculty',
            $this->uniqueAction,
            null,
            'Export test row',
            '10.0.0.5',
            'Exporter/1.0'
        );

        $exportRows = AuditLogDAO::getExportLogs('Export test row', $this->uniqueAction);
        $this->assertGreaterThan(0, count($exportRows));
        $this->assertEquals($this->uniqueAction, $exportRows[0]['action']);
        $this->assertArrayHasKey('faculty_display', $exportRows[0]);
    }
}
