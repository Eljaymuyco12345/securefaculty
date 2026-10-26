<?php
/**
 * CertificateDAOInterface
 * Data Access Object contract for Certificate domain entities
 * ISUFST CICI Systematic Records Management Portal
 */

interface CertificateDAOInterface {
    public static function getAll($categoryOrFilters = null, ?string $search = null, ?int $facultyId = null, ?string $dateFrom = null, ?string $dateTo = null, ?string $issuedYear = null): array;
    public static function getForFaculty(int $facultyId, ?array $filters = null): array;
    public static function find(int $id): ?array;
    public static function findByHash(string $hash): ?array;
    public static function getByIds(array $ids, ?int $facultyId = null): array;
    public static function findDuplicateByTitleAndDate(int $facultyId, string $title, string $dateIssued): ?array;
    public static function isAuthorOrCoAuthor(int $certificateId, int $userId): bool;
    public static function create(array $data, array $coAuthorIds = []): int;
    public static function createBatch(array $batchData): void;
    public static function delete(int $id, int $requestingUserId, bool $isDean = false): bool;
    public static function bulkDelete(array $ids, int $requestingUserId, bool $isDean): int;
    public static function getAvailableIssuedYears(): array;
    public static function getQuotaStatus(): array;
    public static function incrementQuota(): void;
    public static function getDepartmentMetrics(): array;
}
