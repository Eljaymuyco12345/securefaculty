<?php
/**
 * PasswordResetService & EmailService Micro-Unit Tests
 */

require_once __DIR__ . '/../../TestCase.php';

class PasswordResetServiceTest extends TestCase {
    private ?int $testUserId = null;
    private string $testEmail = 'otp.test.faculty@isufst.edu.ph';

    public function setUp(): void {
        parent::setUp();
        $pdo = Database::getConnection();
        $this->testUserId = UserDAO::create([
            'username' => 'otp_test_' . uniqid(),
            'password' => 'initial123',
            'role' => 'faculty',
            'full_name' => 'OTP Test Faculty',
            'email' => $this->testEmail,
            'department' => 'CICI'
        ]);
    }

    public function tearDown(): void {
        if ($this->testUserId) {
            $pdo = Database::getConnection();
            $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$this->testUserId]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$this->testUserId]);
        }
        parent::tearDown();
    }

    public function testEmailServicePlaceholdersAreDetectedAsUnconfigured(): void {
        $this->assertFalse(EmailService::isConfigured());
        $res = EmailService::sendOtpEmail($this->testEmail, 'OTP Test Faculty', '123456');
        $this->assertFalse($res['success']);
        $this->assertStringContains('not configured', $res['message']);
    }

    public function testEmailServiceBuildOtpHtmlEscapesAndFormats(): void {
        $html = EmailService::buildOtpHtml('<script>alert(1)</script>', '654321', 15);
        $this->assertStringContains('654321', $html);
        $this->assertStringContains('15 minutes', $html);
        $this->assertFalse(str_contains($html, '<script>alert(1)</script>'));
    }

    public function testChangePasswordValidationAndSuccess(): void {
        // Mismatch
        $r1 = PasswordResetService::changePassword($this->testUserId, 'initial123', 'newpass123', 'different');
        $this->assertFalse($r1['success']);
        $this->assertEquals(400, $r1['code']);

        // Wrong current password
        $r2 = PasswordResetService::changePassword($this->testUserId, 'wrongpass', 'newpass123', 'newpass123');
        $this->assertFalse($r2['success']);
        $this->assertEquals(401, $r2['code']);

        // Valid change
        $r3 = PasswordResetService::changePassword($this->testUserId, 'initial123', 'newpass123', 'newpass123');
        $this->assertTrue($r3['success']);

        $updated = UserDAO::find($this->testUserId);
        $this->assertTrue(password_verify('newpass123', $updated['password_hash']));
    }

    public function testRequestOtpUnknownEmailReturnsGenericSuccess(): void {
        $res = PasswordResetService::requestOtp('nonexistent.user@isufst.edu.ph');
        $this->assertTrue($res['success']);
        $this->assertStringContains('If an active account', $res['message']);
    }

    public function testVerifyOtpAndResetFlow(): void {
        $pdo = Database::getConnection();
        $otp = '482910';
        $hash = hash('sha256', $otp);
        $expiresAt = date('Y-m-d H:i:s', time() + 900);

        $pdo->prepare("INSERT INTO password_resets (user_id, email, token_hash, attempts, expires_at) VALUES (?, ?, ?, 0, ?)")
            ->execute([$this->testUserId, $this->testEmail, $hash, $expiresAt]);

        // Wrong OTP increments attempts
        $bad = PasswordResetService::verifyOtpAndReset($this->testEmail, '000000', 'resetpass99', 'resetpass99');
        $this->assertFalse($bad['success']);
        $this->assertStringContains('Incorrect', $bad['message']);

        // Valid OTP resets password and marks used_at
        $good = PasswordResetService::verifyOtpAndReset($this->testEmail, $otp, 'resetpass99', 'resetpass99');
        $this->assertTrue($good['success']);

        $updated = UserDAO::find($this->testUserId);
        $this->assertTrue(password_verify('resetpass99', $updated['password_hash']));

        // Reusing same OTP fails
        $reuse = PasswordResetService::verifyOtpAndReset($this->testEmail, $otp, 'another123', 'another123');
        $this->assertFalse($reuse['success']);
    }
}
