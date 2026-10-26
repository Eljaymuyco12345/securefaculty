<?php
/**
 * ExportController Micro-Unit Test
 * Tests ExportController accreditation ledger rendering, export preparation, and parameter scoping
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/ExportController.php';

class TestableExportController extends ExportController {
    public ?string $renderedView = null;
    public array $renderedData = [];

    protected function renderView(string $viewFile, array $data = []): void {
        $this->renderedView = $viewFile;
        $this->renderedData = $data;
    }
}

class ExportControllerTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        AuthService::initSession();
        $_SESSION = [];
    }

    public function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testIndexRendersExportViewForDean(): void {
        AuthService::attempt('dean', 'admin123');

        $controller = new TestableExportController();
        $_GET['format'] = 'html';
        $controller->index();

        $this->assertEquals('export/index.php', $controller->renderedView);
        $this->assertTrue($controller->renderedData['isDean']);
        $this->assertArrayHasKey('records', $controller->renderedData);
        $this->assertArrayHasKey('currentUser', $controller->renderedData);
        $this->assertEquals('records', $controller->renderedData['activeNav']);
    }

    public function testIndexRendersExportViewForFaculty(): void {
        AuthService::attempt('mikhail', 'faculty123');

        $controller = new TestableExportController();
        $_GET['format'] = 'html';
        $controller->index();

        $this->assertEquals('export/index.php', $controller->renderedView);
        $this->assertFalse($controller->renderedData['isDean']);
        $this->assertArrayHasKey('records', $controller->renderedData);
    }
}
