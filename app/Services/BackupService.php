<?php
/**
 * BackupService
 * Plan B Disaster Recovery & Database Snapshot Engine
 * Compliant with RA 10173 & Institutional Data Preservation Standards
 * Service Layer
 */

class BackupService {
    /**
     * Create an on-demand database snapshot and return backup file details.
     *
     * @return array ['success' => bool, 'filename' => string, 'filepath' => string, 'size_kb' => float, 'message' => string]
     */
    public static function createBackup(): array {
        $filename = '';
        $targetPath = '';
        try {
            $backupDir = STORAGE_DIR . DIRECTORY_SEPARATOR . 'backups';
            if (!is_dir($backupDir)) {
                if (!mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
                    throw new RuntimeException('Unable to create backup storage directory: ' . $backupDir);
                }
            }

            // Obtain active database connection and determine the actual runtime driver
            $pdo = Database::getConnection();
            $driver = strtolower((string)$pdo->getAttribute(PDO::ATTR_DRIVER_NAME));

            $timestamp = date('Ymd_His');

            if ($driver === 'sqlite') {
                $sourcePath = defined('SQLITE_PATH') ? SQLITE_PATH : (STORAGE_DIR . DIRECTORY_SEPARATOR . 'securefaculty.sqlite');
                if (!file_exists($sourcePath)) {
                    throw new RuntimeException('SQLite database file not found at: ' . $sourcePath);
                }

                $filename = "securefaculty_planb_snapshot_{$timestamp}.sqlite";
                $targetPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

                if (file_exists($targetPath)) {
                    @unlink($targetPath);
                }

                // Strategy 1: Attempt SQLite native VACUUM INTO for clean, transactional snapshot
                $vacuumSuccess = false;
                try {
                    // Flush WAL frames if WAL mode is active
                    try {
                        $pdo->exec("PRAGMA wal_checkpoint(PASSIVE);");
                    } catch (Throwable $pe) {}

                    $normalizedTarget = str_replace('\\', '/', $targetPath);
                    $escapedTarget = str_replace("'", "''", $normalizedTarget);
                    $pdo->exec("VACUUM INTO '{$escapedTarget}'");
                    if (file_exists($targetPath) && filesize($targetPath) > 0) {
                        $vacuumSuccess = true;
                    }
                } catch (Throwable $ve) {
                    $vacuumSuccess = false;
                }

                // Strategy 2: If VACUUM INTO fails (e.g. older SQLite or locked), perform safe locked stream copy
                if (!$vacuumSuccess) {
                    $src = @fopen($sourcePath, 'rb');
                    if (!$src) {
                        throw new RuntimeException('Failed to open SQLite source database for backup reading.');
                    }

                    // Acquire shared read lock to prevent reading half-written / torn pages
                    @flock($src, LOCK_SH);

                    $dst = @fopen($targetPath, 'wb');
                    if (!$dst) {
                        @flock($src, LOCK_UN);
                        @fclose($src);
                        throw new RuntimeException('Failed to open backup destination file for writing.');
                    }

                    $copied = stream_copy_to_stream($src, $dst);
                    @flock($src, LOCK_UN);
                    @fclose($src);
                    @fclose($dst);

                    if ($copied === false || !file_exists($targetPath)) {
                        throw new RuntimeException('Failed to copy SQLite database file to backup repository.');
                    }
                }
            } else {
                // MySQL database dump generation
                $filename = "securefaculty_planb_snapshot_{$timestamp}.sql";
                $targetPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

                $fp = @fopen($targetPath, 'wb');
                if (!$fp) {
                    throw new RuntimeException('Failed to create MySQL backup dump file: ' . $targetPath);
                }

                // Write SQL dump headers
                fwrite($fp, "-- ====================================================================\n");
                fwrite($fp, "-- SecureFaculty Disaster Recovery Plan B Database Snapshot\n");
                fwrite($fp, "-- Institution: ISUFST College of Informatics & Computing Innovations\n");
                fwrite($fp, "-- Compliance: RA 10173 (Data Privacy Act of 2012)\n");
                fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
                fwrite($fp, "-- Engine: MySQL / MariaDB (Dual Compatible)\n");
                fwrite($fp, "-- ====================================================================\n\n");
                fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n");
                fwrite($fp, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
                fwrite($fp, "SET NAMES utf8mb4;\n");
                fwrite($fp, "SET AUTOCOMMIT=0;\n");
                fwrite($fp, "START TRANSACTION;\n\n");

                // Ordered tables respecting relational dependencies
                $orderedTables = [
                    'users',
                    'faculty_profiles',
                    'certificates',
                    'certificate_authors',
                    'processing_batches',
                    'api_quotas',
                    'audit_logs'
                ];

                // Dynamically fetch any additional tables present in database
                $existingTables = [];
                try {
                    $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
                    if ($tablesStmt) {
                        while ($tRow = $tablesStmt->fetch(PDO::FETCH_NUM)) {
                            $existingTables[] = $tRow[0];
                        }
                    }
                } catch (Throwable $e) {}

                // Combine ordered tables with any discovered extra tables
                $allTables = array_unique(array_merge($orderedTables, $existingTables));

                foreach ($allTables as $table) {
                    try {
                        $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                        if ($createStmt) {
                            $createRow = $createStmt->fetch(PDO::FETCH_NUM);
                            if (!empty($createRow[1])) {
                                fwrite($fp, "-- --------------------------------------------------------------------\n");
                                fwrite($fp, "-- Table structure for `{$table}`\n");
                                fwrite($fp, "-- --------------------------------------------------------------------\n");
                                fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");
                                fwrite($fp, $createRow[1] . ";\n\n");
                            }
                        }

                        $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
                        if ($dataStmt) {
                            fwrite($fp, "-- Dumping data for `{$table}`\n");
                            $rowCount = 0;
                            while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
                                $cols = array_map(function($c) {
                                    return "`" . str_replace("`", "``", $c) . "`";
                                }, array_keys($row));

                                $vals = array_map(function($v) use ($pdo) {
                                    if ($v === null) {
                                        return 'NULL';
                                    }
                                    $quoted = $pdo->quote((string)$v);
                                    return $quoted !== false ? $quoted : "''";
                                }, array_values($row));

                                fwrite($fp, "INSERT INTO `{$table}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n");
                                $rowCount++;
                            }
                            if ($rowCount > 0) {
                                fwrite($fp, "\n");
                            }
                        }
                    } catch (Throwable $te) {
                        // Table may not exist yet or query failed; continue with next table
                        continue;
                    }
                }

                fwrite($fp, "COMMIT;\n");
                fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
                fclose($fp);
            }

            $sizeBytes = file_exists($targetPath) ? filesize($targetPath) : 0;
            $sizeKb = round($sizeBytes / 1024, 2);

            AuditLogger::log('DATABASE_BACKUP_CREATED', "Generated Plan B database backup snapshot: {$filename} ({$sizeKb} KB)");

            // Housekeeping: retain 10 most recent backups
            self::pruneOldBackups($backupDir, 10);

            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $targetPath,
                'size_kb' => $sizeKb,
                'message' => "Backup snapshot generated successfully ({$sizeKb} KB)."
            ];
        } catch (Throwable $e) {
            AuditLogger::log('BACKUP_FAILED', "Plan B database backup failed: " . $e->getMessage());
            return [
                'success' => false,
                'filename' => $filename,
                'filepath' => $targetPath,
                'size_kb' => 0.0,
                'message' => 'Backup error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retains the N most recent backups and deletes older archives to prevent storage exhaustion.
     */
    public static function pruneOldBackups(string $dir, int $keepCount = 10): void {
        try {
            $files = glob($dir . DIRECTORY_SEPARATOR . 'securefaculty_planb_snapshot_*.*') ?: [];
            if (count($files) > $keepCount) {
                usort($files, function($a, $b) {
                    $timeA = @filemtime($a) ?: 0;
                    $timeB = @filemtime($b) ?: 0;
                    if ($timeA === $timeB) {
                        return strcmp($b, $a);
                    }
                    return $timeB <=> $timeA;
                });
                $toDelete = array_slice($files, $keepCount);
                foreach ($toDelete as $f) {
                    if (is_file($f)) {
                        @unlink($f);
                    }
                }
            }
        } catch (Throwable $e) {
            // Non-critical pruning error
        }
    }
}
