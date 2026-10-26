<?php
/**
 * CryptoServiceInterface
 * Cryptographic Contract for AES-256 Envelope Encryption and Volatile Memory Decryption
 * ISUFST CICI Systematic Records Management Portal
 */

interface CryptoServiceInterface {
    public static function encryptFile(string $sourcePath, string $destPath): bool;
    public static function decryptFileToMemory(string $encryptedFilePath): ?string;
}
