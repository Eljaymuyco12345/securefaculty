<?php
/**
 * Route Delegator: Login Action
 * Delegated to AuthController
 */

require_once __DIR__ . '/../app/bootstrap.php';

$auth = new AuthController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->handleLogin();
} else {
    $auth->showLogin();
}
