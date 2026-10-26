<?php
/**
 * UserModel
 * Domain Entity & Business Model for User Accounts
 * Model Layer (coordinates with UserDAO for persistence)
 */

require_once __DIR__ . '/../DAO/UserDAO.php';

class UserModel {
    public static function getAllWithProfiles(): array {
        return UserDAO::getAllWithProfiles();
    }

    public static function getFacultyList(): array {
        return UserDAO::getFacultyList();
    }

    public static function getFacultyOnly(): array {
        return UserDAO::getFacultyOnly();
    }

    public static function find(int $id): ?array {
        return UserDAO::find($id);
    }

    public static function findByUsername(string $username): ?array {
        return UserDAO::findByUsername($username);
    }

    public static function getProfile(int $userId): ?array {
        return UserDAO::getProfile($userId);
    }

    public static function getStats(): array {
        return UserDAO::getStats();
    }

    public static function getFacultyLookup(): array {
        return UserDAO::getFacultyLookup();
    }

    public static function create(array $data): int {
        return UserDAO::create($data);
    }

    public static function toggleStatus(int $id, int $currentUserId): array {
        return UserDAO::toggleStatus($id, $currentUserId);
    }

    public static function resetPassword(int $id, string $newPassword): bool {
        return UserDAO::resetPassword($id, $newPassword);
    }

    public static function updateProfile(int $id, array $data, ?int $currentUserId = null): bool {
        return UserDAO::updateProfile($id, $data, $currentUserId);
    }

    public static function delete(int $id, int $currentUserId): array {
        return UserDAO::delete($id, $currentUserId);
    }
}
