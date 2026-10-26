<?php
/**
 * UploadController Micro-Unit Test
 * Tests UploadController pipeline view rendering, upload API validation, CSRF checks, and commit API
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/UploadController.php';

class TestableUploadController extends UploadController {
    public ?string $renderedView = null;
    public array $renderedData = [];
    public ?array $jsonOutput = null;
    public int $jsonStatusCode = 200;

    protected function renderView(string $viewFile, array $data = []): void {
        $this->renderedView = $viewFile;
        $this->renderedData = $data;
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void {
        $this->jsonOutput = $data;
        $this->jsonStatusCode = $statusCode;
        throw new ControllerTerminatedException('json', $data, $statusCode);
    }
}

class UploadControllerTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        AuthService::initSession();
        $_SESSION = [];
    }

    public function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testIndexRendersUploadView(): void {
        AuthService::attempt('dean', 'admin123');

        $controller = new TestableUploadController();
        $this->runControllerAction(fn() => $controller->index());

        $this->assertEquals('upload/index.php', $controller->renderedView);
        $this->assertArrayHasKey('facultyList', $controller->renderedData);
        $this->assertEquals('pipeline', $controller->renderedData['activeNav']);
    }

    public function testHandleCommitApiRejectsNonPostMethod(): void {
        AuthService::attempt('dean', 'admin123');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $controller = new TestableUploadController();
        $this->runControllerAction(fn() => $controller->handleCommitApi());

        $this->assertEquals(405, $controller->jsonStatusCode);
        $this->assertEquals('error', $controller->jsonOutput['status']);
    }

    public function testHandleCommitApiRejectsInvalidCsrfToken(): void {
        AuthService::attempt('dean', 'admin123');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'temp_filename' => 'some_file.pdf',
            'csrf_token' => 'invalid_csrf_token_xyz'
        ];

        $controller = new TestableUploadController();
        $this->runControllerAction(fn() => $controller->handleCommitApi());

        $this->assertEquals(403, $controller->jsonStatusCode);
        $this->assertStringContains('CSRF', $controller->jsonOutput['message']);
    }
}
