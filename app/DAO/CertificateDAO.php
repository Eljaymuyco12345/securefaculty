<?php
/**
 * CertificateDAO
 * Concrete Data Access Object for Certificate persistence operations
 * Implements CertificateDAOInterface
 * DAO Layer
 */

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Interfaces/CertificateDAOInterface.php';

class CertificateDAO implements CertificateDAOInterface {
    public static function find(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, u.full_name as faculty_name, u.department
            FROM certificates c
            JOIN users u ON c.faculty_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $cert = $stmt->fetch();
        return $cert ?: null;
    }

    public static function findByHash(string $hash): ?array {
        if (empty($hash)) return null;
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, u.full_name as faculty_name
            FROM certificates c
            JOIN users u ON c.faculty_id = u.id
            WHERE c.file_hash = ? LIMIT 1
        ");
        $stmt->execute([$hash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function isAuthorOrCoAuthor(int $certificateId, int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificate_authors WHERE certificate_id = ? AND user_id = ?");
        $stmt->execute([$certificateId, $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function getByIds(array $ids, ?int $facultyId = null): array {
        if (empty($ids)) return [];
        $pdo = Database::getConnection();
        $inPlaceholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
            SELECT c.*, u.full_name as faculty_name, fp.academic_rank, fp.employee_id,
                   (SELECT GROUP_CONCAT(u2.full_name, ', ')
                    FROM certificate_authors ca2
                    JOIN users u2 ON ca2.user_id = u2.id
                    WHERE ca2.certificate_id = c.id AND ca2.user_id != c.faculty_id) as co_authors
            FROM certificates c
            JOIN users u ON c.faculty_id = u.id
            LEFT JOIN faculty_profiles fp ON u.id = fp.user_id
            WHERE c.id IN ($inPlaceholders)
        ";
        $params = $ids;

        if ($facultyId !== null) {
            $sql .= " AND (c.faculty_id = ? OR c.id IN (SELECT certificate_id FROM certificate_authors WHERE user_id = ?))";
            $params[] = $facultyId;
            $params[] = $facultyId;
        }

        $sql .= " ORDER BY c.date_issued DESC, c.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findDuplicateByTitleAndDate(int $facultyId, string $title, string $dateIssued): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, title, date_issued FROM certificates WHERE faculty_id = ? AND title = ? AND date_issued = ? LIMIT 1");
        $stmt->execute([$facultyId, $title, $dateIssued]);
        $rec = $stmt->fetch();
        return $rec ?: null;
    }

    public static function getAvailableIssuedYears(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT DISTINCT SUBSTR(date_issued, 1, 4) as year
            FROM certificates
            WHERE date_issued IS NOT NULL AND length(date_issued) >= 4
            ORDER BY year DESC
        ");
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_values(array_filter($years, fn($y) => is_numeric($y) && (int)$y >= 1950 && (int)$y <= 2099));
    }

    public static function create(array $data, array $coAuthorIds = []): int {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO certificates (
                    faculty_id, title, issuer, date_issued, category,
                    file_path, original_filename, file_type, file_size,
                    ocr_raw_text, ocr_confidence, status,
                    file_hash, cert_number, processing_status, batch_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['faculty_id'],
                $data['title'],
                $data['issuer'] ?? 'Not Specified',
                $data['date_issued'] ?? null,
                $data['category'] ?? 'Seminar',
                $data['file_path'],
                $data['original_filename'],
                $data['file_type'],
                $data['file_size'],
                $data['ocr_raw_text'] ?? '',
                $data['ocr_confidence'] ?? 0.95,
                $data['status'] ?? 'approved',
                $data['file_hash'] ?? null,
                $data['cert_number'] ?? null,
                $data['processing_status'] ?? 'completed',
                $data['batch_id'] ?? null
            ]);

            $certId = (int)$pdo->lastInsertId();

            $authorStmt = $pdo->prepare("INSERT INTO certificate_authors (certificate_id, user_id) VALUES (?, ?)");
            $authorStmt->execute([$certId, $data['faculty_id']]);

            foreach ($coAuthorIds as $coId) {
                if ($coId != $data['faculty_id']) {
                    $authorStmt->execute([$certId, (int)$coId]);
                }
            }

            $pdo->commit();
            return $certId;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function createBatch(array $batchData): void {
        $pdo = Database::getConnection();
        $bStmt = $pdo->prepare("
            INSERT INTO processing_batches (batch_uuid, user_id, total_files, processed_files, success_files, failed_files, status, current_stage, log_summary)
            VALUES (?, ?, ?, ?, ?, ?, 'completed', 'Review', ?)
        ");
        $bStmt->execute([
            $batchData['batch_uuid'],
            $batchData['user_id'],
            $batchData['total_files'],
            $batchData['processed_files'],
            $batchData['success_files'],
            $batchData['failed_files'],
            $batchData['log_summary']
        ]);
    }

    public static function getForFaculty(int $facultyId, ?array $filters = null): array {
        $pdo = Database::getConnection();

        $category = $filters['category'] ?? null;
        $search = $filters['search'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $issuedYear = $filters['issued_year'] ?? null;

        $sql = "
            SELECT c.*, u.full_name as faculty_name, fp.academic_rank, fp.employee_id,
                   (SELECT GROUP_CONCAT(u2.full_name, ', ')
                    FROM certificate_authors ca2
                    JOIN users u2 ON ca2.user_id = u2.id
                    WHERE ca2.certificate_id = c.id AND ca2.user_id != c.faculty_id) as co_authors
            FROM certificates c
            JOIN users u ON c.faculty_id = u.id
            LEFT JOIN faculty_profiles fp ON u.id = fp.user_id
            WHERE (c.faculty_id = :faculty_id OR c.id IN (
                SELECT certificate_id FROM certificate_authors WHERE user_id = :faculty_author_id
            ))
        ";
        $params = [
            ':faculty_id' => $facultyId,
            ':faculty_author_id' => $facultyId
        ];

        if ($category && $category !== 'All') {
            $sql .= " AND c.category = :category";
            $params[':category'] = $category;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (c.title LIKE :s_title OR c.issuer LIKE :s_issuer OR c.ocr_raw_text LIKE :s_ocr OR c.cert_number LIKE :s_num)";
            $term = '%' . trim($search) . '%';
            $params[':s_title'] = $term;
            $params[':s_issuer'] = $term;
            $params[':s_ocr'] = $term;
            $params[':s_num'] = $term;
        }

        if ($dateFrom && trim($dateFrom) !== '') {
            $sql .= " AND c.date_issued >= :date_from";
            $params[':date_from'] = trim($dateFrom);
        }

        if ($dateTo && trim($dateTo) !== '') {
            $sql .= " AND c.date_issued <= :date_to";
            $params[':date_to'] = trim($dateTo);
        }

        if ($issuedYear && trim($issuedYear) !== '' && $issuedYear !== 'all') {
            $sql .= " AND c.date_issued LIKE :issued_year";
            $params[':issued_year'] = trim($issuedYear) . '%';
        }

        $sql .= " ORDER BY c.date_issued DESC, c.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getAll($categoryOrFilters = null, ?string $search = null, ?int $facultyId = null, ?string $dateFrom = null, ?string $dateTo = null, ?string $issuedYear = null): array {
        $pdo = Database::getConnection();

        if (is_array($categoryOrFilters)) {
            $category = $categoryOrFilters['category'] ?? null;
            $search = $categoryOrFilters['search'] ?? null;
            $facultyId = $categoryOrFilters['faculty_id'] ?? null;
            $dateFrom = $categoryOrFilters['date_from'] ?? null;
            $dateTo = $categoryOrFilters['date_to'] ?? null;
            $issuedYear = $categoryOrFilters['issued_year'] ?? null;
        } else {
            $category = $categoryOrFilters;
        }

        $sql = "
            SELECT c.*, u.full_name as faculty_name, fp.academic_rank, fp.employee_id,
                   (SELECT GROUP_CONCAT(u2.full_name, ', ')
                    FROM certificate_authors ca2
                    JOIN users u2 ON ca2.user_id = u2.id
                    WHERE ca2.certificate_id = c.id AND ca2.user_id != c.faculty_id) as co_authors
            FROM certificates c
            JOIN users u ON c.faculty_id = u.id
            LEFT JOIN faculty_profiles fp ON u.id = fp.user_id
            WHERE 1=1
        ";
        $params = [];

        if ($facultyId) {
            $sql .= " AND (c.faculty_id = :faculty_id OR c.id IN (SELECT certificate_id FROM certificate_authors WHERE user_id = :faculty_author_id))";
            $params[':faculty_id'] = $facultyId;
            $params[':faculty_author_id'] = $facultyId;
        }

        if ($category && $category !== 'All') {
            $sql .= " AND c.category = :category";
            $params[':category'] = $category;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (c.title LIKE :s_title OR c.issuer LIKE :s_issuer OR u.full_name LIKE :s_name OR c.ocr_raw_text LIKE :s_ocr OR c.cert_number LIKE :s_num)";
            $term = '%' . trim($search) . '%';
            $params[':s_title'] = $term;
            $params[':s_issuer'] = $term;
            $params[':s_name'] = $term;
            $params[':s_ocr'] = $term;
            $params[':s_num'] = $term;
        }

        if ($dateFrom && trim($dateFrom) !== '') {
            $sql .= " AND c.date_issued >= :date_from";
            $params[':date_from'] = trim($dateFrom);
        }

        if ($dateTo && trim($dateTo) !== '') {
            $sql .= " AND c.date_issued <= :date_to";
            $params[':date_to'] = trim($dateTo);
        }

        if ($issuedYear && trim($issuedYear) !== '' && $issuedYear !== 'all') {
            $sql .= " AND c.date_issued LIKE :issued_year";
            $params[':issued_year'] = trim($issuedYear) . '%';
        }

        $sql .= " ORDER BY c.date_issued DESC, c.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function delete(int $id, int $requestingUserId, bool $isDean = false): bool {
        $pdo = Database::getConnection();

        $cert = self::find($id);
        if (!$cert) {
            return false;
        }

        // Case 1: Dean or Primary Certificate Owner (Full Cryptographic Purge)
        if ($isDean || (int)$cert['faculty_id'] === $requestingUserId) {
            $pdo->beginTransaction();
            try {
                $delAuthors = $pdo->prepare("DELETE FROM certificate_authors WHERE certificate_id = ?");
                $delAuthors->execute([$id]);

                $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
                $stmt->execute([$id]);

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                return false;
            }

            $refStmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE file_path = ? AND id != ?");
            $refStmt->execute([$cert['file_path'], $id]);
            if ((int)$refStmt->fetchColumn() === 0) {
                $filePath = VAULT_DIR . DIRECTORY_SEPARATOR . $cert['file_path'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            return true;
        }

        // Case 2: Co-Author (Remove authorship mapping so it is removed from this faculty member's personal vault)
        if (self::isAuthorOrCoAuthor($id, $requestingUserId)) {
            $stmt = $pdo->prepare("DELETE FROM certificate_authors WHERE certificate_id = ? AND user_id = ?");
            $stmt->execute([$id, $requestingUserId]);
            return true;
        }

        // Case 3: Unauthorized user (Neither Dean, primary owner, nor co-author)
        return false;
    }

    public static function bulkDelete(array $ids, int $requestingUserId, bool $isDean): int {
        $count = 0;
        foreach ($ids as $id) {
            $numId = (int)$id;
            if ($numId > 0 && self::delete($numId, $requestingUserId, $isDean)) {
                $count++;
            }
        }
        return $count;
    }

    public static function getQuotaStatus(): array {
        $pdo = Database::getConnection();
        $currentMonth = date('Y-m');

        $stmt = $pdo->prepare("SELECT * FROM api_quotas WHERE service_name = 'OCR_SPACE'");
        $stmt->execute();
        $quota = $stmt->fetch();

        if (!$quota) {
            return [
                'monthly_limit' => OCR_MONTHLY_QUOTA,
                'current_usage' => 0,
                'remaining' => OCR_MONTHLY_QUOTA,
                'percentage' => 0.0,
                'month_year' => $currentMonth
            ];
        }

        $usage = (int)$quota['current_usage'];
        $limit = (int)$quota['monthly_limit'];
        $remaining = max(0, $limit - $usage);
        $percentage = round(($usage / $limit) * 100, 2);

        return [
            'monthly_limit' => $limit,
            'current_usage' => $usage,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'month_year' => $quota['month_year']
        ];
    }

    public static function incrementQuota(): void {
        $pdo = Database::getConnection();
        $check = $pdo->prepare("SELECT id FROM api_quotas WHERE service_name = 'OCR_SPACE'");
        $check->execute();
        if ($check->fetch()) {
            $stmt = $pdo->prepare("UPDATE api_quotas SET current_usage = current_usage + 1, last_updated = CURRENT_TIMESTAMP WHERE service_name = 'OCR_SPACE'");
            $stmt->execute();
        } else {
            $limit = defined('OCR_MONTHLY_QUOTA') ? OCR_MONTHLY_QUOTA : 25000;
            $ins = $pdo->prepare("INSERT INTO api_quotas (service_name, monthly_limit, current_usage, month_year) VALUES ('OCR_SPACE', ?, 1, ?)");
            $ins->execute([$limit, date('Y-m')]);
        }
    }

    public static function getDepartmentMetrics(): array {
        $pdo = Database::getConnection();

        $totalRecords = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
        $totalFaculty = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'faculty'")->fetchColumn();

        $thisMonth = date('Y-m');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE SUBSTR(date_issued, 1, 7) = ?");
        $stmt->execute([$thisMonth]);
        $monthlyRecords = $stmt->fetchColumn();

        $vaultSize = 0;
        if (is_dir(VAULT_DIR)) {
            foreach (scandir(VAULT_DIR) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $vaultSize += filesize(VAULT_DIR . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        $catStmt = $pdo->query("SELECT category, COUNT(*) as count FROM certificates GROUP BY category");
        $categories = $catStmt->fetchAll();

        return [
            'total_records' => (int)$totalRecords,
            'total_faculty' => (int)$totalFaculty,
            'monthly_records' => (int)$monthlyRecords,
            'vault_size_bytes' => $vaultSize,
            'vault_size_mb' => round($vaultSize / (1024 * 1024), 2),
            'categories' => $categories
        ];
    }
}
