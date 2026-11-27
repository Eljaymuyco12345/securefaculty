<?php
/**
 * Route Delegator: User Profile Picture Upload / Removal API
 */

require_once __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
AuthService::initSession();

if (!AuthService::check()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized session.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!AuthService::verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'CSRF Security Violation: Invalid or expired token.']);
    exit;
}

$currentUser = AuthService::user();
$userId = (int)$currentUser['id'];
$action = $_POST['action'] ?? 'upload';

if ($action === 'remove') {
    $res = AvatarService::remove($userId);
} else {
    $res = AvatarService::upload($userId, $_FILES['avatar'] ?? []);
}

if (!$res['success']) {
    http_response_code($res['code'] ?? 400);
    echo json_encode(['status' => 'error', 'message' => $res['message']]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => $res['message'],
    'avatar_path' => $res['avatar_path'] ?? null
]);
