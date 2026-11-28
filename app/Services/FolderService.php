<?php
/**
 * FolderService
 * Business logic, validation, and RA 10173 audit logging for Virtual Folders
 * Service Layer
 */

require_once __DIR__ . '/../DAO/FolderDAO.php';
require_once __DIR__ . '/../Services/AuditLogger.php';

class FolderService {
    public const ALLOWED_COLORS = [
        'blue',
        'emerald',
        'amber',
        'rose',
        'violet',
        'indigo',
        'cyan',
        'teal',
        'slate'
    ];

    /**
     * List virtual folders for a user.
     */
    public static function getForUser(int $userId): array {
        return FolderDAO::getForUser($userId);
    }

    /**
     * Validate folder name (1-120 characters after trimming).
     */
    private static function validateName(string $name): ?string {
        $trimmed = trim($name);
        $len = mb_strlen($trimmed, 'UTF-8');
        if ($len < 1 || $len > 120) {
            return null;
        }
        return $trimmed;
    }

    /**
     * Create a new 1-level virtual folder.
     */
    public static function create(int $userId, string $name, string $color = 'blue'): array {
        $cleanName = self::validateName($name);
        if ($cleanName === null) {
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Folder name must be between 1 and 120 characters.'
            ];
        }

        $cleanColor = strtolower(trim($color));
        if ($cleanColor === '') {
            $cleanColor = 'blue';
        }
        if (!in_array($cleanColor, self::ALLOWED_COLORS, true)) {
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Invalid folder color. Allowed colors: ' . implode(', ', self::ALLOWED_COLORS) . '.'
            ];
        }

        $folderId = FolderDAO::create($userId, $cleanName, $cleanColor);
        $folder = FolderDAO::find($folderId);

        AuditLogger::log(
            'VAULT_FOLDER_CREATED',
            "Created virtual folder '{$cleanName}' (color: {$cleanColor})",
            $folderId
        );

        return [
            'success' => true,
            'folder_id' => $folderId,
            'folder' => $folder
        ];
    }

    /**
     * Rename an existing virtual folder.
     */
    public static function rename(int $folderId, int $userId, string $newName, bool $isDean = false): array {
        if ($folderId <= 0) {
            return ['success' => false, 'code' => 400, 'message' => 'Invalid folder ID.'];
        }

        $cleanName = self::validateName($newName);
        if ($cleanName === null) {
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Folder name must be between 1 and 120 characters.'
            ];
        }

        $folder = FolderDAO::find($folderId);
        if (!$folder) {
            return ['success' => false, 'code' => 404, 'message' => 'Folder not found.'];
        }

        if (!$isDean && (int)$folder['user_id'] !== $userId) {
            return ['success' => false, 'code' => 403, 'message' => 'Access Denied: You do not own this folder.'];
        }

        $ok = FolderDAO::rename($folderId, $userId, $cleanName, $isDean);
        if (!$ok) {
            return ['success' => false, 'code' => 500, 'message' => 'Failed to rename folder.'];
        }

        AuditLogger::log(
            'VAULT_FOLDER_RENAMED',
            "Renamed virtual folder #{$folderId} from '{$folder['name']}' to '{$cleanName}'",
            $folderId
        );

        return [
            'success' => true,
            'folder' => FolderDAO::find($folderId)
        ];
    }

    /**
     * Delete a virtual folder without deleting its certificates (zero data loss).
     */
    public static function delete(int $folderId, int $userId, bool $isDean = false): array {
        if ($folderId <= 0) {
            return ['success' => false, 'code' => 400, 'message' => 'Invalid folder ID.'];
        }

        $folder = FolderDAO::find($folderId);
        if (!$folder) {
            return ['success' => false, 'code' => 404, 'message' => 'Folder not found.'];
        }

        if (!$isDean && (int)$folder['user_id'] !== $userId) {
            return ['success' => false, 'code' => 403, 'message' => 'Access Denied: You do not own this folder.'];
        }

        $ok = FolderDAO::delete($folderId, $userId, $isDean);
        if (!$ok) {
            return ['success' => false, 'code' => 500, 'message' => 'Failed to delete folder.'];
        }

        AuditLogger::log(
            'VAULT_FOLDER_DELETED',
            "Deleted virtual folder '{$folder['name']}' (certificates unlinked safely)",
            $folderId
        );

        return ['success' => true];
    }

    /**
     * Move one or more certificates into a virtual folder (or NULL to unfile).
     */
    public static function moveCertificates(array $certIds, ?int $folderId, int $userId, bool $isDean = false): array {
        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $certIds), fn(int $id): bool => $id > 0)));
        if (empty($cleanIds)) {
            return ['success' => false, 'code' => 400, 'message' => 'No valid certificate IDs provided.'];
        }

        if ($folderId !== null) {
            $folder = FolderDAO::find($folderId);
            if (!$folder) {
                return ['success' => false, 'code' => 404, 'message' => 'Target folder not found.'];
            }
            if ((int)$folder['user_id'] !== $userId) {
                return ['success' => false, 'code' => 403, 'message' => 'Access Denied: Target folder does not belong to you.'];
            }
        }

        $movedCount = FolderDAO::moveCertificates($cleanIds, $folderId, $userId, $isDean);
        $targetLabel = $folderId !== null ? "folder #{$folderId}" : 'unfiled root';

        AuditLogger::log(
            'VAULT_CERTIFICATES_MOVED',
            "Moved {$movedCount} certificate(s) to {$targetLabel} (IDs: " . implode(',', $cleanIds) . ")",
            $folderId
        );

        return [
            'success' => true,
            'moved_count' => $movedCount
        ];
    }
}
