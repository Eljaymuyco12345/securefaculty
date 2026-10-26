<?php
/**
 * AuditLogDAOInterface
 * Data Access Object contract for RA 10173 Audit Logs
 * ISUFST CICI Systematic Records Management Portal
 */

interface AuditLogDAOInterface {
    public static function insertLog(?int $userId, string $username, string $role, string $action, ?int $targetId, string $details, string $ip, string $userAgent): void;
    public static function getRecentLogs(int $limit = 50, ?string $actionFilter = null): array;
    public static function getPaginatedLogs(?string $search = null, ?string $actionFilter = null, int $page = 1, int $perPage = 10): array;
}
