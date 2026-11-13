<?php
/**
 * Database Connection & Schema Management
 * Singleton Pattern for PDO Connection Factory (SQLite / MySQL)
 * Plan B Disaster Recovery & High-Availability Database Engine
 * Core Infrastructure Layer
 */

require_once __DIR__ . '/../config.php';

class Database {
    private static ?PDO $pdo = null;

    /**
     * Reset the active PDO singleton instance (used for testing and failover verification).
     */
    public static function resetConnection(): void {
        self::$pdo = null;
    }

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            $driver = defined('DB_DRIVER') ? DB_DRIVER : 'sqlite';

            try {
                if ($driver === 'sqlite') {
                    $dsn = 'sqlite:' . SQLITE_PATH;
                    self::$pdo = new PDO($dsn, null, null, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_PERSISTENT => false,
                    ]);
                    // Enable foreign keys, busy timeout, and WAL journal mode in SQLite
                    self::$pdo->exec('PRAGMA foreign_keys = ON;');
                    self::$pdo->exec('PRAGMA busy_timeout = 5000;');
                    try {
                        self::$pdo->exec('PRAGMA journal_mode = WAL;');
                    } catch (Throwable $we) {}

                    self::initializeSchema(self::$pdo);
                } else {
                    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                    self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_PERSISTENT => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    ]);
                    // Self-healing: verify tables exist in MySQL
                    self::initializeMySQLSchema(self::$pdo);
                }
            } catch (PDOException $e) {
                // Plan B Disaster Recovery Fallback:
                // If primary MySQL is unreachable, seamlessly fall back to local SQLite snapshot
                if ($driver === 'mysql' && file_exists(SQLITE_PATH)) {
                    try {
                        $dsn = 'sqlite:' . SQLITE_PATH;
                        self::$pdo = new PDO($dsn, null, null, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                            PDO::ATTR_PERSISTENT => false,
                        ]);
                        self::$pdo->exec('PRAGMA foreign_keys = ON;');
                        self::$pdo->exec('PRAGMA busy_timeout = 5000;');
                        try {
                            self::$pdo->exec('PRAGMA journal_mode = WAL;');
                        } catch (Throwable $we) {}
                        self::initializeSchema(self::$pdo);

                        try {
                            AuditLogger::log('DATABASE_FAILOVER_ENGAGED', 'Primary MySQL unavailable (' . $e->getMessage() . '). Seamlessly engaged Plan B SQLite database fallback.');
                        } catch (Throwable $logError) {}

                        return self::$pdo;
                    } catch (Throwable $fallbackError) {
                        die('Database Connection Error: ' . htmlspecialchars($fallbackError->getMessage()));
                    }
                }
                die('Database Connection Error: ' . htmlspecialchars($e->getMessage()));
            }
        }
        return self::$pdo;
    }

    /**
     * Self-healing initialization for MySQL schema
     */
    private static function initializeMySQLSchema(PDO $pdo): void {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if (!$stmt || !$stmt->fetch()) {
                $schemaFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    // Strip database creation / use directives as PDO is already connected to DB_NAME
                    $sql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sql);
                    $sql = preg_replace('/USE `?[^;`]+`?;/i', '', $sql);
                    $pdo->exec($sql);
                }
            }
        } catch (Throwable $e) {
            // Non-fatal if schema already initialized or insufficient DDL permissions
        }
    }

    /**
     * Self-healing initialization for SQLite schema
     */
    private static function initializeSchema(PDO $pdo): void {
        // Create tables if they do not exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL CHECK(role IN ('dean', 'faculty')),
                full_name TEXT NOT NULL,
                email TEXT,
                department TEXT DEFAULT 'CICI',
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS faculty_profiles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER UNIQUE NOT NULL,
                employee_id TEXT,
                academic_rank TEXT,
                specialization TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS certificates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                faculty_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                issuer TEXT,
                date_issued TEXT,
                category TEXT DEFAULT 'Seminar',
                file_path TEXT NOT NULL,
                original_filename TEXT NOT NULL,
                file_type TEXT NOT NULL,
                file_size INTEGER NOT NULL,
                file_hash TEXT,
                cert_number TEXT,
                processing_status TEXT DEFAULT 'completed',
                batch_id TEXT,
                ocr_raw_text TEXT,
                ocr_confidence REAL DEFAULT 0.95,
                status TEXT DEFAULT 'approved',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS certificate_authors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                certificate_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                username TEXT,
                role TEXT,
                action TEXT NOT NULL,
                target_id INTEGER,
                details TEXT,
                ip_address TEXT,
                user_agent TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS api_quotas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                service_name TEXT UNIQUE NOT NULL,
                monthly_limit INTEGER DEFAULT 25000,
                current_usage INTEGER DEFAULT 0,
                month_year TEXT NOT NULL,
                last_updated DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS processing_batches (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                batch_uuid TEXT UNIQUE NOT NULL,
                user_id INTEGER NOT NULL,
                total_files INTEGER DEFAULT 0,
                processed_files INTEGER DEFAULT 0,
                success_files INTEGER DEFAULT 0,
                failed_files INTEGER DEFAULT 0,
                status TEXT DEFAULT 'pending',
                current_stage TEXT DEFAULT 'Upload',
                log_summary TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
        ");

        // Dynamic safe migrations for SQLite backward compatibility
        try {
            $userCols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('is_active', $userCols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN is_active INTEGER DEFAULT 1;");
            }

            $certCols = $pdo->query("PRAGMA table_info(certificates)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('file_hash', $certCols)) {
                $pdo->exec("ALTER TABLE certificates ADD COLUMN file_hash TEXT;");
            }
            if (!in_array('cert_number', $certCols)) {
                $pdo->exec("ALTER TABLE certificates ADD COLUMN cert_number TEXT;");
            }
            if (!in_array('processing_status', $certCols)) {
                $pdo->exec("ALTER TABLE certificates ADD COLUMN processing_status TEXT DEFAULT 'completed';");
            }
            if (!in_array('batch_id', $certCols)) {
                $pdo->exec("ALTER TABLE certificates ADD COLUMN batch_id TEXT;");
            }
        } catch (Throwable $me) {}

        // Ensure indices are established in SQLite
        $pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_certificates_faculty ON certificates(faculty_id);
            CREATE INDEX IF NOT EXISTS idx_certificates_hash ON certificates(file_hash);
            CREATE INDEX IF NOT EXISTS idx_certificates_date ON certificates(date_issued);
            CREATE INDEX IF NOT EXISTS idx_cert_authors_cert ON certificate_authors(certificate_id);
            CREATE INDEX IF NOT EXISTS idx_cert_authors_user ON certificate_authors(user_id);
            CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_logs(created_at);
            CREATE INDEX IF NOT EXISTS idx_audit_action ON audit_logs(action);
            CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_logs(user_id);
            CREATE INDEX IF NOT EXISTS idx_audit_ip ON audit_logs(ip_address);
        ");

        // Seed API quota row if not exists
        $currentMonth = date('Y-m');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_quotas WHERE service_name = 'OCR_SPACE'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $insertQuota = $pdo->prepare("
                INSERT INTO api_quotas (service_name, monthly_limit, current_usage, month_year)
                VALUES ('OCR_SPACE', 25000, 142, ?)
            ");
            $insertQuota->execute([$currentMonth]);
        }

        // Seed users if table is empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() == 0) {
            self::seedInitialData($pdo);
        }
    }

    private static function seedInitialData(PDO $pdo): void {
        $users = [
            [
                'username' => 'dean',
                'password' => 'admin123',
                'role' => 'dean',
                'full_name' => 'Dr. Arlene S. Poblador',
                'email' => 'dean.cici@isufst.edu.ph',
                'employee_id' => 'ISUFST-6438',
                'rank' => 'Dean, CICI',
                'specialization' => 'Educational Management & IT Leadership'
            ],
            [
                'username' => 'mikhail',
                'password' => 'faculty123',
                'role' => 'faculty',
                'full_name' => 'Prof. Levy Mikhail B. Aguilar',
                'email' => 'mikhail.aguilar@isufst.edu.ph',
                'employee_id' => 'ISUFST-9597',
                'rank' => 'Assistant Professor II',
                'specialization' => 'Information Security & Cloud Infrastructure'
            ],
            [
                'username' => 'raejan',
                'password' => 'faculty123',
                'role' => 'faculty',
                'full_name' => 'Prof. Rae Jan B. Corneja',
                'email' => 'raejan.corneja@isufst.edu.ph',
                'employee_id' => 'ISUFST-8969',
                'rank' => 'Instructor I',
                'specialization' => 'Database Systems & Web Engineering'
            ],
            [
                'username' => 'eljay',
                'password' => 'faculty123',
                'role' => 'faculty',
                'full_name' => 'Prof. Eljay Muyco',
                'email' => 'eljay.muyco@isufst.edu.ph',
                'employee_id' => 'ISUFST-4305',
                'rank' => 'Instructor I',
                'specialization' => 'Artificial Intelligence & Computer Vision'
            ]
        ];

        foreach ($users as $u) {
            $hash = password_hash($u['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, full_name, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$u['username'], $hash, $u['role'], $u['full_name'], $u['email']]);
            $userId = (int)$pdo->lastInsertId();

            $pStmt = $pdo->prepare("INSERT INTO faculty_profiles (user_id, employee_id, academic_rank, specialization) VALUES (?, ?, ?, ?)");
            $pStmt->execute([$userId, $u['employee_id'], $u['rank'], $u['specialization']]);
        }

        // Seed initial co-authored research publication matching MySQL schema.sql
        $certCount = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
        if ((int)$certCount === 0) {
            $cStmt = $pdo->prepare("
                INSERT INTO certificates (id, faculty_id, title, issuer, date_issued, category, file_path, original_filename, file_type, file_size, ocr_confidence, status)
                VALUES (1, 2, 'SECUREFACULTY: A SYSTEMATIC RECORDS MANAGEMENT PORTAL', 'Iloilo State University of Fisheries Science and Technology', '2026-09-25', 'Research Publication', 'sample_demo_cert.pdf', 'SecureFaculty_Capstone_Manuscript.pdf', 'pdf', 1048576, 0.98, 'approved')
            ");
            $cStmt->execute();

            $aStmt = $pdo->prepare("INSERT INTO certificate_authors (id, certificate_id, user_id) VALUES (?, ?, ?)");
            $aStmt->execute([1, 1, 2]);
            $aStmt->execute([2, 1, 3]);
            $aStmt->execute([3, 1, 4]);
        }
    }
}
