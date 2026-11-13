<?php
/**
 * DashboardController
 * Centralized Administrative Oversight & Real-Time Cloud Quota Monitor
 * Controller Layer
 */

require_once __DIR__ . '/BaseController.php';

class DashboardController extends BaseController {
    public function index(): void {
        AuthService::requireDean();
        $currentUser = AuthService::user();

        // Fetch Quota Status & Department Metrics
        $quota = CertificateModel::getQuotaStatus();
        $metrics = CertificateModel::getDepartmentMetrics();

        // Fetch Audit Logs for the central live ledger via AuditLogger Service
        $search = trim($_GET['search'] ?? '');
        if (strlen($search) > 100) {
            $search = substr($search, 0, 100);
        }
        $actionFilter = trim($_GET['action'] ?? '');
        if (strlen($actionFilter) > 50) {
            $actionFilter = substr($actionFilter, 0, 50);
        }
        $page = max(1, min(1000, (int)($_GET['page'] ?? 1)));
        $perPage = 10;

        $logData = AuditLogger::getPaginatedLogs($search, $actionFilter, $page, $perPage);
        $displayRows = $logData['rows'];
        $totalRows = $logData['totalRows'];
        $totalPages = $logData['totalPages'];
        $page = $logData['currentPage'];

        $this->renderView('dashboard/index.php', [
            'currentUser' => $currentUser,
            'quota' => $quota,
            'metrics' => $metrics,
            'displayRows' => $displayRows,
            'totalRows' => $totalRows,
            'totalPages' => $totalPages,
            'page' => $page,
            'search' => $search,
            'actionFilter' => $actionFilter,
            'activeNav' => 'dashboard',
            'pageTitle' => "Dean's Centralized Administrative Dashboard"
        ]);
    }

    /**
     * Plan B: Generate and stream on-demand database backup snapshot
     */
    public function backup(): void {
        AuthService::requireDean();
        $result = BackupService::createBackup();
        if (!$result['success']) {
            $this->redirect('dashboard.php?error=' . urlencode($result['message']));
        }

        $filepath = $result['filepath'];
        $filename = basename($result['filename']);

        if (!file_exists($filepath)) {
            $this->redirect('dashboard.php?error=' . urlencode('Backup file could not be read.'));
        }

        // Clean any pre-existing output buffers to prevent file corruption
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: private, no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
        $this->streamFile($filepath);
        exit;
    }

    /**
     * Stream file content to output
     */
    protected function streamFile(string $filepath): void {
        readfile($filepath);
    }
}
