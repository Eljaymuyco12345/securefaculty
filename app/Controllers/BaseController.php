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
}
