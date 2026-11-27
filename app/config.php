<?php
/**
 * SecureFaculty Configuration
 * Systematic Records Management Portal with OCR & Secure Archiving
 * CICI Department - ISUFST Main Campus
 */

// Application Constants
define('APP_NAME', 'SecureFaculty');
define('APP_VERSION', '1.0.0-PROTOTYPE');
define('INSTITUTION_NAME', 'ISUFST - College of Informatics and Computing Innovations');

// Base Paths
define('BASE_DIR', dirname(__DIR__));
define('STORAGE_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'storage');
define('VAULT_DIR', STORAGE_DIR . DIRECTORY_SEPARATOR . 'vault');
define('TEMP_DIR', STORAGE_DIR . DIRECTORY_SEPARATOR . 'temp');
define('BACKUP_DIR', STORAGE_DIR . DIRECTORY_SEPARATOR . 'backups');
define('PYTHON_ENGINE_PATH', BASE_DIR . DIRECTORY_SEPARATOR . 'python_engine' . DIRECTORY_SEPARATOR . 'conversion_gate.py');

// Load Environment Variables from .env (zero-dependency loader; preserves OS/Docker env)
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Env.php';
Env::load(BASE_DIR . DIRECTORY_SEPARATOR . '.env');

// Database Driver Configuration ('sqlite' or 'mysql')
define('DB_DRIVER', Env::get('DB_DRIVER', 'mysql') ?: 'mysql'); // Connected to XAMPP MySQL or Docker MariaDB

// SQLite Configuration
define('SQLITE_PATH', STORAGE_DIR . DIRECTORY_SEPARATOR . 'securefaculty.sqlite');

// MySQL Configuration (For XAMPP / Docker / Production)
define('DB_HOST', Env::get('DB_HOST', 'localhost') ?: 'localhost');
define('DB_PORT', Env::get('DB_PORT', '3306') ?: '3306');
define('DB_NAME', Env::get('DB_NAME', 'securefaculty_db') ?: 'securefaculty_db');
define('DB_USER', Env::get('DB_USER', 'root') ?: 'root');
$dbPass = Env::get('DB_PASS', '');
define('DB_PASS', $dbPass !== null ? (string)$dbPass : '');

// Python Executable Binary
define('PYTHON_BIN', Env::get('PYTHON_BIN', PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3') ?: (PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3'));

// Cryptographic Security (AES-256)
// Loaded from SECUREFACULTY_MASTER_KEY in .env / environment (no hardcoded key in source)
define('AES_KEY_HEX', (string)Env::get('SECUREFACULTY_MASTER_KEY', ''));
define('AES_CIPHER', 'aes-256-cbc');

// OCR.space API Configuration (Loaded from .env / environment)
define('OCR_PRIMARY_KEY', (string)Env::get('OCR_PRIMARY_KEY', ''));
define('OCR_BACKUP_KEY', (string)Env::get('OCR_BACKUP_KEY', ''));
define('OCR_API_KEY', implode(',', array_filter([OCR_PRIMARY_KEY, OCR_BACKUP_KEY], fn($k) => trim($k) !== '')));
define('OCR_MONTHLY_QUOTA', (int)Env::get('OCR_MONTHLY_QUOTA', 25000));

// Gmail SMTP Configuration (Loaded from .env / environment)
define('SMTP_HOST', (string)(Env::get('SMTP_HOST', 'smtp.gmail.com') ?: 'smtp.gmail.com'));
define('SMTP_PORT', (int)(Env::get('SMTP_PORT', 587) ?: 587));
define('SMTP_USER', (string)Env::get('SMTP_USER', ''));
define('SMTP_PASS', (string)Env::get('SMTP_PASS', ''));
define('SMTP_FROM_NAME', (string)(Env::get('SMTP_FROM_NAME', 'SecureFaculty CICI Portal') ?: 'SecureFaculty CICI Portal'));
define('OTP_EXPIRY_MINUTES', (int)(Env::get('OTP_EXPIRY_MINUTES', 15) ?: 15));

// Session Inactivity Timeout (10 minutes = 600 seconds by default)
define('SESSION_TIMEOUT_SECONDS', (int)(Env::get('SESSION_TIMEOUT_SECONDS', 600) ?: 600));

// Ensure critical directories exist
if (!is_dir(VAULT_DIR)) {
    mkdir(VAULT_DIR, 0755, true);
}
if (!is_dir(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0755, true);
}
if (!is_dir(BACKUP_DIR)) {
    mkdir(BACKUP_DIR, 0755, true);
}
