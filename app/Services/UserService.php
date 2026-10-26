<?php
/**
 * UserService
 * Business logic and governance for User Accounts and Faculty Profiles
 * Service Layer
 */

require_once __DIR__ . '/../DAO/UserDAO.php';
require_once __DIR__ . '/../Services/AuditLogger.php';

class UserService {
    public static function create(array $input): array {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');
        $fullName = trim($input['full_name'] ?? '');
        $email = trim($input['email'] ?? '');

        if (empty($username) || empty($password) || empty($fullName)) {
            return ['success' => false, 'code' => 400, 'message' => 'Username, full name, and initial password are required.'];
        }

        // Validate username formatting (alphanumeric, dot, underscore, hyphen, 3-32 characters)
        if (!preg_match('/^[a-zA-Z0-9._-]{3,32}$/', $username)) {
            return ['success' => false, 'code' => 400, 'message' => 'Username must be between 3 and 32 characters and contain only letters, numbers, dots, underscores, or hyphens.'];
        }

        if (strlen($password) < 6 || strlen($password) > 128) {
            return ['success' => false, 'code' => 400, 'message' => 'Password must be between 6 and 128 characters.'];
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'code' => 400, 'message' => 'Invalid email address format.'];
        }

        if (UserDAO::findByUsername($username)) {
            return ['success' => false, 'code' => 409, 'message' => 'Username already registered.'];
        }

        $role = in_array($input['role'] ?? '', ['dean', 'faculty'], true) ? $input['role'] : 'faculty';
        $input['role'] = $role;

        $newId = UserDAO::create($input);
        AuditLogger::log('USER_CREATED', "New user account created: {$username} ({$role})", $newId);

        return ['success' => true, 'user_id' => $newId];
    }

    public static function toggleStatus(int $userId, int $currentUserId): array {
        $res = UserDAO::toggleStatus($userId, $currentUserId);
        if (!$res['success']) {
            return ['success' => false, 'code' => 400, 'message' => $res['message']];
        }

        AuditLogger::log('USER_STATUS_TOGGLED', "User {$res['username']} status set to " . ($res['is_active'] ? 'Active' : 'Deactivated'), $userId);
        return ['success' => true, 'is_active' => $res['is_active']];
    }

    public static function updateProfile(int $userId, array $input, int $currentUserId): array {
        $user = UserDAO::find($userId);
        if (!$user) {
            return ['success' => false, 'code' => 404, 'message' => 'User not found.'];
        }

        $fullName = trim($input['full_name'] ?? '');
        if (empty($fullName)) {
            return ['success' => false, 'code' => 400, 'message' => 'Full name cannot be empty.'];
        }

        $email = trim($input['email'] ?? '');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'code' => 400, 'message' => 'Invalid email address format.'];
        }

        // Self-demotion guard
        if ($userId === $currentUserId && ($user['role'] ?? '') === 'dean' && isset($input['role']) && $input['role'] !== 'dean') {
            return ['success' => false, 'code' => 400, 'message' => 'Cannot demote your own administrator account.'];
        }

        $ok = UserDAO::updateProfile($userId, $input, $currentUserId);
        if (!$ok) {
            return ['success' => false, 'code' => 400, 'message' => 'Failed to update profile or cannot demote sole remaining administrator.'];
        }

        AuditLogger::log('USER_PROFILE_UPDATED', "Updated profile for user ID #{$userId}", $userId);
        return ['success' => true, 'message' => 'User profile updated successfully'];
    }

    public static function resetPassword(int $userId, string $newPassword): array {
        if (!UserDAO::find($userId)) {
            return ['success' => false, 'code' => 404, 'message' => 'Target user does not exist.'];
        }

        if (strlen($newPassword) < 6 || strlen($newPassword) > 128) {
            return ['success' => false, 'code' => 400, 'message' => 'Password must be between 6 and 128 characters.'];
        }

        UserDAO::resetPassword($userId, $newPassword);
        AuditLogger::log('USER_PASSWORD_RESET', "Password reset for user ID #{$userId}", $userId);
        return ['success' => true, 'message' => 'Password reset successfully'];
    }

    public static function delete(int $userId, int $currentUserId): array {
        $res = UserDAO::delete($userId, $currentUserId);
        if (!$res['success']) {
            return ['success' => false, 'code' => 400, 'message' => $res['message']];
        }

        AuditLogger::log('USER_DELETED', "User account {$res['username']} deleted", $userId);
        return ['success' => true, 'message' => 'User deleted successfully'];
    }
}
