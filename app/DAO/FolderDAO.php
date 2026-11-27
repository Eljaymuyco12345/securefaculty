<?php
/**
 * FolderDAO
 * Data Access Object for 1-level Virtual Folders in the Digital Vault
 * DAO Layer
 */

require_once __DIR__ . '/../Core/Database.php';

class FolderDAO {
    /**
     * Get all virtual folders owned by a user with certificate item counts.
     */
    public static function getForUser(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT vf.id,
                   vf.user_id,
                   vf.name,
                   vf.color,
                   vf.created_at,
                   (SELECT COUNT(*) FROM certificates c WHERE c.folder_id = vf.id) AS item_count
            FROM vault_folders vf
            WHERE vf.user_id = ?
            ORDER BY vf.name ASC, vf.id ASC
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['id'] = (int)$row['id'];
            $row['user_id'] = (int)$row['user_id'];
            $row['item_count'] = (int)$row['item_count'];
        }
        unset($row);

        return $rows;
    }

    /**
     * Find a single virtual folder by ID.
     */
    public static function find(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM vault_folders WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Create a new virtual folder for a user and return its ID.
     */
    public static function create(int $userId, string $name, string $color = 'blue'): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO vault_folders (user_id, name, color) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $name, $color]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Rename a virtual folder (enforces ownership unless $isDean is true).
     */
    public static function rename(int $folderId, int $userId, string $newName, bool $isDean = false): bool {
        $folder = self::find($folderId);
        if (!$folder) {
            return false;
        }
        if (!$isDean && (int)$folder['user_id'] !== $userId) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE vault_folders SET name = ? WHERE id = ?");
        return $stmt->execute([$newName, $folderId]);
    }

    /**
     * Delete a virtual folder inside a transaction, safely unlinking all certificates first (zero data loss).
     */
    public static function delete(int $folderId, int $userId, bool $isDean = false): bool {
        $folder = self::find($folderId);
        if (!$folder) {
            return false;
        }
        if (!$isDean && (int)$folder['user_id'] !== $userId) {
            return false;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $unlinkStmt = $pdo->prepare("UPDATE certificates SET folder_id = NULL WHERE folder_id = ?");
            $unlinkStmt->execute([$folderId]);

            $delStmt = $pdo->prepare("DELETE FROM vault_folders WHERE id = ?");
            $delStmt->execute([$folderId]);

            $pdo->commit();
            return $delStmt->rowCount() > 0;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Move certificates into a target folder (or NULL to unfile).
     * Verifies target folder exists and belongs to $userId (if non-null),
     * and only moves certificates owned/co-authored by $userId (or any if $isDean).
     */
    public static function moveCertificates(array $certIds, ?int $folderId, int $userId, bool $isDean = false): int {
        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $certIds), fn(int $id): bool => $id > 0)));
        if (empty($cleanIds)) {
            return 0;
        }

        if ($folderId !== null) {
            $folder = self::find($folderId);
            if (!$folder || (int)$folder['user_id'] !== $userId) {
                return 0;
            }
        }

        $pdo = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));

        if ($isDean) {
            $sql = "UPDATE certificates SET folder_id = ? WHERE id IN ({$placeholders})";
            $params = array_merge([$folderId], $cleanIds);
        } else {
            $sql = "
                UPDATE certificates
                SET folder_id = ?
                WHERE id IN ({$placeholders})
                  AND (faculty_id = ? OR id IN (SELECT certificate_id FROM certificate_authors WHERE user_id = ?))
            ";
            $params = array_merge([$folderId], $cleanIds, [$userId, $userId]);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
