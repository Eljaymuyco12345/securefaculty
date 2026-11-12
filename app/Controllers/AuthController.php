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
        AuthService::logout();
        $this->redirect('index.php?msg=logged_out');
    }
}
