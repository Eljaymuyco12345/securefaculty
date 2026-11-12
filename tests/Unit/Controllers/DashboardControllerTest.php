<?php
/**
 * DashboardController Micro-Unit Test
 * Tests DashboardController administrative view rendering, quota metrics, and Plan B backup download streaming
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/DashboardController.php';

class TestableDashboardController extends DashboardController {
    public ?string $renderedView = null;
    public array $renderedData = [];
    public ?string $redirectedTo = null;
    public ?string $streamedFilePath = null;

    protected function renderView(string $viewFile, array $data = []): void {
        $this->renderedView = $viewFile;
        $this->renderedData = $data;
    }

    protected function redirect(string $url): void {
        $this->redirectedTo = $url;
        throw new ControllerTerminatedException('redirect', $url);
    }

    protected function streamFile(string $filepath): void {
        $this->streamedFilePath = $filepath;
        throw new ControllerTerminatedException('download', $filepath);
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

    public function testBackupActionGeneratesAndStreamsFileAsDean(): void {
        AuthService::attempt('dean', 'admin123');

        $controller = new TestableDashboardController();
        $this->runControllerAction(fn() => $controller->backup());

        $this->assertNotNull($controller->streamedFilePath, "Backup controller should stream the generated snapshot file");
        $this->assertTrue(file_exists($controller->streamedFilePath), "Streamed file must exist on disk");
        $this->assertGreaterThan(0, filesize($controller->streamedFilePath), "Streamed file must not be empty");
    }
}
