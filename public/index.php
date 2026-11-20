<?php
/**
 * Front Controller Entry Point
 * Routes requests to corresponding MVC Controllers
 */

require_once __DIR__ . '/../app/bootstrap.php';

$route = $_GET['route'] ?? '';
$authController = new AuthController();

switch ($route) {
    case 'login':
        $authController->showLogin();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'dashboard':
        (new DashboardController())->index();
        break;
    case 'vault':
        (new VaultController())->index();
        break;
    case 'upload':
        (new UploadController())->index();
        break;
    case 'audit':
    case 'audit_logs':
        (new AuditController())->index();
        break;
    case 'users':
        (new UserController())->index();
        break;
    case 'export':
        (new ExportController())->index();
        break;
    case 'backup':
        (new DashboardController())->backup();
        break;
    case 'view_document':
        (new DocumentController())->view();
        break;
    default:
        $authController->showLogin();
        break;
}

