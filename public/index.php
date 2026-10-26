<?php
/**
 * Front Controller Entry Point
 * Routes requests to corresponding MVC Controllers
 */

require_once __DIR__ . '/../app/bootstrap.php';

$route = $_GET['route'] ?? '';
$authController = new AuthController();

if ($route === 'login') {
    $authController->showLogin();
} elseif ($route === 'logout') {
    $authController->logout();
} else {
    $authController->showLogin();
}
