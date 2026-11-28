<?php
/**
 * AuditLogDAO
 * Concrete Data Access Object for Immutable Compliance Audit Logs
 * Implements AuditLogDAOInterface
 * DAO Layer
 */

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Interfaces/AuditLogDAOInterface.php';

class AuditLogDAO implements AuditLogDAOInterface {
    public static function insertLog(?int $userId, string $username, string $role, string $action, ?int $targetId, string $details, string $ip, string $userAgent): void {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, username, role, action, target_id, details, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $username, $role, $action, $targetId, $details, $ip, $userAgent]);
        } catch (Exception $e) {
            error_log('AuditLogDAO Insert Error: ' . $e->getMessage());
        }
    }

    public static function getRecentLogs(int $limit = 50, ?string $actionFilter = null): array {
        $pdo = Database::getConnection();
        if ($actionFilter) {
            $stmt = $pdo->prepare("SELECT * FROM audit_logs WHERE action = ? ORDER BY created_at DESC LIMIT ?");
            $stmt->bindValue(1, $actionFilter, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    /**
     * Build shared FROM/JOIN, WHERE, SELECT, and parameter bindings for audit log queries.
     * Prevents polymorphic target_id collisions between certificate IDs and user IDs.
     */
    private static function buildQueryParts(
        ?string $search,
        ?string $actionFilter,
        ?int $facultyId,
        ?string $category
    ): array {
        $fromAndJoin = "
            FROM audit_logs a
            LEFT JOIN users u_target ON a.target_id = u_target.id
                AND (a.action LIKE 'USER_%' OR a.action = 'PROFILE_SWITCH' OR a.action = 'PASSWORD_RESET_OTP_SENT')
            LEFT JOIN certificates c ON a.target_id = c.id
                AND a.action IN ('ARCHIVE_DOCUMENT','DELETE_DOCUMENT','VIEW_DOCUMENT','DOWNLOAD_DOCUMENT','COMMIT_VAULT_RECORD','BULK_DELETE_DOCUMENTS')
            LEFT JOIN users u_cert ON c.faculty_id = u_cert.id
            LEFT JOIN users u_actor ON a.user_id = u_actor.id
            WHERE 1=1
        ";

        $params = [];
        $whereClauses = "";

        if ($search && trim($search) !== '') {
            $trimmedSearch = mb_substr(trim($search), 0, 100);
            $whereClauses .= " AND (a.details LIKE :s1 OR a.username LIKE :s2 OR a.ip_address LIKE :s3 OR a.action LIKE :s4 OR u_target.full_name LIKE :s5 OR u_cert.full_name LIKE :s6 OR u_actor.full_name LIKE :s7)";
            $term = "%" . $trimmedSearch . "%";
            $params[':s1'] = $term;
            $params[':s2'] = $term;
            $params[':s3'] = $term;
            $params[':s4'] = $term;
            $params[':s5'] = $term;
            $params[':s6'] = $term;
            $params[':s7'] = $term;
        }

        if ($actionFilter && trim($actionFilter) !== '' && $actionFilter !== 'ALL') {
            $act = trim($actionFilter);
            if ($act === 'LOGIN') {
                $whereClauses .= " AND (a.action LIKE 'LOGIN%' OR a.action = 'LOGOUT')";
            } elseif ($act === 'OCR_EXTRACT') {
                $whereClauses .= " AND (a.action LIKE 'OCR%' OR a.action = 'BATCH_UPLOAD')";
            } elseif ($act === 'EXPORT_REPORT') {
                $whereClauses .= " AND a.action LIKE '%EXPORT%'";
            } elseif ($act === 'DOC_OPERATIONS' || $act === 'DOC_OPS') {
                $whereClauses .= " AND a.action IN ('ARCHIVE_DOCUMENT','DELETE_DOCUMENT','VIEW_DOCUMENT','DOWNLOAD_DOCUMENT','OCR_EXTRACT','COMMIT_VAULT_RECORD','BULK_DELETE_DOCUMENTS')";
            } elseif ($act === 'APPROVED') {
                $whereClauses .= " AND a.action IN ('LOGIN_SUCCESS', 'COMMIT_VAULT_RECORD', 'VIEW_DOCUMENT', 'DOWNLOAD_DOCUMENT')";
            } elseif ($act === 'REJECTED') {
                $whereClauses .= " AND a.action IN ('LOGIN_FAILED', 'UNAUTHORIZED_ACCESS_ATTEMPT', 'LOGIN_BLOCKED', 'CSRF_VIOLATION_BLOCKED', 'BACKUP_FAILED')";
            } else {
                $whereClauses .= " AND a.action = :act";
                $params[':act'] = mb_substr($act, 0, 50);
            }
        }

        if ($facultyId !== null && $facultyId > 0) {
            $whereClauses .= " AND (a.user_id = :fac_id1 OR c.faculty_id = :fac_id2 OR u_target.id = :fac_id3)";
            $params[':fac_id1'] = $facultyId;
            $params[':fac_id2'] = $facultyId;
            $params[':fac_id3'] = $facultyId;
        }

        if ($category && trim($category) !== '' && $category !== 'All') {
            $whereClauses .= " AND (c.category LIKE :cat OR a.details LIKE :cat_term)";
            $params[':cat'] = '%' . trim($category) . '%';
            $params[':cat_term'] = '%' . trim($category) . '%';
        }

        $selectFields = "
            SELECT a.*,
            COALESCE(
                u_cert.full_name,
                u_target.full_name,
                u_actor.full_name,
                CASE 
                    WHEN a.action IN ('DATABASE_BACKUP_CREATED', 'BACKUP_FAILED') THEN 'System Service'
                    WHEN a.action LIKE '%EXPORT%' THEN 'Accreditation Ledger'
                    ELSE 'Anonymous / Unauthenticated'
                END
            ) AS faculty_display
        ";

        return [$fromAndJoin, $whereClauses, $selectFields, $params];
    }

    public static function getPaginatedLogs(?string $search = null, ?string $actionFilter = null, int $page = 1, int $perPage = 10, ?int $facultyId = null, ?string $category = null): array {
        $pdo = Database::getConnection();
        [$fromAndJoin, $whereClauses, $selectFields, $params] = self::buildQueryParts($search, $actionFilter, $facultyId, $category);

        $countQuery = "SELECT COUNT(*) " . $fromAndJoin . $whereClauses;
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalRows = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $logQuery = $selectFields . $fromAndJoin . $whereClauses . " ORDER BY a.created_at DESC, a.id DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($logQuery);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $displayRows = $stmt->fetchAll();

        return [
            'rows' => $displayRows,
            'totalRows' => $totalRows,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }

    public static function getExportLogs(?string $search = null, ?string $actionFilter = null, ?int $facultyId = null, ?string $category = null): array {
        $pdo = Database::getConnection();
        [$fromAndJoin, $whereClauses, $selectFields, $params] = self::buildQueryParts($search, $actionFilter, $facultyId, $category);

        $query = $selectFields . $fromAndJoin . $whereClauses . " ORDER BY a.created_at DESC, a.id DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
