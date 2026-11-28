<?php
/**
 * AuthController
 * Manages user authentication, session initiation, and sign out
 * Controller Layer
 */

require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController {
    /**
     * Show login portal
     */
    public function showLogin(): void {
        AuthService::initSession();
        if (AuthService::check()) {
            if (AuthService::isDean()) {
                $this->redirect('dashboard.php');
            } else {
                $this->redirect('vault.php');
            }
        }

        $rawError = $_GET['error'] ?? null;
        $rawMsg = $_GET['msg'] ?? null;
        $error = $rawError !== null ? htmlspecialchars(strip_tags((string)$rawError), ENT_QUOTES, 'UTF-8') : null;
        $msg = $rawMsg !== null ? htmlspecialchars(strip_tags((string)$rawMsg), ENT_QUOTES, 'UTF-8') : null;
        $this->renderView('auth/login.php', [
            'error' => $error,
            'msg' => $msg
        ]);
    }

    /**
     * Handle login POST request
     */
    public function handleLogin(): void {
        AuthService::initSession();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php');
        }

        // CSRF Verification (OWASP A01 / A07)
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!AuthService::verifyCsrfToken($csrfToken)) {
            $this->redirect('index.php?error=csrf_violation');
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $this->redirect('index.php?error=empty_fields');
        }

        if (AuthService::attempt($username, $password)) {
            if (AuthService::isDean()) {
                $this->redirect('dashboard.php');
            } else {
                $this->redirect('vault.php');
            }
        } else {
            $err = $_SESSION['login_error'] ?? 'invalid_credentials';
            unset($_SESSION['login_error']);
            if ($err === 'rate_limited') {
                if (!headers_sent()) {
                    http_response_code(429);
                    header('Retry-After: 300');
                }
                $this->renderView('auth/login.php', [
                    'error' => 'rate_limited',
                    'msg' => null
                ]);
                return;
            }
            $this->redirect('index.php?error=' . urlencode($err));
        }
    }

    /**
     * Terminate user session
     */
    public function logout(): void {
        $reason = $_GET['reason'] ?? '';
        AuthService::initSession();
        if ($reason === 'timeout' && isset($_SESSION['user_id'])) {
            $mins = max(1, (int)round(AuthService::getTimeoutSeconds() / 60));
            AuditLogger::log('SESSION_TIMEOUT', "User session automatically signed out after {$mins} minutes of inactivity", (int)$_SESSION['user_id']);
        }
        AuthService::logout();
        $msg = ($reason === 'timeout') ? 'session_expired' : 'logged_out';
        $this->redirect('index.php?msg=' . $msg);
    }

    /**
     * Handle authenticated user Change Password API
     */
    public function handleChangePasswordApi(): void {
        if (!headers_sent()) header('Content-Type: application/json');
        AuthService::initSession();

        if (!AuthService::check()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized session.'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!AuthService::verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'CSRF Security Violation: Invalid or expired token.'], 403);
        }

        $currentUser = AuthService::user();
        $res = PasswordResetService::changePassword(
            (int)$currentUser['id'],
            (string)($input['current_password'] ?? ''),
            (string)($input['new_password'] ?? ''),
            (string)($input['confirm_password'] ?? '')
        );

        if (!$res['success']) {
            $this->jsonResponse(['status' => 'error', 'message' => $res['message']], $res['code'] ?? 400);
        }

        $this->jsonResponse(['status' => 'success', 'message' => $res['message']]);
    }

    /**
     * Show Forgot Password (6-Digit OTP) page
     */
    public function showForgotPassword(): void {
        AuthService::initSession();
        if (AuthService::check()) {
            $this->redirect(AuthService::isDean() ? 'dashboard.php' : 'vault.php');
        }
        $this->renderView('auth/forgot_password.php', [
            'smtpConfigured' => EmailService::isConfigured()
        ]);
    }

    /**
     * Handle Forgot Password OTP request & verification API
     */
    public function handleForgotPasswordApi(): void {
        if (!headers_sent()) header('Content-Type: application/json');
        AuthService::initSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!AuthService::verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'CSRF Security Violation: Invalid or expired token.'], 403);
        }

        $action = $input['action'] ?? '';
        if ($action === 'request_otp') {
            $res = PasswordResetService::requestOtp((string)($input['identifier'] ?? ''));
        } elseif ($action === 'verify_reset') {
            $res = PasswordResetService::verifyOtpAndReset(
                (string)($input['identifier'] ?? ''),
                (string)($input['otp'] ?? ''),
                (string)($input['new_password'] ?? ''),
                (string)($input['confirm_password'] ?? '')
            );
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid action.'], 400);
            return;
        }

        if (!$res['success']) {
            $this->jsonResponse(['status' => 'error', 'message' => $res['message']], $res['code'] ?? 400);
        }

        $this->jsonResponse(['status' => 'success', 'message' => $res['message']]);
    }
}
