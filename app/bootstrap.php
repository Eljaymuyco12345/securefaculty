<?php
/**
 * Application Bootstrap & Central Autoloader
 * PSR-4 compliant class loader for MVC Architecture
 * SecureFaculty CICI Portal
 */

require_once __DIR__ . '/config.php';

// Register PSR-style Class Autoloader
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/Controllers/',
        __DIR__ . '/Models/',
        __DIR__ . '/DAO/',
        __DIR__ . '/Services/',
        __DIR__ . '/Interfaces/',
        __DIR__ . '/Core/'
    ];

    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load core services
require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Services/AuditLogger.php';
require_once __DIR__ . '/Services/AuthService.php';
require_once __DIR__ . '/Services/CryptoService.php';
require_once __DIR__ . '/Models/CertificateModel.php';
require_once __DIR__ . '/Models/UserModel.php';

// Class aliases for 100% backward compatibility
if (!class_exists('Auth', false)) {
    class_alias('AuthService', 'Auth');
}
if (!class_exists('Crypto', false)) {
    class_alias('CryptoService', 'Crypto');
}

// Global session initialization
AuthService::initSession();

// Global OWASP Security Headers (OWASP Top 10 A01 & A05)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com; img-src 'self' data: blob:; font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; frame-ancestors 'self';");
    
    // Enforce HSTS when running on HTTPS or behind TLS-terminating reverse proxy
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
