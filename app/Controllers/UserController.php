<?php
/**
 * UserController
 * Faculty & Administrative User Management
 * Controller Layer
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Services/UserService.php';

class UserController extends BaseController {
    public function index(): void {
        AuthService::requireDean();
        $currentUser = AuthService::user();

        $users = UserModel::getAllWithProfiles();
        $stats = UserModel::getStats();

        $search = trim($_GET['search'] ?? '');
        $statusFilter = $_GET['status'] ?? 'all';

        if ($search !== '' || $statusFilter !== 'all') {
            $searchLower = strtolower($search);
            $users = array_values(array_filter($users, function($u) use ($searchLower, $statusFilter) {
                $matchStatus = true;
                if ($statusFilter === 'active') {
                    $matchStatus = ((int)$u['is_active'] === 1);
                } elseif ($statusFilter === 'inactive') {
                    $matchStatus = ((int)$u['is_active'] === 0);
                }

                $matchSearch = true;
                if ($searchLower !== '') {
                    $matchSearch = (
                        str_contains(strtolower($u['full_name'] ?? ''), $searchLower) ||
                        str_contains(strtolower($u['username'] ?? ''), $searchLower) ||
                        str_contains(strtolower($u['email'] ?? ''), $searchLower) ||
                        str_contains(strtolower($u['employee_id'] ?? ''), $searchLower) ||
                        str_contains(strtolower($u['academic_rank'] ?? ''), $searchLower) ||
                        str_contains(strtolower($u['specialization'] ?? ''), $searchLower)
                    );
                }

                return $matchStatus && $matchSearch;
            }));
        }

        $this->renderView('users/index.php', [
            'currentUser' => $currentUser,
            'users' => $users,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'totalUsers' => $stats['totalUsers'],
            'activeUsers' => $stats['activeUsers'],
            'deanCount' => $stats['deanCount'],
            'inactiveUsers' => $stats['inactiveUsers'],
            'activeNav' => 'users',
            'pageTitle' => 'Faculty & User Management'
        ]);
    }

    public function handleApi(): void {
        if (!headers_sent()) header('Content-Type: application/json');
        AuthService::initSession();

        if (!AuthService::check()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access Denied: Authentication required.'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!AuthService::verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'CSRF Security Violation: Invalid or expired token.'], 403);
        }

        $action = $input['action'] ?? '';
        $currentUser = AuthService::user();

        if ($action !== 'update_self_name' && $action !== 'switch_profile' && !AuthService::isDean()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Access Denied: Administrative privileges required.'], 403);
        }

        switch ($action) {
            case 'switch_profile':
                $target = trim($input['target_user'] ?? '');
                $validProfiles = [
                    'dean' => 'admin123',
                    'mikhail' => 'faculty123',
                    'raejan' => 'faculty123',
                    'eljay' => 'faculty123'
                ];
                if (!isset($validProfiles[$target])) {
                    $this->jsonResponse(['status' => 'error', 'message' => 'Invalid target profile requested.'], 400);
                }
                $pwd = $validProfiles[$target];
                AuthService::logout();
                if (AuthService::attempt($target, $pwd)) {
                    $redirectUrl = AuthService::isDean() ? 'dashboard.php' : 'vault.php';
                    $this->jsonResponse([
                        'status' => 'success',
                        'redirect' => $redirectUrl,
                        'message' => 'Profile switched successfully to ' . $target
                    ]);
                } else {
                    $this->jsonResponse(['status' => 'error', 'message' => 'Authentication failed during profile switch.'], 401);
                }
                break;

            case 'update_self_name':
                $selfId = (int)($currentUser['id'] ?? 0);
                $newName = trim($input['full_name'] ?? '');
                $res = UserService::updateProfile($selfId, ['full_name' => $newName], $selfId);
                if (!$res['success']) {
                    $this->jsonResponse(['status' => 'error', 'message' => $res['message']], $res['code'] ?? 400);
                }
                $this->jsonResponse(['status' => 'success', 'message' => 'Your display name has been updated.', 'full_name' => $newName]);

            case 'create':
                $res = UserService::create($input);
                if (!$res['success']) {
                    $this->jsonResponse(['status' => 'error', 'message' => $res['message']], $res['code'] ?? 400);
                }
                $this->jsonResponse(['status' => 'success', 'message' => 'User created successfully', 'user_id' => $res['user_id']]);

            case 'toggle_status':
                $userId = (int)($input['user_id'] ?? 0);
                $res = UserService::toggleStatus($userId, (int)$currentUser['id']);
                if (!$res['success']) {
                    $this->jsonResponse(['status' => 'error', 'message' => $res['message']], $res['code'] ?? 400);
                }
                $this->jsonResponse(['status' => 'success', 'is_active' => $res['is_active']]);

            case 'update_profile':
                $userId = (int)($input['user_id'] ?? 0);
                if ($userId <= 0) {
                    $this->jsonResponse(['status' => 'error', 'message' => 'Invalid user ID'], 400);
                }
                $res = UserService::updateProfile($userId, $input, (int)$currentUser['id']);
                if (!$res['success']) {
                    $this->jsonResponse(['status' => 'error', 'message' => $res['message']], $res['code'] ?? 400);
                }
                $this->jsonResponse(['status' => 'success', 'message' => $res['message']]);

            case 'reset_password':
                $userId = (int)($input['user_id'] ?? 0);
                $newPassword = trim($input['new_password'] ?? '');
                $res = UserService::resetPassword($userId, $newPassword);
                if (!$res['success']) {
                    $this->jsonResponse(['status' => 'error', 'message' => $res['message']], $res['code'] ?? 400);
                }
                $this->jsonResponse(['status' => 'success', 'message' => $res['message']]);

            case 'delete':
                $userId = (int)($input['user_id'] ?? 0);
                $res = UserService::delete($userId, (int)$currentUser['id']);
                if (!$res['success']) {
                    $this->jsonResponse(['status' => 'error', 'message' => $res['message']], $res['code'] ?? 400);
                }
                $this->jsonResponse(['status' => 'success', 'message' => $res['message']]);

            default:
                $this->jsonResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
        }
    }
}
