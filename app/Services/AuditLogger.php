<?php
/**
 * Audit Logging Service
 * Full compliance with Republic Act No. 10173 (Data Privacy Act of 2012)
 * Service Layer (delegates persistence to AuditLogDAO)
 */

require_once __DIR__ . '/../DAO/AuditLogDAO.php';

class AuditLogger {
    public static function log(string $action, string $details, ?int $targetId = null): void {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'Anonymous';
        $role = $_SESSION['role'] ?? 'Guest';
        $ip = self::getClientIp();
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Unknown', 0, 255);

        AuditLogDAO::insertLog($userId, $username, $role, $action, $targetId, $details, $ip, $userAgent);
    }

    public static function getClientIp(): string {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        // Only inspect proxy headers if request originated from local reverse proxy
        if (in_array($remoteAddr, ['127.0.0.1', '::1', '10.0.0.1'])) {
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $candidates = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $first = trim($candidates[0]);
                if (filter_var($first, FILTER_VALIDATE_IP)) {
                    return $first;
                }
            }
        }
        return filter_var($remoteAddr, FILTER_VALIDATE_IP) ? $remoteAddr : '127.0.0.1';
    }

    public static function getRecentLogs(int $limit = 50, ?string $actionFilter = null): array {
        return AuditLogDAO::getRecentLogs($limit, $actionFilter);
    }

    public static function getPaginatedLogs(?string $search = null, ?string $actionFilter = null, int $page = 1, int $perPage = 10): array {
        return AuditLogDAO::getPaginatedLogs($search, $actionFilter, $page, $perPage);
    }

    public static function getExportLogs(?string $search = null, ?string $actionFilter = null): array {
        return AuditLogDAO::getExportLogs($search, $actionFilter);
    }
}
