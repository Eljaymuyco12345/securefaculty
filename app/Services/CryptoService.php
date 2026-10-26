<?php
/**
 * CryptoService
 * Compliant with RA 10173 (Data Privacy Act of 2012)
 * Advanced Encryption Standard: AES-256-CBC with Just-in-Time Decryption
 * Service Layer
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Interfaces/CryptoServiceInterface.php';

class CryptoService implements CryptoServiceInterface {
    private static function getKey(): string {
        return hex2bin(AES_KEY_HEX);
    }

    /**
     * Encrypts a file from source path and stores it at destination path
     * Format: [16-byte IV] + [Ciphertext]
     */
    public static function encryptFile(string $sourcePath, string $destinationPath): bool {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $plaintext = file_get_contents($sourcePath);
        if ($plaintext === false) {
            return false;
        }

        $ivLength = openssl_cipher_iv_length(AES_CIPHER);
        $iv = random_bytes($ivLength);

        $ciphertext = openssl_encrypt($plaintext, AES_CIPHER, self::getKey(), OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            return false;
        }

        // Store IV prepended to ciphertext
        $encryptedPayload = $iv . $ciphertext;
        return file_put_contents($destinationPath, $encryptedPayload) !== false;
    }

    /**
     * Just-in-Time Decryption:
     * Reads encrypted file from disk and returns raw decrypted bytes directly in volatile RAM
     */
    public static function decryptFileToMemory(string $encryptedFilePath): ?string {
        if (!file_exists($encryptedFilePath)) {
            return null;
        }

        $payload = file_get_contents($encryptedFilePath);
        if ($payload === false) {
            return null;
        }

        $ivLength = openssl_cipher_iv_length(AES_CIPHER);
        if (strlen($payload) < $ivLength) {
            return null;
        }

        $iv = substr($payload, 0, $ivLength);
        $ciphertext = substr($payload, $ivLength);

        $plaintext = openssl_decrypt($ciphertext, AES_CIPHER, self::getKey(), OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) {
            return null;
        }

        return $plaintext;
    }
}
