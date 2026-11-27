<?php
/**
 * Route Delegator: Session Keep-Alive Ping API
 * Refreshes server-side last_activity when user clicks "Stay Signed In".
 */

require_once __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
AuthService::initSession();

if (!AuthService::check()) {
    http_response_code(401);
    echo json_encode(['status' => 'expired', 'message' => 'Session expired due to inactivity.']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'timeout_seconds' => AuthService::getTimeoutSeconds()
]);
