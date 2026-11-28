<?php
/**
 * AvatarService Micro-Unit Test
 * Verifies secure image validation, upload, replacement cleanup, and removal
 */

require_once __DIR__ . '/../../TestCase.php';

class AvatarServiceTest extends TestCase {
    private ?int $testUserId = null;
    private string $tempPngPath = '';
    private string $tempFakePath = '';

    public function setUp(): void {
        parent::setUp();
        $this->testUserId = UserDAO::create([
            'username' => 'avatar_test_' . uniqid(),
            'password' => 'secret123',
            'role' => 'faculty',
            'full_name' => 'Avatar Test User',
            'email' => 'avatar.test@isufst.edu.ph',
            'department' => 'CICI'
        ]);

        // Minimal valid 1x1 PNG binary
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        $this->tempPngPath = TEMP_DIR . DIRECTORY_SEPARATOR . 'test_avatar_' . uniqid() . '.png';
        file_put_contents($this->tempPngPath, $pngBytes);

        $this->tempFakePath = TEMP_DIR . DIRECTORY_SEPARATOR . 'test_fake_' . uniqid() . '.png';
        file_put_contents($this->tempFakePath, '<?php echo "not an image"; ?>');
    }

    public function tearDown(): void {
        if ($this->testUserId) {
            AvatarService::remove($this->testUserId);
            $pdo = Database::getConnection();
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$this->testUserId]);
        }
        if (file_exists($this->tempPngPath)) @unlink($this->tempPngPath);
        if (file_exists($this->tempFakePath)) @unlink($this->tempFakePath);
        parent::tearDown();
    }

    public function testRejectsNonImageUpload(): void {
        $res = AvatarService::upload($this->testUserId, [
            'name' => 'fake.png',
            'tmp_name' => $this->tempFakePath,
            'size' => filesize($this->tempFakePath),
            'error' => UPLOAD_ERR_OK
        ]);
        $this->assertFalse($res['success']);
        $this->assertEquals(400, $res['code']);
    }

    public function testUploadReplaceAndRemoveAvatar(): void {
        $res1 = AvatarService::upload($this->testUserId, [
            'name' => 'photo1.png',
            'tmp_name' => $this->tempPngPath,
            'size' => filesize($this->tempPngPath),
            'error' => UPLOAD_ERR_OK
        ]);
        $this->assertTrue($res1['success']);
        $path1 = BASE_DIR . '/public/' . $res1['avatar_path'];
        $this->assertTrue(is_file($path1));

        $user1 = UserDAO::find($this->testUserId);
        $this->assertEquals($res1['avatar_path'], $user1['avatar_path']);

        // Upload replacement — old file must be cleaned up
        $res2 = AvatarService::upload($this->testUserId, [
            'name' => 'photo2.png',
            'tmp_name' => $this->tempPngPath,
            'size' => filesize($this->tempPngPath),
            'error' => UPLOAD_ERR_OK
        ]);
        $this->assertTrue($res2['success']);
        $this->assertFalse(is_file($path1));
        $this->assertTrue(is_file(BASE_DIR . '/public/' . $res2['avatar_path']));

        // Remove avatar
        $rem = AvatarService::remove($this->testUserId);
        $this->assertTrue($rem['success']);
        $this->assertFalse(is_file(BASE_DIR . '/public/' . $res2['avatar_path']));
        $user2 = UserDAO::find($this->testUserId);
        $this->assertNull($user2['avatar_path']);
    }
}
