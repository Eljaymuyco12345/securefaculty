<?php
/**
 * UserController Micro-Unit Test
 * Tests UserController faculty directory view, API routing, CSRF enforcement, and user administration
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/UserController.php';

class TestableUserController extends UserController {
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

class UserControllerTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        AuthService::initSession();
        $_SESSION = [];
    }

    public function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testIndexRendersUsersViewAsDean(): void {
        AuthService::attempt('dean', 'admin123');

        $controller = new TestableUserController();
        $this->runControllerAction(fn() => $controller->index());

        $this->assertEquals('users/index.php', $controller->renderedView);
        $this->assertArrayHasKey('users', $controller->renderedData);
        $this->assertArrayHasKey('totalUsers', $controller->renderedData);
        $this->assertArrayHasKey('activeUsers', $controller->renderedData);
        $this->assertEquals('users', $controller->renderedData['activeNav']);
    }

    public function testHandleApiRejectsUnauthenticatedRequest(): void {
        $controller = new TestableUserController();
        $this->runControllerAction(fn() => $controller->handleApi());

        $this->assertEquals(403, $controller->jsonStatusCode);
        $this->assertEquals('error', $controller->jsonOutput['status']);
    }

    public function testHandleApiRejectsNonPostMethod(): void {
        AuthService::attempt('dean', 'admin123');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $controller = new TestableUserController();
        $this->runControllerAction(fn() => $controller->handleApi());

        $this->assertEquals(405, $controller->jsonStatusCode);
    }

    public function testHandleApiRejectsInvalidCsrfToken(): void {
        AuthService::attempt('dean', 'admin123');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'create',
            'csrf_token' => 'invalid_csrf'
        ];

        $controller = new TestableUserController();
        $this->runControllerAction(fn() => $controller->handleApi());

        $this->assertEquals(403, $controller->jsonStatusCode);
        $this->assertStringContains('CSRF', $controller->jsonOutput['message']);
    }

    public function testHandleApiInvalidAction(): void {
        AuthService::attempt('dean', 'admin123');
        $csrf = AuthService::getCsrfToken();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'invalid_unknown_action',
            'csrf_token' => $csrf
        ];

        $controller = new TestableUserController();
        $this->runControllerAction(fn() => $controller->handleApi());

        $this->assertEquals(400, $controller->jsonStatusCode);
        $this->assertEquals('Invalid action', $controller->jsonOutput['message']);
    }

    public function testHandleApiSwitchProfileDeanToFaculty(): void {
        AuthService::attempt('dean', 'admin123');
        $this->assertTrue(AuthService::isDean());
        $csrf = AuthService::getCsrfToken();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'switch_profile',
            'target_user' => 'mikhail',
            'csrf_token' => $csrf
        ];

        $controller = new TestableUserController();
        $this->runControllerAction(fn() => $controller->handleApi());

        $this->assertEquals(200, $controller->jsonStatusCode);
        $this->assertEquals('success', $controller->jsonOutput['status']);
        $this->assertEquals('vault.php', $controller->jsonOutput['redirect']);
        $this->assertEquals('mikhail', AuthService::user()['username']);
        $this->assertTrue(AuthService::isFaculty());
    }

    public function testHandleApiSwitchProfileFacultyToDean(): void {
        AuthService::attempt('mikhail', 'faculty123');
        $this->assertTrue(AuthService::isFaculty());
        $csrf = AuthService::getCsrfToken();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'switch_profile',
            'target_user' => 'dean',
            'csrf_token' => $csrf
        ];

        $controller = new TestableUserController();
        $this->runControllerAction(fn() => $controller->handleApi());

        $this->assertEquals(200, $controller->jsonStatusCode);
        $this->assertEquals('success', $controller->jsonOutput['status']);
        $this->assertEquals('dashboard.php', $controller->jsonOutput['redirect']);
        $this->assertEquals('dean', AuthService::user()['username']);
        $this->assertTrue(AuthService::isDean());
    }

    public function testHandleApiSwitchProfileInvalidTargetRejects400(): void {
        AuthService::attempt('dean', 'admin123');
        $csrf = AuthService::getCsrfToken();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'switch_profile',
            'target_user' => 'invalid_hacker_target',
            'csrf_token' => $csrf
        ];

        $controller = new TestableUserController();
        $this->runControllerAction(fn() => $controller->handleApi());

        $this->assertEquals(400, $controller->jsonStatusCode);
        $this->assertEquals('error', $controller->jsonOutput['status']);
        $this->assertStringContains('Invalid target profile', $controller->jsonOutput['message']);
    }
}
