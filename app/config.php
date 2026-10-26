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
define('PYTHON_ENGINE_PATH', BASE_DIR . DIRECTORY_SEPARATOR . 'python_engine' . DIRECTORY_SEPARATOR . 'conversion_gate.py');

// Database Driver Configuration ('sqlite' or 'mysql')
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql'); // Connected to XAMPP MySQL or Docker MariaDB

// SQLite Configuration
define('SQLITE_PATH', STORAGE_DIR . DIRECTORY_SEPARATOR . 'securefaculty.sqlite');

// MySQL Configuration (For XAMPP / Docker / Production)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'securefaculty_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Python Executable Binary
define('PYTHON_BIN', getenv('PYTHON_BIN') ?: (PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3'));

// Cryptographic Security (AES-256)
// Key resolution priority: Environment variable SECUREFACULTY_MASTER_KEY -> config constant default
$envMasterKey = getenv('SECUREFACULTY_MASTER_KEY') ?: ($_ENV['SECUREFACULTY_MASTER_KEY'] ?? null);
define('AES_KEY_HEX', $envMasterKey ?: 'a4f89d38c114e590f23b7829a9041be5e8f49d21798ec4e1f8d48e89b2c3a501');
define('AES_CIPHER', 'aes-256-cbc');

// OCR.space API Configuration
// Primary: User's dedicated free key (25,000 monthly quota)
define('OCR_PRIMARY_KEY', 'K85711929288957');
// Backup: Evaluation key fallback if primary reaches quota or limit
define('OCR_BACKUP_KEY', 'helloworld');
// Combined failover chain passed to Python Conversion Gate (Primary -> Backup)
define('OCR_API_KEY', OCR_PRIMARY_KEY . ',' . OCR_BACKUP_KEY);
define('OCR_MONTHLY_QUOTA', 25000); // 25,000 monthly threshold as defined in Capstone document

// Ensure critical directories exist
if (!is_dir(VAULT_DIR)) {
    mkdir(VAULT_DIR, 0755, true);
}
if (!is_dir(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0755, true);
}
