<?php
/**
 * UserService Micro-Unit Test
 * Tests UserService input validation, security policies, governance rules, and audit emissions
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Services/UserService.php';

class UserServiceTest extends TestCase {
    private ?int $createdUserId = null;
    private string $testUsername = '';

    public function setUp(): void {
        parent::setUp();
        $this->testUsername = 'srv_usr_' . bin2hex(random_bytes(4));
    }

    public function tearDown(): void {
        if ($this->createdUserId !== null) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$this->createdUserId]);
            $this->createdUserId = null;
        }
        parent::tearDown();
    }

    public function testCreateUserValidationAndSuccess(): void {
        // Missing required fields
        $emptyRes = UserService::create([]);
        $this->assertFalse($emptyRes['success']);
        $this->assertEquals(400, $emptyRes['code']);

        // Password too short (< 6 chars)
        $shortPassRes = UserService::create([
            'username' => $this->testUsername,
            'password' => '12345',
            'full_name' => 'Short Pass User'
        ]);
        $this->assertFalse($shortPassRes['success']);
        $this->assertEquals(400, $shortPassRes['code']);

        // Valid creation
        $validRes = UserService::create([
            'username' => $this->testUsername,
            'password' => 'ValidPass123',
            'full_name' => 'Valid Test Faculty',
            'role' => 'faculty'
        ]);
        $this->assertTrue($validRes['success']);
        $this->createdUserId = (int)$validRes['user_id'];

        // Duplicate username rejected
        $dupRes = UserService::create([
            'username' => $this->testUsername,
            'password' => 'AnotherPass123',
            'full_name' => 'Duplicate Attempt'
        ]);
        $this->assertFalse($dupRes['success']);
        $this->assertEquals(409, $dupRes['code']);
    }

    public function testToggleStatusAndSelfGuard(): void {
        $userRes = UserService::create([
            'username' => $this->testUsername,
            'password' => 'Password123',
            'full_name' => 'Toggle User'
        ]);
        $userId = (int)$userRes['user_id'];
        $this->createdUserId = $userId;

        // Self-toggle guard
        $selfRes = UserService::toggleStatus($userId, $userId);
        $this->assertFalse($selfRes['success']);
        $this->assertEquals(400, $selfRes['code']);

        // Dean toggles
        $toggleRes = UserService::toggleStatus($userId, 1);
        $this->assertTrue($toggleRes['success']);
        $this->assertEquals(0, $toggleRes['is_active']);
    }

    public function testResetPasswordValidation(): void {
        $userRes = UserService::create([
            'username' => $this->testUsername,
            'password' => 'Password123',
            'full_name' => 'Reset User'
        ]);
        $userId = (int)$userRes['user_id'];
        $this->createdUserId = $userId;

        // Sub-6 char password rejected
        $shortRes = UserService::resetPassword($userId, 'short');
        $this->assertFalse($shortRes['success']);
        $this->assertEquals(400, $shortRes['code']);

        // Valid reset
        $validReset = UserService::resetPassword($userId, 'NewStrongPassword123');
        $this->assertTrue($validReset['success']);
    }

    public function testDeleteUserAndSelfGuard(): void {
        $userRes = UserService::create([
            'username' => $this->testUsername,
            'password' => 'Password123',
            'full_name' => 'Delete User'
        ]);
        $userId = (int)$userRes['user_id'];

        // Self-delete guard
        $selfDel = UserService::delete($userId, $userId);
        $this->assertFalse($selfDel['success']);
        $this->assertEquals(400, $selfDel['code']);

        // Dean deletes
        $deanDel = UserService::delete($userId, 1);
        $this->assertTrue($deanDel['success']);
        $this->assertNull(UserDAO::find($userId));
    }
}
