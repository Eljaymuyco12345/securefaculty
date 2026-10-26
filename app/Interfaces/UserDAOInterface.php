<?php
/**
 * UserDAOInterface
 * Data Access Object contract for User accounts and Faculty profiles
 * ISUFST CICI Systematic Records Management Portal
 */

interface UserDAOInterface {
    public static function getAllWithProfiles(): array;
    public static function getFacultyList(): array;
    public static function getFacultyOnly(): array;
    public static function find(int $id): ?array;
    public static function findByUsername(string $username): ?array;
    public static function getProfile(int $userId): ?array;
    public static function getStats(): array;
    public static function getFacultyLookup(): array;
    public static function create(array $data): int;
    public static function toggleStatus(int $id, int $currentUserId): array;
    public static function resetPassword(int $id, string $newPassword): bool;
    public static function updateProfile(int $id, array $data): bool;
    public static function delete(int $id, int $currentUserId): array;
}
