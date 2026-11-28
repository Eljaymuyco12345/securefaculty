<?php
/**
 * AvatarService
 * Securely validates, stores, and removes user profile pictures.
 */

class AvatarService {
    private const MAX_BYTES = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    public static function getUploadDir(): string {
        $dir = BASE_DIR . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Validate and store an uploaded profile picture for the given user.
     *
     * @return array{success: bool, code?: int, message: string, avatar_path?: string}
     */
    public static function upload(int $userId, array $file): array {
        if (empty($file) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'code' => 400, 'message' => 'No valid image file uploaded.'];
        }

        $tmpPath = $file['tmp_name'] ?? '';
        if ($tmpPath === '' || !file_exists($tmpPath)) {
            return ['success' => false, 'code' => 400, 'message' => 'Uploaded temporary file is missing.'];
        }

        $size = (int)($file['size'] ?? filesize($tmpPath));
        if ($size <= 0 || $size > self::MAX_BYTES) {
            return ['success' => false, 'code' => 400, 'message' => 'Profile picture must be under 5 MB.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath);
        if (!$mime || !isset(self::ALLOWED_MIMES[$mime])) {
            return ['success' => false, 'code' => 400, 'message' => 'Only JPG, PNG, and WEBP images are allowed.'];
        }

        if (@getimagesize($tmpPath) === false) {
            return ['success' => false, 'code' => 400, 'message' => 'Invalid or corrupted image file.'];
        }

        $user = UserDAO::find($userId);
        if (!$user) {
            return ['success' => false, 'code' => 404, 'message' => 'User account not found.'];
        }

        $ext = self::ALLOWED_MIMES[$mime];
        $filename = 'avatar_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $uploadDir = self::getUploadDir();
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        $moved = is_uploaded_file($tmpPath)
            ? @move_uploaded_file($tmpPath, $destPath)
            : @copy($tmpPath, $destPath);

        if (!$moved || !file_exists($destPath)) {
            return ['success' => false, 'code' => 500, 'message' => 'Failed to save profile picture on server.'];
        }

        // Delete old avatar file if one existed
        self::deleteAvatarFile($user['avatar_path'] ?? null);

        $relativePath = 'assets/uploads/avatars/' . $filename;
        UserModel::updateAvatar($userId, $relativePath);
        AuditLogger::log('USER_AVATAR_UPDATED', "User #{$userId} updated their profile picture.", $userId);

        return [
            'success' => true,
            'message' => 'Profile picture updated successfully.',
            'avatar_path' => $relativePath
        ];
    }

    /**
     * Remove the user's current profile picture.
     *
     * @return array{success: bool, code?: int, message: string}
     */
    public static function remove(int $userId): array {
        $user = UserDAO::find($userId);
        if (!$user) {
            return ['success' => false, 'code' => 404, 'message' => 'User account not found.'];
        }

        self::deleteAvatarFile($user['avatar_path'] ?? null);
        UserModel::updateAvatar($userId, null);
        AuditLogger::log('USER_AVATAR_REMOVED', "User #{$userId} removed their profile picture.", $userId);

        return ['success' => true, 'message' => 'Profile picture removed.'];
    }

    private static function deleteAvatarFile(?string $relativePath): void {
        if (!$relativePath || trim($relativePath) === '') {
            return;
        }
        $cleanName = basename($relativePath);
        $fullPath = self::getUploadDir() . DIRECTORY_SEPARATOR . $cleanName;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
