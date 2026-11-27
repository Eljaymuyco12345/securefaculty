<?php
/**
 * PasswordResetService
 * Handles Self-Service Change Password (Authenticated) and Forgot Password 6-Digit Email OTP Recovery.
 */

class PasswordResetService {
    private const MAX_OTP_ATTEMPTS = 5;

    /**
     * Change password for an authenticated user (Faculty or Dean).
     *
     * @return array{success: bool, code?: int, message: string}
     */
    public static function changePassword(
        int $userId,
        string $currentPassword,
        string $newPassword,
        string $confirmPassword
    ): array {
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return ['success' => false, 'code' => 400, 'message' => 'All password fields are required.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'code' => 400, 'message' => 'New password and confirmation do not match.'];
        }

        if (strlen($newPassword) < 6 || strlen($newPassword) > 128) {
            return ['success' => false, 'code' => 400, 'message' => 'New password must be between 6 and 128 characters.'];
        }

        $user = UserDAO::find($userId);
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'code' => 401, 'message' => 'Current password is incorrect.'];
        }

        UserDAO::resetPassword($userId, $newPassword);
        AuditLogger::log('USER_PASSWORD_CHANGED', "User #{$userId} ({$user['username']}) changed their own password.", $userId);

        return ['success' => true, 'message' => 'Your password has been updated successfully.'];
    }

    /**
     * Generate and email a 6-digit OTP for Forgot Password recovery.
     *
     * @return array{success: bool, code?: int, message: string}
     */
    public static function requestOtp(string $identifier): array {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return ['success' => false, 'code' => 400, 'message' => 'Please enter your registered email address or username.'];
        }

        $genericSuccess = [
            'success' => true,
            'message' => 'If an active account with that email exists, a 6-digit OTP has been sent.'
        ];

        $user = self::findActiveUserByIdentifier($identifier);
        if (!$user || empty($user['email']) || !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            return $genericSuccess;
        }

        $pdo = Database::getConnection();
        $expiryMinutes = defined('OTP_EXPIRY_MINUTES') ? (int)OTP_EXPIRY_MINUTES : 15;

        // Invalidate any prior unused OTPs for this user
        $invStmt = $pdo->prepare("UPDATE password_resets SET used_at = CURRENT_TIMESTAMP WHERE user_id = ? AND used_at IS NULL");
        $invStmt->execute([(int)$user['id']]);

        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $tokenHash = hash('sha256', $otp);
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiryMinutes * 60));

        $insStmt = $pdo->prepare("
            INSERT INTO password_resets (user_id, email, token_hash, attempts, expires_at)
            VALUES (?, ?, ?, 0, ?)
        ");
        $insStmt->execute([(int)$user['id'], $user['email'], $tokenHash, $expiresAt]);

        $emailResult = EmailService::sendOtpEmail($user['email'], $user['full_name'], $otp);
        if (!$emailResult['success']) {
            return [
                'success' => false,
                'code' => 503,
                'message' => $emailResult['message']
            ];
        }

        AuditLogger::log('PASSWORD_RESET_OTP_SENT', "Password reset OTP sent to {$user['email']} for user #{$user['id']}.", (int)$user['id']);
        return $genericSuccess;
    }

    /**
     * Verify 6-digit OTP and reset the user's password.
     *
     * @return array{success: bool, code?: int, message: string}
     */
    public static function verifyOtpAndReset(
        string $identifier,
        string $otp,
        string $newPassword,
        string $confirmPassword
    ): array {
        $identifier = trim($identifier);
        $otp = trim($otp);

        if ($identifier === '' || !preg_match('/^\d{6}$/', $otp)) {
            return ['success' => false, 'code' => 400, 'message' => 'Please enter your email/username and a valid 6-digit OTP code.'];
        }

        if ($newPassword === '' || $newPassword !== $confirmPassword) {
            return ['success' => false, 'code' => 400, 'message' => 'New password and confirmation do not match.'];
        }

        if (strlen($newPassword) < 6 || strlen($newPassword) > 128) {
            return ['success' => false, 'code' => 400, 'message' => 'New password must be between 6 and 128 characters.'];
        }

        $user = self::findActiveUserByIdentifier($identifier);
        if (!$user) {
            return ['success' => false, 'code' => 400, 'message' => 'Invalid or expired OTP code.'];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM password_resets
            WHERE user_id = ? AND used_at IS NULL
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([(int)$user['id']]);
        $reset = $stmt->fetch();

        if (!$reset) {
            return ['success' => false, 'code' => 400, 'message' => 'No active OTP request found. Please request a new code.'];
        }

        if ((int)$reset['attempts'] >= self::MAX_OTP_ATTEMPTS) {
            return ['success' => false, 'code' => 429, 'message' => 'Too many invalid OTP attempts. Please request a new 6-digit code.'];
        }

        if (strtotime($reset['expires_at']) < time()) {
            return ['success' => false, 'code' => 400, 'message' => 'Your 6-digit OTP has expired. Please request a new code.'];
        }

        if (!hash_equals($reset['token_hash'], hash('sha256', $otp))) {
            $upd = $pdo->prepare("UPDATE password_resets SET attempts = attempts + 1 WHERE id = ?");
            $upd->execute([(int)$reset['id']]);
            return ['success' => false, 'code' => 400, 'message' => 'Incorrect 6-digit OTP code.'];
        }

        // Mark OTP as used and update password
        $useStmt = $pdo->prepare("UPDATE password_resets SET used_at = CURRENT_TIMESTAMP WHERE id = ?");
        $useStmt->execute([(int)$reset['id']]);

        UserDAO::resetPassword((int)$user['id'], $newPassword);
        AuditLogger::log('USER_PASSWORD_RESET_OTP', "User #{$user['id']} ({$user['username']}) reset password via 6-digit Email OTP.", (int)$user['id']);

        return ['success' => true, 'message' => 'Password has been reset successfully. You may now sign in with your new password.'];
    }

    private static function findActiveUserByIdentifier(string $identifier): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, username, full_name, email, is_active
            FROM users
            WHERE (LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?))
              AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
