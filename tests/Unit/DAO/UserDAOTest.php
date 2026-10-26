<?php
/**
 * UserDAO Micro-Unit Test
 * Tests UserDAO concrete methods, database transactions, profile management, and CRUD lifecycle
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/DAO/UserDAO.php';

class UserDAOTest extends TestCase {
    private ?int $testUserId = null;
    private string $testUsername = '';

    public function setUp(): void {
        parent::setUp();
        $this->testUsername = 'test_user_' . bin2hex(random_bytes(4));
    }

    public function tearDown(): void {
        if ($this->testUserId !== null) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$this->testUserId]);
            $this->testUserId = null;
        }
        parent::tearDown();
    }

    public function testCreateUserAndRetrieveProfile(): void {
        $userData = [
            'username' => $this->testUsername,
            'password' => 'SecurePass123!',
            'role' => 'faculty',
            'full_name' => 'Dr. Test Unit Faculty',
            'email' => "{$this->testUsername}@isufst.edu.ph",
            'department' => 'CICI',
            'employee_id' => 'EMP-' . rand(1000, 9999),
            'academic_rank' => 'Assistant Professor I',
            'specialization' => 'Cybersecurity'
        ];

        $userId = UserDAO::create($userData);
        $this->testUserId = $userId;
        $this->assertGreaterThan(0, $userId, "Created user ID must be positive");

        $found = UserDAO::find($userId);
        $this->assertNotNull($found, "Created user must be found by ID");
        $this->assertEquals($this->testUsername, $found['username']);
        $this->assertEquals($userData['full_name'], $found['full_name']);
        $this->assertEquals($userData['academic_rank'], $found['academic_rank']);
        $this->assertEquals($userData['specialization'], $found['specialization']);
    }

    public function testToggleStatusAndGuard(): void {
        $userId = UserDAO::create([
            'username' => $this->testUsername,
            'password' => 'SecurePass123!',
            'role' => 'faculty',
            'full_name' => 'Toggle Test User'
        ]);
        $this->testUserId = $userId;

        // Self-toggle guard check
        $selfRes = UserDAO::toggleStatus($userId, $userId);
        $this->assertFalse($selfRes['success'], "User cannot toggle their own account");

        // Toggle by admin (ID 1)
        $toggle1 = UserDAO::toggleStatus($userId, 1);
        $this->assertTrue($toggle1['success']);
        $this->assertEquals(0, $toggle1['is_active'], "Status must transition to 0");

        $toggle2 = UserDAO::toggleStatus($userId, 1);
        $this->assertTrue($toggle2['success']);
        $this->assertEquals(1, $toggle2['is_active'], "Status must transition back to 1");
    }

    public function testResetPassword(): void {
        $userId = UserDAO::create([
            'username' => $this->testUsername,
            'password' => 'InitialPassword123',
            'role' => 'faculty',
            'full_name' => 'Reset Test User'
        ]);
        $this->testUserId = $userId;

        $newPassword = 'BrandNewPassword456!';
        $resetOk = UserDAO::resetPassword($userId, $newPassword);
        $this->assertTrue($resetOk);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        $this->assertTrue(password_verify($newPassword, $hash), "New password must verify against stored bcrypt hash");
        $this->assertFalse(password_verify('InitialPassword123', $hash), "Old password must no longer verify");
    }

    public function testUpdateProfile(): void {
        $userId = UserDAO::create([
            'username' => $this->testUsername,
            'password' => 'Password123',
            'role' => 'faculty',
            'full_name' => 'Before Update',
            'academic_rank' => 'Instructor I'
        ]);
        $this->testUserId = $userId;

        $updateData = [
            'full_name' => 'After Update Prof',
            'email' => 'updated@isufst.edu.ph',
            'role' => 'faculty',
            'academic_rank' => 'Associate Professor IV',
            'employee_id' => 'EMP-UPDATED-99',
            'specialization' => 'Artificial Intelligence'
        ];

        $updated = UserDAO::updateProfile($userId, $updateData);
        $this->assertTrue($updated);

        $fresh = UserDAO::find($userId);
        $this->assertEquals('After Update Prof', $fresh['full_name']);
        $this->assertEquals('updated@isufst.edu.ph', $fresh['email']);
        $this->assertEquals('Associate Professor IV', $fresh['academic_rank']);
        $this->assertEquals('Artificial Intelligence', $fresh['specialization']);
    }

    public function testDeleteUserAndGuard(): void {
        $userId = UserDAO::create([
            'username' => $this->testUsername,
            'password' => 'Password123',
            'role' => 'faculty',
            'full_name' => 'Delete Test User'
        ]);

        // Self-delete guard
        $selfDel = UserDAO::delete($userId, $userId);
        $this->assertFalse($selfDel['success'], "Cannot delete own active session");

        // Delete by admin
        $del = UserDAO::delete($userId, 1);
        $this->assertTrue($del['success']);

        $after = UserDAO::find($userId);
        $this->assertNull($after, "Deleted user must no longer exist in DB");
    }
}
