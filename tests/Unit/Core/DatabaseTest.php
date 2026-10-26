<?php
/**
 * Database Micro-Unit Test
 * Tests Core\Database singleton, PDO connection attributes, and SQL execution
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Core/Database.php';

class DatabaseTest extends TestCase {
    public function testGetConnectionReturnsPdoInstance(): void {
        $pdo = Database::getConnection();
        $this->assertNotNull($pdo, "Database connection must not be null");
        $this->assertTrue($pdo instanceof PDO, "Connection must be a PDO instance");
    }

    public function testSingletonPatternReturnsIdenticalInstance(): void {
        $pdo1 = Database::getConnection();
        $pdo2 = Database::getConnection();
        $this->assertSame($pdo1, $pdo2, "Database::getConnection must return the same singleton instance");
    }

    public function testPdoErrorModeIsException(): void {
        $pdo = Database::getConnection();
        $errMode = $pdo->getAttribute(PDO::ATTR_ERRMODE);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $errMode, "PDO error mode must be ERRMODE_EXCEPTION");
    }

    public function testPdoDefaultFetchModeIsAssoc(): void {
        $pdo = Database::getConnection();
        $fetchMode = $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
        $this->assertEquals(PDO::FETCH_ASSOC, $fetchMode, "PDO default fetch mode must be FETCH_ASSOC");
    }

    public function testDatabaseServerVersionQuery(): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT 1 as test_col");
        $row = $stmt->fetch();
        $this->assertArrayHasKey('test_col', $row, "Query result must contain test_col");
        $this->assertEquals(1, $row['test_col'], "Query result value must equal 1");
    }
}
