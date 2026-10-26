<?php
/**
 * CertificateModel
 * Domain Entity & Business Model for Academic Certificate Assets
 * Model Layer (coordinates with CertificateDAO for persistence)
 */

require_once __DIR__ . '/../DAO/CertificateDAO.php';

class CertificateModel {
    /**
     * Domain validation & date normalization into standard ISO YYYY-MM-DD format
     * Returns null if no valid date is provided (prevents false upload-date stamping)
     */
    public static function normalizeDate(?string $str): ?string {
        if (!$str || trim($str) === '') return null;
        $s = trim($str);
        if (preg_match('/^(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})$/', $s, $m)) {
            $y = (int)$m[1]; $mon = (int)$m[2]; $d = (int)$m[3];
            if ($y >= 1950 && $y <= 2099 && $mon >= 1 && $mon <= 12 && $d >= 1 && $d <= 31) {
                return sprintf('%04d-%02d-%02d', $y, $mon, $d);
            }
        }
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $s, $m)) {
            $n1 = (int)$m[1]; $n2 = (int)$m[2]; $y = (int)$m[3];
            if ($y >= 1950 && $y <= 2099) {
                if ($n1 > 12 && $n2 >= 1 && $n2 <= 12 && $n1 <= 31) {
                    // DD/MM/YYYY or DD-MM-YYYY
                    return sprintf('%04d-%02d-%02d', $y, $n2, $n1);
                } elseif ($n1 >= 1 && $n1 <= 12 && $n2 >= 1 && $n2 <= 31) {
                    // MM/DD/YYYY or MM-DD-YYYY
                    return sprintf('%04d-%02d-%02d', $y, $n1, $n2);
                }
            }
        }
        // Match Philippine certificate phrasing: "15th day of December, 2022" or "15 December 2022"
        if (preg_match('/(\d{1,2})(?:st|nd|rd|th)?\s+(?:day\s+of\s+)?([A-Za-z]+),?\s*(\d{4})/i', $s, $m)) {
            $cleaned = $m[1] . ' ' . $m[2] . ' ' . $m[3];
            $ts = strtotime($cleaned);
            if ($ts !== false && $ts > 0) {
                $y = (int)date('Y', $ts);
                if ($y >= 1950 && $y <= 2099) {
                    return date('Y-m-d', $ts);
                }
            }
        }
        $ts = strtotime($s);
        if ($ts !== false && $ts > 0) {
            $y = (int)date('Y', $ts);
            if ($y >= 1950 && $y <= 2099) {
                return date('Y-m-d', $ts);
            }
        }
        return null;
    }

    public static function find(int $id): ?array {
        return CertificateDAO::find($id);
    }

    public static function findByHash(string $hash): ?array {
        return CertificateDAO::findByHash($hash);
    }

    public static function getByIds(array $ids, ?int $facultyId = null): array {
        return CertificateDAO::getByIds($ids, $facultyId);
    }

    public static function findDuplicateByTitleAndDate(int $facultyId, string $title, string $dateIssued): ?array {
        return CertificateDAO::findDuplicateByTitleAndDate($facultyId, $title, $dateIssued);
    }

    public static function isAuthorOrCoAuthor(int $certificateId, int $userId): bool {
        return CertificateDAO::isAuthorOrCoAuthor($certificateId, $userId);
    }

    public static function getForFaculty(int $facultyId, ?array $filters = null): array {
        return CertificateDAO::getForFaculty($facultyId, $filters);
    }

    public static function getAll($categoryOrFilters = null, ?string $search = null, ?int $facultyId = null, ?string $dateFrom = null, ?string $dateTo = null, ?string $issuedYear = null): array {
        return CertificateDAO::getAll($categoryOrFilters, $search, $facultyId, $dateFrom, $dateTo, $issuedYear);
    }

    public static function create(array $data, array $coAuthorIds = []): int {
        return CertificateDAO::create($data, $coAuthorIds);
    }

    public static function createBatch(array $batchData): void {
        CertificateDAO::createBatch($batchData);
    }

    public static function delete(int $id, int $requestingUserId, bool $isDean = false): bool {
        return CertificateDAO::delete($id, $requestingUserId, $isDean);
    }

    public static function bulkDelete(array $ids, int $requestingUserId, bool $isDean): int {
        return CertificateDAO::bulkDelete($ids, $requestingUserId, $isDean);
    }

    public static function getAvailableIssuedYears(): array {
        return CertificateDAO::getAvailableIssuedYears();
    }

    public static function getQuotaStatus(): array {
        return CertificateDAO::getQuotaStatus();
    }

    public static function incrementQuota(): void {
        CertificateDAO::incrementQuota();
    }

    public static function getDepartmentMetrics(): array {
        return CertificateDAO::getDepartmentMetrics();
    }
}
