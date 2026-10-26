<?php
/**
 * Data Migration Tool: SQLite -> MySQL (XAMPP)
 * Transfers all tables, accounts, certificates, co-authors, and audit trails
 */

require_once __DIR__ . '/../app/config.php';

echo "=== MIGRATING DATA FROM SQLITE TO XAMPP MYSQL ===\n\n";

try {
    $sqlite = new PDO('sqlite:' . SQLITE_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "[OK] Connected to SQLite database: " . SQLITE_PATH . "\n";
} catch (Exception $e) {
    die("[FAIL] Could not connect to SQLite: " . $e->getMessage() . "\n");
}

try {
    $mysql = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "[OK] Connected to XAMPP MySQL database: " . DB_NAME . "\n\n";
} catch (Exception $e) {
    die("[FAIL] Could not connect to MySQL: " . $e->getMessage() . "\n");
}

// Disable foreign key checks during migration
$mysql->exec("SET FOREIGN_KEY_CHECKS = 0;");

$tables = [
    'users',
    'faculty_profiles',
    'certificates',
    'certificate_authors',
    'audit_logs',
    'api_quotas',
    'processing_batches'
];

foreach ($tables as $table) {
    echo "Migrating table '{$table}'...\n";
    $mysql->exec("TRUNCATE TABLE `{$table}`;");

    $rows = $sqlite->query("SELECT * FROM `{$table}`")->fetchAll();
    if (empty($rows)) {
        echo "  [INFO] Table is empty, skipped.\n";
        continue;
    }

    $columns = array_keys($rows[0]);
    $colList = implode('`, `', $columns);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $insertSql = "INSERT INTO `{$table}` (`{$colList}`) VALUES ({$placeholders})";
    $stmt = $mysql->prepare($insertSql);

    $count = 0;
    foreach ($rows as $row) {
        $values = array_values($row);
        $stmt->execute($values);
        $count++;
    }

    $mysqlCount = $mysql->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
    echo "  [OK] Successfully migrated {$count} rows (Verified in MySQL: {$mysqlCount} rows)\n";
}

$mysql->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "\n=== MIGRATION COMPLETE! ALL DATA SUCCESSFULLY TRANSFERRED TO MYSQL ===\n";
