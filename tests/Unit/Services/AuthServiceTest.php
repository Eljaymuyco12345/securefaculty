<?php
/**
 * AuthService Micro-Unit Test
 * Tests authentication attempts, session lifecycle, CSRF tokens, and RBAC roles
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Services/AuthService.php';

class AuthServiceTest extends TestCase {
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

    public function testCsrfTokenGenerationAndVerification(): void {
        $token = AuthService::getCsrfToken();
        $this->assertTrue(is_string($token));
        $this->assertEquals(64, strlen($token), "CSRF token must be a 64-character hexadecimal string");

        $this->assertTrue(AuthService::verifyCsrfToken($token), "Valid token must verify");
        $this->assertFalse(AuthService::verifyCsrfToken("invalid_token_xyz"), "Wrong token must fail verification");
        $this->assertFalse(AuthService::verifyCsrfToken(""), "Empty token must fail verification");
        $this->assertFalse(AuthService::verifyCsrfToken(null), "Null token must fail verification");
    }

    public function testSuccessfulDeanAuthenticationAndRoleFlags(): void {
        $success = AuthService::attempt('dean', 'admin123');
        $this->assertTrue($success, "Dean authentication with valid password must succeed");

        $this->assertTrue(AuthService::check(), "AuthService::check() must be true after login");
        $this->assertTrue(AuthService::isDean(), "Dean account must report isDean() = true");
        $this->assertFalse(AuthService::isFaculty(), "Dean account must report isFaculty() = false");

        $user = AuthService::user();
        $this->assertNotNull($user);
        $this->assertEquals('dean', $user['username']);
        $this->assertEquals('dean', $user['role']);
    }

    public function testSuccessfulFacultyAuthenticationAndRoleFlags(): void {
        $success = AuthService::attempt('mikhail', 'faculty123');
        $this->assertTrue($success, "Faculty authentication must succeed");

        $this->assertTrue(AuthService::check());
        $this->assertTrue(AuthService::isFaculty(), "Faculty account must report isFaculty() = true");
        $this->assertFalse(AuthService::isDean(), "Faculty account must report isDean() = false");

        $user = AuthService::user();
        $this->assertNotNull($user);
        $this->assertEquals('mikhail', $user['username']);
        $this->assertEquals('faculty', $user['role']);
    }

    public function testInvalidCredentialsFails(): void {
        $success = AuthService::attempt('dean', 'WrongPassword123');
        $this->assertFalse($success, "Invalid password must be rejected");
        $this->assertFalse(AuthService::check(), "check() must remain false");
        $this->assertEquals('invalid_credentials', $_SESSION['login_error'] ?? null);
    }

    public function testLogoutClearsSession(): void {
        AuthService::attempt('dean', 'admin123');
        $this->assertTrue(AuthService::check());

        AuthService::logout();
        $this->assertFalse(AuthService::check(), "check() must be false after logout");
        $this->assertNull(AuthService::user(), "user() must be null after logout");
    }
}
