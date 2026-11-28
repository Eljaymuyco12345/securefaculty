<?php
/**
 * Virtual Folders API Endpoint
 * Supports JSON actions: create, rename, delete, move_certificates
 */

require_once __DIR__ . '/../../app/bootstrap.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

AuthService::initSession();

if (!AuthService::check()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access Denied: Authentication required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody, true);
if (!is_array($input)) {
    $input = $_POST;
}

$csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!AuthService::verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'CSRF Security Violation: Invalid or expired token.']);
    exit;
}

$currentUser = AuthService::user();
$userId = (int)$currentUser['id'];
$isDean = AuthService::isDean();
$action = $input['action'] ?? '';

switch ($action) {
    case 'create':
        $name = (string)($input['name'] ?? '');
        $color = (string)($input['color'] ?? 'blue');
        $result = FolderService::create($userId, $name, $color);
        break;

    case 'rename':
        $folderId = (int)($input['folder_id'] ?? $input['id'] ?? 0);
        $newName = (string)($input['name'] ?? $input['new_name'] ?? '');
        $result = FolderService::rename($folderId, $userId, $newName, $isDean);
        break;

    case 'delete':
        $folderId = (int)($input['folder_id'] ?? $input['id'] ?? 0);
        $result = FolderService::delete($folderId, $userId, $isDean);
        break;

    case 'move_certificates':
        $certIds = $input['certificate_ids'] ?? $input['cert_ids'] ?? [];
        if (!is_array($certIds)) {
            $certIds = [$certIds];
        }
        $rawFolderId = $input['folder_id'] ?? null;
        $folderId = ($rawFolderId === null || $rawFolderId === '' || (int)$rawFolderId === 0)
            ? null
            : (int)$rawFolderId;
        $result = FolderService::moveCertificates($certIds, $folderId, $userId, $isDean);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
        exit;
}

if (empty($result['success'])) {
    http_response_code($result['code'] ?? 400);
    echo json_encode([
        'status' => 'error',
        'message' => $result['message'] ?? 'Operation failed.'
    ]);
    exit;
}

unset($result['success']);
echo json_encode(array_merge(['status' => 'success'], $result));
