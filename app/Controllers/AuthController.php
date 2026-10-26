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

        $error = $_GET['error'] ?? null;
        $msg = $_GET['msg'] ?? null;
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
