<?php
/**
 * AuthService
 * Authentication & Role-Based Access Control (RBAC) Service
 * Implements AuthServiceInterface
 */

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Services/AuditLogger.php';
require_once __DIR__ . '/../Interfaces/AuthServiceInterface.php';

class AuthService implements AuthServiceInterface {
    private static ?bool $cachedCheckResult = null;
    private static ?int $cachedUserId = null;

    public static function initSession(): void {
        if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function getCsrfToken(): string {
        self::initSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool {
        self::initSession();
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function attempt(string $username, string $password): bool {
        self::initSession();
        self::$cachedCheckResult = null;
        self::$cachedUserId = null;

        $trimmedUser = trim($username);
        if (empty($trimmedUser) || strlen($trimmedUser) > 64) {
            $_SESSION['login_error'] = 'invalid_credentials';
            return false;
        }

        $now = time();
        $throttle = $_SESSION['login_throttle'] ?? ['count' => 0, 'last_time' => $now, 'locked_until' => 0];

        // Check active lockout (5-minute pause after 5 consecutive failures)
        if (!empty($throttle['locked_until']) && $throttle['locked_until'] > $now) {
            $remaining = $throttle['locked_until'] - $now;
            AuditLogger::log('LOGIN_RATE_LIMITED', "Rate limited login attempt for: " . htmlspecialchars($trimmedUser) . " ({$remaining}s remaining)");
            $_SESSION['login_error'] = 'rate_limited';
            return false;
        }

        // Reset throttle window if 5 minutes have elapsed since last attempt
        if ($now - ($throttle['last_time'] ?? 0) > 300) {
            $throttle = ['count' => 0, 'last_time' => $now, 'locked_until' => 0];
        }

        // Check username-targeted brute-force threshold (5 failures within 5 minutes)
        // Neutralize SQL LIKE wildcard characters to prevent wildcard brute-force bypass/denial-of-service
        $pdo = Database::getConnection();
        try {
            $fiveMinsAgo = date('Y-m-d H:i:s', $now - 300);
            $escapedUsername = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $trimmedUser);
            $userPattern = '%username: ' . $escapedUsername . '%';
            $stmtUser = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE action IN ('LOGIN_FAILED', 'LOGIN_RATE_LIMITED', 'BRUTE_FORCE_LOCKOUT') AND details LIKE ? AND created_at >= ?");
            $stmtUser->execute([$userPattern, $fiveMinsAgo]);
            $userFailCount = (int)$stmtUser->fetchColumn();
            if ($userFailCount >= 5) {
                AuditLogger::log('LOGIN_RATE_LIMITED', "Target account brute-force protection engaged ({$userFailCount} failures in 5m): " . htmlspecialchars($trimmedUser));
                $_SESSION['login_error'] = 'rate_limited';
                return false;
            }
        } catch (Exception $e) {
            // Non-blocking fallback
        }
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$trimmedUser]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
                AuditLogger::log('LOGIN_BLOCKED', 'Deactivated account attempted authentication: ' . htmlspecialchars($username), $user['id']);
                $_SESSION['login_error'] = 'deactivated';
                return false;
            }

            // Mitigate Session Fixation (OWASP A07)
            if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
                @session_regenerate_id(true);
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['department'] = $user['department'];
            $_SESSION['is_active'] = 1;

            // Generate fresh CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Clear throttle on successful login
            unset($_SESSION['login_throttle']);

            AuditLogger::log('LOGIN_SUCCESS', 'Successful One-Gate authentication as ' . $user['role'], $user['id']);
            return true;
        }

        // Increment throttle counter
        $throttle['count'] = ($throttle['count'] ?? 0) + 1;
        $throttle['last_time'] = $now;
        if ($throttle['count'] >= 5) {
            $throttle['locked_until'] = $now + 300;
            AuditLogger::log('BRUTE_FORCE_LOCKOUT', "Security pause engaged (5 minutes) after 5 failed logins for: " . htmlspecialchars($username));
            $_SESSION['login_error'] = 'rate_limited';
        } else {
            AuditLogger::log('LOGIN_FAILED', 'Failed login attempt for username: ' . htmlspecialchars($username));
            $_SESSION['login_error'] = 'invalid_credentials';
        }
        $_SESSION['login_throttle'] = $throttle;

        return false;
    }

    public static function check(): bool {
        self::initSession();
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $userId = (int)$_SESSION['user_id'];
        if (self::$cachedCheckResult !== null && self::$cachedUserId === $userId) {
            return self::$cachedCheckResult;
        }

        // Real-Time Account Deactivation Check (OWASP A01)
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $status = $stmt->fetchColumn();

            if ($status === false || (int)$status === 0) {
                self::$cachedCheckResult = false;
                self::$cachedUserId = $userId;
                self::logout();
                return false;
            }
        } catch (Exception $e) {
            if (isset($_SESSION['is_active']) && (int)$_SESSION['is_active'] === 0) {
                self::$cachedCheckResult = false;
                self::$cachedUserId = $userId;
                return false;
            }
        }

        self::$cachedCheckResult = true;
        self::$cachedUserId = $userId;
        return true;
    }

    public static function user(): ?array {
        self::initSession();
        if (!self::check()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'full_name' => $_SESSION['full_name'],
            'department' => $_SESSION['department'] ?? 'CICI',
            'is_active' => $_SESSION['is_active'] ?? 1
        ];
    }

    public static function isDean(): bool {
        self::initSession();
        return self::check() && isset($_SESSION['role']) && $_SESSION['role'] === 'dean';
    }

    public static function isFaculty(): bool {
        self::initSession();
        return self::check() && isset($_SESSION['role']) && $_SESSION['role'] === 'faculty';
    }

    public static function requireLogin(): void {
        self::initSession();
        if (!self::check()) {
            header('Location: index.php?msg=auth_required');
            exit;
        }
    }

    public static function requireDean(): void {
        self::requireLogin();
        if (!self::isDean()) {
            AuditLogger::log('UNAUTHORIZED_ACCESS_ATTEMPT', 'Non-administrative role attempted access to Dean-restricted resource: ' . ($_SERVER['REQUEST_URI'] ?? ''));
            http_response_code(403);
            header('Content-Type: text/html; charset=UTF-8');
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>403 Forbidden | SecureFaculty</title>'
                . '<style>body{font-family:system-ui,-apple-system,sans-serif;background:#081225;color:#e2e8f0;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}'
                . '.card{background:#111c36;border:1px solid #1e293b;border-radius:1rem;padding:2.5rem;max-width:28rem;text-align:center;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);}'
                . 'h1{color:#f43f5e;font-size:1.75rem;margin:0 0 0.5rem 0;}p{color:#94a3b8;font-size:0.875rem;line-height:1.5;margin:0 0 1.5rem 0;}'
                . 'a{display:inline-block;background:#2563eb;color:#fff;text-decoration:none;font-weight:bold;font-size:0.875rem;padding:0.625rem 1.25rem;border-radius:0.5rem;transition:background 0.2s;}'
                . 'a:hover{background:#1d4ed8;}</style></head>'
                . '<body><div class="card"><h1>403 Forbidden</h1>'
                . '<p>Access Denied: Administrative Clearance Required (Dean Role Only). This incident has been recorded in the compliance audit ledger.</p>'
                . '<a href="vault.php">Return to Digital Vault</a></div></body></html>';
            exit;
        }
    }

    public static function logout(): void {
        self::initSession();
        self::$cachedCheckResult = null;
        self::$cachedUserId = null;
        if (isset($_SESSION['user_id'])) {
            AuditLogger::log('LOGOUT', 'User logged out', $_SESSION['user_id']);
        }
        $_SESSION = [];
        if (!headers_sent() && ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }
}
