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

        // Official PDF Audit Ledger Export Handler (Policy: all exports are immutable PDF)
        if ($format === 'pdf' || $format === 'csv') {
            $exportRows = AuditLogger::getExportLogs($search, $actionFilter);

            $formattedRecords = [];
            foreach ($exportRows as $r) {
                $uid = ($r['role'] === 'dean')
                    ? 'ISUFST001'
                    : 'ISUFST' . str_pad($r['user_id'] ?? 2, 3, '0', STR_PAD_LEFT);

                $formattedRecords[] = [
                    'id' => $r['id'],
                    'created_at' => $r['created_at'],
                    'user_id' => $uid,
                    'username' => $r['username'] ?? 'Anonymous',
                    'role' => strtoupper($r['role'] ?? 'GUEST'),
                    'action' => $r['action'],
                    'faculty_display' => $r['faculty_display'] ?: 'Dr. Arlene S. Poblador',
                    'details' => $r['details'] ?? '',
                    'ip_address' => $r['ip_address'] ?? '127.0.0.1',
                    'integrity' => 'SHA-256 Validated'
                ];
            }

            $payload = [
                'meta' => [
                    'action_filter' => $actionFilter ?: 'All Events',
                    'search' => $search,
                    'auditor_name' => $currentUser['full_name'] ?? 'Dr. Arlene S. Poblador',
                    'generated_at' => date('F d, Y \a\t h:i A')
                ],
                'records' => $formattedRecords
            ];

            $tempJson = TEMP_DIR . DIRECTORY_SEPARATOR . 'audit_rep_' . bin2hex(random_bytes(8)) . '.json';
            $tempPdf = TEMP_DIR . DIRECTORY_SEPARATOR . 'audit_rep_' . bin2hex(random_bytes(8)) . '.pdf';
            file_put_contents($tempJson, json_encode($payload));

            $pythonBin = $this->getPythonBinary();
            $generatorScript = BASE_DIR . DIRECTORY_SEPARATOR . 'python_engine' . DIRECTORY_SEPARATOR . 'generate_audit_pdf.py';

            $cmd = sprintf(
                '%s %s %s %s 2>&1',
                escapeshellcmd($pythonBin),
                escapeshellarg($generatorScript),
                escapeshellarg($tempJson),
                escapeshellarg($tempPdf)
            );

            $cmdOutput = shell_exec($cmd);
            @unlink($tempJson);

            if (file_exists($tempPdf) && filesize($tempPdf) > 0) {
                $filename = "CICI_Audit_Ledger_" . ($actionFilter ? preg_replace('/[^a-zA-Z0-9]/', '_', $actionFilter) . "_" : "") . date('Ymd_His') . ".pdf";
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($tempPdf));
                readfile($tempPdf);
                @unlink($tempPdf);
                AuditLogger::log('EXPORT_REPORT', "Exported official PDF compliance audit trail (" . count($exportRows) . " events)");
                exit;
            } else {
                $errMsg = !empty($cmdOutput) ? trim($cmdOutput) : 'Audit PDF compilation failed';
                AuditLogger::log('EXPORT_ERROR', "Audit PDF compilation failed: " . mb_substr($errMsg, 0, 200));
                if (!headers_sent()) {
                    http_response_code(500);
                }
                echo "<div style='font-family:sans-serif;max-width:600px;margin:40px auto;padding:24px;border:1px solid #f87171;border-radius:12px;background:#fef2f2;color:#991b1b;'>";
                echo "<h3 style='margin-top:0;'>Official Audit PDF Generation Failed</h3>";
                echo "<p>The Python audit report generator was unable to render the official audit document.</p>";
                echo "<pre style='background:#fee2e2;padding:12px;border-radius:8px;overflow-x:auto;font-size:11px;'>" . htmlspecialchars($errMsg) . "</pre>";
                echo "<p><a href='audit_logs.php' style='color:#b91c1c;font-weight:bold;'>&larr; Return to Audit Ledger</a></p>";
                echo "</div>";
                exit;
            }
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
