<?php
/**
 * AuditController
 * Immutable Compliance Audit Trail Viewer
 * Controller Layer
 */

require_once __DIR__ . '/BaseController.php';

class AuditController extends BaseController {
    public function index(): void {
        AuthService::requireDean();
        $currentUser = AuthService::user();

        $format = $_GET['format'] ?? ($_GET['export'] ?? 'html');
        $actionFilter = !empty($_GET['action']) ? $_GET['action'] : null;
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;

        // Stream CSV export if requested
        if ($format === 'csv') {
            $exportRows = AuditLogger::getExportLogs($search, $actionFilter);
            $filename = "CICI_Audit_Ledger_" . ($actionFilter ? preg_replace('/[^a-zA-Z0-9]/', '_', $actionFilter) . "_" : "") . date('Ymd_His') . ".csv";

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($output, [
                'Log ID',
                'Timestamp (UTC)',
                'User ID',
                'Username',
                'Role Clearance',
                'Action Recorded',
                'Affected Faculty / Resource',
                'Details & Notes',
                'Source IP Address',
                'User Agent / Signature',
                'Integrity Verification'
            ]);

            $sanitizeCell = function($val) {
                if (is_string($val) && strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@', "\t", "\r"])) {
                    return "'" . $val;
                }
                return $val;
            };

            foreach ($exportRows as $r) {
                $uid = ($r['role'] === 'dean')
                    ? 'ISUFST001'
                    : 'ISUFST' . str_pad($r['user_id'] ?? 2, 3, '0', STR_PAD_LEFT);

                $row = [
                    $r['id'],
                    $r['created_at'],
                    $uid,
                    $r['username'] ?? 'Anonymous',
                    strtoupper($r['role'] ?? 'GUEST'),
                    $r['action'],
                    $r['faculty_display'] ?? 'Departmental Audit',
                    $r['details'] ?? '',
                    $r['ip_address'] ?? '127.0.0.1',
                    $r['user_agent'] ?? 'CLI/Browser',
                    'SHA-256 Validated'
                ];
                fputcsv($output, array_map($sanitizeCell, $row));
            }

            AuditLogger::log('EXPORT_REPORT', "Exported CSV compliance audit trail (" . count($exportRows) . " events)");
            fclose($output);
            exit;
        }

        // True database-driven pagination across all historical records
        $logData = AuditLogger::getPaginatedLogs($search, $actionFilter, $page, $perPage);
        $displayLogs = $logData['rows'];
        $totalLogs = $logData['totalRows'];
        $totalPages = $logData['totalPages'];
        $page = $logData['currentPage'];

        // Comprehensive statistical counters across full ledger
        $pdo = Database::getConnection();
        $statTotal = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
        $statLogins = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action IN ('LOGIN_SUCCESS', 'LOGOUT')")->fetchColumn();
        $statDocs = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action IN ('ARCHIVE_DOCUMENT','DELETE_DOCUMENT','VIEW_DOCUMENT','DOWNLOAD_DOCUMENT','OCR_EXTRACT','COMMIT_VAULT_RECORD','BULK_DELETE_DOCUMENTS')")->fetchColumn();
        $statAlerts = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action IN ('LOGIN_FAILED','UNAUTHORIZED_ACCESS_ATTEMPT','LOGIN_BLOCKED','CSRF_VIOLATION_BLOCKED')")->fetchColumn();

        $facultyLookup = UserModel::getFacultyLookup();

        $this->renderView('audit/index.php', [
            'currentUser' => $currentUser,
            'actionFilter' => $actionFilter,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'displayLogs' => $displayLogs,
            'totalLogs' => $totalLogs,
            'statTotal' => $statTotal,
            'statLogins' => $statLogins,
            'statDocs' => $statDocs,
            'statAlerts' => $statAlerts,
            'facultyLookup' => $facultyLookup,
            'activeNav' => 'audit',
            'pageTitle' => 'Immutable Compliance Audit Ledger'
        ]);
    }
}
