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
        $search = $_GET['search'] ?? '';
        $actionFilter = $_GET['action'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
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
}
