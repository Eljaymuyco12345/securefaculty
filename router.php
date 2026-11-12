<?php
/**
 * SecureFaculty Application Router
 * Provides clean URL routing for PHP Built-in Server & Cloud Environments
 * Usage: php -S 127.0.0.1:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);

// Strip project subfolder prefix if present (e.g. in reverse proxies)
if (str_starts_with($uri, '/securefaculty')) {
    $uri = substr($uri, strlen('/securefaculty'));
}

// Strip /public prefix if present
if (str_starts_with($uri, '/public')) {
    $uri = substr($uri, strlen('/public'));
}

// Handle root
if ($uri === '' || $uri === '/') {
    require __DIR__ . '/public/index.php';
    return true;
}

// Target path inside public directory
$publicPath = __DIR__ . '/public' . $uri;

// 1. Direct .php file in public (e.g. /dashboard.php, /vault.php, /backup.php, /login.php)
if (is_file($publicPath) && str_ends_with($publicPath, '.php')) {
    require $publicPath;
    return true;
}

// 2. Direct .php file where request omitted .php (e.g. /dashboard, /vault, /backup)
if (is_file($publicPath . '.php')) {
    require $publicPath . '.php';
    return true;
}

// 3. Static assets located in public directory (css, js, images, favicon.ico, fonts, etc.)
if (is_file($publicPath)) {
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
    ];
    $ext = strtolower(pathinfo($publicPath, PATHINFO_EXTENSION));
    $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
    header("Content-Type: {$mime}");
    readfile($publicPath);
    return true;
}

// 4. Clean route aliases mapping to public controllers
$routes = [
    '/dashboard' => '/public/dashboard.php',
    '/vault' => '/public/vault.php',
    '/upload' => '/public/upload.php',
    '/users' => '/public/users.php',
    '/audit' => '/public/audit_logs.php',
    '/audit_logs' => '/public/audit_logs.php',
    '/export' => '/public/export.php',
    '/login' => '/public/login.php',
    '/logout' => '/public/logout.php',
    '/view_document' => '/public/view_document.php',
    '/backup' => '/public/backup.php',
];

if (isset($routes[$uri])) {
    require __DIR__ . $routes[$uri];
    return true;
}

// 4. API routes (e.g. /api/upload_handler)
if (str_starts_with($uri, '/api/')) {
    $apiFile = __DIR__ . '/public' . $uri;
    if (is_file($apiFile . '.php')) {
        require $apiFile . '.php';
        return true;
    }
    if (is_file($apiFile)) {
        require $apiFile;
        return true;
    }
}

// Default fallback
if (is_file(__DIR__ . '/public' . $uri)) {
    require __DIR__ . '/public' . $uri;
    return true;
}

// 404 Not Found (XSS Sanitized - OWASP A03)
http_response_code(404);
$safeUri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');
echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>404 Not Found | SecureFaculty</title></head><body><h1>404 Not Found</h1><p>The requested route [{$safeUri}] does not exist.</p></body></html>";
return true;
