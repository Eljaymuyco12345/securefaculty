<?php
/**
 * AuthController Micro-Unit Test
 * Tests AuthController login portal rendering, credential submission, redirects, and session termination
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/AuthController.php';

class TestableAuthController extends AuthController {
    public ?string $redirectedTo = null;
    public ?string $renderedView = null;
    public array $renderedData = [];

    protected function redirect(string $url): void {
        $this->redirectedTo = $url;
        throw new ControllerTerminatedException('redirect', $url);
    }

    protected function renderView(string $viewFile, array $data = []): void {
        $this->renderedView = $viewFile;
        $this->renderedData = $data;
    }
}

class AuthControllerTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        AuthService::initSession();
        $_SESSION = [];
        $pdo = Database::getConnection();
        $pdo->exec("DELETE FROM audit_logs WHERE action IN ('LOGIN_FAILED', 'LOGIN_RATE_LIMITED', 'BRUTE_FORCE_LOCKOUT')");
    }

    public function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testShowLoginWhenLoggedOut(): void {
        $controller = new TestableAuthController();
        $_GET['error'] = 'test_error';
        $_GET['msg'] = 'test_msg';

        $this->runControllerAction(fn() => $controller->showLogin());

        $this->assertEquals('auth/login.php', $controller->renderedView);
        $this->assertEquals('test_error', $controller->renderedData['error']);
        $this->assertEquals('test_msg', $controller->renderedData['msg']);
        $this->assertNull($controller->redirectedTo);
    }

    public function testShowLoginWhenLoggedInAsDeanRedirectsToDashboard(): void {
        AuthService::attempt('dean', 'admin123');

        $controller = new TestableAuthController();
        $this->runControllerAction(fn() => $controller->showLogin());

        $this->assertEquals('dashboard.php', $controller->redirectedTo);
        $this->assertNull($controller->renderedView);
    }

    public function testShowLoginWhenLoggedInAsFacultyRedirectsToVault(): void {
        AuthService::attempt('mikhail', 'faculty123');

        $controller = new TestableAuthController();
        $this->runControllerAction(fn() => $controller->showLogin());

        $this->assertEquals('vault.php', $controller->redirectedTo);
        $this->assertNull($controller->renderedView);
    }

    public function testHandleLoginWithNonPostRedirectsToIndex(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $controller = new TestableAuthController();
        $this->runControllerAction(fn() => $controller->handleLogin());

        $this->assertEquals('index.php', $controller->redirectedTo);
    }

    public function testHandleLoginWithEmptyFieldsRedirectsWithError(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = '';
        $_POST['password'] = '';

        $controller = new TestableAuthController();
        $this->runControllerAction(fn() => $controller->handleLogin());

        $this->assertEquals('index.php?error=empty_fields', $controller->redirectedTo);
    }

    public function testHandleLoginSuccessAsDean(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'dean';
        $_POST['password'] = 'admin123';

        $controller = new TestableAuthController();
        $this->runControllerAction(fn() => $controller->handleLogin());

        $this->assertEquals('dashboard.php', $controller->redirectedTo);
        $this->assertTrue(AuthService::check());
    }

    public function testHandleLoginSuccessAsFaculty(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'mikhail';
        $_POST['password'] = 'faculty123';

        $controller = new TestableAuthController();
        $this->runControllerAction(fn() => $controller->handleLogin());

        $this->assertEquals('vault.php', $controller->redirectedTo);
        $this->assertTrue(AuthService::check());
    }

    public function testHandleLoginInvalidCredentials(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'dean';
        $_POST['password'] = 'WrongPassword';

        $controller = new TestableAuthController();
        $this->runControllerAction(fn() => $controller->handleLogin());

        $this->assertEquals('index.php?error=invalid_credentials', $controller->redirectedTo);
        $this->assertFalse(AuthService::check());
    }

    public function testLogout(): void {
        AuthService::attempt('dean', 'admin123');
        $this->assertTrue(AuthService::check());

        $controller = new TestableAuthController();
        $this->runControllerAction(fn() => $controller->logout());

        $this->assertEquals('index.php?msg=logged_out', $controller->redirectedTo);
        $this->assertFalse(AuthService::check());
    }
}
