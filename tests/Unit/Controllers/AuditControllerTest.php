<?php
/**
 * AuditController Micro-Unit Test
 * Tests AuditController ledger rendering, statistics aggregation, and compliance reporting
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/AuditController.php';

class TestableAuditController extends AuditController {
    public ?string $renderedView = null;
    public array $renderedData = [];

    protected function renderView(string $viewFile, array $data = []): void {
        $this->renderedView = $viewFile;
        $this->renderedData = $data;
    }
}

class AuditControllerTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        AuthService::initSession();
        $_SESSION = [];
    }

    public function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testIndexRendersAuditViewForDean(): void {
        AuthService::attempt('dean', 'admin123');

        $controller = new TestableAuditController();
        $_GET['format'] = 'html';
        $controller->index();

        $this->assertEquals('audit/index.php', $controller->renderedView);
        $this->assertArrayHasKey('displayLogs', $controller->renderedData);
        $this->assertArrayHasKey('totalLogs', $controller->renderedData);
        $this->assertArrayHasKey('statTotal', $controller->renderedData);
        $this->assertArrayHasKey('statLogins', $controller->renderedData);
        $this->assertArrayHasKey('statDocs', $controller->renderedData);
        $this->assertArrayHasKey('statAlerts', $controller->renderedData);
        $this->assertArrayHasKey('facultyLookup', $controller->renderedData);
        $this->assertEquals('audit', $controller->renderedData['activeNav']);
    }
}
