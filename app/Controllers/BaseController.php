<?php
/**
 * BaseController
 * Abstract foundation for all MVC Controllers
 * Controller Layer
 */

abstract class BaseController {
    /**
     * Render a view file with data extracted into local scope
     */
    protected function renderView(string $viewFile, array $data = []): void {
        extract($data);
        $fullPath = __DIR__ . '/../Views/' . $viewFile;
        if (!file_exists($fullPath)) {
            // Fallback for root or legacy view path
            $fullPath = __DIR__ . '/../views/' . $viewFile;
        }

        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            if (!headers_sent()) {
                http_response_code(500);
            }
            echo "View file not found: " . htmlspecialchars($viewFile);
        }
    }

    /**
     * Return JSON response
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Resolves the working Python executable binary across Windows, Linux, and macOS environments.
     */
    public function getPythonBinary(): string {
        static $resolved = null;
        if ($resolved !== null) {
            return $resolved;
        }

        $candidates = [];
        if (defined('PYTHON_BIN') && PYTHON_BIN) {
            $candidates[] = PYTHON_BIN;
        }
        if (PHP_OS_FAMILY === 'Windows') {
            $candidates[] = 'python';
            $candidates[] = 'py';
            $candidates[] = 'python3';
        } else {
            $candidates[] = 'python3';
            $candidates[] = 'python';
        }

        foreach ($candidates as $cand) {
            $testCmd = escapeshellcmd($cand) . ' --version 2>&1';
            $output = @shell_exec($testCmd);
            if ($output && stripos($output, 'Python') !== false && stripos($output, 'not found') === false) {
                $resolved = $cand;
                return $resolved;
            }
        }

        $resolved = (PHP_OS_FAMILY === 'Windows') ? 'python' : 'python3';
        return $resolved;
    }
}
