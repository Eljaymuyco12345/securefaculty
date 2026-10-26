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

    public static function getPaginatedLogs(?string $search = null, ?string $actionFilter = null, int $page = 1, int $perPage = 10): array {
        $pdo = Database::getConnection();
        
        $countQuery = "
            SELECT COUNT(*)
            FROM audit_logs a
            WHERE 1=1
        ";
        $params = [];
        if ($search && trim($search) !== '') {
            $countQuery .= " AND (a.details LIKE :s1 OR a.username LIKE :s2 OR a.ip_address LIKE :s3 OR a.action LIKE :s4)";
            $term = "%" . trim($search) . "%";
            $params[':s1'] = $term;
            $params[':s2'] = $term;
            $params[':s3'] = $term;
            $params[':s4'] = $term;
        }
        if ($actionFilter && trim($actionFilter) !== '') {
            $countQuery .= " AND a.action = :act";
            $params[':act'] = trim($actionFilter);
        }

        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalRows = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $logQuery = "
            SELECT a.*, COALESCE(u.full_name, 'Departmental Audit') as faculty_display
            FROM audit_logs a
            LEFT JOIN users u ON a.target_id = u.id
            WHERE 1=1
        ";
        if ($search && trim($search) !== '') {
            $logQuery .= " AND (a.details LIKE :s1 OR a.username LIKE :s2 OR a.ip_address LIKE :s3 OR a.action LIKE :s4)";
        }
        if ($actionFilter && trim($actionFilter) !== '') {
            $logQuery .= " AND a.action = :act";
        }
        $logQuery .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";

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

    public static function getExportLogs(?string $search = null, ?string $actionFilter = null): array {
        $pdo = Database::getConnection();
        $query = "
            SELECT a.*, COALESCE(u.full_name, 'Departmental Audit') as faculty_display
            FROM audit_logs a
            LEFT JOIN users u ON a.target_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($search && trim($search) !== '') {
            $query .= " AND (a.details LIKE :s1 OR a.username LIKE :s2 OR a.ip_address LIKE :s3 OR a.action LIKE :s4)";
            $term = "%" . trim($search) . "%";
            $params[':s1'] = $term;
            $params[':s2'] = $term;
            $params[':s3'] = $term;
            $params[':s4'] = $term;
        }
        if ($actionFilter && trim($actionFilter) !== '') {
            $query .= " AND a.action = :act";
            $params[':act'] = trim($actionFilter);
        }
        $query .= " ORDER BY a.created_at DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
