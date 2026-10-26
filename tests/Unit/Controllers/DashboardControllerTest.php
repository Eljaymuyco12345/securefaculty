<?php
/**
 * DashboardController Micro-Unit Test
 * Tests DashboardController administrative view rendering and data bundle preparation
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/DashboardController.php';

class TestableDashboardController extends DashboardController {
    public ?string $renderedView = null;
    public array $renderedData = [];

    protected function renderView(string $viewFile, array $data = []): void {
        $this->renderedView = $viewFile;
        $this->renderedData = $data;
    }
}

class DashboardControllerTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        AuthService::initSession();
        $_SESSION = [];
    }

    public function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testDashboardControllerIndexAsDean(): void {
        AuthService::attempt('dean', 'admin123');

        $controller = new TestableDashboardController();
        $controller->index();

        $this->assertEquals('dashboard/index.php', $controller->renderedView);
        $this->assertArrayHasKey('currentUser', $controller->renderedData);
        $this->assertArrayHasKey('quota', $controller->renderedData);
        $this->assertArrayHasKey('metrics', $controller->renderedData);
        $this->assertArrayHasKey('displayRows', $controller->renderedData);
        $this->assertArrayHasKey('pageTitle', $controller->renderedData);
        $this->assertEquals('dashboard', $controller->renderedData['activeNav']);
    }
}
