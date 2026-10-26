<?php
require 'app/bootstrap.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$routes = [
    'dashboard.php',
    'vault.php',
    'vault.php?view=grid',
    'upload.php',
    'export.php',
    'users.php',
    'audit_logs.php',
    'view_document.php?id=1'
];

foreach (['dean' => 'admin123', 'mikhail' => 'faculty123'] as $user => $pass) {
    AuthService::attempt($user, $pass);
    echo "=== Testing user: {$user} ===\n";
    foreach ($routes as $r) {
        if ($user === 'mikhail' && in_array($r, ['users.php', 'audit_logs.php', 'dashboard.php'])) continue;
        $_SERVER['REQUEST_URI'] = '/' . $r;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];
        if (strpos($r, '?') !== false) {
            parse_str(substr($r, strpos($r, '?') + 1), $_GET);
        }
        $file = __DIR__ . '/../public/' . strtok($r, '?');
        ob_start();
        try {
            require $file;
            $html = ob_get_clean();
            if (preg_match('/(Notice|Warning|Deprecated|Fatal error):.*/i', $html, $m)) {
                echo "  [FAIL] {$r} -> {$m[0]}\n";
            } else {
                echo "  [OK]   {$r}\n";
            }
        } catch (Throwable $e) {
            ob_end_clean();
            echo "  [EXCEPTION] {$r} -> " . $e->getMessage() . "\n";
        }
    }
}
