<?php
/**
 * CryptoService Micro-Unit Test
 * Tests AES-256-CBC cryptographic engine, IV randomness, zero-plaintext storage, and Just-in-Time RAM decryption
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Services/CryptoService.php';

class CryptoServiceTest extends TestCase {
    private string $tempPlain = '';
    private string $tempEnc1 = '';
    private string $tempEnc2 = '';

    public function setUp(): void {
        parent::setUp();
        $this->tempPlain = TEMP_DIR . '/crypto_test_' . bin2hex(random_bytes(6)) . '.txt';
        $this->tempEnc1 = VAULT_DIR . '/crypto_test_' . bin2hex(random_bytes(6)) . '_1.enc';
        $this->tempEnc2 = VAULT_DIR . '/crypto_test_' . bin2hex(random_bytes(6)) . '_2.enc';
    }

    public function tearDown(): void {
        foreach ([$this->tempPlain, $this->tempEnc1, $this->tempEnc2] as $f) {
            if (file_exists($f)) {
                @unlink($f);
            }
        }
        parent::tearDown();
    }

    public function testEncryptAndDecryptRoundTrip(): void {
        $secret = "TOP_SECRET_ISUFST_RECORD_" . bin2hex(random_bytes(32));
        file_put_contents($this->tempPlain, $secret);

        $encOk = CryptoService::encryptFile($this->tempPlain, $this->tempEnc1);
        $this->assertTrue($encOk, "Encryption must succeed");
        $this->assertTrue(file_exists($this->tempEnc1), "Ciphertext file must be created on disk");

        // Verify zero-plaintext persistence
        $rawBytes = file_get_contents($this->tempEnc1);
        $this->assertFalse(str_contains($rawBytes, $secret), "Plaintext must never leak into ciphertext");
        $this->assertGreaterThan(16, strlen($rawBytes), "Ciphertext must contain IV (16 bytes) + encrypted blocks");

        // Decrypt to RAM
        $recovered = CryptoService::decryptFileToMemory($this->tempEnc1);
        $this->assertNotNull($recovered);
        $this->assertEquals($secret, $recovered, "Decrypted bytes must match original plaintext");
    }

    public function testIvRandomnessNoIvReuse(): void {
        $payload = "IDENTICAL_PLAINTEXT_PAYLOAD_FOR_NONCE_TEST";
        file_put_contents($this->tempPlain, $payload);

        CryptoService::encryptFile($this->tempPlain, $this->tempEnc1);
        CryptoService::encryptFile($this->tempPlain, $this->tempEnc2);

        $bytes1 = file_get_contents($this->tempEnc1);
        $bytes2 = file_get_contents($this->tempEnc2);

        $iv1 = substr($bytes1, 0, 16);
        $iv2 = substr($bytes2, 0, 16);

        $this->assertNotEquals($iv1, $iv2, "Consecutive encryptions must never reuse initialization vectors (IVs)");
        $this->assertNotEquals($bytes1, $bytes2, "Different IVs must yield distinct ciphertexts for identical plaintext");
    }

    public function testNonExistentFileReturnsFalseOrNull(): void {
        $this->assertFalse(CryptoService::encryptFile('/non/existent/path/file.txt', $this->tempEnc1));
        $this->assertNull(CryptoService::decryptFileToMemory('/non/existent/path/file.enc'));
    }

    public function testTruncatedCiphertextReturnsNull(): void {
        file_put_contents($this->tempEnc1, "SHORT_DATA");
        $decrypted = CryptoService::decryptFileToMemory($this->tempEnc1);
        $this->assertNull($decrypted, "Ciphertext shorter than IV length (16 bytes) must safely return null");
    }
}
