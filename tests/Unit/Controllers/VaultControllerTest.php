<?php
/**
 * VaultController Micro-Unit Test
 * Tests VaultController personal and institutional vault view rendering, filtering, and delete operations
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/VaultController.php';

class TestableVaultController extends VaultController {
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

class VaultControllerTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        AuthService::initSession();
        $_SESSION = [];
    }

    public function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testIndexAsDeanRendersInstitutionalVault(): void {
        AuthService::attempt('dean', 'admin123');

        $controller = new TestableVaultController();
        $this->runControllerAction(fn() => $controller->index());

        $this->assertEquals('vault/index.php', $controller->renderedView);
        $this->assertTrue($controller->renderedData['isDean']);
        $this->assertEquals('Institutional Department Vault', $controller->renderedData['pageTitle']);
        $this->assertArrayHasKey('facultyMembers', $controller->renderedData);
        $this->assertArrayHasKey('certificates', $controller->renderedData);
    }

    public function testIndexAsFacultyRendersPersonalVault(): void {
        AuthService::attempt('mikhail', 'faculty123');

        $controller = new TestableVaultController();
        $this->runControllerAction(fn() => $controller->index());

        $this->assertEquals('vault/index.php', $controller->renderedView);
        $this->assertFalse($controller->renderedData['isDean']);
        $this->assertEquals('Faculty Personal Digital Vault', $controller->renderedData['pageTitle']);
        $this->assertArrayHasKey('certificates', $controller->renderedData);
    }

    public function testHandleDeleteCsrfViolationBlocksDeletion(): void {
        AuthService::attempt('mikhail', 'faculty123');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['action'] = 'delete';
        $_POST['certificate_id'] = '999';
        $_POST['csrf_token'] = 'invalid_csrf_token';

        $controller = new TestableVaultController();
        $this->runControllerAction(fn() => $controller->index());

        $this->assertEquals('vault.php?error=csrf_violation', $controller->redirectedTo);
    }
}
